<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hamnaghsheh Profile Management Class
 * Handles user profile display and updates
 * Phase 1: Independent of WooCommerce
 */
class Hamnaghsheh_Profile
{
    // Phone number pattern constant
    const PHONE_USERNAME_PATTERN = '/^u(\d{11})$/';
    
    public function __construct()
    {
        // Constructor can be used for hooks if needed
    }

    /**
     * Render profile page shortcode
     * 
     * @return string HTML output
     */
    public static function render_shortcode()
    {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return '<p class="text-center text-red-600">برای دسترسی به این صفحه باید وارد حساب کاربری خود شوید.</p>';
        }

        // Check user access (similar to dashboard)
        $msg = Hamnaghsheh_Users::ensure_user_access();
        if ($msg !== false) {
            return $msg;
        }

        // Get current user data
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        // Get user meta
        $first_name = get_user_meta($user_id, 'first_name', true);
        $last_name = get_user_meta($user_id, 'last_name', true);
        $display_name = $current_user->display_name;
        $email = $current_user->user_email;

        // Get custom address fields
        $address = get_user_meta($user_id, 'hamnaghsheh_address', true);
        $city = get_user_meta($user_id, 'hamnaghsheh_city', true);
        $postal_code = get_user_meta($user_id, 'hamnaghsheh_postal_code', true);

        // Get formatted phone from username
        $phone = self::get_formatted_phone($current_user->user_login);

        // Get user access level and storage info
        global $wpdb;
        $users_table = $wpdb->prefix . 'hamnaghsheh_users';
        
