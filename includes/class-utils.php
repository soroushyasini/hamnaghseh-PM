<?php
if (!defined('ABSPATH'))
    exit;

/**
 * Utility Helper Class
 * Updated by soroush - 12/02/2025
 */
class Hamnaghsheh_Utils
{
    /**
     * Get project type label in Persian
     * 
     * @param string $value Project type value
     * @return string Persian label
     */
    public static function get_project_type_label($value)
    {
        $labels = [
            'residential' => 'ساخت‌وساز مسکونی',
            'commercial' => 'ساخت‌وساز تجاری',
            'renovation' => 'بازسازی',
            'infrastructure' => 'زیرساخت',
            'other' => 'سایر'
        ];
        return $labels[$value] ?? 'نامشخص';
    }

    /**
     * Get access level label in Persian
     * Added by soroush - 12/02/2025
     * 
     * @param string $access_level Access level value (free, premium, enterprise)
     * @return string Persian label
     */
    public static function get_access_level_label($access_level)
    {
        $labels = [
            'free' => 'رایگان',
            'premium' => 'شخصی',
            'enterprise' => 'سازمانی'
        ];
        return $labels[$access_level] ?? 'نامشخص';
    }

    /**
     * Get access level badge HTML
     * Added by soroush - 12/02/2025
     * 
     * @param string $access_level Access level value
     * @return string HTML badge
     */
    public static function get_access_level_badge($access_level)
    {
        $badges = [
            'free' => '<span class="badge badge-free" style="background: #e5e7eb; color: #374151; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">🆓 رایگان</span>',
            'premium' => '<span class="badge badge-premium" style="background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">⭐ پرمیوم</span>',
            'enterprise' => '<span class="badge badge-enterprise" style="background: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">🏢 سازمانی</span>'
        ];
        return $badges[$access_level] ?? '';
    }

    /**
     * Get allowed file formats for access level
     * Added by soroush - 12/02/2025
     * 
     * @param string $access_level Access level value
     * @return string Comma-separated file formats
     */
    public static function get_allowed_formats($access_level)
    {
        $formats = [
            'free' => 'هیچ فرمتی (فقط مشاهده)',
            'premium' => 'DWG, DXF, TXT',
            'enterprise' => 'DWG, DXF, TXT, PDF, PNG, JPG'
        ];
        return $formats[$access_level] ?? 'نامشخص';
    }

    /**
     * Get storage limit label
     * Added by soroush - 12/02/2025
     * 
     * @param int $bytes Storage in bytes
     * @return string Human-readable storage size
     */
    public static function format_storage_size($bytes)
    {
        if ($bytes == 0) {
            return 'بدون فضا';
        }
        
        return size_format($bytes);
    }

    /**
     * Get storage usage percentage
     * Added by soroush - 12/02/2025
     * 
     * @param int $used_bytes Used storage in bytes
     * @param int $total_bytes Total storage in bytes
     * @return int Percentage (0-100)
     */
    public static function get_storage_percentage($used_bytes, $total_bytes)
    {
        if ($total_bytes <= 0) {
            return 0;
        }
        
        return min(100, round(($used_bytes / $total_bytes) * 100));
    }

