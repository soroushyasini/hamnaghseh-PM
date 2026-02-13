<?php
if (!defined('ABSPATH'))
    exit;
?>

<div class="wrap hamnaghsheh-services p-5 lg:p-10" dir="rtl">
    <div class="max-w-6xl mx-auto">
        <!-- Hero Header Section -->
        <div class="services-hero">
            <!-- Decorative Icon -->
            <div class="hero-icon-wrapper">
                <div class="service-hero-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 17L12 22L22 17" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M2 12L12 17L22 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            </div>
            
            <h1>خدمات نقشه‌برداری</h1>
            <p>خدمات نقشه‌برداری را انتخاب کرده و سفارش خود را ثبت کنید.</p>
            
            <!-- Trust Badges -->
            <div class="trust-badges">
                <span class="trust-badge">۵۰۰+ پروژه موفق</span>
                <span class="trust-badge">خدمات حرفه‌ای</span>
                <span class="trust-badge">پشتیبانی ۲۴ ساعته</span>
            </div>
        </div>

        <?php if (empty($services)) : ?>
            <div class="bg-yellow-100 border-r-4 border-yellow-500 text-yellow-700 p-4 rounded" role="alert">
                <p>در حال حاضر خدمتی در دسترس نیست.</p>
            </div>
        <?php else : ?>
            <div class="flex flex-wrap justify-center services-grid">
                <?php foreach ($services as $service) : ?>
                    <?php include HAMNAGHSHEH_DIR . 'templates/parts/service-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Quantity controls
    $(document).on('click', '.quantity-minus', function() {
        var input = $(this).siblings('.quantity-input');
        var val = parseInt(input.val());
        if (val > 1) {
            input.val(val - 1).trigger('change');
        }
    });

    $(document).on('click', '.quantity-plus', function() {
        var input = $(this).siblings('.quantity-input');
        var val = parseInt(input.val());
        input.val(val + 1).trigger('change');
    });

    // Update total price
    $(document).on('change', '.quantity-input', function() {
        var card = $(this).closest('.service-card');
        var quantity = parseInt($(this).val());
        var price = parseFloat(card.data('price'));
        var total = quantity * price;
        
        card.find('.total-price').text(total.toLocaleString('fa-IR'));
    });

    // Order button
    $(document).on('click', '.order-service-btn', function() {
        var card = $(this).closest('.service-card');
        var serviceKey = card.data('service-key');
        var serviceName = card.data('service-name');
        var quantity = parseInt(card.find('.quantity-input').val());
        var pricePerSession = parseFloat(card.data('price'));
        var totalPrice = quantity * pricePerSession;

        // Store in session storage
        sessionStorage.setItem('order_data', JSON.stringify({
            service_type: serviceKey,
            service_name: serviceName,
            quantity: quantity,
            price_per_session: pricePerSession,
            total_price: totalPrice
        }));

        // Redirect to order form
        window.location.href = '<?php echo site_url('/order-details/'); ?>';
    });
});
</script>
