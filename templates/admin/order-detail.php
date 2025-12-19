<?php
/**
 * Admin Order Detail Template - SIMPLIFIED VERSION
 * Simple form for setting final price and status after phone discussion
 */
if (!defined('ABSPATH'))
    exit;

$status_badge_class = Hamnaghsheh_Orders::get_status_badge_class($order->status);
$status_label = Hamnaghsheh_Orders::get_status_label($order->status);
?>

<div class="wrap" dir="rtl">
    <h1>جزئیات سفارش #<?php echo esc_html($order->order_number); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=hamnaghsheh-orders'); ?>" class="page-title-action">بازگشت به لیست</a>
    <hr class="wp-header-end">

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px;">
        
        <!-- Main Column -->
        <div>
            
            <!-- Customer Information -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2>👤 اطلاعات مشتری</h2>
                </div>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th>نام:</th>
                            <td>
                                <a href="<?php echo admin_url('user-edit.php?user_id=' . $user->ID); ?>">
                                    <?php echo esc_html($user->display_name); ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>ایمیل:</th>
                            <td><?php echo esc_html($user->user_email); ?></td>
                        </tr>
                        <tr>
                            <th>شماره تماس:</th>
                            <td>
                                <strong style="color: #2271b1; font-size: 16px;">
                                    <?php echo esc_html($order->phone); ?>
                                </strong>
                                <span style="color: #666; font-size: 12px; margin-right: 10px;">← برای تماس با مشتری</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Order Details -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2>📋 جزئیات سفارش</h2>
                </div>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th>خدمت:</th>
                            <td><?php echo $service ? esc_html($service->service_name_fa) : esc_html($order->service_type); ?></td>
                        </tr>
                        <tr>
                            <th>تعداد جلسات:</th>
                            <td><?php echo esc_html($order->requested_quantity); ?> جلسه</td>
                        </tr>
                        <tr>
                            <th>برآورد اولیه:</th>
                            <td><strong><?php echo number_format($order->requested_total_price, 0, '.', ','); ?> تومان</strong></td>
                        </tr>
                        <tr>
                            <th>آدرس:</th>
                            <td><?php echo nl2br(esc_html($order->address)); ?></td>
                        </tr>
                        <tr>
                            <th>متراژ:</th>
                            <td><?php echo esc_html($order->area_size); ?> متر مربع</td>
                        </tr>
                        <?php if ($order->special_requirements) : ?>
                        <tr>
                            <th>نیازمندیهای ویژه:</th>
                            <td><?php echo nl2br(esc_html($order->special_requirements)); ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if ($order->uploaded_files) : ?>
                        <tr>
                            <th>فایل‌های بارگذاری شده:</th>
                            <td>
                                <?php
                                $files = explode(',', $order->uploaded_files);
                                foreach ($files as $file) {
                                    if ($file) {
                                        echo '<a href="' . esc_url($file) . '" target="_blank">دانلود فایل</a><br>';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- Set Final Price -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2>💰 تنظیم قیمت نهایی</h2>
                </div>
                <div class="inside">
                    <p style="background: #fff3cd; padding: 10px; border-right: 4px solid #ffc107; margin-bottom: 20px;">
                        <strong>راهنما:</strong> بعد از تماس تلفنی با مشتری و توافق بر روی قیمت، قیمت نهایی را وارد کرده و وضعیت را تغییر دهید.
                    </p>

                    <form id="price-form" method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order->id; ?>">
                        <?php wp_nonce_field('hamnaghsheh_set_price', 'price_nonce'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th>قیمت نهایی (تومان):</th>
                                <td>
                                    <input type="number" name="final_price" id="final_price" 
                                           value="<?php echo $order->final_price ? $order->final_price : $order->requested_total_price; ?>" 
                                           style="width: 200px; font-size: 16px; font-weight: bold;"
                                           min="0" step="1000">
                                    <p class="description">قیمت توافق شده با مشتری را وارد کنید</p>
                                </td>
                            </tr>
                            <tr>
                                <th>وضعیت:</th>
                                <td>
                                    <select name="status" id="order_status" style="width: 300px;">
                                        <option value="pending" <?php selected($order->status, 'pending'); ?>>در انتظار بررسی (pending)</option>
                                        <option value="awaiting_payment" <?php selected($order->status, 'awaiting_payment'); ?>>آماده پرداخت (awaiting_payment)</option>
                                        <option value="paid" <?php selected($order->status, 'paid'); ?>>پرداخت شده (paid)</option>
                                        <option value="in_progress" <?php selected($order->status, 'in_progress'); ?>>در حال انجام (in_progress)</option>
                                        <option value="completed" <?php selected($order->status, 'completed'); ?>>تکمیل شده (completed)</option>
                                        <option value="cancelled" <?php selected($order->status, 'cancelled'); ?>>لغو شده (cancelled)</option>
                                    </select>
                                    <p class="description">
                                        <strong>مراحل:</strong> pending → awaiting_payment → paid → in_progress → completed
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>یادداشت داخلی (پنهان از مشتری):</th>
                                <td>
                                    <textarea name="admin_notes" rows="4" style="width: 100%;"><?php echo esc_textarea($order->admin_notes); ?></textarea>
                                    <p class="description">این یادداشت فقط برای ادمین قابل مشاهده است</p>
                                </td>
                            </tr>
                        </table>

                        <p class="submit">
                            <button type="submit" class="button button-primary button-large" id="save-changes-btn">
                                💾 ذخیره تغییرات
                            </button>
                        </p>
                    </form>
                </div>
            </div>

            <!-- Project Management -->
            <?php if ($order->status == 'paid' && !$order->project_id) : ?>
            <div class="postbox">
                <div class="postbox-header">
                    <h2>📁 ایجاد پروژه</h2>
                </div>
                <div class="inside">
                    <p>پرداخت تایید شده است. می‌توانید پروژه را ایجاد کنید.</p>
                    <button type="button" class="button button-primary" id="create-project-btn" data-order-id="<?php echo $order->id; ?>">
                        ایجاد پروژه
                    </button>
                </div>
            </div>
            <?php elseif ($order->project_id) : ?>
            <div class="postbox">
                <div class="postbox-header">
                    <h2>📁 پروژه مرتبط</h2>
                </div>
                <div class="inside">
                    <p>پروژه ایجاد شده است.</p>
                    <a href="<?php echo site_url('/project-show/?id=' . $order->project_id); ?>" class="button" target="_blank">
                        مشاهده پروژه
                    </a>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Sidebar -->
        <div>
            
            <!-- Quick Info -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2>اطلاعات سریع</h2>
                </div>
                <div class="inside">
                    <p><strong>شماره سفارش:</strong><br><?php echo esc_html($order->order_number); ?></p>
                    <p><strong>تاریخ ثبت:</strong><br><?php echo date_i18n('Y/m/d - H:i', strtotime($order->created_at)); ?></p>
                    <p><strong>آخرین بروزرسانی:</strong><br><?php echo date_i18n('Y/m/d - H:i', strtotime($order->updated_at)); ?></p>
                    <p>
                        <strong>وضعیت:</strong><br>
                        <span style="display: inline-block; padding: 6px 12px; border-radius: 4px; font-weight: 600; margin-top: 5px;" class="<?php echo esc_attr($status_badge_class); ?>">
                            <?php echo esc_html($status_label); ?>
                        </span>
                    </p>
                </div>
            </div>

            <!-- Activity Timeline -->
            <?php if ($activity && count($activity) > 0) : ?>
            <div class="postbox">
                <div class="postbox-header">
                    <h2>📅 تاریخچه فعالیت</h2>
                </div>
                <div class="inside">
                    <?php include HAMNAGHSHEH_DIR . 'templates/parts/order-activity.php'; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
// Ensure hamnaghsheh_ajax is available (fallback if external script loads late)
if (typeof hamnaghsheh_ajax === 'undefined') {
    var hamnaghsheh_ajax = {
        ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
        nonce: '<?php echo wp_create_nonce('hamnaghsheh_admin_nonce'); ?>'
    };
}

jQuery(document).ready(function($) {
    // Save changes
    $('#price-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'hamnaghsheh_admin_set_price',
            nonce: hamnaghsheh_ajax.nonce,
            order_id: $('input[name="order_id"]').val(),
            final_price: $('#final_price').val(),
            status: $('#order_status').val(),
            admin_notes: $('textarea[name="admin_notes"]').val()
        };
        
        $.ajax({
            url: hamnaghsheh_ajax.ajax_url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                $('#save-changes-btn').prop('disabled', true).text('در حال ذخیره...');
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                    $('#save-changes-btn').prop('disabled', false).text('💾 ذخیره تغییرات');
                }
            },
            error: function() {
                alert('خطا در ارتباط با سرور');
                $('#save-changes-btn').prop('disabled', false).text('💾 ذخیره تغییرات');
            }
        });
    });

    // Create project
    $('#create-project-btn').on('click', function() {
        if (!confirm('آیا از ایجاد پروژه اطمینان دارید؟')) {
            return;
        }
        
        var orderId = $(this).data('order-id');
        
        $.ajax({
            url: hamnaghsheh_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hamnaghsheh_admin_create_project',
                nonce: hamnaghsheh_ajax.nonce,
                order_id: orderId
            },
            beforeSend: function() {
                $('#create-project-btn').prop('disabled', true).text('در حال ایجاد...');
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                    $('#create-project-btn').prop('disabled', false).text('ایجاد پروژه');
                }
            }
        });
    });
});
</script>

<style>
.form-table th {
    width: 200px;
    padding: 15px 10px 15px 0;
}
.form-table td {
    padding: 15px 10px;
}
</style>
