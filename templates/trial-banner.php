<?php
/**
 * Trial Banner Component
 * Shows different states based on trial status
 * Created by soroush - 12/08/2025
 */

if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
$trial_status = Hamnaghsheh_Trial_Manager::get_trial_status($user_id);

// Don't show banner for premium/enterprise users
if (in_array($trial_status['state'], ['premium', 'enterprise'])) {
    return;
}

?>

<!-- Trial Banner Container -->
<div id="trial-banner" class="trial-banner-container mb-6">
    
    <?php if ($trial_status['state'] === 'basic_free' && $trial_status['can_activate']): ?>
        <!-- State 1: Trial Available -->
        <div class="trial-banner trial-available bg-gradient-to-r from-blue-500 to-blue-600 text-white p-5 rounded-xl shadow-lg">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-3xl">🎁</span>
                        <h3 class="text-lg font-bold">شروع آزمایش رایگان 14 روزه</h3>
                    </div>
                    <p class="text-sm opacity-90">
                        10 مگابایت فضا + امکان آپلود، حذف و مدیریت کامل فایل‌های DWG، DXF و TXT
                    </p>
                </div>
                <button 
                    id="activate-trial-btn"
                    class="bg-white text-blue-600 font-bold px-6 py-3 rounded-lg hover:bg-blue-50 transition-all shadow-md hover:shadow-lg transform hover:scale-105">
                    🚀 شروع آزمایش رایگان
                </button>
            </div>
        </div>
    
    <?php elseif ($trial_status['state'] === 'trial_active'): ?>
        <!-- State 2: Trial Active -->
        <?php 
        $days_remaining = $trial_status['days_remaining'];
        $storage_info = Hamnaghsheh_Users::get_user_storage_info($user_id);
        $used_mb = round($storage_info['used_space'] / 1048576, 1);
        $total_mb = round($storage_info['storage_limit'] / 1048576);
        ?>
        <div class="trial-banner trial-active bg-gradient-to-r from-green-500 to-emerald-600 text-white p-5 rounded-xl shadow-lg">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-3xl">⏱️</span>
                        <h3 class="text-lg font-bold">دوره آزمایشی شما فعال است</h3>
                    </div>
                    <p class="text-sm opacity-90">
                        باقیمانده: <strong><?php echo $days_remaining; ?> روز</strong> | 
                        فضا: <strong><?php echo $used_mb; ?> از <?php echo $total_mb; ?> مگابایت</strong>
                    </p>
                </div>
                <a 
                    href="<?php echo esc_url(site_url('/plans')); ?>"
                    class="bg-white text-green-600 font-bold px-6 py-3 rounded-lg hover:bg-green-50 transition-all shadow-md hover:shadow-lg">
                    📦 خرید اشتراک
                </a>
            </div>
        </div>
    
    <?php elseif ($trial_status['state'] === 'trial_expired'): ?>
        <!-- State 3: Trial Expired -->
        <div class="trial-banner trial-expired bg-gradient-to-r from-amber-500 to-orange-600 text-white p-5 rounded-xl shadow-lg border-2 border-amber-300">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-3xl">⚠️</span>
                        <h3 class="text-lg font-bold">دوره آزمایشی شما به پایان رسیده است</h3>
                    </div>
                    <p class="text-sm opacity-90">
                        شما از دوره آزمایشی 14 روزه استفاده کرده‌اید. برای ادامه کار با امکانات کامل، اشتراک تهیه کنید.
                    </p>
                </div>
                <a 
                    href="<?php echo esc_url(site_url('/plans')); ?>"
                    class="bg-white text-orange-600 font-bold px-6 py-3 rounded-lg hover:bg-orange-50 transition-all shadow-md hover:shadow-lg">
                    💳 خرید اشتراک
                </a>
            </div>
        </div>
    
    <?php endif; ?>
    
</div>

<!-- Trial Activation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const activateBtn = document.getElementById('activate-trial-btn');
    
    if (activateBtn) {
        activateBtn.addEventListener('click', function() {
            if (!confirm('آیا می‌خواهید دوره آزمایشی 14 روزه را فعال کنید؟\n\nتوجه: این امکان فقط یک بار قابل استفاده است.')) {
                return;
            }
            
            // Disable button and show loading
            activateBtn.disabled = true;
            activateBtn.innerHTML = '⏳ در حال فعال‌سازی...';
            
            // Send AJAX request
            fetch(hamnaghsheh_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'hamnaghsheh_activate_trial',
                    nonce: hamnaghsheh_ajax.nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.data.message);
                    location.reload(); // Reload to show new trial state
                } else {
                    alert(data.data.message || 'خطا در فعال‌سازی دوره آزمایشی');
                    activateBtn.disabled = false;
                    activateBtn.innerHTML = '🚀 شروع آزمایش رایگان';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('خطا در ارتباط با سرور. لطفاً دوباره تلاش کنید.');
                activateBtn.disabled = false;
                activateBtn.innerHTML = '🚀 شروع آزمایش رایگان';
            });
        });
    }
});
</script>

<style>
.trial-banner-container {
    animation: slideDown 0.5s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.trial-banner {
    position: relative;
    overflow: hidden;
}

.trial-banner::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    animation: shine 3s infinite;
}

@keyframes shine {
    to {
        left: 100%;
    }
}
</style>