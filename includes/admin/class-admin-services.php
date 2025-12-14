<?php
if (!defined('ABSPATH'))
    exit;

class Hamnaghsheh_Admin_Services
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_hamnaghsheh_admin_save_service', array($this, 'ajax_save_service'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu()
    {
        add_submenu_page(
            null, // Hidden from menu, accessed via settings
            'مدیریت خدمات',
            'خدمات',
            'manage_options',
            'hamnaghsheh-services',
            array($this, 'render_services_page')
        );
    }

    /**
     * Render services management page
     */
    public function render_services_page()
    {
        $services = Hamnaghsheh_Services::get_active_services();
        
        include HAMNAGHSHEH_DIR . 'templates/admin/services-settings.php';
    }

    /**
     * Static callback for admin menu
     */
    public static function render_services_page_callback()
    {
        $services = Hamnaghsheh_Services::get_active_services();
        
        include HAMNAGHSHEH_DIR . 'templates/admin/services-settings.php';
    }

    /**
     * AJAX: Save service
     */
    public function ajax_save_service()
    {
        check_ajax_referer('hamnaghsheh_ajax_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'دسترسی غیرمجاز.'));
        }

        $service_id = intval($_POST['service_id']);
        
        $data = array(
            'service_name_fa' => sanitize_text_field($_POST['service_name_fa']),
            'price_per_session' => floatval($_POST['price_per_session']),
            'description' => sanitize_textarea_field($_POST['description']),
            'is_active' => intval($_POST['is_active'])
        );

        if (!empty($_POST['image_url'])) {
            $data['image_url'] = esc_url_raw($_POST['image_url']);
        }

        $result = Hamnaghsheh_Services::update_service($service_id, $data);

        if ($result !== false) {
            wp_send_json_success(array('message' => 'خدمات بروزرسانی شد.'));
        } else {
            wp_send_json_error(array('message' => 'خطا در بروزرسانی خدمات.'));
        }
    }
}
