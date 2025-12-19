<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Hamnaghsheh_Deactivator {
    public static function deactivate() {
        // On deactivate we do not remove data; uninstall.php handles permanent deletion if user removes plugin.
        // Optional: Remove custom capabilities from all roles
        self::remove_custom_capabilities();
    }
    
    /**
     * Remove hamnaghsheh_admin capability from all roles
     */
    private static function remove_custom_capabilities() {
        global $wp_roles;
        
        foreach ($wp_roles->roles as $role_name => $role_info) {
            $role = get_role($role_name);
            if ($role) {
                $role->remove_cap('hamnaghsheh_admin');
            }
        }
    }
}
