<?php
/*
Plugin Name: Ultra flexible QR code
Plugin URI:  https://wordpress.org/plugins/ultra-flexible-qr-code
Description: Create, manage & share dynamic QR code content.
Version:     1.0.0
Author:      John Albert Catama
Author URI:  https://github.com/jcatama
License:     GPL2
 
Ultra flexible QR code is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Ultra flexible QR code is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Ultra flexible QR code. If not, see https://github.com/jcatama/ultra-flexible-qr-code/blob/master/LICENSE.md.
*/

/**
 * Define plugin constants
 */
define('UFQC_TABLE_NAME', 'ufqc_qrs');
define('UFQC_MENU_SLUG', 'ufqc-admin');
define('UFQC_UPLOAD_FOLDER', '/ufqc');
define('UFQC_PAGE', 'UFQC Read');
define('UFQC_PAGE_SLUG', 'ufqc-read');

/**
 * Create table upon activation
 * Do not create if table does exist
 */
register_activation_hook( __FILE__, 'ufqc_create_database_table');
function ufqc_create_database_table() {
  global $wpdb;
  $charset_collate = $wpdb->get_charset_collate();
  $wp_ufqc_qrs_table = $wpdb->prefix . UFQC_TABLE_NAME;
  if($wpdb->get_var( "show tables like '$wp_ufqc_qrs_table'" ) != $wp_ufqc_qrs_table) {
    $sql = "CREATE TABLE `{$wp_ufqc_qrs_table}` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `qid` varchar(450) NOT NULL,
      `label` varchar(450) NOT NULL,
      `content` text NOT NULL,
      `qr` longblob NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      UNIQUE KEY `id` (`id`)
    ) {$charset_collate}";
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
    dbDelta($sql);
  }
}

/**
 * Create plugin folder on activate
 */
register_activation_hook( __FILE__, 'ufqc_create_plugin_folder');
function ufqc_create_plugin_folder() {
  $upload = wp_upload_dir();
  $upload_dir = $upload['basedir'];
  $upload_dir = $upload_dir . UFQC_UPLOAD_FOLDER;
  if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0700);
  }
}

/**
 * Create qr redirection page  on activate
 */
register_activation_hook( __FILE__, 'ufqc_create_plugin_page');
function ufqc_create_plugin_page() {
  if(is_page(UFQC_PAGE_SLUG) == false) {
    $ufqc_post = array(
      'post_title'    => UFQC_PAGE,
      'post_name'     => UFQC_PAGE_SLUG,
      'post_status'   => 'publish',
      'post_author'   => 1,
      'post_type'     => 'page',
    );
    wp_insert_post($ufqc_post);
  }
}

/**
 * Regsiter a custom template for QR redirect page
 */
add_filter('page_template', 'ufqc_page_template');
function ufqc_page_template($page_template){
  if(is_page(UFQC_PAGE_SLUG)) {
    $page_template = plugin_dir_path( __FILE__ ) . 'public/template/ufqc-read.php';
  }
  return $page_template;
}

/**
 * Inlcude JS
 */
add_action('admin_enqueue_scripts', 'ufqc_enqueue');
function ufqc_enqueue() {
  wp_enqueue_script('my_custom_script', plugin_dir_url( __FILE__ ) . '/admin/js/admin.js');
}

/**
 * Third Party vendor for generating qr images.
 * phpqrcode.sourceforge.net
 */
if(class_exists('QRcode') == false) {
  require_once plugin_dir_path( __FILE__ ) . 'vendor/phpqrcode/qrlib.php';
}

/**
 * The class responsible for defining all actions that occur in the admin area.
 */
if(class_exists('UFQCAdmin') == false) {
  require_once plugin_dir_path( __FILE__ ) . 'admin/ultra-flexible-qr-code-admin.php';
}

/**
 * The class responsible for defining all db to ui table actions that occur in the admin area.
 */
if(class_exists('UFQC_List') == false) {
  require_once plugin_dir_path( __FILE__ ) . 'admin/backend/ultra-flexible-qr-code-list.php';
}

/**
 * The class responsible for creating qr code in the admin area.
 */
if(class_exists('UFQC_Create') == false) {
  require_once plugin_dir_path( __FILE__ ) . 'admin/backend/ultra-flexible-qr-code-create.php';
}
?>