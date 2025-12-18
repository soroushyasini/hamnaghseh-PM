<?php
if (!defined('ABSPATH'))
    exit;

class Hamnaghsheh_Activator
{

    public static function activate()
    {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $projects_table = $wpdb->prefix . 'hamnaghsheh_projects';
        $files_table = $wpdb->prefix . 'hamnaghsheh_files';
        $users_table = $wpdb->prefix . 'hamnaghsheh_users';
        $shares_table = $wpdb->prefix . 'hamnaghsheh_shares';
        $assignments_table = $wpdb->prefix . 'hamnaghsheh_project_assignments';
        $file_logs_table  = $wpdb->prefix . 'hamnaghsheh_file_logs';
        $services_table = $wpdb->prefix . 'hamnaghsheh_services';
        $orders_table = $wpdb->prefix . 'hamnaghsheh_orders';
        $order_messages_table = $wpdb->prefix . 'hamnaghsheh_order_messages';
        $order_activity_table = $wpdb->prefix . 'hamnaghsheh_order_activity';

        $current_db_version = '3.0';
        $installed_db_version = get_option('hamnaghsheh_db_version');

        if ($installed_db_version !== $current_db_version) {

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';

            $sql2 = "CREATE TABLE {$projects_table} (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id BIGINT UNSIGNED NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                type ENUM('residential','commercial','renovation','infrastructure') DEFAULT 'residential',
                status ENUM('active','completed','archived') DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                share_token VARCHAR(100) DEFAULT NULL,
                archive TINYINT(1) DEFAULT 0,
                permission ENUM('view','download') DEFAULT 'view',
                PRIMARY KEY (id),
                KEY user_id (user_id)
            ) {$charset_collate};";

            $sql3 = "CREATE TABLE {$files_table} (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                project_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                file_name VARCHAR(255) NOT NULL,
                file_path TEXT NOT NULL,
                file_size BIGINT UNSIGNED NOT NULL,
                file_type VARCHAR(50),
                uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY project_id (project_id),
                KEY user_id (user_id)
            ) {$charset_collate};";

            $sql4 = "CREATE TABLE {$users_table} (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id BIGINT UNSIGNED NOT NULL,
                username VARCHAR(255) NOT NULL,
                display_name VARCHAR(255) DEFAULT NULL,
                email VARCHAR(255) DEFAULT NULL,
                active TINYINT(1) DEFAULT 1,
                storage_limit  BIGINT UNSIGNED DEFAULT 0,
                access_level ENUM('free', 'premium','enterprise') DEFAULT 'free',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) {$charset_collate};";

            $sql5 = "CREATE TABLE $shares_table (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                project_id BIGINT UNSIGNED NOT NULL,
                owner_id BIGINT UNSIGNED NOT NULL,
                token VARCHAR(64) NOT NULL,
                permission ENUM('view', 'upload') DEFAULT 'view',
                expires_at DATETIME NULL,
                usage_limit INT DEFAULT 0,
                usage_count INT DEFAULT 0,
                is_guest TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) $charset_collate;";


            $sql6 = "CREATE TABLE $assignments_table (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                project_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                permission ENUM('view', 'upload') DEFAULT 'view',
                assigned_by BIGINT UNSIGNED NULL,
                assigned_via_token VARCHAR(64) NULL,
                assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_assignment (project_id, user_id)
            ) $charset_collate;";

             $sql7 = "CREATE TABLE $file_logs_table (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                file_id BIGINT UNSIGNED NOT NULL,
                project_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                action_type ENUM('upload','replace','delete','download','see') NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) $charset_collate;";

            dbDelta($sql2);
            dbDelta($sql3);
            dbDelta($sql4);
            dbDelta($sql5);
            dbDelta($sql6);
            dbDelta($sql7);

            // Order management tables
            $sql8 = "CREATE TABLE {$services_table} (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                service_key VARCHAR(50) NOT NULL,
                service_name_fa VARCHAR(255) NOT NULL,
                price_per_session DECIMAL(10,2) NOT NULL DEFAULT 0,
                description TEXT,
                image_url VARCHAR(500),
                is_active TINYINT(1) DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY service_key (service_key)
            ) {$charset_collate};";

            $sql9 = "CREATE TABLE {$orders_table} (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id BIGINT UNSIGNED NOT NULL,
                order_number VARCHAR(50) NOT NULL,
                service_type VARCHAR(50) NOT NULL,
                requested_quantity INT(11) NOT NULL,
                requested_price_per_session DECIMAL(10,2) NOT NULL,
                requested_total_price DECIMAL(10,2) NOT NULL,
                admin_estimated_service_type VARCHAR(50),
                admin_estimated_quantity INT(11),
                admin_estimated_price_per_session DECIMAL(10,2),
                admin_estimated_total_price DECIMAL(10,2),
                admin_notes TEXT,
                address TEXT NOT NULL,
                area_size VARCHAR(100) NOT NULL,
                phone VARCHAR(20) NOT NULL,
                special_requirements TEXT,
                uploaded_files TEXT,
                status VARCHAR(50) DEFAULT 'pending',
                project_id BIGINT UNSIGNED,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY order_number (order_number),
                KEY user_id (user_id),
                KEY status (status)
            ) {$charset_collate};";

            $sql10 = "CREATE TABLE {$order_messages_table} (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                order_id BIGINT UNSIGNED NOT NULL,
                user_id BIGINT UNSIGNED NOT NULL,
                message TEXT NOT NULL,
                is_admin TINYINT(1) DEFAULT 0,
                is_read TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY order_id (order_id),
                KEY is_read (is_read)
            ) {$charset_collate};";

            $sql11 = "CREATE TABLE {$order_activity_table} (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                order_id BIGINT UNSIGNED NOT NULL,
                activity_type VARCHAR(50) NOT NULL,
                old_value TEXT,
                new_value TEXT,
                description TEXT,
                created_by BIGINT UNSIGNED,
                is_admin TINYINT(1) DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY order_id (order_id)
            ) {$charset_collate};";

            dbDelta($sql8);
            dbDelta($sql9);
            dbDelta($sql10);
            dbDelta($sql11);

            // Insert initial service data
            $existing_services = $wpdb->get_var("SELECT COUNT(*) FROM {$services_table}");
            if ($existing_services == 0) {
                $wpdb->insert($services_table, array(
                    'service_key' => 'half_day',
                    'service_name_fa' => 'نقشه برداری نیم روزه',
                    'price_per_session' => 3000000,
                    'description' => 'خدمات نقشه برداری نیم روزه برای پروژه‌های کوچک تا متوسط',
                    'image_url' => HAMNAGHSHEH_URL . 'assets/img/placeholder-service.jpg',
                    'is_active' => 1
                ));
                $wpdb->insert($services_table, array(
                    'service_key' => 'full_day',
                    'service_name_fa' => 'نقشه برداری تمام روزه',
                    'price_per_session' => 5000000,
                    'description' => 'خدمات نقشه برداری تمام روزه برای پروژه‌های بزرگ و پیچیده',
                    'image_url' => HAMNAGHSHEH_URL . 'assets/img/placeholder-service.jpg',
                    'is_active' => 1
                ));
            }

            wp_mkdir_p(HAMNAGHSHEH_UPLOAD_DIR);

            update_option('hamnaghsheh_db_version', $current_db_version);
        }
    }
}
