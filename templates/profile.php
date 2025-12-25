<?php
if (!defined('ABSPATH'))
    exit;

// Variables are passed from class-profile.php
// $current_user, $first_name, $last_name, $display_name, $email, $phone
// $address, $city, $postal_code, $access_level, $access_label
// $storage_used, $storage_limit, $storage_percent, $storage_used_human, $storage_limit_human
// $created_at, $trial_activated, $trial_ends_at, $projects
?>

<div class="wrap hamnaghsheh-dashboard rounded-2xl p-5 lg:p-10">
    <div class="flex flex-col lg:flex-row gap-6">

        <?php 
        $current_page = 'profile';
        include HAMNAGHSHEH_DIR . 'templates/parts/user-sidebar.php'; 
        ?>

        <!-- MAIN CONTENT -->
        <main class="flex-1">
            <div class="mb-5 xl:mb-8">
                <h1 class="font-black text-lg xl:text-2xl mb-3 text-[#09375B]">پروفایل کاربری</h1>
            </div>
            <hr class="border-gray-300 mb-5">

            <!-- Success/Error Messages -->
            <div id="profile-messages" class="mb-4"></div>

            <!-- Personal Information Section -->
            <div class="rounded border border-slate-200 mb-6">
                <div class="flex items-center justify-between rounded-t bg-[#09375B]/10 p-3">
                    <h2 class="text-md xl:text-lg font-bold text-[#09375B]">اطلاعات شخصی</h2>
                </div>
                <div class="p-4 xl:p-6">
                    <form id="profile-form" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">نام</label>
                            <input type="text" name="first_name" id="first_name" 
                                   value="<?php echo esc_attr($first_name); ?>"
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FFCF00]">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">نام خانوادگی</label>
                            <input type="text" name="last_name" id="last_name" 
                                   value="<?php echo esc_attr($last_name); ?>"
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FFCF00]">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">نام نمایشی</label>
                            <input type="text" name="display_name" id="display_name" 
                                   value="<?php echo esc_attr($display_name); ?>"
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FFCF00]">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ایمیل</label>
                            <input type="email" name="email" id="email" 
                                   value="<?php echo esc_attr($email); ?>"
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FFCF00]">
                        </div>

                        <?php if (!empty($phone)): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">شماره تلفن</label>
                            <input type="text" value="<?php echo esc_attr($phone); ?>" 
                                   readonly
                                   class="w-full border border-gray-200 rounded px-3 py-2 text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                            <p class="text-xs text-gray-500 mt-1">شماره تلفن قابل تغییر نیست</p>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Default Address Section -->
            <div class="rounded border border-slate-200 mb-6">
                <div class="flex items-center justify-between rounded-t bg-[#09375B]/10 p-3">
                    <h2 class="text-md xl:text-lg font-bold text-[#09375B]">آدرس پیش‌فرض</h2>
                </div>
                <div class="p-4 xl:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">آدرس</label>
                            <textarea name="address" id="address" rows="3"
                                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FFCF00]"><?php echo esc_textarea($address); ?></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">شهر</label>
                            <input type="text" name="city" id="city" 
                                   value="<?php echo esc_attr($city); ?>"
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FFCF00]">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">کد پستی</label>
                            <input type="text" name="postal_code" id="postal_code" 
                                   value="<?php echo esc_attr($postal_code); ?>"
                                   class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FFCF00]">
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="button" id="save-profile" 
                                class="bg-[#09375B] hover:bg-[#062a45] text-white font-semibold py-2 px-6 rounded transition-all">
                            ذخیره تغییرات
                        </button>
                    </div>
                </div>
            </div>

            <!-- Account Information Section (Read-only) -->
            <div class="rounded border border-slate-200 mb-6">
                <div class="flex items-center justify-between rounded-t bg-[#09375B]/10 p-3">
                    <h2 class="text-md xl:text-lg font-bold text-[#09375B]">اطلاعات حساب کاربری</h2>
                </div>
                <div class="p-4 xl:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">سطح دسترسی</label>
                            <div class="w-full border border-gray-200 rounded px-3 py-2 text-sm bg-gray-50">
                                <span class="font-semibold text-[#09375B]"><?php echo esc_html($access_label); ?></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">فضای ذخیره‌سازی</label>
                            <div class="w-full border border-gray-200 rounded px-3 py-2 text-sm bg-gray-50">
                                <?php if ($storage_limit > 0): ?>
                                    <?php echo esc_html($storage_used_human . ' / ' . $storage_limit_human); ?>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                                        <div class="bg-[#FFCF00] h-1.5 rounded-full" 
                                             style="width: <?php echo esc_attr($storage_percent); ?>%;"></div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-500">بدون فضای ذخیره‌سازی</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">عضویت از تاریخ</label>
                            <div class="w-full border border-gray-200 rounded px-3 py-2 text-sm bg-gray-50">
                                <?php echo esc_html(date_i18n('Y/m/d', strtotime($created_at))); ?>
                            </div>
                        </div>

                        <?php if ($trial_activated == 1 && !empty($trial_ends_at)): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">وضعیت آزمایشی</label>
                            <div class="w-full border border-gray-200 rounded px-3 py-2 text-sm bg-gray-50">
                                <?php 
                                $now = current_time('timestamp');
                                $trial_end = strtotime($trial_ends_at);
                                if ($trial_end > $now):
                                    $days_left = ceil(($trial_end - $now) / DAY_IN_SECONDS);
                                ?>
                                    <span class="text-green-600">فعال - <?php echo $days_left; ?> روز باقی‌مانده</span>
                                <?php else: ?>
                                    <span class="text-red-600">منقضی شده در <?php echo date_i18n('Y/m/d', $trial_end); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Security Section -->
            <div class="rounded border border-slate-200 mb-6">
                <div class="flex items-center justify-between rounded-t bg-[#09375B]/10 p-3">
                    <h2 class="text-md xl:text-lg font-bold text-[#09375B]">امنیت</h2>
                </div>
                <div class="p-4 xl:p-6">
                    <button type="button" id="change-password-btn"
                            class="bg-white border border-[#09375B] text-[#09375B] hover:bg-[#09375B] hover:text-white font-semibold py-2 px-6 rounded transition-all">
                        تغییر رمز عبور
                    </button>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- Change Password Modal -->
