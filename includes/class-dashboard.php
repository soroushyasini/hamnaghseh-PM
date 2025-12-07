<?php
if (!defined('ABSPATH'))
    exit;

class Hamnaghsheh_Dashboard
{
    public static function render_shortcode()
    {
        
        $msg = Hamnaghsheh_Users::ensure_user_access();
        if ($msg !== false) {
            return $msg;
        }
        
        $projects = Hamnaghsheh_Projects::get_user_projects(Hamnaghsheh_Users::current_id());
        $archived_project = Hamnaghsheh_Projects::get_archived_project(Hamnaghsheh_Users::current_id());

        global $wpdb;
        $current_user_id = Hamnaghsheh_Users::current_id();

        // ✅ FIXED: Get storage limit directly from user table
        // This works even if user has no projects
        $storage_info = Hamnaghsheh_Users::get_user_storage_info($current_user_id);
        $total_space = intval($storage_info['storage_limit']);

        // ✅ Calculate used space (only where user has files)
        $used_space = $wpdb->get_var($wpdb->prepare("
            SELECT COALESCE(SUM(f.file_size), 0)
            FROM {$wpdb->prefix}hamnaghsheh_files AS f
            INNER JOIN {$wpdb->prefix}hamnaghsheh_projects AS p ON f.project_id = p.id
            WHERE p.user_id = %d
        ", $current_user_id));

        $used_space = $used_space ? intval($used_space) : 0;

        // محاسبه درصد مصرف
        $percent = $total_space > 0 ? min(100, round(($used_space / $total_space) * 100)) : 0;

        // فرمت خوانا
        $used_human = size_format($used_space);
        $total_human = size_format($total_space);

        // لود قالب داشبورد
        ob_start();
        include HAMNAGHSHEH_DIR . 'templates/dashboard.php';
        return ob_get_clean();
    }
}