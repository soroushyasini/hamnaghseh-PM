<?php
if (!defined('ABSPATH'))
    exit;

class Hamnaghsheh_Project_Show
{
    /**
     * Check if user can access a project
     * 
     * @param int $project_id Project ID
     * @param int $user_id User ID
     * @return bool
     */
    private static function can_user_access_project($project_id, $user_id)
    {
        // Users with hamnaghsheh_admin capability have full access
        if (current_user_can('hamnaghsheh_admin')) {
            return true;
        }
        
        global $wpdb;
        
        // Check if user is the project owner
        $owner_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->prefix}hamnaghsheh_projects WHERE id = %d",
            $project_id
        ));
        
        if (!$owner_id) {
            return false; // Project doesn't exist
        }
        
        if ($owner_id == $user_id) {
            return true; // Owner has access
        }
        
        // Check if user is assigned to the project
        $is_assigned = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}hamnaghsheh_project_assignments 
             WHERE project_id = %d AND user_id = %d",
            $project_id,
            $user_id
        ));
        
        return $is_assigned > 0;
    }
    
    /**
     * Check if user can upload to a project
     * 
     * @param int $project_id Project ID
     * @param int $user_id User ID
     * @return bool
     */
    private static function can_user_upload_to_project($project_id, $user_id)
    {
        // Admins can upload to any project
        if (current_user_can('hamnaghsheh_admin')) {
            return true;
        }
        
        global $wpdb;
        
        // Check if owner
        $owner_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->prefix}hamnaghsheh_projects WHERE id = %d",
            $project_id
        ));
        
        if ($owner_id && $owner_id == $user_id) {
            return true;
        }
        
        // Check if assigned with upload permission
        $has_upload_permission = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}hamnaghsheh_project_assignments 
             WHERE project_id = %d AND user_id = %d AND permission = 'upload'",
            $project_id,
            $user_id
        ));
        
        return $has_upload_permission > 0;
    }

    public static function render_shortcode()
    {
        $msg = Hamnaghsheh_Users::ensure_user_access();
        if ($msg !== false) {
            return $msg;
        }

        // دریافت ID پروژه از query string
        $project_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($project_id <= 0) {
            return '<div class="hamnaghsheh-notice text-red-800 bg-red-100 w-full p-4 rounded-lg text-md text-center">شناسه پروژه معتبر نیست</div>';
        }

        global $wpdb;
        $table_projects = $wpdb->prefix . 'hamnaghsheh_projects';
        $table_files = $wpdb->prefix . 'hamnaghsheh_files';
        $table_usersp = $wpdb->prefix . 'hamnaghsheh_users';
        $table_assigns = $wpdb->prefix . 'hamnaghsheh_project_assignments';

        $table_users = $wpdb->users;
        $current_user_id = Hamnaghsheh_Users::current_id();



        $project_id = (int) $project_id;
        
        // Use capability-based access check
        if (!self::can_user_access_project($project_id, $current_user_id)) {
            return '<div class="hamnaghsheh-notice text-red-800 bg-red-100 w-full p-4 rounded-lg text-md text-center">شما به این پروژه دسترسی ندارید</div>';
        }

        $query = $wpdb->prepare("
                SELECT 
                    p.*, 
                    u.user_login,
                    CASE 
                        WHEN u.display_name IS NULL OR u.display_name = '' THEN u.user_nicename 
                        ELSE u.display_name 
                    END AS display_name,
                    CASE
                        WHEN p.user_id = %d THEN 'owner'
                        WHEN a.permission IS NOT NULL THEN a.permission
                        ELSE NULL
                    END AS user_permission
                FROM $table_projects AS p
                INNER JOIN $table_users AS u ON p.user_id = u.ID
                LEFT JOIN $table_assigns AS a 
                    ON a.project_id = p.id AND a.user_id = %d
                WHERE p.id = %d
            ", $current_user_id, $current_user_id, $project_id);

        $project = $wpdb->get_row($query);


        if (!$project) {
            return '<div class="hamnaghsheh-notice text-red-800 bg-red-100 w-full p-4 rounded-lg text-md text-center">پروژه مورد نظر یافت نشد</div>';
        }

        // ✅ FIX: Ensure admins have full owner-like permissions even if not assigned
        if (current_user_can('hamnaghsheh_admin') && empty($project->user_permission)) {
            $project->user_permission = 'owner';
        }

        if ($project->archive != 0) {
            return '<div class="hamnaghsheh-notice text-red-800 bg-red-100 w-full p-4 rounded-lg text-md text-center">پروژه مورد نظر یافت نشد</div>';
        }

        // بررسی مالکیت یا assign بودن کاربر
        $is_owner = ($project->user_id == $current_user_id);

        // $owner_id = $wpdb->get_var($wpdb->prepare("
        //     SELECT user_id FROM $table_projects WHERE id = %d
        // ", $project_id));

        // $result = $wpdb->get_row($wpdb->prepare("
        //     SELECT 
        //         COALESCE(SUM(f.file_size), 0) AS used_space,
        //         u.storage_limit AS total_space
        //     FROM $table_projects AS p
        //     INNER JOIN $table_usersp AS u ON p.user_id = u.user_id
        //     LEFT JOIN $table_files AS f ON p.id = f.project_id
        //     WHERE p.user_id = %d
        //     GROUP BY u.storage_limit
        // ", $owner_id), ARRAY_A);

        // $used_space = isset($result['used_space']) ? intval($result['used_space']) : 0;
        // $total_space = isset($result['total_space']) ? intval($result['total_space']) : 52428800;

        // $percent = $total_space > 0 ? min(100, round(($used_space / $total_space) * 100)) : 0;

        $result = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COALESCE(SUM(f.file_size), 0) AS used_space,
                u.storage_limit AS total_space
            FROM {$wpdb->prefix}hamnaghsheh_users AS u
            LEFT JOIN {$wpdb->prefix}hamnaghsheh_projects AS p 
                ON u.user_id = p.user_id
            LEFT JOIN {$wpdb->prefix}hamnaghsheh_files AS f 
                ON p.id = f.project_id
            WHERE u.user_id = %d
            AND p.user_id = %d
            GROUP BY u.storage_limit
        ", $current_user_id, $current_user_id), ARRAY_A);

        // اگر نتیجه خالی بود مقدار پیش‌فرض بده
        $used_space = isset($result['used_space']) ? intval($result['used_space']) : 0;
        // $total_space = isset($result['total_space']) ? intval($result['total_space']) : 0; #changed to zerom like dashbard.php logic - 9 dec 00:45
        // ✅ FIXED: Get storage limit directly from user table
        // This works even if user has no projects
        $storage_info = Hamnaghsheh_Users::get_user_storage_info($current_user_id);
        $total_space = intval($storage_info['storage_limit']);
        // محاسبه درصد مصرف
        $percent = $total_space > 0 ? min(100, round(($used_space / $total_space) * 100)) : 0;
        
        

        // فرمت خوانا
        $used_human = size_format($used_space);
        $total_human = size_format($total_space);

        $files = $wpdb->get_results($wpdb->prepare("
            SELECT id, file_name, file_size, file_path, uploaded_at 
            FROM $table_files 
            WHERE project_id = %d
            ORDER BY uploaded_at DESC
        ", $project_id), ARRAY_A);


        $members = $wpdb->get_results($wpdb->prepare("
            SELECT 
                u.ID AS user_id,
                CASE 
                    WHEN u.display_name IS NULL OR u.display_name = '' THEN u.user_nicename
                    ELSE u.display_name
                END AS user_name
            FROM $table_assigns AS perm
            INNER JOIN $table_users AS u ON perm.user_id = u.ID
            WHERE perm.project_id = %d
        ", $project_id));

        // لود قالب داشبورد
        ob_start();
        include HAMNAGHSHEH_DIR . 'templates/project-show.php';
        return ob_get_clean();
    }
}