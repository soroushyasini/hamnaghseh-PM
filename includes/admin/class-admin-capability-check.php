<?php
if (!defined('ABSPATH'))
    exit;

class Hamnaghsheh_Admin_Capability_Check
{
    private $notice_shown = false;

    public function __construct()
    {
        add_action('admin_init', array($this, 'check_and_fix_capabilities'));
        add_action('admin_notices', array($this, 'show_capability_notice'));
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

        // Check if we already verified the capability for this installation
        if (get_option('hamnaghsheh_capability_verified')) {
            return;
        }

        $admin_role = get_role('administrator');
        
        if ($admin_role) {
            if (!$admin_role->has_cap('hamnaghsheh_admin')) {
                // Auto-fix: add the capability
                $admin_role->add_cap('hamnaghsheh_admin');
                
                // Set transient to show notice
                set_transient('hamnaghsheh_capability_fixed', 1, 10);
            }
            
            // Mark as verified only if admin role exists and we processed it
            update_option('hamnaghsheh_capability_verified', 1, false);
        }
    }

    /**
     * Show admin notice if capability was just fixed
     */
    public function show_capability_notice()
    {
        if (get_transient('hamnaghsheh_capability_fixed') && !$this->notice_shown) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><strong><?php echo esc_html('همنقشه PM'); ?>:</strong> <?php echo esc_html('دسترسی‌های مدیر به‌روزرسانی شد. لطفاً صفحه را بازخوانی کنید.'); ?></p>
            </div>
            <?php
            $this->notice_shown = true;
            delete_transient('hamnaghsheh_capability_fixed');
        }
    }
}
