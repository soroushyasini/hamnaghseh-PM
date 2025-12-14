<?php
if (!defined('ABSPATH'))
    exit;
?>

<div class="wrap hamnaghsheh-services p-5 lg:p-10" dir="rtl">
    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <h1 class="font-black text-2xl xl:text-3xl mb-3 text-[#09375B]">خدمات نقشه‌برداری</h1>
            <p class="text-gray-600">خدمات نقشه‌برداری را انتخاب کرده و سفارش خود را ثبت کنید.</p>
        </div>

        <?php if (empty($services)) : ?>
            <div class="bg-yellow-100 border-r-4 border-yellow-500 text-yellow-700 p-4 rounded" role="alert">
                <p>در حال حاضر خدمتی در دسترس نیست.</p>
            </div>
        <?php else : ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
