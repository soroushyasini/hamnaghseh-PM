<?php
/**
 * Template: Dashboard
 */

if (!defined('ABSPATH')) exit;

// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Get access level
$access_level = Hamnaghsheh_Utils::get_user_access_level($user_id);

// Get access label
$access_label = Hamnaghsheh_Utils::get_access_level_label($access_level);

// Get storage info for sidebar
$storage_info = Hamnaghsheh_Users::get_user_storage_info($current_user->ID);
$total_space = intval($storage_info['storage_limit']);

// Calculate used space
global $wpdb;
$used_space = $wpdb->get_var($wpdb->prepare("
    SELECT COALESCE(SUM(f.file_size), 0)
    FROM {$wpdb->prefix}hamnaghsheh_files AS f
    INNER JOIN {$wpdb->prefix}hamnaghsheh_projects AS p ON f.project_id = p.id
    WHERE p.user_id = %d
", $current_user->ID));

$used_space = $used_space ? intval($used_space) : 0;

// Calculate percentage
$percent = $total_space > 0 ? min(100, round(($used_space / $total_space) * 100)) : 0;

// Human readable formats
$used_human = size_format($used_space);
$total_human = size_format($total_space);

// Get archived projects variable
$archived_projects = Hamnaghsheh_Projects::get_user_projects($user_id, 'archived');

get_header();
?>

<div class="hamnaghsheh-dashboard-container">
    <?php include HAMNAGHSHEH_PLUGIN_DIR . 'templates/parts/user-sidebar.php'; ?>
    
    <div class="hamnaghsheh-main-content">
        <div class="hamnaghsheh-dashboard-header">
            <h1><?php _e('داشبورد', 'hamnaghsheh'); ?></h1>
            <p><?php printf(__('خوش آمدید، %s', 'hamnaghsheh'), $current_user->display_name); ?></p>
        </div>

        <div class="hamnaghsheh-stats-grid">
            <?php
            // Get statistics
            $stats = Hamnaghsheh_Projects::get_user_stats($user_id);
            ?>
            
            <div class="hamnaghsheh-stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_projects']; ?></h3>
                    <p><?php _e('کل پروژه‌ها', 'hamnaghsheh'); ?></p>
                </div>
            </div>

            <div class="hamnaghsheh-stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <h3><?php echo $stats['active_projects']; ?></h3>
                    <p><?php _e('پروژه‌های فعال', 'hamnaghsheh'); ?></p>
                </div>
            </div>

            <div class="hamnaghsheh-stat-card">
                <div class="stat-icon">📁</div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_files']; ?></h3>
                    <p><?php _e('کل فایل‌ها', 'hamnaghsheh'); ?></p>
                </div>
            </div>

            <div class="hamnaghsheh-stat-card">
                <div class="stat-icon">💾</div>
                <div class="stat-content">
                    <h3><?php echo size_format($stats['total_storage']); ?></h3>
                    <p><?php _e('فضای استفاده شده', 'hamnaghsheh'); ?></p>
                </div>
            </div>
        </div>

        <div class="hamnaghsheh-recent-projects">
            <h2><?php _e('پروژه‌های اخیر', 'hamnaghsheh'); ?></h2>
            <?php
            $recent_projects = Hamnaghsheh_Projects::get_user_projects($user_id, 'active', 5);
            if ($recent_projects) :
            ?>
                <div class="hamnaghsheh-projects-list">
                    <?php foreach ($recent_projects as $project) : ?>
                        <div class="hamnaghsheh-project-card">
                            <h3><?php echo esc_html($project->name); ?></h3>
                            <p><?php echo esc_html($project->description); ?></p>
                            <div class="project-meta">
                                <span><?php echo date_i18n(get_option('date_format'), strtotime($project->created_at)); ?></span>
                                <a href="<?php echo add_query_arg(['page' => 'hamnaghsheh-project', 'id' => $project->id], admin_url('admin.php')); ?>" class="button">
                                    <?php _e('مشاهده', 'hamnaghsheh'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p><?php _e('هیچ پروژه‌ای وجود ندارد.', 'hamnaghsheh'); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
get_footer();
