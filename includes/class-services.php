<?php
if (!defined('ABSPATH'))
    exit;

class Hamnaghsheh_Services
{
    public function __construct()
    {
        add_shortcode('hamnaghsheh_services', array($this, 'render_services_page'));
    }

    /**
     * Render services page shortcode
     */
    public static function render_services_page()
    {
        if (!is_user_logged_in()) {
            return '';
        }

        $services = self::get_active_services();

        ob_start();
        include HAMNAGHSHEH_DIR . 'templates/services-page.php';
        return ob_get_clean();
    }

    /**
     * Get all active services
     */
    public static function get_active_services()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_services';
        
        return $wpdb->get_results(
            "SELECT * FROM {$table} WHERE is_active = 1 ORDER BY id ASC"
        );
    }

    /**
     * Get service by ID
     */
    public static function get_service_by_id($service_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_services';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $service_id
        ));
    }

    /**
     * Get service by key
     */
    public static function get_service_by_key($service_key)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_services';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE service_key = %s",
            $service_key
        ));
    }

    /**
     * Update service (admin only)
     */
    public static function update_service($service_id, $data)
    {
        if (!current_user_can('manage_options')) {
            return false;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_services';

        $update_data = array();
        
        if (isset($data['service_name_fa'])) {
            $update_data['service_name_fa'] = sanitize_text_field($data['service_name_fa']);
        }
        if (isset($data['price_per_session'])) {
            $update_data['price_per_session'] = floatval($data['price_per_session']);
        }
        if (isset($data['description'])) {
            $update_data['description'] = sanitize_textarea_field($data['description']);
        }
        if (isset($data['image_url'])) {
            $update_data['image_url'] = esc_url_raw($data['image_url']);
        }
        if (isset($data['is_active'])) {
            $update_data['is_active'] = intval($data['is_active']);
        }

        if (empty($update_data)) {
            return false;
        }

        return $wpdb->update(
            $table,
            $update_data,
            array('id' => $service_id),
            array('%s', '%f', '%s', '%s', '%d'),
            array('%d')
        );
    }
}
