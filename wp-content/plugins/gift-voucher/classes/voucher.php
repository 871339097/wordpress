<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

/**
* WPGV_Voucher_List Class
*/
class WPGV_Voucher_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() 
	{
		parent::__construct( array(
			'singular' => __( 'Voucher', 'gift-voucher' ), //singular name of the listed records
			'plural'   => __( 'Vouchers', 'gift-voucher' ), //plural name of the listed records
			'ajax'     => true //does this table support ajax?
		) );
	}

	/**
	 * Retrieve vouchers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_vouchers( $per_page = 20, $page_number = 1 ) 
	{
		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}giftvouchers_list ORDER BY {$wpdb->prefix}giftvouchers_list.`id` DESC";

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	/**
	 * Delete a voucher record.
	 *
	 * @param int $id voucher id
	 */
	public static function delete_voucher( $id ) 
	{
		global $wpdb;

		$wpdb->update(
			"{$wpdb->prefix}giftvouchers_list",
			array('id'=>$id, 'status'=>'used'),
			array('id'=>$id)
		);
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() 
	{
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}giftvouchers_list";

		return $wpdb->get_var( $sql );
	}

	/** Text displayed when no voucher data is available */
	public function no_items() 
	{
		_e( 'No purchased voucher codes yet.', 'gift-voucher' );
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_id
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_id ) 
	{
		switch ( $column_id ) {
			case 'couponcode':
			case 'voucheradd_time':
			case 'voucher_info':
				return $item[ $column_id ];
			case 'buyer_info':
				return $item[ $column_id ];
			case 'mark_used':
				return $item[ $column_id ];
			case 'receipt':
				return $item[ $column_id ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() 
	{
		$columns = array(
			'cb'      				=> '<input type="checkbox" />',
			'id'    				=> __( 'Order id', 'gift-voucher' ),
			'couponcode'    		=> __( 'Voucher Code', 'gift-voucher' ),
			'voucher_info'			=> __( 'Voucher Information', 'gift-voucher' ),
			'buyer_info'			=> __( 'Buyer\'s Information', 'gift-voucher' ),
			'mark_used'				=> __( 'Mark as Used', 'gift-voucher' ),
			'receipt'	 			=> __( 'Receipt', 'gift-voucher' ),
			'voucheradd_time'	 	=> __( 'Order Date', 'gift-voucher' ),
		);

		return $columns;
	}

	/**
	 * Render the bulk used checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) 
	{
		return sprintf(
			'<input type="checkbox" name="bulk-used[]" value="%s" />', $item['id']
		);
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_id( $item ) 
	{
		$title = '<strong>' . $item['id'] . '</strong>';

		$actions = [
			'order_detail' => sprintf( '<a href="?page=%s&action=%s&voucher_id=%s">%s</a>', esc_attr( 'view-voucher-details' ), 'view_voucher', $item['id'], 'View Details')
		];

		return $title . $this->row_actions( $actions );
	}

	/**
	 * Method for voucher information
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_voucher_info( $item ) 
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'giftvouchers_setting';
		$options = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = 1" );
	?>
		<table style="width: 100%;">
			<tr>
				<th width="22%;" style="font-weight:bold;"><?php echo __('From', 'gift-voucher') ?>:</th>
				<td width="77%;"><?php echo $item['from_name']; ?></td>
			</tr>
			<tr>
				<th width="22%;" style="font-weight:bold;"><?php echo __('To', 'gift-voucher') ?>:</th>
				<td width="77%;"><?php echo $item['to_name']; ?></td>
			</tr>
			<tr>
				<th width="22%;" style="font-weight:bold;"><?php echo __('Amount', 'gift-voucher') ?>:</th>
				<td width="77%;"><?php echo ($options->currency_position == 'Left') ? $options->currency.''.$item['amount'] : $item['amount'].''.$options->currency; ?></td>
			</tr>
			<tr>
				<th width="22%;" style="font-weight:bold;"><?php echo __('Message', 'gift-voucher') ?>:</th>
				<td width="77%;"><?php echo $item['message']; ?></td>
			</tr>
		</table>
	<?php
	}

	/**
	 * Method for buyer information
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_buyer_info( $item ) 
	{
	?>
		<table style="width: 100%;">
			<tr>
				<th width="40%;" style="font-weight:bold;"><?php echo __('Name', 'gift-voucher') ?>:</th>
				<td width="60%;"><?php echo $item['firstname'].' '.$item['lastname']; ?></td>
			</tr>
			<tr>
				<th width="40%;" style="font-weight:bold;"><?php echo __('Email', 'gift-voucher') ?>:</th>
				<td width="60%;"><?php echo $item['email']; ?></td>
			</tr>
			<tr>
				<th width="40%;" style="font-weight:bold;"><?php echo __('Address', 'gift-voucher') ?>:</th>
				<td width="60%;"><?php echo $item['address']; ?></td>
			</tr>
			<tr>
				<th width="40%;" style="font-weight:bold;"><?php echo __('Postcode', 'gift-voucher') ?>:</th>
				<td width="60%;"><?php echo $item['postcode']; ?></td>
			</tr>
			<tr>
				<th width="40%;" style="font-weight:bold;"><?php echo __('Payment Method', 'gift-voucher') ?>:</th>
				<td width="60%;"><?php echo $item['pay_method']; ?></td>
			</tr>
			<tr>
				<th width="40%;" style="font-weight:bold;"><?php echo __('Payment Status', 'gift-voucher') ?>:</th>
				<td width="60%;"><?php echo $item['payment_status']; ?></td>
			</tr>
			<tr>
				<th width="40%;" style="font-weight:bold;"><?php echo __('Expiry', 'gift-voucher') ?>:</th>
				<td width="60%;"><abbr title="<?php echo $item['expiry']; ?>"><?php echo $item['expiry']; ?></abbr></td>
			</tr>
		</table>
	<?php
	}

	/**
	 * Method for mark as used link
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_mark_used( $item )
	{
		$delete_nonce = wp_create_nonce( 'used_voucher' );
		if($item['status'] == 'unused') {
			$order_id = "";
			$actions = array('used' => sprintf( '<a href="?page=%s&action=%s&voucher=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'used', absint( $item['id'] ), $delete_nonce, 'Mark as Used' ) );
			return $order_id . $this->row_actions( $actions, true );
		} else {
			$order_id = '<span class="vused">'.__('Voucher Used', 'gift-voucher').'</span>';
			return $order_id;
		}
	}

	/**
	 * Method for create receipt
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_voucheradd_time( $item )
	{
	?>
		<abbr title="<?php echo date('Y/m/d H:i:s a', strtotime($item['voucheradd_time'])); ?>"><?php echo date('Y/m/d', strtotime($item['voucheradd_time'])); ?></abbr>
	<?php
	}

	/**
	 * Method for create receipt
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_receipt( $item )
	{
		return '<a href="'.$item['voucherpdf_link'].'" title="click to show order receipt" target="_blank"><img src="'.WPGIFT__PLUGIN_URL. '/assets/img/pdf.png" /></a>';
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions()
	{
			$actions = array(
			'bulk-delete' => __('Mark as Used', 'gift-voucher')
		);

		return $actions;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() 
	{
		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'vouchers_per_page', 20 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( array(
			'total_items' => $total_items, 	//WE have to calculate the total number of items
			'per_page'    => $per_page 		//WE have to determine how many items to show on a page
		) );

		$this->items = self::get_vouchers( $per_page, $current_page );
	}

	/**
	 * Handles data for mark as used the bulk action
	 */
	public function process_bulk_action() 
	{
		//Detect when a bulk action is being triggered...
		if ( 'used' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'used_voucher' ) ) {
				wp_die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_voucher( absint( $_GET['voucher'] ) );
		        // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		        // add_query_arg() return the current url
		        wp_safe_redirect( "?page=vouchers-lists");
				exit;
			}
		}

		if ( 'order_detail' === $this->current_action() ) { 

			$order_id = $_REQUEST['order_id'];
			global $wpdb;
			$voucher_table_name = $wpdb->prefix . 'giftvouchers_list';
			$order_detail = $wpdb->get_row( "SELECT * FROM $voucher_table_name WHERE id = $order_id" );
			?>
			<div class="admin-modal">
				<div class="admin-custom-modal add-new">
					<span class="close dashicons dashicons-no-alt"></span>
					<h3><?php echo __('Order Details', 'gift-voucher') ?> (Order ID: <?php echo $order_id; ?>)  <?php 
					if($order_detail->status == "unused") {
						echo "<strong style='color:#fff;font-size:14px;background:#ddd;padding:2px 5px;'>Unused</strong>";
					} 
					else if($order_detail->status == "used") { 
						echo "<strong style='color:#fff;font-size:14px;display: inline-block;background:#233dcc;padding:2px 5px;'>Used</strong>";
					} ?></h3>
				</div>
			</div>

		<?php 
		}
	}

}