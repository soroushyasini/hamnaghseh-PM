<?php
if (!defined('ABSPATH'))
    exit;
?>

<div class="wrap" dir="rtl">
    <h1>مدیریت خدمات نقشه‌برداری</h1>
    <hr class="wp-header-end">

    <div style="margin-top: 20px;">
        <?php if (empty($services)) : ?>
            <div class="notice notice-info">
                <p>خدمتی یافت نشد.</p>
            </div>
        <?php else : ?>
            <?php foreach ($services as $service) : ?>
                <div class="postbox" style="margin-bottom: 20px;">
                    <div class="postbox-header">
                        <h2><?php echo esc_html($service->service_name_fa); ?></h2>
                    </div>
                    <div class="inside">
                        <form class="service-form" data-service-id="<?php echo $service->id; ?>">
                            <table class="form-table">
                                <tr>
                                    <th>کد خدمت:</th>
                                    <td>
                                        <code><?php echo esc_html($service->service_key); ?></code>
                                    </td>
                                </tr>
                                <tr>
                                    <th>نام فارسی:</th>
                                    <td>
                                        <input type="text" name="service_name_fa" value="<?php echo esc_attr($service->service_name_fa); ?>" 
                                               style="width: 100%; max-width: 400px;">
                                    </td>
                                </tr>
                                <tr>
                                    <th>قیمت هر جلسه (تومان):</th>
                                    <td>
                                        <input type="number" name="price_per_session" value="<?php echo esc_attr($service->price_per_session); ?>" 
                                               style="width: 200px;" step="1000">
                                    </td>
                                </tr>
                                <tr>
                                    <th>توضیحات:</th>
                                    <td>
                                        <textarea name="description" rows="3" style="width: 100%; max-width: 600px;"><?php echo esc_textarea($service->description); ?></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th>تصویر:</th>
                                    <td>
                                        <div style="margin-bottom: 10px;">
                                            <img src="<?php echo esc_url($service->image_url); ?>" 
                                                 style="max-width: 300px; max-height: 200px; border: 1px solid #ddd; padding: 5px;">
                                        </div>
                                        <input type="text" name="image_url" value="<?php echo esc_attr($service->image_url); ?>" 
                                               style="width: 100%; max-width: 500px;" placeholder="URL تصویر">
                                        <p class="description">URL تصویر را وارد کنید یا از کتابخانه رسانه آپلود کنید.</p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>وضعیت:</th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="is_active" value="1" <?php checked($service->is_active, 1); ?>>
                                            فعال
                                        </label>
                                    </td>
                                </tr>
                            </table>
                            
                            <p class="submit">
                                <button type="submit" class="button button-primary">ذخیره تغییرات</button>
                            </p>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var nonce = '<?php echo wp_create_nonce('hamnaghsheh_ajax_nonce'); ?>';

    $('.service-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var serviceId = form.data('service-id');
        var formData = form.serializeArray();
        
        // Add service_id and checkbox handling
        var data = {
            action: 'hamnaghsheh_admin_save_service',
            nonce: nonce,
            service_id: serviceId
        };
        
        $.each(formData, function(i, field) {
            data[field.name] = field.value;
        });
        
        // Handle checkbox
        if (!data.is_active) {
            data.is_active = 0;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: data,
            beforeSend: function() {
                form.find('button[type="submit"]').prop('disabled', true).text('در حال ذخیره...');
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                }
                form.find('button[type="submit"]').prop('disabled', false).text('ذخیره تغییرات');
            },
            error: function() {
                alert('خطا در ارتباط با سرور.');
                form.find('button[type="submit"]').prop('disabled', false).text('ذخیره تغییرات');
            }
        });
    });
});
</script>
