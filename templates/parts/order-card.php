<?php
if (!defined('ABSPATH'))
    exit;

$status_badge_class = Hamnaghsheh_Orders::get_status_badge_class($order->status);
$status_label = Hamnaghsheh_Orders::get_status_label($order->status);
?>

<div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow border border-gray-200">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex-1">
            <div class="flex items-start gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h3 class="text-lg font-bold text-[#09375B]">
                            سفارش #<?php echo esc_html($order->order_number); ?>
                        </h3>
                        <span class="<?php echo esc_attr($status_badge_class); ?> px-3 py-1 rounded-full text-xs font-semibold">
                            <?php echo esc_html($status_label); ?>
                        </span>
                        <?php if ($unread_count > 0) : ?>
                            <span class="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                                <?php echo $unread_count; ?> پیام جدید
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600 mb-3">
                        <div>
                            <span class="font-semibold">خدمت:</span>
                            <?php echo $service ? esc_html($service->service_name_fa) : esc_html($order->service_type); ?>
                        </div>
                        <div>
                            <span class="font-semibold">تعداد:</span>
                            <?php echo esc_html($order->requested_quantity); ?> جلسه
                        </div>
                        <div>
                            <span class="font-semibold">تاریخ ثبت:</span>
                            <?php echo time_ago_persian($order->created_at); ?>
                        </div>
                        <div>
                            <span class="font-semibold">مبلغ:</span>
                            <span class="text-green-600 font-bold">
                                <?php 
                                $final_price = $order->admin_estimated_total_price ? $order->admin_estimated_total_price : $order->requested_total_price;
                                echo number_format($final_price, 0, '.', ','); 
                                ?> تومان
                            </span>
                        </div>
                    </div>

                    <?php if ($order->admin_estimated_total_price && $order->admin_estimated_total_price != $order->requested_total_price) : ?>
                        <div class="bg-orange-50 border-r-4 border-orange-400 p-2 text-sm text-orange-800 mb-2">
                            مبلغ برآورد شده توسط کارشناس متفاوت است. لطفاً جزئیات را بررسی کنید.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-2 lg:w-48">
            <a href="<?php echo site_url('/order/?order_id=' . $order->id); ?>" 
               class="text-center bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-all">
                مشاهده جزئیات
            </a>
            
            <?php if ($order->status == 'awaiting_payment') : ?>
                <a href="https://hamnaghsheh.ir/pay-with-card/" 
                   class="text-center bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded transition-all">
                    پرداخت
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>