<div id="password-modal" class="fixed inset-0 hidden bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 relative mx-4">
        <button type="button" id="close-password-modal" class="absolute top-5 left-5 text-gray-500 hover:text-gray-700">✕</button>

        <h2 class="text-xl font-bold text-[#09375B] mb-4">تغییر رمز عبور</h2>

        <div id="password-messages" class="mb-4"></div>

        <form id="password-form" class="flex flex-col gap-3">
            <div>
                <label class="text-sm text-gray-700 block mb-1">رمز عبور فعلی</label>
                <input type="password" name="current_password" id="current_password" required
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FFCF00]">
            </div>

            <div>
                <label class="text-sm text-gray-700 block mb-1">رمز عبور جدید</label>
                <input type="password" name="new_password" id="new_password" required
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FFCF00]">
                <p class="text-xs text-gray-500 mt-1">حداقل 8 کاراکتر</p>
            </div>

            <div>
                <label class="text-sm text-gray-700 block mb-1">تکرار رمز عبور جدید</label>
                <input type="password" name="confirm_password" id="confirm_password" required
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#FFCF00]">
            </div>

            <button type="submit"
                    class="bg-[#09375B] text-white text-sm rounded py-2 mt-3 hover:bg-[#062a45] transition-all">
                تغییر رمز عبور
            </button>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Profile Update
    $('#save-profile').on('click', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var originalText = $btn.text();
        $btn.prop('disabled', true).text('در حال ذخیره...');

        var data = {
            action: 'hamnaghsheh_update_profile',
            nonce: hamnaghsheh_ajax.nonce,
            first_name: $('#first_name').val(),
            last_name: $('#last_name').val(),
            display_name: $('#display_name').val(),
            email: $('#email').val(),
            address: $('#address').val(),
            city: $('#city').val(),
            postal_code: $('#postal_code').val()
        };

        $.post(hamnaghsheh_ajax.ajax_url, data, function(response) {
            if (response.success) {
                $('#profile-messages').html(
                    '<div class="border-r-4 border-green-400 bg-green-100 p-4 rounded text-sm text-green-700">' +
                    response.data.message + ' صفحه در حال بارگذاری مجدد...</div>'
                );
                // Reload page after 1.5 seconds to refresh sidebar and all user data
                // This ensures consistency across the UI
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                $('#profile-messages').html(
                    '<div class="border-r-4 border-red-400 bg-red-100 p-4 rounded text-sm text-red-700">' +
                    response.data.message + '</div>'
                );
                $btn.prop('disabled', false).text(originalText);
            }
        }).fail(function() {
            $('#profile-messages').html(
                '<div class="border-r-4 border-red-400 bg-red-100 p-4 rounded text-sm text-red-700">' +
                'خطا در ارتباط با سرور. لطفاً دوباره تلاش کنید.</div>'
            );
            $btn.prop('disabled', false).text(originalText);
        }).always(function() {
            // Scroll to messages
            $('html, body').animate({
                scrollTop: $('#profile-messages').offset().top - 100
            }, 500);
        });
    });

    // Open password modal
    $('#change-password-btn').on('click', function() {
        $('#password-modal').removeClass('hidden');
        $('#password-form')[0].reset();
        $('#password-messages').html('');
    });

    // Close password modal
    $('#close-password-modal').on('click', function() {
        $('#password-modal').addClass('hidden');
    });

    // Password Change
    $('#password-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var originalText = $btn.text();
        $btn.prop('disabled', true).text('در حال بروزرسانی...');

        var data = {
            action: 'hamnaghsheh_change_password',
            nonce: hamnaghsheh_ajax.nonce,
            current_password: $('#current_password').val(),
            new_password: $('#new_password').val(),
            confirm_password: $('#confirm_password').val()
        };

        $.post(hamnaghsheh_ajax.ajax_url, data, function(response) {
            if (response.success) {
                $('#password-messages').html(
                    '<div class="border-r-4 border-green-400 bg-green-100 p-4 rounded text-sm text-green-700">' +
                    response.data.message + '</div>'
                );
                // Close modal after 2 seconds
                setTimeout(function() {
                    $('#password-modal').addClass('hidden');
                    $form[0].reset();
                }, 2000);
            } else {
                $('#password-messages').html(
                    '<div class="border-r-4 border-red-400 bg-red-100 p-4 rounded text-sm text-red-700">' +
                    response.data.message + '</div>'
                );
            }
        }).always(function() {
            $btn.prop('disabled', false).text(originalText);
        });
    });
});
</script>
