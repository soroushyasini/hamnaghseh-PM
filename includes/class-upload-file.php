<?php
if (!defined('ABSPATH'))
    exit;

class Hamnaghsheh_File_Upload
{
    public function __construct()
    {
        if (!session_id())
            session_start();

        add_action('admin_post_hamnaghsheh_upload_file', [$this, 'upload_file']);
        add_action('admin_post_nopriv_hamnaghsheh_upload_file', [$this, 'upload_file']);

        add_action('admin_post_hamnaghsheh_delete_file', [$this, 'delete_file']);
        add_action('admin_post_nopriv_hamnaghsheh_delete_file', [$this, 'delete_file']);

        add_action('admin_post_hamnaghsheh_replace_file', [$this, 'replace_file']);
        add_action('admin_post_nopriv_hamnaghsheh_replace_file', [$this, 'replace_file']);
    }

    public function upload_file()
    {
        if (!is_user_logged_in())
            wp_die('برای آپلود فایل باید وارد شوید.');

        if (empty($_POST['project_id']) || empty($_FILES['file']))
            wp_die('درخواست ناقص است.');

        global $wpdb;
        $user_id = get_current_user_id();
        $project_id = intval($_POST['project_id']);
        $file = $_FILES['file'];

        $table_projects = $wpdb->prefix . 'hamnaghsheh_projects';
        $table_users = $wpdb->prefix . 'hamnaghsheh_users';
        $table_files = $wpdb->prefix . 'hamnaghsheh_files';
        $table_assign = $wpdb->prefix . 'hamnaghsheh_project_assignments';
        $table_file_logs = $wpdb->prefix . 'hamnaghsheh_file_logs';

        // بررسی وجود پروژه
        $project = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_projects WHERE id = %d", $project_id));
        if (!$project)
            wp_die('پروژه یافت نشد.');

        // بررسی دسترسی
        $is_owner = ($project->user_id == $user_id);
        $has_permission = false;

        // Admins can upload to any project
        if (current_user_can('hamnaghsheh_admin')) {
            $has_permission = true;
        } elseif ($is_owner) {
            $has_permission = true;
        } else {
            // بررسی اگر کاربر از طریق لینک اسایگن شده باشد
            $assign = $wpdb->get_row($wpdb->prepare("
                SELECT permission FROM $table_assign
                WHERE project_id = %d AND user_id = %d
            ", $project_id, $user_id));

            if ($assign && $assign->permission === 'upload') {
                $has_permission = true;
            }
        }

        if (!$has_permission) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'شما مجاز به آپلود در این پروژه نیستید.'];
            wp_redirect(home_url('/show-project/?id=' . $project_id));
            exit;
        }

        // ✅ NEW: Comprehensive file validation with security checks - added by soroush - 28/12/2025
        $file_validation = Hamnaghsheh_File_Validator::validate_file_comprehensive($file, $user_id);
        if (!$file_validation['valid']) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => $file_validation['message']];
            wp_redirect(home_url('/show-project/?id=' . $project_id));
            exit;
        }

        // ✅ NEW: Check storage quota using validator  -  added by soroush - 4 Dec 2025
        $new_file_size = intval($file['size']);
        $quota_check = Hamnaghsheh_File_Validator::check_storage_quota($project_id, $new_file_size, $project->user_id);
        if (!$quota_check['valid']) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => $quota_check['message']];
            wp_redirect(home_url('/show-project/?id=' . $project_id));
            exit;
        }

        // ✅ Upload to MinIO
        $minio = Hamnaghsheh_Minio::instance();
        $response = $minio->upload($file['tmp_name'], $file['name']);
        
        $relative_path = '';
        $key = '';
        if ($response['success']) {
            $relative_path = $response['url'];
            $key = $response['key'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => $response['error']];
            wp_redirect(home_url('/show-project/?id=' . $project_id));
            exit;
        }

        // ✅ Save file record with sanitized filename - updated by soroush - 28/12/2025
        $file_name = Hamnaghsheh_File_Security::sanitize_filename($file['name']);
        $wpdb->insert($table_files, [
            'project_id' => $project_id,
            'user_id' => $user_id,
            'file_name' => $file_name,
            'key_file' => $key,
            'file_path' => $relative_path,
            'file_size' => $new_file_size,
            'file_type' => $file['type'],
            'uploaded_at' => current_time('mysql')
        ]);

        $file_id = $wpdb->insert_id;

        // ✅ Log the upload action
        $wpdb->insert(
            $table_file_logs,
            [
                'file_id' => $file_id,
                'project_id' => $project_id,
                'user_id' => $user_id,
                'action_type' => 'upload',
            ],
            ['%d', '%d', '%d', '%s']
        );

        // ✅ Fire hook for chat plugin
        do_action('hamnaghsheh_file_action', $file_id, $project_id, $user_id, 'upload');

        $_SESSION['alert'] = ['type' => 'success', 'message' => '✅ فایل با موفقیت آپلود شد.'];
        wp_redirect(home_url('/show-project/?id=' . $project_id));
        exit;
    }

    public function delete_file()
    {
        if (!is_user_logged_in())
            wp_die('برای حذف فایل باید وارد شوید.');
        if (empty($_GET['file_id']) || empty($_GET['project_id']))
            wp_die('درخواست ناقص است.');

        global $wpdb;
        $file_id = intval($_GET['file_id']);
        $project_id = intval($_GET['project_id']);
        $user_id = get_current_user_id();

        $table_files = $wpdb->prefix . 'hamnaghsheh_files';
        $table_projects = $wpdb->prefix . 'hamnaghsheh_projects';
        $table_file_logs = $wpdb->prefix . 'hamnaghsheh_file_logs';

        $file = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_files WHERE id = %d", $file_id));
        if (!$file) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'فایل یافت نشد.'];
            wp_redirect(home_url('/show-project/?id=' . $project_id));
            exit;
        }

        $project = $wpdb->get_row($wpdb->prepare("SELECT user_id FROM $table_projects WHERE id = %d", $project_id));
        if (!$project || $project->user_id != $user_id) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'فقط مالک پروژه مجاز به حذف فایل‌هاست.'];
            wp_redirect(home_url('/show-project/?id=' . $project_id));
            exit;
        }
        
        // ✅ Check if user is premium or enterprise before allowing delete
        if (!Hamnaghsheh_Users::is_premium_user($user_id)) {
            $access_level = Hamnaghsheh_Users::get_user_access_level($user_id);
            $message = Hamnaghsheh_File_Validator::get_upgrade_message('delete', $access_level);
            $_SESSION['alert'] = ['type' => 'error', 'message' => $message];
            wp_redirect(home_url('/show-project/?id=' . $project_id));
            exit;
        }

        // ✅ Delete from MinIO
        $minio = Hamnaghsheh_Minio::instance();
        $result = $minio->delete($file->key_file);

        // ✅ Delete from database
        $wpdb->delete($table_files, ['id' => $file_id], ['%d']);
        
        // ✅ Log the delete action
        $wpdb->insert(
            $table_file_logs,
            [
                'file_id' => $file_id,
                'project_id' => $project_id,
                'user_id' => $user_id,
                'action_type' => 'delete',
            ],
            ['%d', '%d', '%d', '%s']
        );

        // ✅ Fire hook for chat plugin
        do_action('hamnaghsheh_file_action', $file_id, $project_id, $user_id, 'delete');

        $_SESSION['alert'] = ['type' => 'success', 'message' => '✅ فایل با موفقیت حذف شد.'];
        wp_redirect(home_url('/show-project/?id=' . $project_id));
        exit;
    }

    public function replace_file()
    {
        if (!is_user_logged_in())
            wp_die('برای جایگزینی فایل باید وارد شوید.');

        if (empty($_POST['project_id']) || empty($_POST['file_id']) || empty($_FILES['file']))
            wp_die('درخواست ناقص است.');

        global $wpdb;
        $user_id = get_current_user_id();
        $project_id = intval($_POST['project_id']);
        $file_id = intval($_POST['file_id']);
        $file = $_FILES['file'];

        $table_projects = $wpdb->prefix . 'hamnaghsheh_projects';
        $table_files = $wpdb->prefix . 'hamnaghsheh_files';
        $table_assign = $wpdb->prefix . 'hamnaghsheh_project_assignments';
        $table_file_logs = $wpdb->prefix . 'hamnaghsheh_file_logs';
        $table_users = $wpdb->prefix . 'hamnaghsheh_users';

        $project = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_projects WHERE id = %d", $project_id));
        if (!$project)
            wp_die('پروژه یافت نشد.');

        $is_owner = ($project->user_id == $user_id);
        $has_permission = false;

        // Admins can upload to any project
        if (current_user_can('hamnaghsheh_admin')) {
            $has_permission = true;
        } elseif ($is_owner) {
            $has_permission = true;
        } else {
            $assign = $wpdb->get_row($wpdb->prepare("
            SELECT permission FROM $table_assign
            WHERE project_id = %d AND user_id = %d
        ", $project_id, $user_id));

            if ($assign && $assign->permission === 'upload') {
                $has_permission = true;
            }
        }

        if (!$has_permission) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'شما مجاز به جایگزینی فایل در این پروژه نیستید.'];
            wp_redirect(home_url('/show-project/?id=' . $project_id));
            exit;
        }
        
        // ✅ Check if user is premium or enterprise before allowing replace
        // Only check for project owner, not for assigned users
        if ($is_owner && !Hamnaghsheh_Users::is_premium_user($user_id)) {
            $access_level = Hamnaghsheh_Users::get_user_access_level($user_id);
            $message = Hamnaghsheh_File_Validator::get_upgrade_message('replace', $access_level);
            $_SESSION['alert'] = ['type' => 'error', 'message' => $message];
            wp_redirect(home_url('/show-project/?id=' . $project_id));
            exit;
        }

        // ✅ NEW: Comprehensive file validation - updated by soroush - 28/12/2025
        $file_validation = Hamnaghsheh_File_Validator::validate_file_comprehensive($file, $user_id);
        if (!$file_validation['valid']) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => $file_validation['message']];
            wp_redirect(home_url('/show-project/?id=' . $project_id));
            exit;
        }
        
        $old_file = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_files WHERE id = %d", $file_id));
        if (!$old_file) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'فایل مورد نظر یافت نشد.'];
            wp_redirect(home_url('/show-project/?id=' . $project_id));
            exit;
        }
        
        // ✅ Delete old file from MinIO
        $minio = Hamnaghsheh_Minio::instance();
        $result = $minio->delete($old_file->key_file);
        
        // ✅ Upload new file to MinIO
        $response = $minio->upload($file['tmp_name'], $file['name']);
        
        $relative_path = '';
        $key = '';
        if ($response['success']) {
            $relative_path = $response['url'];
            $key = $response['key'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => $response['error']];
            wp_redirect(home_url('/show-project/?id=' . $project_id));
            exit;
        }
        
        // ✅ Update database record with sanitized filename - updated by soroush - 28/12/2025
        $file_name = Hamnaghsheh_File_Security::sanitize_filename($file['name']);
        $wpdb->update(
            $table_files,
            [
                'file_name' => $file_name,
                'file_path' => $relative_path,
                'key_file' => $key,
                'file_size' => intval($file['size']),
                'file_type' => $file['type'],
                'uploaded_at' => current_time('mysql')
            ],
            ['id' => $file_id],
            ['%s', '%s', '%s', '%d', '%s', '%s'],
            ['%d']
        );

        // ✅ Log the replace action
        $wpdb->insert(
            $table_file_logs,
            [
                'file_id' => $file_id,
                'project_id' => $project_id,
                'user_id' => $user_id,
                'action_type' => 'replace'
            ],
            ['%d', '%d', '%d', '%s']
        );

        // ✅ Fire hook for chat plugin
        do_action('hamnaghsheh_file_action', $file_id, $project_id, $user_id, 'replace');

        $_SESSION['alert'] = ['type' => 'success', 'message' => '✅ فایل با موفقیت جایگزین شد.'];
        wp_redirect(home_url('/show-project/?id=' . $project_id));
        exit;
    }

    /**
     * Get file details by ID
     * Used by chat plugin to display file information
     * 
     * @param int $file_id
     * @return object|null File object or null if not found
     * @since 1.2.0
     */
    public static function get_file_by_id($file_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_files';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $file_id
        ));
    }

    /**
     * Get all files for a project
     * Used by chat plugin for file mention autocomplete
     * 
     * @param int $project_id
     * @return array Array of file objects
     * @since 1.2.0
     */
    public static function get_project_files($project_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'hamnaghsheh_files';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT id, file_name, file_size, file_path FROM {$table} 
             WHERE project_id = %d 
             ORDER BY uploaded_at DESC",
            $project_id
        ));
    }
}