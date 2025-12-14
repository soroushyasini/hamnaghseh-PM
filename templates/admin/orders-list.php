<?php
if (!defined('ABSPATH'))
    exit;
?>

<div class="wrap" dir="rtl">
    <h1 class="wp-heading-inline">مدیریت سفارش‌ها</h1>
    <a href="<?php echo admin_url('admin.php?page=hamnaghsheh-services'); ?>" class="page-title-action">تنظیمات خدمات</a>
    <hr class="wp-header-end">

    <!-- Filters -->
    <div class="tablenav top" style="margin: 20px 0;">
        <form method="get" style="display: inline-flex; gap: 10px; align-items: center;">
            <input type="hidden" name="page" value="hamnaghsheh-orders">
            
            <select name="status" style="height: 32px;">
                <option value="">همه وضعیت‌ها</option>
                <option value="pending" <?php selected($status_filter, 'pending'); ?>>در انتظار بررسی</option>
                <option value="reviewed" <?php selected($status_filter, 'reviewed'); ?>>در حال کارشناسی</option>
                <option value="quoted" <?php selected($status_filter, 'quoted'); ?>>برآورد ارسال شده</option>
                <option value="user_accepted" <?php selected($status_filter, 'user_accepted'); ?>>تایید شده</option>
                <option value="awaiting_payment" <?php selected($status_filter, 'awaiting_payment'); ?>>در انتظار پرداخت</option>
                <option value="paid" <?php selected($status_filter, 'paid'); ?>>پرداخت شده</option>
                <option value="in_progress" <?php selected($status_filter, 'in_progress'); ?>>در حال انجام</option>
                <option value="completed" <?php selected($status_filter, 'completed'); ?>>تکمیل شده</option>
                <option value="cancelled" <?php selected($status_filter, 'cancelled'); ?>>لغو شده</option>
            </select>

            <select name="service_type" style="height: 32px;">
                <option value="">همه خدمات</option>
                <option value="half_day" <?php selected($service_filter, 'half_day'); ?>>نقشه برداری نیم روزه</option>
                <option value="full_day" <?php selected($service_filter, 'full_day'); ?>>نقشه برداری تمام روزه</option>
            </select>

            <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="جستجو...">
            
            <button type="submit" class="button">فیلتر</button>
            
            <?php if ($status_filter || $service_filter || $search) : ?>
                <a href="<?php echo admin_url('admin.php?page=hamnaghsheh-orders'); ?>" class="button">پاک کردن فیلترها</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Orders Table -->
    <?php if (empty($orders)) : ?>
        <div class="notice notice-info">
            <p>سفارشی یافت نشد.</p>
        </div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>شماره سفارش</th>
                    <th>کاربر</th>
                    <th>خدمت</th>
                    <th>تعداد درخواستی</th>
                    <th>تعداد برآوردی</th>
                    <th>وضعیت</th>
                    <th>مبلغ نهایی</th>
                    <th>تاریخ</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order) : 
                    $user = get_userdata($order->user_id);
                    $service = Hamnaghsheh_Services::get_service_by_key($order->service_type);
                    $unread_count = Hamnaghsheh_Orders::get_unread_count($order->id, true);
                    $status_badge_class = Hamnaghsheh_Orders::get_status_badge_class($order->status);
                    $status_label = Hamnaghsheh_Orders::get_status_label($order->status);
                ?>
                <tr>
                    <td>
                        <strong>
                            <a href="<?php echo admin_url('admin.php?page=hamnaghsheh-order-detail&order_id=' . $order->id); ?>">
                                #<?php echo esc_html($order->order_number); ?>
                            </a>
                        </strong>
                        <?php if ($unread_count > 0) : ?>
                            <span class="dashicons dashicons-email" style="color: red;" title="<?php echo $unread_count; ?> پیام جدید"></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($user) : ?>
                            <a href="<?php echo admin_url('user-edit.php?user_id=' . $user->ID); ?>">
                                <?php echo esc_html($user->display_name); ?>
                            </a>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $service ? esc_html($service->service_name_fa) : esc_html($order->service_type); ?></td>
                    <td><?php echo esc_html($order->requested_quantity); ?></td>
                    <td>
                        <?php 
                        if ($order->admin_estimated_quantity) {
                            echo esc_html($order->admin_estimated_quantity);
                            if ($order->admin_estimated_quantity != $order->requested_quantity) {
                                echo ' <span style="color: orange;">⚠</span>';
                            }
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td>
                        <span style="display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;" class="<?php echo esc_attr($status_badge_class); ?>">
                            <?php echo esc_html($status_label); ?>
                        </span>
                    </td>
                    <td>
                        <?php 
                        $final_price = $order->admin_estimated_total_price ? $order->admin_estimated_total_price : $order->requested_total_price;
                        echo number_format($final_price, 0, '.', ','); 
                        ?> تومان
                    </td>
                    <td><?php echo date_i18n('Y/m/d H:i', strtotime($order->created_at)); ?></td>
                    <td>
                        <a href="<?php echo admin_url('admin.php?page=hamnaghsheh-order-detail&order_id=' . $order->id); ?>" class="button button-small">
                            مشاهده
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
.wp-list-table td, .wp-list-table th {
    text-align: right;
}
</style>
