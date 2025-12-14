<?php
if (!defined('ABSPATH'))
    exit;

class Hamnaghsheh_Order_Activity
{
    /**
     * Log activity
     */
    public static function log_activity($order_id, $activity_type, $old_value = '', $new_value = '', $description = '', $created_by = null, $is_admin = false)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_order_activity';

        if ($created_by === null) {
            $created_by = get_current_user_id();
        }

        $insert_data = array(
            'order_id' => intval($order_id),
            'activity_type' => sanitize_text_field($activity_type),
            'old_value' => sanitize_text_field($old_value),
            'new_value' => sanitize_text_field($new_value),
            'description' => sanitize_text_field($description),
            'created_by' => $created_by,
            'is_admin' => $is_admin ? 1 : 0,
            'created_at' => current_time('mysql')
        );

        return $wpdb->insert($table, $insert_data);
    }

    /**
     * Get order activity
     */
    public static function get_order_activity($order_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_order_activity';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE order_id = %d ORDER BY created_at DESC",
            $order_id
        ));
    }

    /**
     * Get activity type label in Persian
     */
    public static function get_activity_label($activity_type)
    {
        $labels = array(
            'status_changed' => 'تغییر وضعیت',
            'price_set' => 'تعیین قیمت',
            'message_sent' => 'ارسال پیام',
            'order_edited' => 'ویرایش سفارش',
            'project_created' => 'ایجاد پروژه',
            'order_cancelled' => 'لغو سفارش'
        );

        return isset($labels[$activity_type]) ? $labels[$activity_type] : $activity_type;
    }
}
