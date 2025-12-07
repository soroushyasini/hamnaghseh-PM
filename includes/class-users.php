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
        
        // ✅ Insert new user with active = 0 (inactive by default)
        // disabled the activation process by make any new person actived by defult.
        // updated the insert quary and the email notif. 6 Dec 2025
        $wpdb->insert(
            $table_name,
            [
                'user_id' => $user_id,
                'username' => $username,
                'email' => $email,
                'display_name' => $name,
                'active' => 1, // ✅ NEW: Inactive by default
                'access_level' => 'free', // ✅ NEW: Default to free
                'storage_limit' => 0 // ✅ NEW: Free users have no storage
            ],
            ['%d', '%s', '%s', '%s', '%d', '%s', '%d']
        );

        // ✅ Send email notification to admin
        $this->send_admin_notification($user_id, $username, $email);
    }

    /**
     * Send email to admin when new user registers
     * Added by soroush - 12/02/2025
     * changed the text for match alway-active-new-user logic :
     */
    private function send_admin_notification($user_id, $username, $email)
    {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        $edit_user_url = admin_url('user-edit.php?user_id=' . $user_id);

        $subject = sprintf('[%s] کاربر جدید در انتظار تایید', $site_name);
        
        $message = sprintf(
            "سلام،\n\n" .
            "یک کاربر جدید در سایت %s ثبت‌نام کرده است و در انتظار تایید شماست.\n\n" .
            "اطلاعات کاربر:\n" .
            "- نام کاربری: %s\n" .
            "- ایمیل: %s\n" .
            "- تاریخ ثبت‌نام: %s\n\n" .
            "برای فعال‌سازی کاربر، به لینک زیر بروید:\n%s\n\n" .
            "توجه: تا زمانی که کاربر را فعال نکنید، او نمی‌تواند به سیستم دسترسی داشته باشد.",
            $site_name,
            $username,
            $email,
            current_time('Y-m-d H:i:s'),
            $edit_user_url
        );

        // Send email
        wp_mail($admin_email, $subject, $message);
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
            return '<p class="hamnaghsheh-notice text-red-800 bg-red-100 w-full p-4 rounded-lg text-md text-center">دسترسی شما به سیستم هنوز فعال نشده است لطفاً با پشتیبانی تماس بگیرید</p>';
        }
        
        return false;
    }

    public static function current_id()
    {
        return get_current_user_id();
    }

    /**
     * Check if user is premium or enterprise
     * Updated by soroush - 12/02/2025
     * 
     * @param int|null $user_id User ID to check, defaults to current user
     * @return bool True if premium or enterprise, false otherwise
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
        
        // ✅ Return true if premium OR enterprise
        return in_array($access_level, ['premium', 'enterprise']);
    }

    /**
     * Check if user is enterprise level
     * Added by soroush - 12/02/2025
     * 
     * @param int|null $user_id User ID to check, defaults to current user
     * @return bool True if enterprise, false otherwise
     */
    public static function is_enterprise_user($user_id = null)
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
        
        return ($access_level === 'enterprise');
    }

    /**
     * Get user access level (free, premium, or enterprise)
     * Updated by soroush - 12/02/2025
     * 
     * @param int|null $user_id User ID to check, defaults to current user
     * @return string Access level ('free', 'premium', or 'enterprise')
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
                'storage_limit' => 0, // ✅ Changed: Free users have 0 storage
                'access_level' => 'free',
                'active' => 0 // ✅ Changed: Default to inactive
            ];
        }
        
        return $user_data;
    }
}