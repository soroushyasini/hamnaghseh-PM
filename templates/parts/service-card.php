<?php
if (!defined('ABSPATH'))
    exit;
?>

<div class="service-card"
     data-service-key="<?php echo esc_attr($service->service_key); ?>"
     data-service-name="<?php echo esc_attr($service->service_name_fa); ?>"
     data-price="<?php echo esc_attr($service->price_per_session); ?>">
    
    <!-- Accent Bar -->
    <div class="service-card-accent"></div>
    
    <!-- Image with Gradient Overlay -->
    <div class="service-card-image">
        <img src="<?php echo esc_url($service->image_url); ?>" 
             alt="<?php echo esc_attr($service->service_name_fa); ?>">
        <div class="service-card-image-overlay"></div>
    </div>

    <div class="p-5">
        <!-- Service Icon and Title -->
        <div class="service-header">
            <div class="service-icon">
                <?php if (strpos($service->service_key, 'half') !== false): ?>
                    <!-- Half-day icon (clock/half-circle) -->
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 6V12L16 14" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="12" cy="12" r="8" stroke="white" stroke-width="2"/>
                    </svg>
                <?php elseif (strpos($service->service_key, 'full') !== false): ?>
                    <!-- Full-day icon (sun/full-circle) -->
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <circle cx="12" cy="12" r="4" stroke="white" stroke-width="2"/>
                        <path d="M12 2V4M12 20V22M22 12H20M4 12H2M19.07 4.93L17.66 6.34M6.34 17.66L4.93 19.07M19.07 19.07L17.66 17.66M6.34 6.34L4.93 4.93" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                <?php else: ?>
                    <!-- Generic surveying icon (layers/map) -->
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                <?php endif; ?>
            </div>
            <h3 class="text-xl font-bold text-[#09375B]"><?php echo esc_html($service->service_name_fa); ?></h3>
        </div>
        
        <?php if ($service->description) : ?>
            <p class="text-gray-600 text-sm mb-4"><?php echo esc_html($service->description); ?></p>
        <?php endif; ?>

        <!-- Price Info Item -->
        <div class="price-info mb-4">
            <div class="text-sm text-gray-500 mb-1">قیمت هر جلسه:</div>
            <div class="text-2xl font-bold text-[#09375B]">
                <?php echo number_format($service->price_per_session, 0, '.', ','); ?> تومان
            </div>
        </div>

        <!-- Quantity Selector -->
        <div class="mb-4">
            <label class="text-sm font-semibold text-gray-700 mb-2 block">تعداد جلسات:</label>
            <div class="flex items-center justify-center gap-3">
                <button type="button" class="quantity-minus">
                    <span class="quantity-btn-text">−</span>
                </button>
                <input type="number" class="quantity-input" value="1" min="1" readonly>
                <button type="button" class="quantity-plus">
                    <span class="quantity-btn-text">+</span>
                </button>
            </div>
        </div>

        <!-- Total Section -->
        <div class="total-section mb-4">
            <div class="text-sm text-gray-600 mb-1">مجموع:</div>
            <div class="text-xl font-bold total-value">
                <span class="total-price"><?php echo number_format($service->price_per_session, 0, '.', ','); ?></span> تومان
            </div>
        </div>

        <!-- CTA Button -->
        <button type="button" class="order-service-btn">
            <span>ثبت سفارش</span>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M9 2L20 12L9 22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </div>
</div>
