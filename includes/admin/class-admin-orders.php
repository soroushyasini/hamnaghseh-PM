<?php
if (!defined('ABSPATH'))
    exit;

class Hamnaghsheh_Admin_Orders
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_hamnaghsheh_admin_set_quote', array($this, 'ajax_set_quote'));
        add_action('wp_ajax_hamnaghsheh_admin_update_status', array($this, 'ajax_update_status'));
        add_action('wp_ajax_hamnaghsheh_admin_send_message', array($this, 'ajax_send_message'));
        add_action('wp_ajax_hamnaghsheh_admin_create_project', array($this, 'ajax_create_project'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu()
    {
        add_menu_page(
            'سفارش‌ها',
            'سفارش‌ها',
            'manage_options',
            'hamnaghsheh-orders',
            array($this, 'render_orders_list'),
            'dashicons-cart',
            26
        );

        add_submenu_page(
            'hamnaghsheh-orders',
            'همه سفارش‌ها',
            'همه سفارش‌ها',
            'manage_options',
            'hamnaghsheh-orders',
            array($this, 'render_orders_list')
        );

        add_submenu_page(
            'hamnaghsheh-orders',
            'تنظیمات خدمات',
            'تنظیمات خدمات',
            'manage_options',
            'hamnaghsheh-services',
            array('Hamnaghsheh_Admin_Services', 'render_services_page')
        );

        // Hidden submenu for single order
        add_submenu_page(
            null,
            'جزئیات سفارش',
            'جزئیات سفارش',
            'manage_options',
            'hamnaghsheh-order-detail',
            array($this, 'render_order_detail')
        );
    }

    /**
     * Render orders list page
     */
    public function render_orders_list()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_orders';

        // Get filter params
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $service_filter = isset($_GET['service_type']) ? sanitize_text_field($_GET['service_type']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        // Build query
        $where = array('1=1');
        
        if ($status_filter) {
            $where[] = $wpdb->prepare('status = %s', $status_filter);
        }
        
        if ($service_filter) {
            $where[] = $wpdb->prepare('service_type = %s', $service_filter);
        }
        
        if ($search) {
            $where[] = $wpdb->prepare('(order_number LIKE %s OR user_id IN (SELECT ID FROM ' . $wpdb->users . ' WHERE display_name LIKE %s))', '%' . $search . '%', '%' . $search . '%');
        }

        $where_clause = implode(' AND ', $where);
        
        $orders = $wpdb->get_results("SELECT * FROM {$table} WHERE {$where_clause} ORDER BY created_at DESC");

        include HAMNAGHSHEH_DIR . 'templates/admin/orders-list.php';
    }

    /**
     * Render order detail page
     */
    public function render_order_detail()
    {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $order = Hamnaghsheh_Orders::get_order_by_id($order_id);

        if (!$order) {
            echo '<div class="wrap"><h1>سفارش یافت نشد</h1></div>';
            return;
        }

        $user = get_userdata($order->user_id);
        $service = Hamnaghsheh_Services::get_service_by_key($order->service_type);
        $messages = Hamnaghsheh_Order_Messages::get_order_messages($order_id);
        $activity = Hamnaghsheh_Order_Activity::get_order_activity($order_id);
        $services = Hamnaghsheh_Services::get_active_services();

        // Mark admin messages as read
        Hamnaghsheh_Order_Messages::mark_as_read($order_id, true);

        include HAMNAGHSHEH_DIR . 'templates/admin/order-detail.php';
    }

    /**
     * AJAX: Set quote
     */
    public function ajax_set_quote()
    {
        check_ajax_referer('hamnaghsheh_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'دسترسی غیرمجاز.'));
        }

        $order_id = intval($_POST['order_id']);
        $order = Hamnaghsheh_Orders::get_order_by_id($order_id);

        if (!$order) {
            wp_send_json_error(array('message' => 'سفارش یافت نشد.'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_orders';

        $update_data = array(
            'admin_estimated_service_type' => sanitize_text_field($_POST['service_type']),
            'admin_estimated_quantity' => intval($_POST['quantity']),
            'admin_estimated_price_per_session' => floatval($_POST['price_per_session']),
            'admin_estimated_total_price' => floatval($_POST['total_price']),
            'admin_notes' => sanitize_textarea_field($_POST['admin_notes']),
            'status' => 'quoted'
        );

        $result = $wpdb->update($table, $update_data, array('id' => $order_id));

        if ($result !== false) {
            // Log activity
            Hamnaghsheh_Order_Activity::log_activity(
                $order_id,
                'price_set',
                '',
                '',
                'برآورد جدید ارسال شد',
                get_current_user_id(),
                true
            );

            // Send notification
            do_action('hamnaghsheh_quote_received', $order_id);

            wp_send_json_success(array('message' => 'برآورد با موفقیت ارسال شد.'));
        } else {
            wp_send_json_error(array('message' => 'خطا در ارسال برآورد.'));
        }
    }

    /**
     * AJAX: Update status
     */
    public function ajax_update_status()
    {
        check_ajax_referer('hamnaghsheh_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'دسترسی غیرمجاز.'));
        }

        $order_id = intval($_POST['order_id']);
        $new_status = sanitize_text_field($_POST['status']);

        $result = Hamnaghsheh_Orders::update_status($order_id, $new_status, true);

        if ($result) {
            // Send appropriate notifications
            if ($new_status == 'paid') {
                do_action('hamnaghsheh_payment_confirmed', $order_id);
            } elseif ($new_status == 'completed') {
                do_action('hamnaghsheh_order_completed', $order_id);
            }

            wp_send_json_success(array('message' => 'وضعیت بروزرسانی شد.'));
        } else {
            wp_send_json_error(array('message' => 'خطا در بروزرسانی وضعیت.'));
        }
    }

    /**
     * AJAX: Send message
     */
    public function ajax_send_message()
    {
        check_ajax_referer('hamnaghsheh_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'دسترسی غیرمجاز.'));
        }

        $order_id = intval($_POST['order_id']);
        $message = sanitize_textarea_field($_POST['message']);

        if (empty($message)) {
            wp_send_json_error(array('message' => 'پیام نمی‌تواند خالی باشد.'));
        }

        $result = Hamnaghsheh_Order_Messages::add_message($order_id, $message, true);

        if ($result) {
            wp_send_json_success(array('message' => 'پیام ارسال شد.'));
        } else {
            wp_send_json_error(array('message' => 'خطا در ارسال پیام.'));
        }
    }

    /**
     * AJAX: Create project for order
     */
    public function ajax_create_project()
    {
        check_ajax_referer('hamnaghsheh_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'دسترسی غیرمجاز.'));
        }

        $order_id = intval($_POST['order_id']);
        $order = Hamnaghsheh_Orders::get_order_by_id($order_id);

        if (!$order) {
            wp_send_json_error(array('message' => 'سفارش یافت نشد.'));
        }

        if ($order->project_id) {
            wp_send_json_error(array('message' => 'پروژه قبلاً ایجاد شده است.'));
        }

        // Get service name
        $service = Hamnaghsheh_Services::get_service_by_key($order->service_type);
        $service_name = $service ? $service->service_name_fa : 'نقشه‌برداری';
        
        // Create project
        global $wpdb;
        $projects_table = $wpdb->prefix . 'hamnaghsheh_projects';
        
        $project_name = 'سفارش #' . $order->order_number . ' - ' . $service_name;
        
        $result = $wpdb->insert($projects_table, array(
            'user_id' => $order->user_id,
            'name' => $project_name,
            'description' => 'پروژه ایجاد شده از سفارش ' . $order->order_number,
            'type' => 'residential',
            'status' => 'active',
            'created_at' => current_time('mysql')
        ));

        if ($result) {
            $project_id = $wpdb->insert_id;
            
            // Link project to order
            $orders_table = $wpdb->prefix . 'hamnaghsheh_orders';
            $wpdb->update(
                $orders_table,
                array('project_id' => $project_id, 'status' => 'in_progress'),
                array('id' => $order_id)
            );

            // Log activity
            Hamnaghsheh_Order_Activity::log_activity(
                $order_id,
                'project_created',
                '',
                $project_id,
                'پروژه ایجاد شد',
                get_current_user_id(),
                true
            );

            // Send notification
            do_action('hamnaghsheh_project_created', $order_id);

            wp_send_json_success(array(
                'message' => 'پروژه با موفقیت ایجاد شد.',
                'project_id' => $project_id
            ));
        } else {
            wp_send_json_error(array('message' => 'خطا در ایجاد پروژه.'));
        }
    }
}
