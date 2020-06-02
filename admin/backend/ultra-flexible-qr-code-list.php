<?php

/**
 * UFQC_List for qr listing
 * @package    ultra-flexible-qr-code/admin
 * @subpackage ultra-flexible-qr-code/admin/backend
 */


if(class_exists( 'WP_List_Table' ) == false) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class UFQC_List extends WP_List_Table {

	public function __construct() {
    global $status, $page;

		parent::__construct( [
			'singular' => __( 'qr', 'ufqc' ),
			'plural'   => __( 'qrs', 'ufqc' ),
			'ajax'     => false
		] );
	}

	/**
	 * Retrieve QR list data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_qr_list($per_page = 5, $page_number = 1) {
		global $wpdb;
    $sql = "SELECT * FROM {$wpdb->prefix}".UFQC_TABLE_NAME;
    if(isset($_POST['s'])) {
      $s = sanitize_text_field($_POST['s']);
      $sql .= ' WHERE label like "%'.esc_sql($s).'%" OR label like "%'.esc_sql($s).'%" ';
    }
		if(!empty($_REQUEST['orderby'])) {
			$sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
			$sql .= ! empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
		}
		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
		$result = $wpdb->get_results( $sql, 'ARRAY_A' );
		return $result;
	}

	/**
	 * Delete a qr record.
	 *
	 * @param int $id qr ID
	 */
	public static function delete_qr( $id ) {
		global $wpdb;
		$wpdb->delete(
			"{$wpdb->prefix}".UFQC_TABLE_NAME,
			[ 'id' => $id ],
			[ '%d' ]
		);
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;
		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}".UFQC_TABLE_NAME;
		return $wpdb->get_var($sql);
	}


	/** Text displayed when no qr data is available */
	public function no_items() {
	  _e('No QR avaliable.', 'ufqc');
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		$base64pngstr = 'data:image/png;base64,';
		switch($column_name) {
			case 'label':
			case 'content':
        return $item[$column_name];
      case 'qr':
				return sprintf('<img src="'.$base64pngstr.$item[$column_name].'" />');
			case 'action':
				return sprintf('<a download="'.$item['label'].'-qr.png" href="'.$base64pngstr.$item['qr'].'">Download</a><br>
				<a class="ufqc-copy-clipboard" href="'.get_page_link(get_page_by_title(UFQC_PAGE)).'?qid='.urlencode($item['qid']).'" >Copy URL</a>
				');
			default:
				return print_r( $item, true );
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
		);
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'cb'      => '<input type="checkbox" />',
			'label'    => __( 'Label', 'ufqc' ),
      'content' => __( 'Content', 'ufqc' ),
			'qr' => __( 'QR', 'ufqc' ),
			'action' => __( 'Action', 'ufqc' )
		];
		return $columns;
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'label' => array( 'label', true ),
			'content' => array( 'content', false )
		);
		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => __('Delete')
		];
		return $actions;
  }

  /**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {
		$this->_column_headers = $this->get_column_info();
		$this->process_bulk_action();
		$per_page     = $this->get_items_per_page('qrs_per_page', 5);
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();
		$this->set_pagination_args([
			'total_items' => $total_items, 
			'per_page'    => $per_page
		]);
		$this->items = self::get_qr_list($per_page, $current_page);
	}

	public function process_bulk_action() {
		if(isset($_GET['QR']) && 'delete' === $this->current_action()) {
			$nonce = esc_attr($_REQUEST['_wpnonce']);
			if(!wp_verify_nonce($nonce, 'ufqc_delete_qr')) {
				die('Action is not allowed!');
			} else {
				$qrid = sanitize_text_field($_GET['QR']);
				self::delete_qr(absint($qrid));
				wp_redirect(admin_url('admin.php?page='.UFQC_MENU_SLUG));
				exit;
			}
		}

		if(isset($_POST['bulk-delete']) && (isset($_POST['action']) && $_POST['action'] == 'bulk-delete')) {
			$delete_ids = esc_sql($_POST['bulk-delete']);
			foreach($delete_ids as $id) {
				self::delete_qr($id);
			}
			wp_redirect(admin_url('admin.php?page='.UFQC_MENU_SLUG));
			exit;
		}
	}
  
}

add_action('init', 'ufqc_do_output_buffer');
function ufqc_do_output_buffer() {
  ob_start();
}
?>