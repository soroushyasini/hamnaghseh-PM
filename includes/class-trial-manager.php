<?php
if (!defined('ABSPATH'))
    exit;

/**
 * Trial Manager Class
 * Handles 14-day free trial system
 * Created by soroush - 12/08/2025
 */
class Hamnaghsheh_Trial_Manager
{
    const TRIAL_DURATION_DAYS = 14;
    const TRIAL_STORAGE_BYTES = 10485760; // 10 MB

    public function __construct()
    {
        // AJAX handler for trial activation
        add_action('wp_ajax_hamnaghsheh_activate_trial', [$this, 'ajax_activate_trial']);
    }

    /**
     * Check if user can activate trial
     * 
     * @param int $user_id User ID
     * @return bool True if can activate, false if already used
     */
    public static function can_activate_trial($user_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_users';
        
        $trial_activated = $wpdb->get_var($wpdb->prepare(
            "SELECT trial_activated FROM $table WHERE user_id = %d",
            $user_id
        ));
        
        // Can activate if never activated before
        return ($trial_activated == 0 || $trial_activated === null);
    }

    /**
     * Check if user's trial is currently active
     * 
     * @param int $user_id User ID
     * @return bool True if trial is active and not expired
     */
    public static function is_trial_active($user_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_users';
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT trial_activated, trial_ends_at FROM $table WHERE user_id = %d",
            $user_id
        ), ARRAY_A);
        
        if (!$result || $result['trial_activated'] != 1) {
            return false;
        }
        
        // Check if not expired
        if ($result['trial_ends_at']) {
            $now = current_time('mysql');
            return (strtotime($result['trial_ends_at']) > strtotime($now));
        }
        
        return false;
    }

    /**
     * Get remaining trial days
     * 
     * @param int $user_id User ID
     * @return int Days remaining (0 if expired or not activated)
     */
    public static function get_remaining_days($user_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_users';
        
        $trial_ends_at = $wpdb->get_var($wpdb->prepare(
            "SELECT trial_ends_at FROM $table WHERE user_id = %d AND trial_activated = 1",
            $user_id
        ));
        
        if (!$trial_ends_at) {
            return 0;
        }
        
        $now = current_time('timestamp');
        $end = strtotime($trial_ends_at);
        
        if ($end <= $now) {
            return 0;
        }
        
        $diff = $end - $now;
        return ceil($diff / DAY_IN_SECONDS);
    }

    /**
     * Activate trial for user
     * 
     * @param int $user_id User ID
     * @return array Result with success status and message
     */
    public static function activate_trial($user_id)
    {
        // Check if already used
        if (!self::can_activate_trial($user_id)) {
            return [
                'success' => false,
                'message' => 'شما قبلاً از دوره آزمایشی استفاده کرده‌اید.'
            ];
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_users';
        
        $now = current_time('mysql');
        $end_date = date('Y-m-d H:i:s', strtotime($now . ' +' . self::TRIAL_DURATION_DAYS . ' days'));
        
        // Update user record
        $updated = $wpdb->update(
            $table,
            [
                'trial_activated' => 1,
                'trial_started_at' => $now,
                'trial_ends_at' => $end_date,
                'storage_limit' => self::TRIAL_STORAGE_BYTES // Set 10MB storage
            ],
            ['user_id' => $user_id],
            ['%d', '%s', '%s', '%d'],
            ['%d']
        );
        
        if ($updated !== false) {
            return [
                'success' => true,
                'message' => '✅ دوره آزمایشی 14 روزه شما فعال شد! اکنون می‌توانید از تمام امکانات استفاده کنید.',
                'end_date' => $end_date
            ];
        }
        
        return [
            'success' => false,
            'message' => 'خطا در فعال‌سازی دوره آزمایشی. لطفاً دوباره تلاش کنید.'
        ];
    }

    /**
     * Get trial status for user
     * 
     * @param int $user_id User ID
     * @return array Status information
     */
    public static function get_trial_status($user_id)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_users';
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT access_level, trial_activated, trial_started_at, trial_ends_at, storage_limit 
            FROM $table WHERE user_id = %d",
            $user_id
        ), ARRAY_A);
        
        if (!$result) {
            return [
                'state' => 'basic_free',
                'can_activate' => true,
                'is_active' => false,
                'is_expired' => false,
                'days_remaining' => 0
            ];
        }
        
        // Premium or Enterprise user
        if (in_array($result['access_level'], ['premium', 'enterprise'])) {
            return [
                'state' => $result['access_level'],
                'can_activate' => false,
                'is_active' => false,
                'is_expired' => false,
                'days_remaining' => 0
            ];
        }
        
        // Free user - check trial status
        $trial_activated = $result['trial_activated'];
        
        if (!$trial_activated) {
            // Never activated trial
            return [
                'state' => 'basic_free',
                'can_activate' => true,
                'is_active' => false,
                'is_expired' => false,
                'days_remaining' => 0
            ];
        }
        
        // Trial was activated - check if expired
        $now = current_time('timestamp');
        $end = strtotime($result['trial_ends_at']);
        
        if ($end > $now) {
            // Trial active
            $days_remaining = self::get_remaining_days($user_id);
            return [
                'state' => 'trial_active',
                'can_activate' => false,
                'is_active' => true,
                'is_expired' => false,
                'days_remaining' => $days_remaining,
                'trial_ends_at' => $result['trial_ends_at']
            ];
        } else {
            // Trial expired
            return [
                'state' => 'trial_expired',
                'can_activate' => false,
                'is_active' => false,
                'is_expired' => true,
                'days_remaining' => 0
            ];
        }
    }

    /**
     * AJAX handler for trial activation
     */
    public function ajax_activate_trial()
    {
        // Check nonce
        if (!check_ajax_referer('hamnaghsheh_ajax_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid nonce']);
            return;
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'You must be logged in']);
            return;
        }
        
        $user_id = get_current_user_id();
        $result = self::activate_trial($user_id);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * Check if user has trial features (active trial OR paid plan)
     * 
     * @param int $user_id User ID
     * @return bool True if user can upload/delete/archive
     */
    public static function has_trial_features($user_id)
    {
        $access_level = Hamnaghsheh_Users::get_user_access_level($user_id);
        
        // Premium/Enterprise always have features
        if (in_array($access_level, ['premium', 'enterprise'])) {
            return true;
        }
        
        // Free users only if trial is active
        return self::is_trial_active($user_id);
    }
}