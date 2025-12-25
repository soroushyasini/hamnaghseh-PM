<?php
/**
 * @deprecated Use templates/parts/user-sidebar.php instead
 * This file is kept for backward compatibility
 */
trigger_error('sidebar-dashboard.php is deprecated. Use templates/parts/user-sidebar.php instead.', E_USER_DEPRECATED);

// Ensure current_page is set for backward compatibility
if (!isset($current_page)) {
    $current_page = 'dashboard';
}

include HAMNAGHSHEH_DIR . 'templates/parts/user-sidebar.php';
