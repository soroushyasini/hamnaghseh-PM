<?php
if (!defined('ABSPATH'))
    exit;

$current_user = wp_get_current_user();
$projects = Hamnaghsheh_Projects::get_user_projects($current_user->ID);

// ✅ Get user access level SAFELY (with fallback)  -- the code  WAS  problematic with the new storage badge, its fixed now - soroush 6 Dec 2025
$access_level = 'free';
$access_label = 'رایگان';
if (class_exists('Hamnaghsheh_Users')) {
    $access_level = Hamnaghsheh_Users::get_user_access_level($current_user->ID);
    if (class_exists('Hamnaghsheh_Utils')) {
        $access_label = Hamnaghsheh_Utils::get_access_level_label($access_level);
    }
}

?>

<aside class="w-full lg:w-56 bg-[#09375B] rounded-2xl p-4 hidden lg:flex flex-col items-center text-center text-white shadow-lg self-start">

    <!-- آواتار -->
    <!--<div class="w-20 h-20 rounded-full overflow-hidden mb-3 border-4 border-[#FFCF00]">-->
    <!--    <img src="<?php echo esc_url(get_avatar_url($current_user->ID)); ?>" alt="Avatar"-->
    <!--        class="w-full h-full object-cover">-->
    <!--</div>-->

    <!-- نام کاربر -->
    <?php
    $display_name = trim($current_user->user_firstname . ' ' . $current_user->user_lastname);
    ?>
    <div class="mb-2">
        <?php if (!empty($display_name)): ?>
            <div class="font-semibold text-base text-[#FFCF00]"><?php echo esc_html($display_name); ?></div>
            <div class="text-xs text-gray-300 mt-1"><?php echo esc_html($current_user->user_login); ?></div>
        <?php else: ?>
            <div class="font-semibold text-base text-[#FFCF00]"><?php echo esc_html($current_user->user_login); ?></div>
        <?php endif; ?>
    </div>

    <!-- ✅ Plan Badge (Safe version with fallback) -->
    <div class="mb-4 plan-badge" style="display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 600; 
        <?php 
        if ($access_level === 'free') echo 'background: rgba(255, 255, 255, 0.2); color: #fff;';
        elseif ($access_level === 'premium') echo 'background: #FFCF00; color: #09375B;';
        else echo 'background: rgba(255, 255, 255, 0.9); color: #09375B;';
        ?>">
        <?php 
        if ($access_level === 'free') echo 'اشتراک ' . $access_label;
        elseif ($access_level === 'premium') echo 'اشتراک ' . $access_label;
        else echo 'اشتراک ' . $access_label;
        ?>
    </div>

    <a href="<?php echo get_site_url() . '/dashboard'; ?>"
        class="mb-4 block text-white hover:text-[#FFCF00] text-sm truncate transition-colors outline-none">
        پروژه‌ها
    </a>
    <a href="<?php echo get_site_url() . '/profile'; ?>"
        class="mb-4 block text-white hover:text-[#FFCF00] text-sm truncate transition-colors outline-none">
        پروفایل
    </a>
    <a href="<?php echo get_site_url() . '/services'; ?>"
        class="mb-4 block text-white hover:text-[#FFCF00] text-sm truncate transition-colors outline-none">
        خرید خدمت
    </a>
        <a href="<?php echo get_site_url() . '/my-orders'; ?>"
        class="mb-4 block text-white hover:text-[#FFCF00] text-sm truncate transition-colors outline-none">
        سوابق خرید
    </a>
    
    <?php $logout_url = wc_get_account_endpoint_url('customer-logout'); ?>
    <a href="<?php echo esc_url($logout_url); ?>"
       class="mb-4 block text-white hover:text-[#FFCF00] text-sm truncate transition-colors outline-none"
       onclick="return confirm('آیا مطمئن هستید که می‌خواهید خارج شوید؟');">
        خروج از حساب
    </a>
    
    <!-- پروژه‌ها -->
    <div class="w-full text-right mb-10">
        <h3 class="text-sm font-semibold mb-2 text-[#FFCF00] border-b border-[#FFCF00]/40 pb-1">پروژه‌ها</h3>
        <?php if ($projects): ?>
            <ul class="space-y-1">
                <?php foreach ($projects as $p): ?>
                    <li>
                        <a href="<?php echo get_site_url() . '/show-project?id=' . esc_attr($p->id); ?>"
                            class="block text-white hover:text-[#FFCF00] text-xs truncate transition-colors outline-none">
                            • <?php echo esc_html($p->name) . ' - ' . esc_html($p->owner_name) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-xs text-gray-300">پروژه‌ای وجود ندارد</p>
        <?php endif; ?>
    </div>

    <?php if ($role): ?>
        <!-- اعضای پروژه -->
        <div class="w-full text-right mb-6">
            <h3 class="text-sm font-semibold mb-2 text-[#FFCF00] border-b border-[#FFCF00]/40 pb-1">اعضای پروژه</h3>
            <?php if ($members): ?>
                <ul class="space-y-1">
                    <?php foreach ($members as $member): ?>
                        <li class="text-xs text-gray-200 flex items-center justify-between">
                            <div>
                                <span class="text-[#FFCF00]">•</span>
                                <?php echo esc_html($member->user_name); ?>
                            </div>
                            <div>
                                <?php if ($can_manage): ?>
                                    <form action="<?= esc_url(admin_url('admin-post.php')) ?>" method="post">
                                        <input type="hidden" name="action" value="hamnaghsheh_unassigned">
                                        <input type="hidden" name="project_id" value="<?= esc_attr($project->id) ?>">
                                        <input type="hidden" name="user_id" value="<?= esc_attr($member->user_id) ?>">
                                        <button type="submit" class="text-[10px] text-slate-400" style="background: transparent; color: white; font-size: 10px; padding: 0;">
                                            حذف
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-xs text-gray-300">عضوی ثبت نشده</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- حجم مصرفی -->
    <div class="w-full mt-6">
        <h3 class="text-xs font-semibold mb-2 text-[#FFCF00]">حجم مصرفی</h3>
        
        <?php if (isset($total_space) && $total_space > 0): ?>
            <!-- Your Original Progress Bar -->
            <div class="w-full bg-white/20 rounded-full h-2 mb-1">
                <div class="bg-[#FFCF00] h-2 rounded-full transition-all duration-300"
                    style="width: <?php echo esc_attr($percent); ?>%;"></div>
            </div>
            <p class="text-[11px] mt-2 text-gray-200">
                <?php echo esc_html($used_human . ' از ' . $total_human); ?>
            </p>
        <?php else: ?>
            <!-- Free User - No Storage -->
            <div class="text-center p-3 bg-white/10 rounded-lg">
                <p class="text-[11px] text-gray-300 mb-2">
                    🆓 کاربر رایگان<br>بدون فضای ذخیره‌سازی
                </p>
                <a href="<?php echo esc_url(site_url('/plans')); ?>" 
                   class="text-[10px] text-[#FFCF00] hover:underline">
                    ارتقا اشتراک →
                </a>
            </div>
        <?php endif; ?>
        
        <!-- ✅ Allowed Formats (Safe with fallback) -->
        <?php if (class_exists('Hamnaghsheh_Utils')): ?>
        <div class="mt-3 text-center">
            <p class="text-[10px] text-gray-400 mb-1">فرمت‌های مجاز:</p>
            <p class="text-[9px] text-gray-300">
                <?php echo Hamnaghsheh_Utils::get_allowed_formats($access_level); ?>
            </p>
        </div>
        <?php endif; ?>
    </div>

</aside>

<div class="lg:hidden">
  <nav class="hm-bottom-nav">
    <a href="<?php echo esc_url(get_site_url() . '/dashboard'); ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
      </svg>
      پروژه‌ها
    </a>
    <a href="<?php echo esc_url(get_site_url() . '/profile'); ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
      </svg>
      پروفایل
    </a>
    <a href="<?php echo esc_url(get_site_url() . '/services'); ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
      </svg>
      خرید
    </a>
    <a href="<?php echo esc_url(get_site_url() . '/my-orders'); ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
      </svg>
      سفارش‌ها
    </a>
  </nav>
</div>