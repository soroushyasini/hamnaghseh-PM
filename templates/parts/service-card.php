<?php
if (!defined('ABSPATH'))
    exit;
?>

<div class="service-card bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition-shadow mx-auto max-w-sm"
     data-service-key="<?php echo esc_attr($service->service_key); ?>"
     data-service-name="<?php echo esc_attr($service->service_name_fa); ?>"
     data-price="<?php echo esc_attr($service->price_per_session); ?>">
    
    <div class="h-48 bg-gray-200">
        <img src="<?php echo esc_url($service->image_url); ?>" 
             alt="<?php echo esc_attr($service->service_name_fa); ?>"
             class="w-full h-full object-cover">
    </div>

    <div class="p-5">
        <h3 class="text-xl font-bold mb-2 text-[#09375B]"><?php echo esc_html($service->service_name_fa); ?></h3>
        
        <?php if ($service->description) : ?>
            <p class="text-gray-600 text-sm mb-4"><?php echo esc_html($service->description); ?></p>
        <?php endif; ?>

        <div class="mb-4">
            <div class="text-sm text-gray-500 mb-1">قیمت هر جلسه:</div>
            <div class="text-2xl font-bold text-[#09375B]">
                <?php echo number_format($service->price_per_session, 0, '.', ','); ?> تومان
            </div>
        </div>

        <div class="mb-4">
            <label class="text-sm font-semibold text-gray-700 mb-2 block">تعداد جلسات:</label>
            <div class="flex items-center gap-2">
                <button type="button" class="quantity-minus bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded">
                    −
                </button>
                <input type="number" class="quantity-input w-20 text-center border border-gray-300 rounded py-2" value="1" min="1" readonly>
                <button type="button" class="quantity-plus bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded">
                    +
                </button>
            </div>
        </div>

        <div class="mb-4 p-3 bg-gray-50 rounded">
            <div class="text-sm text-gray-600 mb-1">مجموع:</div>
            <div class="text-xl font-bold text-green-600">
                <span class="total-price"><?php echo number_format($service->price_per_session, 0, '.', ','); ?></span> تومان
            </div>
        </div>

        <button type="button" class="order-service-btn w-full bg-[#FFCF00] hover:bg-[#e6bd00] text-[#09375B] font-bold py-3 px-4 rounded transition-all">
            ثبت سفارش
        </button>
    </div>
</div>
