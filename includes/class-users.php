<?php
if (!defined('ABSPATH')) {
    exit;
}

class Hamnaghsheh_Users
{
    public function __construct()
    {
        add_action('user_register', [$this, 'on_user_register'], 10, 1);
    }

    public function on_user_register($user_id)
    {
        global $wpdb;
        $user_info = get_userdata($user_id);
        $email = $user_info->user_email;
        $username = $user_info->user_login;
        $name = $user_info->display_name;
        $table_name = $wpdb->prefix . 'hamnaghsheh_users';
        
        $wpdb->insert(
            $table_name,
            [
                'user_id' => $user_id,
                'username' => $username,
                'email' => $email,
                'display_name' => $name
            ],
            ['%d', '%s', '%s', '%s']
        );
    }

    public static function check_active_user($user_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'hamnaghsheh_users';
        $active = $wpdb->get_var($wpdb->prepare(
            "SELECT active FROM $table_name WHERE user_id = %d ORDER BY id DESC LIMIT 1",
            $user_id
        ));
        return $active;
    }

    public static function ensure_user_access()
    {
        if (!is_user_logged_in()) {
            exit;
        }
        
        $user_id = get_current_user_id();
        if (!self::check_active_user($user_id)) {
            return '<p class="hamnaghsheh-notice text-red-800 bg-red-100 w-full p-4 rounded-lg text-md text-center">دسترسی شما به سیستم هنوز فعال نشده است لطفاً با مدیر تماس بگیرید</p>';
        }
        
        return false;
    }

    public static function current_id()
    {
        return get_current_user_id();
    }

    /**
     * Check if user is premium
     * 
     * @param int|null $user_id User ID to check, defaults to current user
     * @return bool True if premium, false otherwise
     */
    public static function is_premium_user($user_id = null)
    {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'hamnaghsheh_users';
        $access_level = $wpdb->get_var($wpdb->prepare(
            "SELECT access_level FROM $table_name WHERE user_id = %d ORDER BY id DESC LIMIT 1",
            $user_id
        ));
        
        // Return true if premium, false otherwise (including null/not found)
        return ($access_level === 'premium');
    }

    /**
     * Get user access level (free or premium)
     * Added by soroush - 11/12/2025
     * 
     * @param int|null $user_id User ID to check, defaults to current user
     * @return string Access level ('free' or 'premium')
     */
    public static function get_user_access_level($user_id = null)
    {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'hamnaghsheh_users';
        $access_level = $wpdb->get_var($wpdb->prepare(
            "SELECT access_level FROM $table_name WHERE user_id = %d ORDER BY id DESC LIMIT 1",
            $user_id
        ));
        
        // Default to 'free' if not found
        return $access_level ?: 'free';
    }

    /**
     * Get user storage info including access level
     * 
     * @param int|null $user_id User ID to check, defaults to current user
     * @return array Array with storage_limit, access_level, and active status
     */
    public static function get_user_storage_info($user_id = null)
    {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'hamnaghsheh_users';
        $user_data = $wpdb->get_row($wpdb->prepare(
            "SELECT storage_limit, access_level, active FROM $table_name WHERE user_id = %d ORDER BY id DESC LIMIT 1",
            $user_id
        ), ARRAY_A);
        
        if (!$user_data) {
            return [
                'storage_limit' => 52428800, // 50MB default
                'access_level' => 'free',
                'active' => 1
            ];
        }
        
        return $user_data;
    }
}