<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

/**
* WPGV_Voucher_Template Class
*/
class WPGV_Voucher_Template extends WP_List_Table {

	/** Class constructor */
	public function __construct() 
	{
		parent::__construct( array(
			'singular' => __( 'Template', 'gift-voucher' ), //singular name of the listed records
			'plural'   => __( 'Templates', 'gift-voucher' ), //plural name of the listed records
			'ajax'     => true //does this table support ajax?
		) );
	}

	/**
	 * Retrieve templates data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_templates( $per_page = 20, $page_number = 1 ) 
	{
		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}giftvouchers_template ORDER BY {$wpdb->prefix}giftvouchers_template.`id` DESC";

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}

	/**
	 * Delete a template record.
	 *
	 * @param int $id template id
	 */
	public static function delete_template( $id ) 
	{
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}giftvouchers_template",
			array( 'id' => $id ),
			array( '%d' )
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

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}giftvouchers_template";

		return $wpdb->get_var( $sql );
	}

	/** Text displayed when no template data is available */
	public function no_items() 
	{
		_e( 'No templates yet.', 'gift-voucher' );
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
			case 'title':
			case 'image':
			case 'orderno':
				return $item['orderno'];
			case 'active':
				return ($item['active']) ? 'Active' : 'Inactive';
			case 'templateadd_time':
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
			'cb'      			=> '<input type="checkbox" />',
			'title'    			=> __( 'Title', 'gift-voucher' ),
			'image'				=> __( 'Image', 'gift-voucher' ),
			'orderno'			=> __( 'Order', 'gift-voucher' ),
			'active'			=> __( 'Status', 'gift-voucher' ),
			'templateadd_time'	=> __( 'Order Date', 'gift-voucher' ),
		);

		return $columns;
	}

	/**
	 * Render the bulk delete checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) 
	{
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
		);
	}

	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_title( $item ) 
	{
		$delete_nonce = wp_create_nonce( 'delete_template' );
		$title = '<strong>' . $item['title'] . '</strong>';

		$actions = array(
			'edit_template' => sprintf( '<a href="?page=%s&action=%s&template_id=%s">%s</a>', esc_attr( 'new-voucher-template' ), 'edit_template', $item['id'], __('Edit Template', 'gift-voucher')),
			'delete' => sprintf( '<a href="?page=%s&action=%s&template_id=%s&_wpnonce=%s">%s</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce, __('Delete', 'gift-voucher'))
		);

		return $title . $this->row_actions( $actions );
	}

	/**
	 * Method for display template create date
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_image( $item )
	{
		$image_attributes = wp_get_attachment_image_src( $item['image'], 'voucher-thumb' );
		if ( $image_attributes ) : ?>
    		<img src="<?php echo $image_attributes[0]; ?>" height="60" />
		<?php
		endif;
	}

	/**
	 * Method for display template create date
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_templateadd_time( $item )
	{
	?>
		<abbr title="<?php echo date('Y/m/d H:i:s a', strtotime($item['templateadd_time'])); ?>"><?php echo date('Y/m/d', strtotime($item['templateadd_time'])); ?></abbr>
	<?php
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions()
	{
			$actions = array(
			'bulk-delete' => __('Delete', 'gift-voucher' )
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

		$per_page     = $this->get_items_per_page( 'templates_per_page', 20 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( array(
			'total_items' => $total_items, 	//WE have to calculate the total number of items
			'per_page'    => $per_page 		//WE have to determine how many items to show on a page
		) );

		$this->items = self::get_templates( $per_page, $current_page );
	}

	/**
	 * Handles data for delete the bulk action
	 */
	public function process_bulk_action()
	{
		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'delete_template' ) ) {
				wp_die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_template( absint( $_GET['template_id'] ) );
		        // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		        // add_query_arg() return the current url
		        wp_safe_redirect( "?page=voucher-templates");
				exit;
			}
		}
	}
}