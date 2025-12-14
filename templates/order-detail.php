<?php
if (!defined('ABSPATH'))
    exit;

$status_badge_class = Hamnaghsheh_Orders::get_status_badge_class($order->status);
$status_label = Hamnaghsheh_Orders::get_status_label($order->status);
?>

<div class="wrap hamnaghsheh-order-detail p-5 lg:p-10" dir="rtl">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="font-black text-2xl xl:text-3xl mb-2 text-[#09375B]">
                        سفارش #<?php echo esc_html($order->order_number); ?>
                    </h1>
                    <p class="text-gray-600">
                        ثبت شده در <?php echo date_i18n('Y/m/d - H:i', strtotime($order->created_at)); ?>
                    </p>
                </div>
                <span class="<?php echo esc_attr($status_badge_class); ?> px-4 py-2 rounded-full text-sm font-semibold">
                    <?php echo esc_html($status_label); ?>
                </span>
            </div>
            <a href="<?php echo site_url('/my-orders/'); ?>" 
               class="inline-block text-blue-600 hover:text-blue-800 text-sm">
                ← بازگشت به لیست سفارش‌ها
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Order Summary -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4 text-[#09375B]">خلاصه سفارش</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-600 mb-1">خدمت درخواستی:</div>
                            <div class="font-semibold"><?php echo $service ? esc_html($service->service_name_fa) : esc_html($order->service_type); ?></div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600 mb-1">تعداد:</div>
                            <div class="font-semibold"><?php echo esc_html($order->requested_quantity); ?> جلسه</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600 mb-1">قیمت هر جلسه:</div>
                            <div class="font-semibold"><?php echo number_format($order->requested_price_per_session, 0, '.', ','); ?> تومان</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600 mb-1">مجموع:</div>
                            <div class="font-semibold text-green-600"><?php echo number_format($order->requested_total_price, 0, '.', ','); ?> تومان</div>
                        </div>
                    </div>
                </div>

                <!-- Admin Re-estimation -->
                <?php if ($order->admin_estimated_total_price) : ?>
                <div class="bg-orange-50 border border-orange-200 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4 text-orange-800">برآورد کارشناسی</h2>
                    <div class="bg-white rounded p-4 mb-4">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-right py-2"></th>
                                    <th class="text-right py-2">درخواست شما</th>
                                    <th class="text-right py-2">پیشنهاد کارشناس</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b">
                                    <td class="py-2 font-semibold">نوع خدمت:</td>
                                    <td class="py-2"><?php echo $service ? esc_html($service->service_name_fa) : ''; ?></td>
                                    <td class="py-2">
                                        <?php 
                                        $admin_service = Hamnaghsheh_Services::get_service_by_key($order->admin_estimated_service_type);
                                        echo $admin_service ? esc_html($admin_service->service_name_fa) : '';
                                        ?>
                                    </td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-2 font-semibold">تعداد:</td>
                                    <td class="py-2"><?php echo esc_html($order->requested_quantity); ?> جلسه</td>
                                    <td class="py-2"><?php echo esc_html($order->admin_estimated_quantity); ?> جلسه</td>
                                </tr>
                                <tr>
                                    <td class="py-2 font-semibold">مبلغ نهایی:</td>
                                    <td class="py-2"><?php echo number_format($order->requested_total_price, 0, '.', ','); ?> تومان</td>
                                    <td class="py-2 text-green-600 font-bold"><?php echo number_format($order->admin_estimated_total_price, 0, '.', ','); ?> تومان</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($order->admin_notes) : ?>
                    <div class="bg-white rounded p-4 mb-4">
                        <div class="text-sm font-semibold text-gray-700 mb-2">توضیحات کارشناس:</div>
                        <p class="text-gray-700"><?php echo nl2br(esc_html($order->admin_notes)); ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($order->status == 'quoted') : ?>
                    <div class="flex gap-3">
                        <button id="accept-quote-btn" data-order-id="<?php echo $order->id; ?>"
                                class="flex-1 bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition-all">
                            تایید و ادامه
                        </button>
                        <button id="reject-quote-btn" 
                                class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition-all">
                            عدم تایید
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Order Information -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4 text-[#09375B]">اطلاعات سفارش</h2>
                    <div class="space-y-3">
                        <div>
                            <div class="text-sm text-gray-600 mb-1">آدرس:</div>
                            <div><?php echo nl2br(esc_html($order->address)); ?></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-gray-600 mb-1">متراژ:</div>
                                <div><?php echo esc_html($order->area_size); ?> متر مربع</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600 mb-1">شماره تماس:</div>
                                <div><?php echo esc_html($order->phone); ?></div>
                            </div>
                        </div>
                        <?php if ($order->special_requirements) : ?>
                        <div>
                            <div class="text-sm text-gray-600 mb-1">نیازمندیهای ویژه:</div>
                            <div><?php echo nl2br(esc_html($order->special_requirements)); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Messages -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4 text-[#09375B]">پیام‌ها و گفتگو</h2>
                    <?php include HAMNAGHSHEH_DIR . 'templates/parts/order-messages.php'; ?>
                </div>

                <!-- Payment Section -->
                <?php if ($order->status == 'awaiting_payment') : ?>
                <div class="bg-green-50 border border-green-200 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4 text-green-800">پرداخت</h2>
                    <div class="mb-4">
                        <div class="text-sm text-gray-600 mb-1">مبلغ قابل پرداخت:</div>
                        <div class="text-3xl font-bold text-green-600">
                            <?php 
                            $payment_amount = $order->admin_estimated_total_price ? $order->admin_estimated_total_price : $order->requested_total_price;
                            echo number_format($payment_amount, 0, '.', ','); 
                            ?> تومان
                        </div>
                    </div>
                    <a href="https://hamnaghsheh.ir/pay-with-card/" 
                       class="inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded transition-all">
                        پرداخت آنلاین
                    </a>
                    <p class="text-sm text-gray-600 mt-4">
                        پس از پرداخت، رسید خود را از طریق بخش پیام‌ها ارسال کنید.
                    </p>
                </div>
                <?php endif; ?>

                <!-- Linked Project -->
                <?php if ($order->project_id) : ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4 text-blue-800">پروژه مرتبط</h2>
                    <p class="mb-4">پروژه شما ایجاد شده است و می‌توانید فایل‌های نقشه‌برداری را مشاهده کنید.</p>
                    <a href="<?php echo site_url('/project-show/?id=' . $order->project_id); ?>" 
                       class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-all">
                        مشاهده فایل‌های پروژه
                    </a>
                </div>
                <?php endif; ?>

            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Actions -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold mb-4 text-[#09375B]">عملیات</h3>
                    <div class="space-y-2">
                        <?php if ($order->status == 'pending') : ?>
                        <button class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-all">
                            ویرایش سفارش
                        </button>
                        <?php endif; ?>
                        
                        <?php if (!in_array($order->status, array('completed', 'cancelled'))) : ?>
                        <button id="cancel-order-btn" data-order-id="<?php echo $order->id; ?>"
                                class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition-all">
                            لغو سفارش
                        </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Activity Timeline -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold mb-4 text-[#09375B]">تاریخچه فعالیت</h3>
                    <?php include HAMNAGHSHEH_DIR . 'templates/parts/order-activity.php'; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Accept quote
    $('#accept-quote-btn').on('click', function() {
        if (!confirm('آیا از تایید این برآورد اطمینان دارید؟')) {
            return;
        }
        
        var orderId = $(this).data('order-id');
        
        $.ajax({
            url: hamnaghsheh_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hamnaghsheh_accept_quote',
                nonce: hamnaghsheh_ajax.nonce,
                order_id: orderId
            },
            beforeSend: function() {
                $('#accept-quote-btn').prop('disabled', true).text('در حال پردازش...');
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                    $('#accept-quote-btn').prop('disabled', false).text('تایید و ادامه');
                }
            }
        });
    });

    // Cancel order
    $('#cancel-order-btn').on('click', function() {
        if (!confirm('آیا از لغو این سفارش اطمینان دارید؟')) {
            return;
        }
        
        var orderId = $(this).data('order-id');
        
        $.ajax({
            url: hamnaghsheh_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'hamnaghsheh_cancel_order',
                nonce: hamnaghsheh_ajax.nonce,
                order_id: orderId
            },
            beforeSend: function() {
                $('#cancel-order-btn').prop('disabled', true).text('در حال پردازش...');
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                } else {
                    alert(response.data.message);
                    $('#cancel-order-btn').prop('disabled', false).text('لغو سفارش');
                }
            }
        });
    });

    // Mark messages as read
    $.ajax({
        url: hamnaghsheh_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'hamnaghsheh_mark_messages_read',
            nonce: hamnaghsheh_ajax.nonce,
            order_id: <?php echo $order->id; ?>
        }
    });
});
</script>
