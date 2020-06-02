<?php

/**
 * UFQC_Create for adding qr
 * @package    ultra-flexible-qr-code/admin
 * @subpackage ultra-flexible-qr-code/admin/backend
 */

class UFQC_Create {

  /**
   * Class intance
   */
  protected static $instance;

  public function __construct() {
    add_action('admin_post_ufqc_submit_action',  array($this, 'ufqc_submit_qr'));
    add_action('admin_post_nopriv_ufqc_submit_action',  array($this, 'ufqc_submit_qr_deny'));
	}

  /**
   * Admin form for adding qr
   */
  public function ufqc_submit_qr() {
    if(isset($_POST['ufqc_label']) && isset($_POST['ufqc_content'])) {

      if(empty(trim($_POST['ufqc_label'])) || empty(trim(isset($_POST['ufqc_content'])))) {
        wp_redirect(admin_url('admin.php?page='.UFQC_MENU_SLUG));
        exit;
      }

      global $wpdb;
      $wpdb->query('START TRANSACTION');
      $charset_collate = $wpdb->get_charset_collate();
      $ufcq_table = $wpdb->prefix . UFQC_TABLE_NAME;

      $ufcqlabel = sanitize_text_field($_POST['ufqc_label']);
      $ufcqlabelcontent = sanitize_text_field($_POST['ufqc_content']);

      try {
        $wpdb->insert($ufcq_table, array(
          "label" => $ufcqlabel,
          "content" => $ufcqlabelcontent,
          "qr" => ''
        ));
        $qrid = $wpdb->insert_id;

        if($qrid) {
          $qid = md5($qrid.$ufcqlabel.microtime(true));
          $base64Qr = $this->ufcq_generate_qr_code($qid);
          $wpdb->update($ufcq_table, array('qid' => $qid, 'qr' => $base64Qr), array( 'ID' => $qrid));
          $wpdb->query('COMMIT');
        } else {
          $wpdb->query('ROLLBACK');
        }
        
      } catch(Exception $e) {
        error_log($e->getMessage());
        $wpdb->query('ROLLBACK');
      }

      wp_redirect(admin_url('admin.php?page='.UFQC_MENU_SLUG));
    } else {
      wp_die('Post data not valid.');
    }
  }

  /**
	 * Method for generating qr code
	 *
   * @param int $id
	 * @param string $label
	 *
	 * @return string
	 */
  private function ufcq_generate_qr_code($qid) {
    $upload = wp_upload_dir();
    $upload_dir = $upload['basedir'] . UFQC_UPLOAD_FOLDER;
    $filepath =  $upload_dir . '/tmp_'.$qid.'.png';
    $codeContents = get_page_link(get_page_by_title(UFQC_PAGE)).'?qid='.urlencode($qid);
    QRcode::png($codeContents, $filepath);
    $base64Qr = base64_encode(file_get_contents($filepath));
    unlink($filepath);
    return $base64Qr;
  }

  /**
   * Deny admin form submission
   */
  public function ufqc_submit_qr_deny() {
    wp_die('Permissin denied.');
  }

  /** Singleton instance */
  public static function get_instance() {
    if(!isset(self::$create_instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

}