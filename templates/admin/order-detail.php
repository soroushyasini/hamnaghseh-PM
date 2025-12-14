<?php
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
                    <h2>اطلاعات مشتری</h2>
                </div>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th>نام کاربر:</th>
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
                            <th>تاریخ ثبت:</th>
                            <td><?php echo date_i18n('Y/m/d - H:i', strtotime($order->created_at)); ?></td>
                        </tr>
                        <tr>
                            <th>وضعیت فعلی:</th>
                            <td>
                                <span style="display: inline-block; padding: 6px 12px; border-radius: 4px; font-weight: 600;" class="<?php echo esc_attr($status_badge_class); ?>">
                                    <?php echo esc_html($status_label); ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Original Request -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2>درخواست اولیه مشتری</h2>
                </div>
                <div class="inside">
                    <table class="form-table">
                        <tr>
                            <th>خدمت:</th>
                            <td><?php echo $service ? esc_html($service->service_name_fa) : esc_html($order->service_type); ?></td>
                        </tr>
                        <tr>
                            <th>تعداد جلسات:</th>
                            <td><?php echo esc_html($order->requested_quantity); ?></td>
                        </tr>
                        <tr>
                            <th>قیمت هر جلسه:</th>
                            <td><?php echo number_format($order->requested_price_per_session, 0, '.', ','); ?> تومان</td>
                        </tr>
                        <tr>
                            <th>مجموع:</th>
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
                        <tr>
                            <th>شماره تماس:</th>
                            <td><?php echo esc_html($order->phone); ?></td>
                        </tr>
                        <?php if ($order->special_requirements) : ?>
                        <tr>
                            <th>نیازمندیهای ویژه:</th>
                            <td><?php echo nl2br(esc_html($order->special_requirements)); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <!-- Re-estimation Form -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2>برآورد کارشناسی</h2>
                </div>
                <div class="inside">
                    <form id="quote-form">
                        <input type="hidden" name="order_id" value="<?php echo $order->id; ?>">
                        
                        <table class="form-table">
                            <tr>
                                <th>نوع خدمت پیشنهادی:</th>
                                <td>
                                    <?php foreach ($services as $srv) : ?>
                                        <label style="display: block; margin-bottom: 8px;">
                                            <input type="radio" name="service_type" value="<?php echo esc_attr($srv->service_key); ?>" 
                                                   data-price="<?php echo esc_attr($srv->price_per_session); ?>"
                                                   <?php checked($order->admin_estimated_service_type ? $order->admin_estimated_service_type : $order->service_type, $srv->service_key); ?>>
                                            <?php echo esc_html($srv->service_name_fa); ?>
                                            (<?php echo number_format($srv->price_per_session, 0, '.', ','); ?> تومان)
                                        </label>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>تعداد جلسات پیشنهادی:</th>
                                <td>
                                    <input type="number" id="estimated-quantity" name="quantity" min="1" 
                                           value="<?php echo $order->admin_estimated_quantity ? $order->admin_estimated_quantity : $order->requested_quantity; ?>"
                                           style="width: 100px;">
                                </td>
                            </tr>
                            <tr>
                                <th>قیمت هر جلسه:</th>
                                <td>
                                    <label>
                                        <input type="radio" name="price_type" value="standard" checked>
                                        قیمت استاندارد: <span id="standard-price">0</span> تومان
                                    </label>
                                    <br>
                                    <label>
                                        <input type="radio" name="price_type" value="custom">
                                        قیمت سفارشی: 
                                        <input type="number" id="custom-price" name="custom_price" style="width: 150px;" disabled>
                                        تومان
                                    </label>
                                    <input type="hidden" id="final-price-per-session" name="price_per_session">
                                </td>
                            </tr>
                            <tr>
                                <th>مبلغ نهایی:</th>
                                <td>
                                    <strong id="total-price" style="font-size: 18px; color: green;">0 تومان</strong>
                                    <input type="hidden" id="total-price-value" name="total_price">
                                </td>
                            </tr>
                            <tr>
                                <th>توضیحات برای مشتری:</th>
                                <td>
                                    <textarea name="admin_notes" rows="4" style="width: 100%;"><?php echo esc_textarea($order->admin_notes); ?></textarea>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <button type="submit" class="button button-primary button-large">
                                ارسال برآورد به مشتری
                            </button>
                        </p>
                    </form>
                </div>
            </div>

            <!-- Messages -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2>گفتگو با مشتری</h2>
                </div>
                <div class="inside">
                    <div style="max-height: 400px; overflow-y: auto; margin-bottom: 20px; padding: 10px; background: #f5f5f5; border-radius: 4px;">
                        <?php if (empty($messages)) : ?>
                            <p style="color: #666;">پیامی وجود ندارد.</p>
                        <?php else : ?>
                            <?php foreach ($messages as $msg) : 
                                $sender = get_userdata($msg->user_id);
                                $is_admin_msg = $msg->is_admin == 1;
                            ?>
                            <div style="margin-bottom: 15px; padding: 10px; background: <?php echo $is_admin_msg ? '#e3f2fd' : '#f1f8e9'; ?>; border-radius: 4px;">
                                <div style="font-weight: bold; font-size: 12px; color: #666; margin-bottom: 5px;">
                                    <?php echo $is_admin_msg ? 'شما (ادمین)' : esc_html($sender->display_name); ?>
                                    - <?php echo date_i18n('Y/m/d H:i', strtotime($msg->created_at)); ?>
                                </div>
                                <div><?php echo nl2br(esc_html($msg->message)); ?></div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <form id="admin-message-form">
                        <input type="hidden" name="order_id" value="<?php echo $order->id; ?>">
                        <textarea name="message" rows="3" style="width: 100%; margin-bottom: 10px;" placeholder="پیام خود را بنویسید..." required></textarea>
                        <button type="submit" class="button button-secondary">ارسال پیام</button>
                    </form>
                </div>
            </div>

        </div>

        <!-- Sidebar -->
        <div>
            
            <!-- Status Management -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2>مدیریت وضعیت</h2>
                </div>
                <div class="inside">
                    <select id="order-status" style="width: 100%; margin-bottom: 10px;">
                        <option value="pending" <?php selected($order->status, 'pending'); ?>>در انتظار بررسی</option>
                        <option value="reviewed" <?php selected($order->status, 'reviewed'); ?>>در حال کارشناسی</option>
                        <option value="quoted" <?php selected($order->status, 'quoted'); ?>>برآورد ارسال شده</option>
                        <option value="user_accepted" <?php selected($order->status, 'user_accepted'); ?>>تایید شده</option>
                        <option value="awaiting_payment" <?php selected($order->status, 'awaiting_payment'); ?>>در انتظار پرداخت</option>
                        <option value="payment_uploaded" <?php selected($order->status, 'payment_uploaded'); ?>>رسید بارگذاری شده</option>
                        <option value="paid" <?php selected($order->status, 'paid'); ?>>پرداخت تایید شده</option>
                        <option value="in_progress" <?php selected($order->status, 'in_progress'); ?>>در حال انجام</option>
                        <option value="completed" <?php selected($order->status, 'completed'); ?>>تکمیل شده</option>
                        <option value="cancelled" <?php selected($order->status, 'cancelled'); ?>>لغو شده</option>
                    </select>
                    <button id="update-status-btn" class="button button-primary" style="width: 100%;">
                        بروزرسانی وضعیت
                    </button>
                </div>
            </div>

            <!-- Project Linking -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2>پیوند پروژه</h2>
                </div>
                <div class="inside">
                    <?php if ($order->project_id) : ?>
                        <p>
                            <strong>پروژه ایجاد شده:</strong><br>
                            <a href="<?php echo admin_url('admin.php?page=project-detail&id=' . $order->project_id); ?>">
                                مشاهده پروژه #<?php echo $order->project_id; ?>
                            </a>
                        </p>
                    <?php else : ?>
                        <?php if ($order->status == 'paid' || $order->status == 'in_progress') : ?>
                            <button id="create-project-btn" class="button button-secondary" style="width: 100%;">
                                ایجاد پروژه برای مشتری
                            </button>
                        <?php else : ?>
                            <p style="color: #666;">پروژه پس از پرداخت قابل ایجاد است.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="postbox">
                <div class="postbox-header">
                    <h2>تاریخچه فعالیت</h2>
                </div>
                <div class="inside">
                    <div style="max-height: 300px; overflow-y: auto;">
                        <?php if (empty($activity)) : ?>
                            <p style="color: #666;">بدون فعالیت</p>
                        <?php else : ?>
                            <?php foreach ($activity as $act) : 
                                $act_user = $act->created_by ? get_userdata($act->created_by) : null;
                                $activity_label = Hamnaghsheh_Order_Activity::get_activity_label($act->activity_type);
                            ?>
                            <div style="margin-bottom: 15px; padding-right: 10px; border-right: 3px solid <?php echo $act->is_admin ? '#2196f3' : '#4caf50'; ?>;">
                                <div style="font-weight: bold; font-size: 12px;"><?php echo esc_html($activity_label); ?></div>
                                <?php if ($act->description) : ?>
                                    <div style="font-size: 11px; color: #666;"><?php echo esc_html($act->description); ?></div>
                                <?php endif; ?>
                                <div style="font-size: 11px; color: #999; margin-top: 3px;">
                                    <?php echo date_i18n('Y/m/d H:i', strtotime($act->created_at)); ?>
                                    <?php if ($act_user) : ?>
                                        - <?php echo $act->is_admin ? 'ادمین' : esc_html($act_user->display_name); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    var nonce = '<?php echo wp_create_nonce('hamnaghsheh_ajax_nonce'); ?>';

    // Calculate price
    function calculatePrice() {
        var selectedService = $('input[name="service_type"]:checked');
        var quantity = parseInt($('#estimated-quantity').val()) || 1;
        var standardPrice = parseFloat(selectedService.data('price')) || 0;
        var priceType = $('input[name="price_type"]:checked').val();
        var pricePerSession = standardPrice;

        if (priceType === 'custom') {
            pricePerSession = parseFloat($('#custom-price').val()) || 0;
        }

        var total = quantity * pricePerSession;

        $('#standard-price').text(standardPrice.toLocaleString('fa-IR'));
        $('#total-price').text(total.toLocaleString('fa-IR') + ' تومان');
        $('#final-price-per-session').val(pricePerSession);
        $('#total-price-value').val(total);
    }

    $('input[name="service_type"], #estimated-quantity, input[name="price_type"], #custom-price').on('change', calculatePrice);

    $('input[name="price_type"]').on('change', function() {
        $('#custom-price').prop('disabled', $(this).val() !== 'custom');
        calculatePrice();
    });

    calculatePrice();

    // Submit quote
    $('#quote-form').on('submit', function(e) {
        e.preventDefault();
        
        if (!confirm('آیا از ارسال این برآورد به مشتری اطمینان دارید؟')) {
            return;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: $(this).serialize() + '&action=hamnaghsheh_admin_set_quote&nonce=' + nonce,
            beforeSend: function() {
                $('button[type="submit"]').prop('disabled', true).text('در حال ارسال...');
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                    $('button[type="submit"]').prop('disabled', false).text('ارسال برآورد به مشتری');
                }
            }
        });
    });

    // Update status
    $('#update-status-btn').on('click', function() {
        var newStatus = $('#order-status').val();
        
        if (!confirm('آیا از تغییر وضعیت سفارش اطمینان دارید؟')) {
            return;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hamnaghsheh_admin_update_status',
                nonce: nonce,
                order_id: <?php echo $order->id; ?>,
                status: newStatus
            },
            beforeSend: function() {
                $('#update-status-btn').prop('disabled', true).text('در حال پردازش...');
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                    $('#update-status-btn').prop('disabled', false).text('بروزرسانی وضعیت');
                }
            }
        });
    });

    // Send message
    $('#admin-message-form').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: $(this).serialize() + '&action=hamnaghsheh_admin_send_message&nonce=' + nonce,
            beforeSend: function() {
                $('button[type="submit"]').prop('disabled', true).text('در حال ارسال...');
            },
            success: function(response) {
                if (response.success) {
                    $('textarea[name="message"]').val('');
                    alert('پیام ارسال شد.');
                    location.reload();
                } else {
                    alert(response.data.message);
                }
                $('button[type="submit"]').prop('disabled', false).text('ارسال پیام');
            }
        });
    });

    // Create project
    $('#create-project-btn').on('click', function() {
        if (!confirm('آیا از ایجاد پروژه برای این سفارش اطمینان دارید؟')) {
            return;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'hamnaghsheh_admin_create_project',
                nonce: nonce,
                order_id: <?php echo $order->id; ?>
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
                    $('#create-project-btn').prop('disabled', false).text('ایجاد پروژه برای مشتری');
                }
            }
        });
    });
});
</script>
