<?php
/**
 * Dashboard Template
 */

defined('ABSPATH') || exit;

$current_user = wp_get_current_user();
$access_level = Hamnaghsheh_Users::get_user_access_level($current_user->ID);

// Check access
$is_admin = current_user_can('manage_options');

// Get access label
$access_label = Hamnaghsheh_Utils::get_access_level_label($access_level);

// Get storage info
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

get_header();
?>

<div class="hamnaghsheh-dashboard">
    <div class="dashboard-header">
        <h1><?php esc_html_e('Dashboard', 'hamnaghsheh'); ?></h1>
    </div>

    <div class="dashboard-content">
        <!-- Dashboard content will be added here -->
        <p><?php printf(esc_html__('Welcome, %s!', 'hamnaghsheh'), esc_html($current_user->display_name)); ?></p>
        <p><?php printf(esc_html__('Access Level: %s', 'hamnaghsheh'), esc_html($access_label)); ?></p>
        <p><?php printf(esc_html__('Storage: %s / %s (%d%%)', 'hamnaghsheh'), esc_html($used_human), esc_html($total_human), $percent); ?></p>
    </div>
</div>

<?php
get_footer();
