<?php
/**
 * User Order Detail Template - SIMPLIFIED VERSION
 * Shows order details with status-based pricing and payment flow
 */
if (!defined('ABSPATH'))
    exit;

$status_badge_class = Hamnaghsheh_Orders::get_status_badge_class($order->status);
$status_label = Hamnaghsheh_Orders::get_status_label($order->status);

// Determine price to display
$display_price = $order->final_price ? $order->final_price : $order->requested_total_price;
?>

<div class="wrap hamnaghsheh-order-detail p-5 lg:p-10" dir="rtl">
    <div class="max-w-4xl mx-auto">
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

        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold mb-4 text-[#09375B]">📋 خلاصه سفارش</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-600 mb-1">خدمت:</div>
                    <div class="font-semibold"><?php echo $service ? esc_html($service->service_name_fa) : esc_html($order->service_type); ?></div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-1">تعداد جلسات:</div>
                    <div class="font-semibold"><?php echo esc_html($order->requested_quantity); ?> جلسه</div>
                </div>
                <div>
                    <div class="text-sm text-gray-600 mb-1">تاریخ ثبت:</div>
                    <div class="font-semibold"><?php echo date_i18n('Y/m/d', strtotime($order->created_at)); ?></div>
                </div>
            </div>
        </div>

        <!-- Order Details -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold mb-4 text-[#09375B]">📍 جزئیات</h2>
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

        <!-- Pricing Section - Status Based -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold mb-4 text-[#09375B]">💰 قیمت</h2>
            
            <?php if ($order->status == 'pending') : ?>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-3">
                    <div class="text-sm text-gray-600 mb-1">برآورد اولیه:</div>
                    <div class="text-2xl font-bold text-gray-700">
                        <?php echo number_format($order->requested_total_price, 0, '.', ','); ?> تومان
                    </div>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="text-blue-500 ml-3 text-2xl">ℹ️</div>
                        <div>
                            <div class="font-semibold text-blue-900 mb-1">کارشناس ما به زودی با شما تماس می‌گیرد</div>
                            <div class="text-sm text-blue-700">برای هماهنگی نهایی و تعیین قیمت دقیق، کارشناسان ما از طریق شماره <?php echo esc_html($order->phone); ?> با شما تماس خواهند گرفت.</div>
                        </div>
                    </div>
                </div>
            
            <?php elseif ($order->status == 'awaiting_payment') : ?>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-3">
                    <div class="text-sm text-gray-600 mb-1">قیمت نهایی:</div>
                    <div class="text-3xl font-bold text-green-600">
                        <?php echo number_format($display_price, 0, '.', ','); ?> تومان
                    </div>
                </div>
                <a href="https://hamnaghsheh.ir/pay-with-card/" 
                   class="inline-block w-full text-center bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg transition-all">
                    🔗 پرداخت سفارش
                </a>
                <p class="text-sm text-gray-600 mt-3 text-center">
                    پس از پرداخت، رسید خود را برای ما ارسال کنید تا سفارش شما تایید شود.
                </p>
            
            <?php elseif (in_array($order->status, array('paid', 'in_progress', 'completed'))) : ?>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="text-sm text-gray-600 mb-1">پرداخت شده:</div>
                    <div class="text-2xl font-bold text-green-600 mb-2">
                        <?php echo number_format($display_price, 0, '.', ','); ?> تومان
                    </div>
                    <div class="flex items-center text-green-700">
                        <span class="text-xl ml-2">✅</span>
                        <span class="font-semibold">پرداخت تایید شده است</span>
                    </div>
                </div>
            
            <?php elseif ($order->status == 'cancelled') : ?>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center text-red-700">
                        <span class="text-xl ml-2">❌</span>
                        <span class="font-semibold">سفارش لغو شده است</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Project Files Section -->
        <?php if (in_array($order->status, array('in_progress', 'completed')) && $order->project_id) : ?>
        <div class="bg-blue-50 border border-blue-200 rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold mb-4 text-blue-800">📁 پروژه و فایل‌ها</h2>
            <p class="mb-4 text-blue-900">
                <?php if ($order->status == 'in_progress') : ?>
                    نقشه‌برداری در حال انجام است. می‌توانید پیشرفت کار را در پروژه مشاهده کنید.
                <?php else : ?>
                    نقشه‌برداری تکمیل شده است. فایل‌های نهایی در پروژه شما آماده دانلود است.
                <?php endif; ?>
            </p>
            <a href="<?php echo site_url('/project-show/?id=' . $order->project_id); ?>" 
               class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-all">
                🔗 مشاهده پروژه
            </a>
        </div>
        <?php endif; ?>

        <!-- Activity Timeline -->
        <?php if ($activity && count($activity) > 0) : ?>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4 text-[#09375B]">📅 تاریخچه فعالیت</h2>
            <?php include HAMNAGHSHEH_DIR . 'templates/parts/order-activity.php'; ?>
        </div>
        <?php endif; ?>

    </div>
</div>
