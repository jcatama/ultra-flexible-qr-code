<?php
/**
 * Template Name: UFQC read
 * 
 * For qr scan redirect url
 */

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

ob_start();

$notfoundurl = get_home_url().'/404';

if(!isset($_GET['qid'])) {
	header("Location: ".$notfoundurl, true, 301);
	exit;
}

$qid = sanitize_text_field($_GET['qid']);

global $wpdb;
$charset_collate = $wpdb->get_charset_collate();
$wp_ufqc_qrs_table = $wpdb->prefix . UFQC_TABLE_NAME;

$ufqc_result = $wpdb->get_row("SELECT * FROM `$wp_ufqc_qrs_table` WHERE `qid` = '".$qid."'");
if($ufqc_result) {
  $prefix_uri = strpos($ufqc_result->content, 'http') !== 0 ? 'http://' : '';
	$redir_url = $prefix_uri.$ufqc_result->content;
	$file_headers = @get_headers($redir_url);
  /**
   * Check if content is URL or not
   * URL -> redirect
   * Not URL -> return JSON
   */
	if(!$file_headers || in_array($file_headers[0], ['HTTP/1.1 404 Not Found','HTTP/1.1 403 Forbidden']) ) {
    header('Content-Type: application/json');
    echo json_encode(
      array(
        'success'=> true,
        'label'=> $ufqc_result->label,
        'content'=> $ufqc_result->content
      )
    );
		exit;
	} else {
    header("Location: ".$redir_url, true, 301);
    exit;
  }
} else {
	header("Location: ".$notfoundurl, true, 301);
	exit;
}