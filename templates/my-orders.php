<?php
if (!defined('ABSPATH'))
    exit;

function time_ago_persian($datetime) {
    $time_ago = strtotime($datetime);
    $current_time = current_time('timestamp');
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);
    
    if ($seconds <= 60) {
        return "لحظاتی پیش";
    } else if ($minutes <= 60) {
        return "$minutes دقیقه پیش";
    } else if ($hours <= 24) {
        return "$hours ساعت پیش";
    } else if ($days <= 7) {
        return "$days روز پیش";
    } else if ($weeks <= 4.3) {
        return "$weeks هفته پیش";
    } else if ($months <= 12) {
        return "$months ماه پیش";
    } else {
        return "$years سال پیش";
    }
}
?>

<div class="wrap hamnaghsheh-my-orders p-5 lg:p-10" dir="rtl">
    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <h1 class="font-black text-2xl xl:text-3xl mb-3 text-[#09375B]">سفارش‌های من</h1>
            <p class="text-gray-600">مشاهده و پیگیری سفارش‌های نقشه‌برداری شما</p>
        </div>

        <?php if (empty($orders)) : ?>
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <p class="text-gray-600 mb-4">شما هنوز هیچ سفارشی ثبت نکرده‌اید.</p>
                <a href="<?php echo site_url('/services/'); ?>" 
                   class="inline-block bg-[#FFCF00] hover:bg-[#e6bd00] text-[#09375B] font-bold py-3 px-6 rounded transition-all">
                    مشاهده خدمات
                </a>
            </div>
        <?php else : ?>
            <div class="space-y-4">
                <?php foreach ($orders as $order) : 
                    $service = Hamnaghsheh_Services::get_service_by_key($order->service_type);
                    // REMOVED: unread_count - no messaging in simplified version
                ?>
                    <?php include HAMNAGHSHEH_DIR . 'templates/parts/order-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
