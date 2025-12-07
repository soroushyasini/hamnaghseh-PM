<?php
if (!defined('ABSPATH'))
    exit;

/**
 * File Validator Class
 * Handles file type restrictions and storage quota validation
 * Created by soroush - 12/02/2025
 */
class Hamnaghsheh_File_Validator
{
    /**
     * Get allowed file extensions based on user access level
     * 
     * @param string $access_level User's access level (free, premium, enterprise)
     * @return array Array of allowed extensions
     */
    public static function get_allowed_extensions($access_level)
    {
        $extensions = [
            'free' => [], // Free users cannot upload any files
            'premium' => ['dwg', 'dxf', 'txt'],
            'enterprise' => ['dwg', 'dxf', 'txt', 'pdf', 'png', 'jpg', 'jpeg']
        ];

        return isset($extensions[$access_level]) ? $extensions[$access_level] : [];
    }

    /**
     * Check if user can upload files
     * 
     * @param int|null $user_id User ID to check
     * @return array ['can_upload' => bool, 'message' => string, 'access_level' => string]
     */
    public static function can_user_upload($user_id = null)
    {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }

        $access_level = Hamnaghsheh_Users::get_user_access_level($user_id);

        if ($access_level === 'free') {
            return [
                'can_upload' => false,
                'message' => '⚠️ کاربران رایگان امکان آپلود فایل ندارند. شما فقط می‌توانید به پروژه‌های دیگران دعوت شوید و فایل‌ها را مشاهده کنید. برای آپلود فایل، لطفاً به پلن شخصی ارتقا دهید.',
                'access_level' => $access_level
            ];
        }

