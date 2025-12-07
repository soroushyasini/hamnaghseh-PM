<?php
if (!defined('ABSPATH'))
    exit;

class Hamnaghsheh_User_Settings
{

    private $table;

    public function __construct()
    {
        global $wpdb;
        $this->table = $wpdb->prefix . 'hamnaghsheh_users';

        add_action('show_user_profile', [$this, 'render_fields']);
        add_action('edit_user_profile', [$this, 'render_fields']);

        add_action('personal_options_update', [$this, 'save_fields']);
        add_action('edit_user_profile_update', [$this, 'save_fields']);
    }

    /**
     * واکشی داده کاربر از جدول سفارشی
     */
    private function get_user_data($user_id)
    {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE user_id = %d", $user_id),
            ARRAY_A
        );
    }

    /**
     * نمایش فیلدها در پروفایل کاربر
     * Updated by soroush - 12/02/2025 - Added enterprise level
     */
    public function render_fields($user)
    {
        $data = $this->get_user_data($user->ID);
        $active = isset($data['active']) ? (bool) $data['active'] : false;
        $storage_limit = isset($data['storage_limit']) ? esc_attr($data['storage_limit']) : '0';
        $access_level = isset($data['access_level']) ? esc_attr($data['access_level']) : 'free';
        
        // ✅ Calculate storage in MB/GB for better display
        $storage_mb = round($storage_limit / 1048576, 2); // Convert bytes to MB
        $storage_gb = round($storage_limit / 1073741824, 2); // Convert bytes to GB
        ?>
        <hr />
        <h2 style="color: #fff;font-weight: bold;background: rgba(9, 55, 91, 1);padding: 10px;">تنظیمات اختصاصی کاربر در هم نقشه</h2>
        <table class="form-table" role="presentation">

            <tr>
                <th><label for="ham_active">وضعیت فعال</label></th>
                <td>
                    <label>
                        <input type="checkbox" name="ham_active" id="ham_active" value="1" <?php checked($active, true); ?> />
                        <strong>فعال</strong>
                    </label>
                    <p class="description">
                        ⚠️ اگر غیرفعال باشد، کاربر نمی‌تواند به سیستم دسترسی داشته باشد.<br>
                        💡 کاربران جدید به طور پیش‌فرض غیرفعال هستند و نیاز به تایید شما دارند.
                    </p>
                </td>
            </tr>

            <tr>
                <th><label for="ham_access_level">سطح دسترسی</label></th>
                <td>
                    <select name="ham_access_level" id="ham_access_level" style="width: 300px;">
                        <option value="free" <?php selected($access_level, 'free'); ?>>
                            🆓 رایگان (فقط مشاهده - بدون فضای ذخیره‌سازی)
                        </option>
                        <option value="premium" <?php selected($access_level, 'premium'); ?>>
                            ⭐ شخصی (100 مگابایت - فرمت‌های: dwg, dxf, txt)
                        </option>
                        <option value="enterprise" <?php selected($access_level, 'enterprise'); ?>>
                            🏢 سازمانی (1 گیگابایت - تمام فرمت‌ها: dwg, dxf, txt, pdf, png, jpg)
                        </option>
                    </select>
                    <p class="description">
                        <strong>توضیحات پلن‌ها:</strong><br>
                        • <strong>رایگان:</strong> فقط می‌تواند به پروژه‌های دیگران دعوت شود و فایل‌ها را مشاهده کند<br>
                        • <strong>شخصی:</strong> آپلود/حذف/جایگزینی فایل + آرشیو پروژه (فرمت‌های dwg, dxf, txt)<br>
                        • <strong>سازمانی:</strong> تمام امکانات شخصی + فرمت‌های اضافی (pdf, png, jpg)
                    </p>
                </td>
            </tr>

            <tr>
                <th><label for="ham_storage_limit">سقف فضای ذخیره‌سازی</label></th>
                <td>
                    <input type="number" name="ham_storage_limit" id="ham_storage_limit" value="<?php echo $storage_limit; ?>"
                        class="regular-text" min="0" step="1048576" />
                    <p class="description">
                        <strong>راهنمای تنظیم فضا:</strong><br>
                        • فعلی: <strong><?php echo $storage_mb; ?> MB</strong> (<?php echo $storage_gb; ?> GB)<br>
                        • رایگان: <code>0</code> بایت (بدون فضا)<br>
                        • شخصی: <code>104857600</code> بایت (100 مگابایت)<br>
                        • سازمانی: <code>1073741824</code> بایت (1 گیگابایت)<br>
                        <br>
                        💡 <strong>میانبرها:</strong><br>
                        <button type="button" class="button" onclick="document.getElementById('ham_storage_limit').value='0'">بدون فضا</button>
                        <button type="button" class="button" onclick="document.getElementById('ham_storage_limit').value='104857600'">100 مگابایت</button>
                        <button type="button" class="button" onclick="document.getElementById('ham_storage_limit').value='1073741824'">1 گیگابایت</button>
                        <button type="button" class="button" onclick="document.getElementById('ham_storage_limit').value='5368709120'">5 گیگابایت</button>
                    </p>
                </td>
            </tr>
        </table>

        <style>
            .form-table th {
                width: 200px;
            }
            .form-table .description {
                margin-top: 8px;
                line-height: 1.6;
            }
            .form-table .button {
                margin-right: 5px;
                margin-top: 5px;
            }
        </style>
        <?php
    }

    /**
     * ذخیره داده‌ها در جدول سفارشی
     * Updated by soroush - 12/02/2025
     */
    public function save_fields($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }

        global $wpdb;

        $active = isset($_POST['ham_active']) ? 1 : 0;
        $storage_limit = isset($_POST['ham_storage_limit']) ? intval($_POST['ham_storage_limit']) : 0;
        $access_level = isset($_POST['ham_access_level']) ? sanitize_text_field($_POST['ham_access_level']) : 'free';

        // ✅ Validate access_level
        if (!in_array($access_level, ['free', 'premium', 'enterprise'])) {
            $access_level = 'free';
        }

        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = %d", $user_id));

        if ($exists) {
            $wpdb->update(
                $this->table,
                [
                    'active' => $active,
                    'storage_limit' => $storage_limit,
                    'access_level' => $access_level
                ],
                ['user_id' => $user_id],
                ['%d', '%d', '%s'],
                ['%d']
            );
        } else {
            // ✅ If record doesn't exist, create it
            $user_info = get_userdata($user_id);
            $wpdb->insert(
                $this->table,
                [
                    'user_id' => $user_id,
                    'username' => $user_info->user_login,
                    'email' => $user_info->user_email,
                    'display_name' => $user_info->display_name,
                    'active' => $active,
                    'storage_limit' => $storage_limit,
                    'access_level' => $access_level
                ],
                ['%d', '%s', '%s', '%s', '%d', '%d', '%s']
            );
        }

        // ✅ Show admin notice after saving
        add_action('admin_notices', function() use ($access_level, $active) {
            $level_labels = [
                'free' => 'رایگان',
                'premium' => 'شخصی',
                'enterprise' => 'سازمانی'
            ];
            $status = $active ? 'فعال' : 'غیرفعال';
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>✅ تنظیمات کاربر ذخیره شد:</strong> سطح دسترسی: ' . $level_labels[$access_level] . ' | وضعیت: ' . $status . '</p>';
            echo '</div>';
        });
    }
}