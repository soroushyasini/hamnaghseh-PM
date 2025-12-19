<?php
if (!defined('ABSPATH'))
    exit;

class Hamnaghsheh_Admin_Orders
{
    public function __construct()
    {
        // Only users with view_all_orders can access order admin pages
        if (!current_user_can('view_all_orders')) {
            return;
        }
        
        add_action('admin_menu', array($this, 'add_admin_menu'));
        // SIMPLIFIED VERSION: Remove quote and messaging, add simple price setter
        add_action('wp_ajax_hamnaghsheh_admin_set_price', array($this, 'ajax_set_price'));
        add_action('wp_ajax_hamnaghsheh_admin_update_status', array($this, 'ajax_update_status'));
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
            array('Hamnaghsheh_Admin_Services', 'render_services_page_callback')
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
     * SIMPLIFIED VERSION: No messaging
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
        // REMOVED: messages - no longer needed in simplified version
        $activity = Hamnaghsheh_Order_Activity::get_order_activity($order_id);
        $services = Hamnaghsheh_Services::get_active_services();

        include HAMNAGHSHEH_DIR . 'templates/admin/order-detail.php';
    }

    /**
     * AJAX: Set final price (SIMPLIFIED VERSION)
     * After phone discussion, admin sets final price and status
     */
    public function ajax_set_price()
    {
        check_ajax_referer('hamnaghsheh_admin_nonce', 'nonce');

        // Check capability instead of hardcoded role
        if (!current_user_can('set_order_prices')) {
            wp_send_json_error(array('message' => 'شما دسترسی لازم برای این عملیات را ندارید'));
        }

        $order_id = intval($_POST['order_id']);
        $order = Hamnaghsheh_Orders::get_order_by_id($order_id);

        if (!$order) {
            wp_send_json_error(array('message' => 'سفارش یافت نشد.'));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_orders';

        $final_price = isset($_POST['final_price']) ? floatval($_POST['final_price']) : null;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : $order->status;
        $admin_notes = isset($_POST['admin_notes']) ? sanitize_textarea_field($_POST['admin_notes']) : '';

        $update_data = array(
            'admin_notes' => $admin_notes,
            'status' => $status
        );

        if ($final_price !== null && $final_price > 0) {
            $update_data['final_price'] = $final_price;
        }

        $result = $wpdb->update($table, $update_data, array('id' => $order_id));

        if ($result !== false) {
            // Log activity
            Hamnaghsheh_Order_Activity::log_activity(
                $order_id,
                'price_set',
                '',
                $final_price,
                'قیمت نهایی تنظیم شد',
                get_current_user_id(),
                true
            );

            // Send notification if price was set and status is awaiting_payment
            if ($status == 'awaiting_payment' && $final_price > 0) {
                do_action('hamnaghsheh_price_set', $order_id);
            }

            wp_send_json_success(array('message' => 'تغییرات با موفقیت ذخیره شد.'));
        } else {
            wp_send_json_error(array('message' => 'خطا در ذخیره تغییرات.'));
        }
    }

    /**
     * AJAX: Update status (SIMPLIFIED VERSION)
     */
    public function ajax_update_status()
    {
        check_ajax_referer('hamnaghsheh_admin_nonce', 'nonce');

        // Check capability
        if (!current_user_can('manage_orders')) {
            wp_send_json_error(array('message' => 'شما دسترسی لازم برای این عملیات را ندارید'));
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

    // REMOVED: ajax_send_message - No messaging in simplified version

    /**
     * AJAX: Create project for order
     */
    public function ajax_create_project()
    {
        check_ajax_referer('hamnaghsheh_admin_nonce', 'nonce');

        // Check capability
        if (!current_user_can('manage_projects')) {
            wp_send_json_error(array('message' => 'شما دسترسی لازم برای ایجاد پروژه را ندارید'));
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
