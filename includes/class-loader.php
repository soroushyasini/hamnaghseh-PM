<?php
if (!defined('ABSPATH'))
  exit;

class Hamnaghsheh_Loader
{

  public function __construct()
  {
    $this->requires();
  }

  private function requires()
  {
    require_once HAMNAGHSHEH_DIR . 'includes/class-activator.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-deactivator.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-users.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-dashboard.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-new-project.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-project-show.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-projects.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-utils.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-file-validator.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-trial-manager.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-upload-file.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-shares.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-auth.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-log-file.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-file-download.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-user-setting.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-pages.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-minio.php';
    
    // Order management system (simplified version)
    require_once HAMNAGHSHEH_DIR . 'includes/class-services.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-orders.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-order-activity.php';
    require_once HAMNAGHSHEH_DIR . 'includes/class-email-notifications.php';
    // REMOVED in simplified version:  class-order-messages.php
    require_once HAMNAGHSHEH_DIR . 'includes/admin/class-admin-services.php';
    require_once HAMNAGHSHEH_DIR . 'includes/admin/class-admin-orders.php';

    new Hamnaghsheh_Users();
    new Hamnaghsheh_File_Upload();
    new Hamnaghsheh_Share();
    new Hamnaghsheh_Auth();
    new Hamnaghsheh_Logs();
    new Hamnaghsheh_File_Download();
    new Hamnaghsheh_User_Settings();
    new Hamnaghsheh_Pages();
    new Hamnaghsheh_Trial_Manager();
    
    // Initialize order management system
    new Hamnaghsheh_Services();
    new Hamnaghsheh_Orders();
    new Hamnaghsheh_Email_Notifications();
    
    // Initialize admin classes if in admin
    if (is_admin()) {
      require_once HAMNAGHSHEH_DIR . 'includes/admin/class-admin-capability-check.php';
      new Hamnaghsheh_Admin_Capability_Check();
      new Hamnaghsheh_Admin_Services();
      new Hamnaghsheh_Admin_Orders();
    }

    add_action('wp_enqueue_scripts', [$this, 'tailwind_assets']);
  }

  public function init_textdomain()
  {
    load_plugin_textdomain('hamnaghsheh', false, dirname(plugin_basename(__FILE__)) . '/../languages/');
  }

  public function public_assets()
  {
    wp_register_style('hamnaghsheh-style', HAMNAGHSHEH_URL . 'assets/css/style.css', array(), HAMNAGHSHEH_VERSION);
    wp_enqueue_style('hamnaghsheh-style');

    // Order management styles
    if (is_page(array('services', 'order-details', 'my-orders', 'order'))) {
      wp_register_style('hamnaghsheh-orders', HAMNAGHSHEH_URL . 'assets/css/orders.css', array(), HAMNAGHSHEH_VERSION);
      wp_enqueue_style('hamnaghsheh-orders');

      wp_register_script('hamnaghsheh-orders', HAMNAGHSHEH_URL . 'assets/js/orders.js', array('jquery'), HAMNAGHSHEH_VERSION, true);
      wp_enqueue_script('hamnaghsheh-orders');
    }
  }

  public function admin_assets()
  {
    wp_register_style('hamnaghsheh-admin', HAMNAGHSHEH_URL . 'assets/css/dashboard.css', array(), HAMNAGHSHEH_VERSION);
    wp_enqueue_style('hamnaghsheh-admin');

    // Order management admin styles
    $screen = get_current_screen();
    if ($screen && (strpos($screen->id, 'hamnaghsheh-orders') !== false || strpos($screen->id, 'hamnaghsheh-services') !== false)) {
      wp_register_style('hamnaghsheh-admin-orders', HAMNAGHSHEH_URL . 'assets/css/admin-orders.css', array(), HAMNAGHSHEH_VERSION);
      wp_enqueue_style('hamnaghsheh-admin-orders');

      wp_register_script('hamnaghsheh-admin-orders', HAMNAGHSHEH_URL . 'assets/js/admin-orders.js', array('jquery'), HAMNAGHSHEH_VERSION, true);
      wp_enqueue_script('hamnaghsheh-admin-orders');
      
      // Localize admin script with AJAX data
      wp_localize_script('hamnaghsheh-admin-orders', 'hamnaghsheh_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('hamnaghsheh_admin_nonce')
      ));
    }
  }

  public function tailwind_assets()
  {
    wp_enqueue_script(
      'tailwindcdn',
      'https://cdn.tailwindcss.com',
      [],
      null,
      false
    );

    $custom_tailwind = "
            tailwind.config = {
              theme: {
                extend: {
                  fontFamily: {
                    sans: ['Vazirmatn', 'ui-sans-serif', 'system-ui']
                  },
                  colors: {
                    primary: '#2563eb',
                    secondary: '#1e293b'
                  }
                }
              }
            }
        ";
    wp_add_inline_script('tailwindcdn', $custom_tailwind, 'after');
  }
}