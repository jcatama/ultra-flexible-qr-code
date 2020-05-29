<?php

/**
 * The admin-specific functionality of the plugin.
 * @package    ultra-flexible-qr-code
 * @subpackage ultra-flexible-qr-code/admin
 */

class UFQCAdmin {

  /**
   * Class intance
   */
  protected static $instance;

	/**
   * QR WP_List_Table object
   */ 
  public $ufqc_qrs_obj;
  
   
  public function __construct() {
    /**
     * Add admin menu & screen option in page
     */
    add_filter('set-screen-option',  array($this , 'ufqc_set_screen' ), 10, 3);
    add_action('admin_menu',  array($this, 'ufqc_plugin_setup_menu'));
  }

  /**
   * Add main menu to the admin dashboard
   */
  public function ufqc_plugin_setup_menu() {
    $hook = add_menu_page('Flexible QR Page', 'Flexible QR', 'manage_options', UFQC_MENU_SLUG,  array($this, 'ufqc_init_admin_display'), 'dashicons-forms', 20);
    add_action("load-$hook", array($this, 'ufqc_screen_option'));
  }

  /**
   * Add screen options
   */
  public function ufqc_set_screen($status, $option, $value ) {
		return $value;
  }
  
  /**
	 * Screen options
	 */
	public function ufqc_screen_option() {
		$option = 'per_page';
		$args   = [
			'label'   => 'QRs',
			'default' => 20,
			'option'  => 'qrs_per_page'
		];
		add_screen_option( $option, $args );
    $this->ufqc_qrs_obj = new UFQC_List();
	}

  /**
   * Admin template display
   */
  public function ufqc_init_admin_display() {
    include_once('ultra-flexible-qr-code-admin-display.php');
  }

  /** Singleton instance */
  public static function get_instance() {
    if(!isset(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

}

add_action('plugins_loaded', function() {
  UFQCAdmin::get_instance();
  UFQC_Create::get_instance();
});