        return [
            'can_upload' => true,
            'message' => '',
            'access_level' => $access_level
        ];
    }

    /**
     * Validate file extension against user's allowed formats
     * 
     * @param string $filename File name to validate
     * @param int|null $user_id User ID to check
     * @return array ['valid' => bool, 'message' => string, 'extension' => string]
     */
    public static function validate_file_type($filename, $user_id = null)
    {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }

        $access_level = Hamnaghsheh_Users::get_user_access_level($user_id);
        $allowed_extensions = self::get_allowed_extensions($access_level);
        
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Check if user can upload at all
        if (empty($allowed_extensions)) {
            return [
                'valid' => false,
                'message' => '⚠️ کاربران رایگان امکان آپلود فایل ندارند. برای ارتقا به پلن شخصی با پشتیبانی تماس بگیرید.',
                'extension' => $file_ext
            ];
        }

        // Check if file extension is allowed
        if (!in_array($file_ext, $allowed_extensions)) {
            $allowed_str = implode(', ', array_map('strtoupper', $allowed_extensions));
            
            if ($access_level === 'premium') {
                return [
                    'valid' => false,
                    'message' => sprintf(
                        '⚠️ فرمت فایل %s برای کاربران شخصی مجاز نیست. فرمت‌های مجاز: %s. برای آپلود فایل‌های PDF, PNG, JPG به پلن سازمانی ارتقا دهید.',
                        strtoupper($file_ext),
                        $allowed_str
                    ),
                    'extension' => $file_ext
                ];
            }

            return [
                'valid' => false,
                'message' => sprintf(
                    '⚠️ فرمت فایل %s مجاز نیست. فرمت‌های مجاز برای شما: %s',
                    strtoupper($file_ext),
                    $allowed_str
                ),
                'extension' => $file_ext
            ];
        }

        return [
            'valid' => true,
            'message' => '',
            'extension' => $file_ext
        ];
    }

    /**
     * Check storage quota for user
     * 
     * @param int $project_id Project ID
     * @param int $new_file_size Size of new file in bytes
     * @param int|null $owner_id Project owner ID (if different from current user)
     * @return array ['valid' => bool, 'message' => string, 'used_space' => int, 'total_space' => int, 'remaining' => int]
     */
    public static function check_storage_quota($project_id, $new_file_size, $owner_id = null)
    {
        global $wpdb;
        
        if ($owner_id === null) {
            // Get project owner
            $owner_id = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM {$wpdb->prefix}hamnaghsheh_projects WHERE id = %d",
                $project_id
            ));
        }

        // Get owner's storage info
        $storage_info = Hamnaghsheh_Users::get_user_storage_info($owner_id);
        $total_space = intval($storage_info['storage_limit']);
        
        // Free users have 0 storage
        if ($total_space === 0) {
            return [
                'valid' => false,
                'message' => '⚠️ این کاربر فضای ذخیره‌سازی ندارد. لطفاً برای آپلود فایل، ابتدا پلن خود را ارتقا دهید.',
                'used_space' => 0,
                'total_space' => 0,
                'remaining' => 0
            ];
        }

        // Calculate used space for ALL projects of this owner
        $used_space = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(f.file_size), 0) 
            FROM {$wpdb->prefix}hamnaghsheh_files AS f
            INNER JOIN {$wpdb->prefix}hamnaghsheh_projects AS p ON f.project_id = p.id
            WHERE p.user_id = %d",
            $owner_id
        ));
        
        if (!$used_space) {
            $used_space = 0;
        }

        $remaining = $total_space - $used_space;

        // Check if new file fits
        if (($used_space + $new_file_size) > $total_space) {
            return [
                'valid' => false,
                'message' => sprintf(
                    '⚠️ فضای ذخیره‌سازی کافی نیست. استفاده شده: %s از %s. فایل جدید: %s. لطفاً فایل‌های قدیمی را حذف کنید یا پلن خود را ارتقا دهید.',
                    size_format($used_space),
                    size_format($total_space),
                    size_format($new_file_size)
                ),
                'used_space' => $used_space,
                'total_space' => $total_space,
                'remaining' => $remaining
            ];
        }

        return [
            'valid' => true,
            'message' => '',
            'used_space' => $used_space,
            'total_space' => $total_space,
            'remaining' => $remaining
        ];
    }

    /**
     * Get human-readable list of allowed formats for user
     * 
     * @param int|null $user_id User ID to check
     * @return string Comma-separated list of allowed formats
     */
    public static function get_allowed_formats_text($user_id = null)
    {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }

        $access_level = Hamnaghsheh_Users::get_user_access_level($user_id);
        $extensions = self::get_allowed_extensions($access_level);

        if (empty($extensions)) {
            return 'هیچ فرمتی (کاربر رایگان)';
        }

        return implode(', ', array_map('strtoupper', $extensions));
    }

    /**
     * Get upgrade message for restricted features
     * 
     * @param string $feature Feature name (upload, delete, replace, archive)
     * @param string $current_level Current user access level
     * @return string Upgrade message
     */
    public static function get_upgrade_message($feature, $current_level = 'free')
    {
        $messages = [
            'upload' => [
                'free' => '⚠️ آپلود فایل فقط برای کاربران شخصی و سازمانی امکان‌پذیر است. برای ارتقا با پشتیبانی تماس بگیرید.',
                'premium' => 'شما می‌توانید فایل‌های DWG, DXF, TXT آپلود کنید. برای فرمت‌های بیشتر به پلن سازمانی ارتقا دهید.'
            ],
            'delete' => [
                'free' => '⚠️ حذف فایل فقط برای کاربران شخصی و سازمانی امکان‌پذیر است. برای ارتقا با پشتیبانی تماس بگیرید.'
            ],
            'replace' => [
                'free' => '⚠️ جایگزینی فایل فقط برای کاربران شخصی و سازمانی امکان‌پذیر است. برای ارتقا با پشتیبانی تماس بگیرید.'
            ],
            'archive' => [
                'free' => '⚠️ آرشیو پروژه فقط برای کاربران شخصی و سازمانی امکان‌پذیر است. برای ارتقا با پشتیبانی تماس بگیرید.'
            ]
        ];

        if (isset($messages[$feature][$current_level])) {
            return $messages[$feature][$current_level];
        }

        return '⚠️ این امکان برای شما محدود است. برای ارتقا با پشتیبانی تماس بگیرید.';
    }
}