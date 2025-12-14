<?php
if (!defined('ABSPATH'))
    exit;
?>

<div class="wrap hamnaghsheh-order-form p-5 lg:p-10" dir="rtl">
    <div class="max-w-4xl mx-auto">
        <div class="mb-8">
            <h1 class="font-black text-2xl xl:text-3xl mb-3 text-[#09375B]">جزئیات سفارش</h1>
            <p class="text-gray-600">لطفاً اطلاعات سفارش خود را تکمیل کنید.</p>
        </div>

        <form id="order-form" class="bg-white rounded-lg shadow-md p-6">
            <!-- Service Summary -->
            <div id="service-summary" class="mb-6 p-4 bg-blue-50 rounded-lg">
                <h3 class="font-bold text-lg mb-2 text-[#09375B]">خلاصه سفارش</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">خدمت:</span>
                        <span id="summary-service" class="font-semibold mr-2">-</span>
                    </div>
                    <div>
                        <span class="text-gray-600">تعداد:</span>
                        <span id="summary-quantity" class="font-semibold mr-2">-</span>
                    </div>
                    <div>
                        <span class="text-gray-600">قیمت هر جلسه:</span>
                        <span id="summary-price" class="font-semibold mr-2">-</span>
                    </div>
                    <div>
                        <span class="text-gray-600">مجموع:</span>
                        <span id="summary-total" class="font-semibold mr-2 text-green-600">-</span>
                    </div>
                </div>
            </div>

            <!-- Hidden fields -->
            <input type="hidden" name="service_type" id="service_type" required>
            <input type="hidden" name="quantity" id="quantity" required>
            <input type="hidden" name="price_per_session" id="price_per_session" required>
            <input type="hidden" name="total_price" id="total_price" required>

            <!-- Address -->
            <div class="mb-4">
                <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">
                    آدرس دقیق <span class="text-red-500">*</span>
                </label>
                <textarea name="address" id="address" rows="3" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="آدرس محل پروژه را به طور دقیق وارد کنید"></textarea>
            </div>

            <!-- Area Size -->
            <div class="mb-4">
                <label for="area_size" class="block text-sm font-semibold text-gray-700 mb-2">
                    متراژ زمین (متر مربع) <span class="text-red-500">*</span>
                </label>
                <input type="text" name="area_size" id="area_size" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="مثال: 250">
            </div>

            <!-- Phone -->
            <div class="mb-4">
                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                    شماره تماس <span class="text-red-500">*</span>
                </label>
                <input type="tel" name="phone" id="phone" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="09123456789">
            </div>

            <!-- Special Requirements -->
            <div class="mb-4">
                <label for="special_requirements" class="block text-sm font-semibold text-gray-700 mb-2">
                    نیازمندیهای ویژه
                </label>
                <textarea name="special_requirements" id="special_requirements" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="در صورت وجود نیازهای خاص یا توضیحات اضافی، در اینجا بنویسید"></textarea>
            </div>

            <!-- Disclaimer -->
            <div class="mb-6 p-4 bg-yellow-50 border-r-4 border-yellow-400 rounded">
                <div class="flex items-start">
                    <input type="checkbox" id="disclaimer_accept" required class="mt-1 ml-3">
                    <label for="disclaimer_accept" class="text-sm text-gray-700">
                        <strong>توجه:</strong> پروژه‌های نقشه‌برداری ممکن است در عمل با زمان مورد نظر شما تفاوت داشته باشند. 
                        کارشناسان ما پس از بررسی اولیه، برآورد نهایی را برای شما ارسال خواهند کرد. 
                        با ثبت این سفارش، شما این موضوع را می‌پذیرید.
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-4">
                <button type="submit" 
                        class="flex-1 bg-[#FFCF00] hover:bg-[#e6bd00] text-[#09375B] font-bold py-3 px-6 rounded transition-all">
                    ثبت سفارش
                </button>
                <a href="<?php echo site_url('/services/'); ?>" 
                   class="flex-1 text-center bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-6 rounded transition-all">
                    بازگشت
                </a>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Load order data from session storage
    var orderData = sessionStorage.getItem('order_data');
    
    if (!orderData) {
        window.location.href = '<?php echo site_url('/services/'); ?>';
        return;
    }
    
    orderData = JSON.parse(orderData);
    
    // Populate hidden fields
    $('#service_type').val(orderData.service_type);
    $('#quantity').val(orderData.quantity);
    $('#price_per_session').val(orderData.price_per_session);
    $('#total_price').val(orderData.total_price);
    
    // Update summary
    $('#summary-service').text(orderData.service_name);
    $('#summary-quantity').text(orderData.quantity + ' جلسه');
    $('#summary-price').text(orderData.price_per_session.toLocaleString('fa-IR') + ' تومان');
    $('#summary-total').text(orderData.total_price.toLocaleString('fa-IR') + ' تومان');
    
    // Handle form submission
    $('#order-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: hamnaghsheh_ajax.ajax_url,
            type: 'POST',
            data: formData + '&action=hamnaghsheh_submit_order&nonce=' + hamnaghsheh_ajax.nonce,
            beforeSend: function() {
                $('button[type="submit"]').prop('disabled', true).text('در حال ثبت...');
            },
            success: function(response) {
                if (response.success) {
                    sessionStorage.removeItem('order_data');
                    alert(response.data.message);
                    window.location.href = response.data.redirect;
                } else {
                    alert(response.data.message);
                    $('button[type="submit"]').prop('disabled', false).text('ثبت سفارش');
                }
            },
            error: function() {
                alert('خطا در ارتباط با سرور. لطفاً دوباره تلاش کنید.');
                $('button[type="submit"]').prop('disabled', false).text('ثبت سفارش');
            }
        });
    });
});
</script>
