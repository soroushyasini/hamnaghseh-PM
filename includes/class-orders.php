<?php
if (!defined('ABSPATH'))
    exit;

class Hamnaghsheh_Orders
{
    public function __construct()
    {
        add_shortcode('hamnaghsheh_order_form', array($this, 'render_order_form'));
        add_shortcode('hamnaghsheh_my_orders', array($this, 'render_my_orders'));
        add_shortcode('hamnaghsheh_order_detail', array($this, 'render_order_detail'));

        // AJAX endpoints - SIMPLIFIED VERSION
        add_action('wp_ajax_hamnaghsheh_submit_order', array($this, 'ajax_submit_order'));
        // REMOVED: quote acceptance, messaging, order editing - no longer needed in simplified version
    }

    /**
     * Render order form shortcode
     */
    public function render_order_form()
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $services = Hamnaghsheh_Services::get_active_services();

        ob_start();
        include HAMNAGHSHEH_DIR . 'templates/order-form.php';
        return ob_get_clean();
    }

    /**
     * Render my orders list shortcode
     */
    public function render_my_orders()
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $user_id = get_current_user_id();
        $orders = self::get_user_orders($user_id);

        ob_start();
        include HAMNAGHSHEH_DIR . 'templates/my-orders.php';
        return ob_get_clean();
    }

    /**
     * Render order detail shortcode
     * SIMPLIFIED VERSION: No messaging, just order details and activity
     */
    public function render_order_detail()
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $order = self::get_order_by_id($order_id);

        if (!$order || $order->user_id != get_current_user_id()) {
            return '<p>سفارش یافت نشد.</p>';
        }

        // REMOVED: messages - no longer needed in simplified version
        $activity = Hamnaghsheh_Order_Activity::get_order_activity($order_id);
        $service = Hamnaghsheh_Services::get_service_by_key($order->service_type);

        ob_start();
        include HAMNAGHSHEH_DIR . 'templates/order-detail.php';
        return ob_get_clean();
    }

    /**
     * Generate order number
     */
    public static function generate_order_number()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_orders';
        
        $year_month = date('Ym');
        $prefix = 'HN-' . $year_month . '-';
        
        $last_order = $wpdb->get_var($wpdb->prepare(
            "SELECT order_number FROM {$table} WHERE order_number LIKE %s ORDER BY id DESC LIMIT 1",
            $prefix . '%'
        ));

        if ($last_order) {
            $last_num = intval(str_replace($prefix, '', $last_order));
            $new_num = $last_num + 1;
        } else {
            $new_num = 1;
        }

        return $prefix . str_pad($new_num, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create order
     */
    public static function create_order($data)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_orders';

        $order_number = self::generate_order_number();

        $insert_data = array(
            'user_id' => get_current_user_id(),
            'order_number' => $order_number,
            'service_type' => sanitize_text_field($data['service_type']),
            'requested_quantity' => intval($data['quantity']),
            'requested_price_per_session' => floatval($data['price_per_session']),
            'requested_total_price' => floatval($data['total_price']),
            'address' => sanitize_textarea_field($data['address']),
            'area_size' => sanitize_text_field($data['area_size']),
            'phone' => sanitize_text_field($data['phone']),
            'special_requirements' => sanitize_textarea_field($data['special_requirements']),
            'uploaded_files' => isset($data['uploaded_files']) ? sanitize_text_field($data['uploaded_files']) : '',
            'status' => 'pending',
            'created_at' => current_time('mysql')
        );

        $result = $wpdb->insert($table, $insert_data);

        if ($result) {
            $order_id = $wpdb->insert_id;
            
            // Log activity
            Hamnaghsheh_Order_Activity::log_activity($order_id, 'status_changed', '', 'pending', 'سفارش ثبت شد');

            // Send notification (hook for email)
            do_action('hamnaghsheh_order_submitted', $order_id);
            do_action('hamnaghsheh_new_order', $order_id);

            return $order_id;
        }

        return false;
    }

    /**
     * Get user orders
     */
    public static function get_user_orders($user_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_orders';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ));
    }

    /**
     * Get order by ID
     */
    public static function get_order_by_id($order_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_orders';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $order_id
        ));
    }

    /**
     * Update order status
     */
    public static function update_status($order_id, $new_status, $is_admin = false)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_orders';

        $order = self::get_order_by_id($order_id);
        if (!$order) {
            return false;
        }

        $old_status = $order->status;

        $result = $wpdb->update(
            $table,
            array('status' => $new_status),
            array('id' => $order_id)
        );

        if ($result !== false) {
            // Log activity
            Hamnaghsheh_Order_Activity::log_activity(
                $order_id,
                'status_changed',
                $old_status,
                $new_status,
                'وضعیت سفارش تغییر کرد',
                $is_admin ? get_current_user_id() : $order->user_id,
                $is_admin
            );

            return true;
        }

        return false;
    }

    /**
     * AJAX: Submit order
     */
    public function ajax_submit_order()
    {
        check_ajax_referer('hamnaghsheh_ajax_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'لطفاً وارد شوید.'));
        }

        $required_fields = array('service_type', 'quantity', 'price_per_session', 'total_price', 'address', 'area_size', 'phone');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(array('message' => 'لطفاً تمام فیلدهای الزامی را پر کنید.'));
            }
        }

        $order_id = self::create_order($_POST);

        if ($order_id) {
            wp_send_json_success(array(
                'message' => 'سفارش شما با موفقیت ثبت شد.',
                'order_id' => $order_id,
                'redirect' => site_url('/my-orders/')
            ));
        } else {
            wp_send_json_error(array('message' => 'خطا در ثبت سفارش. لطفاً دوباره تلاش کنید.'));
        }
    }

    // REMOVED: ajax_accept_quote - No longer needed in simplified version
    // REMOVED: ajax_send_message - No messaging in simplified version
    // REMOVED: ajax_edit_order - No order editing in simplified version
    // REMOVED: ajax_cancel_order - Removed for users, admin can cancel
    // REMOVED: ajax_mark_messages_read - No messaging in simplified version

    /**
     * Get status label in Persian
     * SIMPLIFIED VERSION: Only 6 statuses
     */
    public static function get_status_label($status)
    {
        $labels = array(
            'pending' => 'در انتظار بررسی',
            'awaiting_payment' => 'آماده پرداخت',
            'paid' => 'پرداخت شده',
            'in_progress' => 'در حال انجام',
            'completed' => 'تکمیل شده',
            'cancelled' => 'لغو شده'
        );

        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    /**
     * Get status badge class
     * SIMPLIFIED VERSION: Only 6 statuses with updated colors
     */
    public static function get_status_badge_class($status)
    {
        $classes = array(
            'pending' => 'bg-gray-500 text-white',
            'awaiting_payment' => 'bg-yellow-500 text-white',
            'paid' => 'bg-green-600 text-white',
            'in_progress' => 'bg-blue-600 text-white',
            'completed' => 'bg-green-800 text-white',
            'cancelled' => 'bg-red-600 text-white'
        );

        return isset($classes[$status]) ? $classes[$status] : 'bg-gray-500 text-white';
    }
}
