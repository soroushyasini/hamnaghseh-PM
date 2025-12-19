<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Hamnaghsheh_Deactivator {
    public static function deactivate() {
        // On deactivate we do not remove data; uninstall.php handles permanent deletion if user removes plugin.
        // Optional: Remove custom capabilities from all roles
        self::remove_custom_capabilities();
    }
    
    /**
     * Remove custom capabilities from all roles
     */
    private static function remove_custom_capabilities() {
        $roles = array('administrator', 'editor', 'project_manager'); // Add custom roles if any
        
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                $role->remove_cap('view_all_projects');
                $role->remove_cap('manage_projects');
                $role->remove_cap('upload_to_any_project');
                $role->remove_cap('view_all_orders');
                $role->remove_cap('manage_orders');
                $role->remove_cap('set_order_prices');
            }
        }
    }
}
