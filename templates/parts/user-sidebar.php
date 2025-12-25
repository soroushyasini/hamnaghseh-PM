<?php
/**
 * Reusable User Sidebar Component
 * 
 * This sidebar displays user information, navigation menu, projects list,
 * and storage indicators for authenticated users.
 * 
 * Required Variables:
 * @var WP_User $current_user       - WordPress user object for current user
 * @var array   $projects           - Array of user projects from Hamnaghsheh_Projects::get_user_projects()
 * @var string  $access_level       - User access level ('free', 'premium', 'personal', 'organization')
 * @var string  $access_label       - Localized label for access level (e.g., 'رایگان', 'پریمیوم')
 * @var int     $total_space        - Total storage space in bytes
 * @var int     $used_space         - Used storage space in bytes
 * @var float   $percent            - Storage usage percentage (0-100)
 * @var string  $used_human         - Human-readable used storage (e.g., '2.5 MB')
 * @var string  $total_human        - Human-readable total storage (e.g., '50 MB')
 * @var string  $current_page       - Current page identifier for active state highlighting
 *                                    Values: 'dashboard', 'profile', 'services', 'my-orders', 'project'
 * 
 * Optional Variables (for project-specific view):
 * @var bool    $role               - Whether to show project-specific sections
 * @var array   $members            - Array of project members (when $role is true)
 * @var bool    $can_manage         - Whether current user can manage project members
 * @var object  $project            - Project object (required when $can_manage is true, for member removal)
 * 
 * @package Hamnaghsheh
 * @since 1.1.7
 */

if (!defined('ABSPATH'))
    exit;

?>

<aside class="w-full lg:w-56 bg-[#09375B] rounded-2xl p-4 flex flex-col items-center text-center text-white shadow-lg self-start">

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
        if ($access_level === 'free') echo 'اشتراک ' . esc_html($access_label);
        elseif ($access_level === 'premium') echo 'اشتراک ' . esc_html($access_label);
        else echo 'اشتراک ' . esc_html($access_label);
        ?>
    </div>

    <!-- Navigation Links with Active State -->
    <a href="<?php echo esc_url(get_site_url() . '/dashboard'); ?>"
        class="mb-4 block text-sm truncate transition-colors outline-none <?php echo isset($current_page) && $current_page === 'dashboard' ? 'text-[#FFCF00] font-bold' : 'text-white hover:text-[#FFCF00]'; ?>">
        پروژه‌ها
    </a>
    <a href="<?php echo esc_url(get_site_url() . '/profile'); ?>"
        class="mb-4 block text-sm truncate transition-colors outline-none <?php echo isset($current_page) && $current_page === 'profile' ? 'text-[#FFCF00] font-bold' : 'text-white hover:text-[#FFCF00]'; ?>">
        پروفایل
    </a>
    <a href="<?php echo esc_url(get_site_url() . '/services'); ?>"
        class="mb-4 block text-sm truncate transition-colors outline-none <?php echo isset($current_page) && $current_page === 'services' ? 'text-[#FFCF00] font-bold' : 'text-white hover:text-[#FFCF00]'; ?>">
        خرید خدمت
    </a>
    <a href="<?php echo esc_url(get_site_url() . '/my-orders'); ?>"
        class="mb-4 block text-sm truncate transition-colors outline-none <?php echo isset($current_page) && $current_page === 'my-orders' ? 'text-[#FFCF00] font-bold' : 'text-white hover:text-[#FFCF00]'; ?>">
        سوابق خرید
    </a>
    
    <?php $logout_url = wp_logout_url(home_url()); ?>
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
                        <a href="<?php echo esc_url(get_site_url() . '/show-project?id=' . esc_attr($p->id)); ?>"
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

    <?php if (isset($role) && $role): ?>
        <!-- اعضای پروژه -->
        <div class="w-full text-right mb-6">
            <h3 class="text-sm font-semibold mb-2 text-[#FFCF00] border-b border-[#FFCF00]/40 pb-1">اعضای پروژه</h3>
            <?php if (isset($members) && $members): ?>
                <ul class="space-y-1">
                    <?php foreach ($members as $member): ?>
                        <li class="text-xs text-gray-200 flex items-center justify-between">
                            <div>
                                <span class="text-[#FFCF00]">•</span>
                                <?php echo esc_html($member->user_name); ?>
                            </div>
                            <div>
                                <?php if (isset($can_manage) && $can_manage && isset($project)): ?>
                                    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                                        <input type="hidden" name="action" value="hamnaghsheh_unassigned">
                                        <input type="hidden" name="project_id" value="<?php echo esc_attr($project->id); ?>">
                                        <input type="hidden" name="user_id" value="<?php echo esc_attr($member->user_id); ?>">
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
