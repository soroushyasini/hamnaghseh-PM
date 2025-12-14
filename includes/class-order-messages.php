<?php
if (!defined('ABSPATH'))
    exit;

class Hamnaghsheh_Order_Messages
{
    /**
     * Add a new message
     */
    public static function add_message($order_id, $message, $is_admin = false)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_order_messages';

        $insert_data = array(
            'order_id' => intval($order_id),
            'user_id' => get_current_user_id(),
            'message' => sanitize_textarea_field($message),
            'is_admin' => $is_admin ? 1 : 0,
            'is_read' => 0,
            'created_at' => current_time('mysql')
        );

        $result = $wpdb->insert($table, $insert_data);

        if ($result) {
            // Log activity
            Hamnaghsheh_Order_Activity::log_activity(
                $order_id,
                'message_sent',
                '',
                '',
                $is_admin ? 'پیام جدید از ادمین' : 'پیام جدید از کاربر',
                get_current_user_id(),
                $is_admin
            );

            // Send notification
            if ($is_admin) {
                do_action('hamnaghsheh_order_message', $order_id);
            } else {
                do_action('hamnaghsheh_admin_message', $order_id);
            }

            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Get all messages for an order
     */
    public static function get_order_messages($order_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_order_messages';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE order_id = %d ORDER BY created_at ASC",
            $order_id
        ));
    }

    /**
     * Mark messages as read
     */
    public static function mark_as_read($order_id, $is_admin = false)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_order_messages';

        $is_admin_val = $is_admin ? 1 : 0;

        return $wpdb->update(
            $table,
            array('is_read' => 1),
            array(
                'order_id' => $order_id,
                'is_admin' => $is_admin_val
            )
        );
    }

    /**
     * Get unread count
     */
    public static function get_unread_count($order_id, $for_admin = false)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_order_messages';

        $is_admin = $for_admin ? 1 : 0;

        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE order_id = %d AND is_read = 0 AND is_admin != %d",
            $order_id,
            $is_admin
        ));
    }
}