        // Try to get user info with trial fields, fall back if they don't exist
        // Using COALESCE to handle missing columns gracefully
        $user_info = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                access_level, 
                storage_limit, 
                created_at,
                COALESCE(trial_activated, 0) as trial_activated,
                trial_ends_at
             FROM $users_table 
             WHERE user_id = %d 
             ORDER BY id DESC 
             LIMIT 1",
            $user_id
        ), ARRAY_A);

        if (!$user_info) {
            $user_info = [
                'access_level' => 'free',
                'storage_limit' => 0,
                'created_at' => $current_user->user_registered,
                'trial_activated' => 0,
                'trial_ends_at' => null
            ];
        } else {
            // Ensure trial fields exist even if query failed
            if (!isset($user_info['trial_activated'])) {
                $user_info['trial_activated'] = 0;
            }
            if (!isset($user_info['trial_ends_at'])) {
                $user_info['trial_ends_at'] = null;
            }
        }

        $access_level = $user_info['access_level'];
        $storage_limit = intval($user_info['storage_limit']);
        $created_at = $user_info['created_at'];
        $trial_activated = intval($user_info['trial_activated']);
        $trial_ends_at = $user_info['trial_ends_at'];

        // Calculate storage used
        $storage_used = $wpdb->get_var($wpdb->prepare("
            SELECT COALESCE(SUM(f.file_size), 0)
            FROM {$wpdb->prefix}hamnaghsheh_files AS f
            INNER JOIN {$wpdb->prefix}hamnaghsheh_projects AS p ON f.project_id = p.id
            WHERE p.user_id = %d
        ", $user_id));

        $storage_used = $storage_used ? intval($storage_used) : 0;

        // Get user projects for sidebar
        $projects = Hamnaghsheh_Projects::get_user_projects($user_id);

        // Calculate storage percentage
        $storage_percent = $storage_limit > 0 ? min(100, round(($storage_used / $storage_limit) * 100)) : 0;
        $storage_used_human = size_format($storage_used);
        $storage_limit_human = size_format($storage_limit);

        // Get access level label
        $access_label = Hamnaghsheh_Utils::get_access_level_label($access_level);

        // Load template
        ob_start();
        include HAMNAGHSHEH_DIR . 'templates/profile.php';
        return ob_get_clean();
    }

    /**
     * Handle AJAX profile update
     */
    public static function ajax_update_profile()
    {
        // Verify nonce
        check_ajax_referer('hamnaghsheh_ajax_nonce', 'nonce');

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'شما وارد سیستم نشده‌اید.']);
            return;
        }

        $user_id = get_current_user_id();

        // Sanitize and validate inputs
        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $display_name = isset($_POST['display_name']) ? sanitize_text_field($_POST['display_name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $address = isset($_POST['address']) ? sanitize_text_field($_POST['address']) : '';
        $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
        $postal_code = isset($_POST['postal_code']) ? sanitize_text_field($_POST['postal_code']) : '';

        // Validate email
        if (!empty($email) && !is_email($email)) {
            wp_send_json_error(['message' => 'فرمت ایمیل نامعتبر است.']);
            return;
        }

        // Check if email is already used by another user
        if (!empty($email)) {
            $email_exists = email_exists($email);
            if ($email_exists && $email_exists != $user_id) {
                wp_send_json_error(['message' => 'این ایمیل قبلاً استفاده شده است.']);
                return;
            }
        }

        // Update user meta
        update_user_meta($user_id, 'first_name', $first_name);
        update_user_meta($user_id, 'last_name', $last_name);
        update_user_meta($user_id, 'hamnaghsheh_address', $address);
        update_user_meta($user_id, 'hamnaghsheh_city', $city);
        update_user_meta($user_id, 'hamnaghsheh_postal_code', $postal_code);

        // Update user table (email and display name)
        $user_data = [
            'ID' => $user_id,
        ];

        if (!empty($display_name)) {
            $user_data['display_name'] = $display_name;
        }

        if (!empty($email)) {
            $user_data['user_email'] = $email;
        }

        $result = wp_update_user($user_data);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => 'خطا در بروزرسانی اطلاعات: ' . $result->get_error_message()]);
            return;
        }

        wp_send_json_success(['message' => 'اطلاعات شما با موفقیت بروزرسانی شد.']);
    }

    /**
     * Handle AJAX password change
     */
    public static function ajax_change_password()
    {
        // Verify nonce
        check_ajax_referer('hamnaghsheh_ajax_nonce', 'nonce');

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'شما وارد سیستم نشده‌اید.']);
            return;
        }

        $user_id = get_current_user_id();

        // Get and validate passwords
        $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        // Validate inputs
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            wp_send_json_error(['message' => 'لطفاً همه فیلدها را پر کنید.']);
            return;
        }

        // Check if new password matches confirmation
        if ($new_password !== $confirm_password) {
            wp_send_json_error(['message' => 'رمز عبور جدید و تکرار آن یکسان نیستند.']);
            return;
        }

        // Validate password strength (minimum 8 characters)
        if (strlen($new_password) < 8) {
            wp_send_json_error(['message' => 'رمز عبور باید حداقل 8 کاراکتر باشد.']);
            return;
        }

        // Verify current password
        $user = get_userdata($user_id);
        if (!wp_check_password($current_password, $user->user_pass, $user_id)) {
            wp_send_json_error(['message' => 'رمز عبور فعلی اشتباه است.']);
            return;
        }

        // Update password
        wp_set_password($new_password, $user_id);

        wp_send_json_success(['message' => 'رمز عبور با موفقیت تغییر کرد.']);
    }

    /**
     * Extract and format phone number from username
     * Converts u09134763457 to 0913-476-3457
     * 
     * @param string $username Username to extract phone from
     * @return string Formatted phone number or empty string
     */
    public static function get_formatted_phone($username)
    {
        // Check if username matches phone pattern
        if (preg_match(self::PHONE_USERNAME_PATTERN, $username, $matches)) {
            $phone = $matches[1];
            // Format as XXXX-XXX-XXXX
            return substr($phone, 0, 4) . '-' . substr($phone, 4, 3) . '-' . substr($phone, 7);
        }

        return '';
    }
}
