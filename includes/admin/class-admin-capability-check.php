<?php
if (!defined('ABSPATH'))
    exit;

class Hamnaghsheh_Admin_Capability_Check
{
    public function __construct()
    {
        add_action('admin_init', array($this, 'check_and_fix_capabilities'));
    }

    /**
     * Check if admin has capability and fix if needed
     */
    public function check_and_fix_capabilities()
    {
        // Only run for administrators
        if (!current_user_can('manage_options')) {
            return;
        }

        $admin_role = get_role('administrator');
        
        if ($admin_role && !$admin_role->has_cap('hamnaghsheh_admin')) {
            // Auto-fix: add the capability
            $admin_role->add_cap('hamnaghsheh_admin');
            
            // Set transient to show notice only once
            set_transient('hamnaghsheh_capability_fixed', 1, 60);
            
            // Add admin notice
            add_action('admin_notices', function() {
                if (get_transient('hamnaghsheh_capability_fixed')) {
                    echo '<div class="notice notice-success is-dismissible">';
                    echo '<p><strong>همنقشه PM:</strong> دسترسی‌های مدیر به‌روزرسانی شد. لطفاً صفحه را بازخوانی کنید.</p>';
                    echo '</div>';
                    delete_transient('hamnaghsheh_capability_fixed');
                }
            });
        }
    }
}