    /**
     * Get storage progress bar HTML
     * Added by soroush - 12/02/2025
     * 
     * @param int $used_bytes Used storage
     * @param int $total_bytes Total storage
     * @return string HTML progress bar
     */
    public static function get_storage_progress_bar($used_bytes, $total_bytes)
    {
        $percentage = self::get_storage_percentage($used_bytes, $total_bytes);
        $used_human = self::format_storage_size($used_bytes);
        $total_human = self::format_storage_size($total_bytes);
        
        // Determine color based on usage
        if ($percentage >= 90) {
            $color = '#dc2626'; // Red
        } elseif ($percentage >= 70) {
            $color = '#f59e0b'; // Orange
        } else {
            $color = '#10b981'; // Green
        }
        
        $html = '<div class="storage-progress" style="margin: 10px 0;">';
        $html .= '<div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 13px;">';
        $html .= '<span>استفاده شده: <strong>' . $used_human . '</strong></span>';
        $html .= '<span>کل: <strong>' . $total_human . '</strong></span>';
        $html .= '</div>';
        $html .= '<div style="width: 100%; background: #e5e7eb; border-radius: 8px; height: 8px; overflow: hidden;">';
        $html .= '<div style="width: ' . $percentage . '%; background: ' . $color . '; height: 100%; transition: width 0.3s ease;"></div>';
        $html .= '</div>';
        $html .= '<div style="text-align: center; margin-top: 5px; font-size: 12px; color: #6b7280;">';
        $html .= $percentage . '% استفاده شده';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Get plan features comparison
     * Added by soroush - 12/02/2025
     * 
     * @return array Plan features data
     */
    public static function get_plan_features()
    {
        return [
            'free' => [
                'name' => 'پایه',
                'name_en' => 'Free',
                'price' => 'رایگان',
                'storage' => 0,
                'storage_label' => 'بدون فضا',
                'formats' => [],
                'formats_label' => 'فقط مشاهده',
                'share_limit' => 10,
                'can_upload' => false,
                'can_delete' => false,
                'can_replace' => false,
                'can_archive' => false,
                'can_download' => true,
                'features' => [
                    'مشاهده آنلاین' => true,
                    'دانلود' => true,
                    'اشتراک‌گذاری' => '10 نفر',
                    'آپلود فایل' => false,
                    'جایگزینی و حذف' => false,
                    'آرشیو پروژه' => false,
                ]
            ],
            'premium' => [
                'name' => 'شخصی',
                'name_en' => 'Premium',
                'price' => '1,000,000 تومان/سال',
                'storage' => 104857600, // 100 MB
                'storage_label' => '100 مگابایت',
                'formats' => ['dwg', 'dxf', 'txt'],
                'formats_label' => 'DWG, DXF, TXT',
                'share_limit' => 100,
                'can_upload' => true,
                'can_delete' => true,
                'can_replace' => true,
                'can_archive' => true,
                'can_download' => true,
                'features' => [
                    'مشاهده آنلاین' => true,
                    'دانلود' => true,
                    'اشتراک‌گذاری' => '100 نفر',
                    'آپلود فایل' => true,
                    'جایگزینی و حذف' => true,
                    'آرشیو پروژه' => true,
                ]
            ],
            'enterprise' => [
                'name' => 'سازمانی',
                'name_en' => 'Enterprise',
                'price' => '5,000,000 تومان/سال',
                'storage' => 1073741824, // 1 GB
                'storage_label' => '1 گیگابایت',
                'formats' => ['dwg', 'dxf', 'txt', 'pdf', 'png', 'jpg', 'jpeg'],
                'formats_label' => 'DWG, DXF, TXT, PDF, PNG, JPG',
                'share_limit' => -1, // Unlimited
                'can_upload' => true,
                'can_delete' => true,
                'can_replace' => true,
                'can_archive' => true,
                'can_download' => true,
                'features' => [
                    'مشاهده آنلاین' => true,
                    'دانلود' => true,
                    'اشتراک‌گذاری' => 'نامحدود',
                    'آپلود فایل' => true,
                    'جایگزینی و حذف' => true,
                    'آرشیو پروژه' => true,
                ]
            ]
        ];
    }

    /**
     * Check if user can perform action based on plan
     * Added by soroush - 12/02/2025
     * 
     * @param string $action Action name (upload, delete, replace, archive)
     * @param string $access_level User access level
     * @return bool True if allowed
     */
    public static function can_perform_action($action, $access_level)
    {
        $plans = self::get_plan_features();
        
        if (!isset($plans[$access_level])) {
            return false;
        }
        
        $action_key = 'can_' . $action;
        
        return isset($plans[$access_level][$action_key]) ? $plans[$access_level][$action_key] : false;
    }
}