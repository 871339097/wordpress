<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

/**
* WPGiftVoucherAdminPages Class for add Admin Pages in Menu
*/
class WPGiftVoucherAdminPages
{
	// class instance
	static $instance;

	// Voucher WP_List_Table object
	public $vouchers_obj;

	public function __construct()
	{
		add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
		add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_assets' ) );
	}

	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Admin CSS and JS Files
	 */
	function admin_register_assets( $hook )
	{
        wp_enqueue_style( 'wp-color-picker' ); 
       	wp_enqueue_style( 'voucher-style', WPGIFT__PLUGIN_URL.'/assets/css/admin-style.css');
       	wp_enqueue_script('voucher-script', WPGIFT__PLUGIN_URL  . '/assets/js/admin-script.js', array( 'wp-color-picker' ), '1.0.0', true);
		wp_localize_script( 'voucher-script', 'WPGiftAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
    }

    /**
	 * Voucher Menu page
	 */
	public function plugin_menu() 
	{
		$hook = add_menu_page(
			__('Gift Vouchers', 'gift-voucher' ),
			__('Gift Vouchers', 'gift-voucher' ),
			'manage_options',
			'vouchers-lists',
			array( $this, 'voucher_list_page' ),
			'dashicons-tickets-alt',
			10
		);
		$templatehook = add_submenu_page( 'vouchers-lists', __('Voucher Templates', 'gift-voucher' ), __('Voucher Templates', 'gift-voucher' ), 'manage_options', 'voucher-templates', array( $this, 'voucher_template_page' ));
		add_submenu_page( 'vouchers-lists', __('Add New Template', 'gift-voucher' ), __('Add New Template', 'gift-voucher' ), 'manage_options', 'new-voucher-template', array( $this, 'new_voucher_template_page' ));
		add_submenu_page( NULL, __('View Voucher Details', 'gift-voucher' ), __('View Voucher Details', 'gift-voucher' ), 'manage_options', 'view-voucher-details', array( $this, 'view_voucher_details' ));
		add_submenu_page( 'vouchers-lists', __('Settings', 'gift-voucher' ), __('Settings', 'gift-voucher' ), 'manage_options', 'voucher-setting', array( $this, 'voucher_settings_page' ));
		add_action( "load-$hook", array( $this, 'screen_option_voucher' ) );
		add_action( "load-$templatehook", array( $this, 'screen_option_template' ) );
	}

	/**
	 * Voucher List page
	 */
	public function voucher_list_page()
	{
		global $wpdb;
		?>
		<div class="wrap voucher-page">
			<h1><?php echo __( 'Voucher Codes', 'gift-voucher' ) ?></h1><br>
			<div class="content">
				<?php $this->export_orders(); ?>
				<h2 class="nav-tab-wrapper">
					<a class="nav-tab nav-tab-active" href="#"><?php echo __( 'Purchased Voucher Codes', 'gift-voucher' ) ?></a>
				</h2>
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->vouchers_obj->prepare_items();
								$this->vouchers_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php 
	}

	/**
	 * Method for view details of an voucher
	 */
	public function view_voucher_details() 
	{
		global $wpdb;
		$voucher_table 	= $wpdb->prefix . 'giftvouchers_list';
		$setting_table 	= $wpdb->prefix . 'giftvouchers_setting';
		$template_table = $wpdb->prefix . 'giftvouchers_template';

   		if ( !current_user_can( 'manage_options' ) )
   		{
      		wp_die( 'You are not allowed to be on this page.' );
   		}

   		$voucher_id = $_REQUEST['voucher_id'];
   		$voucher_options = $wpdb->get_row( "SELECT * FROM $voucher_table WHERE id = $voucher_id" );

   		$setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );
   		$template_options = $wpdb->get_row( "SELECT * FROM $template_table WHERE id = $voucher_options->template_id" );
   		$image_attributes = wp_get_attachment_image_src( $template_options->image, 'voucher-thumb' );
   		?>
   		<div class="wrap">
			<h1><?php echo __( 'Voucher Order ID', 'gift-voucher' ) ?>: <?php echo $voucher_options->id; ?></h1>
				<p class="description"><?php echo __( 'Here you can find detailed information for a voucher code.', 'gift-voucher' ) ?></p><br>
			<div id="voucher-details">
				<table class="widefat main">
					<thead>
						<th><?php echo __( 'Voucher Code', 'gift-voucher' )?></th>
						<th><?php echo __( 'Order Date', 'gift-voucher' )?></th>
						<th><?php echo __( 'Status', 'gift-voucher' )?></th>
						<th><?php echo __( 'See Receipt (PDF)', 'gift-voucher' )?></th>
					</thead>
					<tbody>
						<tr>
							<td><h3><?php echo $voucher_options->couponcode; ?></h3></td>
							<td><abbr title="<?php echo date('Y/m/d H:i:s a', strtotime($voucher_options->voucheradd_time)); ?>"><?php echo date('Y/m/d', strtotime($voucher_options->voucheradd_time)); ?></abbr></td>
							<td><?php if($voucher_options->status == 'unused') echo '<span class="vunused">'.__('Unused', 'gift-voucher' ).'</span>'; else echo '<span class="vused">'.__('Voucher Used', 'gift-voucher' ).'</span>'; ?></td>
							<td><?php echo '<a href="'.$voucher_options->voucherpdf_link.'" title="click to show order receipt" target="_blank"><img src="'.WPGIFT__PLUGIN_URL. '/assets/img/pdf.png" width="50"/></a>'; ?></td>
						</tr>
					</tbody>
				</table><br>
				<h2 class="hndle ui-sortable-handle"><span><?php echo __( 'Template Information', 'gift-voucher' )?></span></h2>
				<table class="widefat">
					<thead>
						<tr>
							<th><?php echo __( 'Template Name', 'gift-voucher' )?></th>
							<th><?php echo __( 'Template Image', 'gift-voucher' )?></th>
						</tr>							
					</thead>
					<tbody>
						<tr>
							<td><?php echo $template_options->title; ?></td>
							<td><img src="<?php echo $image_attributes[0]; ?>" height="60"></td>
						</tr>							
					</tbody>
				</table>
				<h2 class="hndle ui-sortable-handle"><span><?php echo __( 'Voucher Information', 'gift-voucher' )?></span></h2>
				<table class="widefat">
					<thead>
						<tr>
							<th width="15%"><?php echo __( 'From', 'gift-voucher' )?></th>
							<th width="15%"><?php echo __( 'To', 'gift-voucher' )?></th>
							<th width="10%"><?php echo __( 'Amount', 'gift-voucher' )?></th>
							<th width="60%"><?php echo __( 'Message', 'gift-voucher' )?></th>
						</tr>							
					</thead>
					<tbody>
						<tr>
							<td><?php echo $voucher_options->from_name; ?></td>
							<td><?php echo $voucher_options->to_name; ?></td>
							<td><?php echo ($setting_options->currency_position == 'Left') ? $setting_options->currency.''.$voucher_options->amount : $voucher_options->amount.''.$setting_options->currency; ?></td>
							<td><?php echo $voucher_options->message; ?></td>
						</tr>
					</tbody>
				</table>
				<h2 class="hndle ui-sortable-handle"><span><?php echo __( 'Buyers Information', 'gift-voucher' ) ?></span></h2>
				<table class="widefat">
					<thead>
						<tr>
							<th width="10%"><?php echo __( 'Name', 'gift-voucher' ) ?></th>
							<th width="20%"><?php echo __( 'Email', 'gift-voucher' ) ?></th>
							<th width="40%"><?php echo __( 'Address', 'gift-voucher' ) ?></th>
							<th width="10%"><?php echo __( 'Postcode', 'gift-voucher' ) ?></th>
							<th width="10%"><?php echo __( 'Payment Method', 'gift-voucher' ) ?></th>
							<th width="10%"><?php echo __( 'Expiry Date', 'gift-voucher' ) ?></th>
						</tr>							
					</thead>
					<tbody>
						<tr>
							<td><?php echo $voucher_options->firstname.' '. $voucher_options->lastname; ?></td>
							<td><?php echo $voucher_options->email; ?></td>
							<td><?php echo $voucher_options->address; ?></td>
							<td><?php echo $voucher_options->postcode; ?></td>
							<td><?php echo $voucher_options->pay_method; ?></td>
							<td><abbr title="<?php echo $voucher_options->expiry; ?>"><?php echo $voucher_options->expiry; ?></abbr></td>
						</tr>							
					</tbody>
				</table><br>
				<a href="<?php echo admin_url( 'admin.php' ); ?>?page=vouchers-lists" class="button button-primary"><?php echo __( 'Back to Vouchers List', 'gift-voucher' )?></a>
			</div>
		</div>
	<?php
	}

	/**
	 * Method for export vouchers in xls
	 */
	function export_orders(){
    	if(is_admin()){
        	global $wpdb;
            if(isset($_POST["tbl_name"])){
                $tablename = sanitize_text_field($_POST["tbl_name"]);
                $sql = "SHOW TABLES";
                $table_list  = $wpdb->get_results($sql,ARRAY_N);
                $IsValidTableName = 0;
                foreach($table_list as $table_name){
                    foreach ($table_name as $singlevalue){
                        if($singlevalue == $tablename){
                            $IsValidTableName = 1;
                        }
                    }
                }
                if($IsValidTableName==1){
					$filename = "export-orders";
                    $con = mysql_connect($wpdb->dbhost,$wpdb->dbuser,$wpdb->dbpassword);
                    mysql_select_db($wpdb->dbname,$con ) or die("Couldn't select database.");
                    $sql = "SELECT * FROM $tablename";
                    $result = @mysql_query($sql) or die("Couldn't execute query:<br>".mysql_error().'<br>'.mysql_errno());
		            ob_clean();
                    header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
                    header("Content-Disposition: attachment; filename= ".$filename."-".date('d-m-y').".xls");  //File name extension was wrong
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Cache-Control: private",false);
                    echo "<html>";
                    echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=Windows-1252\">";
                    echo "<body>";
                    echo "<table>";
                    print("<tr>");
                    for ($i = 0; $i < mysql_num_fields($result); $i++) {  // display name of the column as names of the database fields
                        echo "<th  style='border: thin solid; background-color: #83b4d8;'>" . mysql_field_name($result, $i) . "</th>";
                    }
                    print("</tr>");
                    while($row = mysql_fetch_row($result)){
                        $output = '';
                        $output = "<tr>";
                        for($j=0; $j<mysql_num_fields($result); $j++){
                            if(!isset($row[$j]))
                                $output .= "<td>NULL\t</td>";
                            else
                                $output .= "<td style='border: thin solid;'>$row[$j]\t</td>";
                        }
                        $output .= "</tr>";
                        $output = preg_replace("/\r\n|\n\r|\n|\r/", ' ', $output);
                        print(trim($output));
                    }
                    echo "</table>";
                    echo "</body>";
                    echo "</html>";
                }
                else{
                    echo __('Invalid Request.', 'gift-voucher' );
                }
            }
	?>
		<form action="" method="POST" style="display: inline-block;padding: 0 10px;float:right;">
            <input type="hidden" name="tbl_name" value="<?php echo $wpdb->prefix . "giftvouchers_list"; ?>"/>
            <input class="button button-primary exportbtn" name="exportbtn" type="submit" name="table_display" value="<?php echo __('Export All Orders', 'gift-voucher' ) ?>"/>
		</form>
		<?php 
		} 
	}

	/**
	 * Voucher settings page
	 */
	public function voucher_settings_page() 
	{
		global $wpdb;
		$setting_table_name = $wpdb->prefix . 'giftvouchers_setting';

   		if ( !current_user_can( 'manage_options' ) )
   		{
      		wp_die( 'You are not allowed to be on this page.' );
   		}

   		if ( isset($_POST['company_name']) )
   		{
      		// Check that nonce field
   	  		wp_verify_nonce( $_POST['voucher_settings_verify'], 'voucher_settings_verify' );

		    $company_name 	 		= sanitize_text_field( $_POST['company_name'] );
      		$paypal_email 	 		= sanitize_email( $_POST['paypal_email'] );
      		$sofort_configure_key 	= sanitize_text_field( $_POST['sofort_configure_key'] );
      		$reason_for_payment 	= sanitize_text_field( $_POST['reason_for_payment'] );
      		$sender_name 			= sanitize_text_field( $_POST['sender_name'] );
      		$sender_email 	 		= sanitize_email( $_POST['sender_email'] );
      		$currency_code			= sanitize_text_field($_POST['currency_code']);
      		$currency 		 		= sanitize_text_field( $_POST['currency'] );
      		$paypal 		 		= sanitize_text_field( $_POST['paypal'] );
      		$sofort 		 		= sanitize_text_field( $_POST['sofort'] );
      		$voucher_bgcolor 		= sanitize_text_field( substr($_POST['voucher_bgcolor'],1) );
      		$voucher_color 			= sanitize_text_field( substr($_POST['voucher_color'],1) );
      		$template_col 			= sanitize_text_field( $_POST['template_col'] );
      		$voucher_min_value		= sanitize_text_field( $_POST['voucher_min_value'] );
      		$voucher_max_value		= sanitize_text_field( $_POST['voucher_max_value'] );
      		$voucher_expiry_type	= sanitize_text_field( $_POST['voucher_expiry_type'] );
      		$voucher_expiry			= sanitize_text_field( $_POST['voucher_expiry'] );
      		$voucher_terms_note		= sanitize_text_field( $_POST['voucher_terms_note'] );
      		$currency_position 		= sanitize_text_field( $_POST['currency_position'] );
      		$test_mode 		 		= sanitize_text_field( $_POST['test_mode'] );

		   	$wpdb->update(
				$setting_table_name,
				array( 
					'company_name' 			=> $company_name,
					'paypal_email' 			=> $paypal_email,
					'sofort_configure_key' 	=> $sofort_configure_key,
					'reason_for_payment' 	=> $reason_for_payment,
					'sender_name' 			=> $sender_name,
					'sender_email' 			=> $sender_email,
					'paypal'				=> $paypal,
					'sofort'				=> $sofort,
					'currency_code'			=> $currency_code,
					'currency' 				=> $currency,
					'voucher_bgcolor' 		=> $voucher_bgcolor,
					'voucher_color' 		=> $voucher_color,
					'template_col' 			=> $template_col,
					'voucher_min_value' 	=> $voucher_min_value,
					'voucher_max_value' 	=> $voucher_max_value,
					'voucher_expiry_type'	=> $voucher_expiry_type,
					'voucher_expiry' 		=> $voucher_expiry,
					'voucher_terms_note' 	=> $voucher_terms_note,
					'currency_position' 	=> $currency_position,
					'test_mode' 			=> $test_mode,
				),
				array('id'=>1)
			);
			$settype = 'updated';
			$setmessage = __('Your Settings Saved Successfully.', 'gift-voucher');
			add_settings_error(
    	    	'wooenc_settings_updated',
	        	esc_attr( 'settings_updated' ),
        		$setmessage,
        		$settype
	    	);
   		}

		$options = $wpdb->get_row( "SELECT * FROM $setting_table_name WHERE id = 1" );

		?>
		<div class="wrap wpgiftv-settings">
			<h1><?php echo __( 'Settings', 'gift-voucher' ); ?></h1>
			<hr>
			<?php settings_errors(); ?>
	<div class="wpgiftv-row">
		<div class="wpgiftv-col75">
			<div class="white-box">
			<form method="post" name="voucher-settings" id="voucher-settings" action="<?php echo admin_url( 'admin.php' ); ?>?page=voucher-setting">
				<input type="hidden" name="action" value="save_voucher_settings_option" />
				<?php $nonce = wp_create_nonce( 'voucher_settings_verify' ); ?>
				<input type="hidden" name="voucher_settings_verify" value="<?php echo($nonce); ?>">
				<table class="form-table">
					<tbody>
						<tr><th colspan="2" style="padding-bottom:0;padding-top: 0;"><h3><?php echo __( 'General Settings', 'gift-voucher' ); ?></h3></th></tr>
						<tr>
							<th scope="row">
								<label for="company_name"><?php echo __( 'Company Name', 'gift-voucher' ); ?> <span class="description">(required)</span></label>
							</th>
							<td>
								<input name="company_name" type="text" id="company_name" value="<?php echo esc_html( $options->company_name ); ?>" class="regular-text" aria-required="true" required="required">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="currency_code"><?php echo __( 'Currency Code', 'gift-voucher'  ); ?> <span class="description">(required)</span></label>
							</th>
							<td>
								<input name="currency_code" type="text" id="currency_code" value="<?php echo esc_html( $options->currency_code ); ?>" class="regular-text" aria-required="true" required="required">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="currency"><?php echo __( 'Currency Symbol', 'gift-voucher'  ); ?> <span class="description">(required)</span></label>
							</th>
							<td>
								<input name="currency" type="text" id="currency" value="<?php echo esc_html( $options->currency ); ?>" class="regular-text" aria-required="true" required="required">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="currency_position"><?php echo __( 'Currency Position', 'gift-voucher' ); ?> </label>
							</th>
							<td>
								<select name="currency_position" id="currency_position">
									<option value="Left" <?php echo ($options->currency_position == 'Left') ? 'selected' : ''; ?>>Left</option>
									<option value="Right" <?php echo ($options->currency_position == 'Right') ? 'selected' : ''; ?>>Right</option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="voucher_bgcolor"><?php echo __( 'Voucher Background Color', 'gift-voucher' ); ?> <span class="description">(required)</span></label>
							</th>
							<td>
								<input name="voucher_bgcolor" type="text" id="voucher_bgcolor" value="#<?php echo esc_html( $options->voucher_bgcolor ); ?>" class="regular-text" aria-required="true">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="voucher_color"><?php echo __( 'Voucher Text Color', 'gift-voucher' ); ?> <span class="description">(required)</span></label>
							</th>
							<td>
								<input name="voucher_color" type="text" id="voucher_color" value="#<?php echo esc_html( $options->voucher_color ); ?>" class="regular-text" aria-required="true">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="template_col"><?php echo __( 'Templates Columns', 'gift-voucher'  ); ?></label>
								<p class="description"><?php echo __( 'How many templates show in a row. (Gift Voucher Shortcode)', 'gift-voucher'  ); ?></p>
							</th>
							<td>
								<select name="template_col" id="template_col">
									<option value="3" <?php echo ($options->template_col == 3) ? 'selected' : ''; ?>>3</option>
									<option value="4" <?php echo ($options->template_col == 4) ? 'selected' : ''; ?>>4</option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="voucher_min_value"><?php echo __( 'Minimum Voucher Value', 'gift-voucher'  ); ?></label>
								<p class="description"><?php echo __( 'Leave 0 if no minimum value', 'gift-voucher'  ); ?></p>
							</th>
							<td>
								<input name="voucher_min_value" type="text" id="voucher_min_value" value="<?php echo esc_html( $options->voucher_min_value ); ?>" class="regular-text" aria-required="true">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="voucher_max_value"><?php echo __( 'Maximum Voucher Value', 'gift-voucher'  ); ?></label>
							</th>
							<td>
								<input name="voucher_max_value" type="text" id="voucher_max_value" value="<?php echo esc_html( $options->voucher_max_value ); ?>" class="regular-text" aria-required="true">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="voucher_expiry_type"><?php echo __( 'Voucher Expiry Type', 'gift-voucher'  ); ?></label>
								<p class="description"><?php echo __( 'Select the type of voucher expiration?', 'gift-voucher'  ); ?></p>
							</th>
							<td>
								<select name="voucher_expiry_type" id="template_col">
									<option value="days" <?php echo ($options->voucher_expiry_type == 'days') ? 'selected' : ''; ?>>Days</option>
									<option value="fixed" <?php echo ($options->voucher_expiry_type == 'fixed') ? 'selected' : ''; ?>>Fixed Date</option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="voucher_expiry"><?php echo __( 'Voucher Expiry Value', 'gift-voucher'  ); ?></label>
								<p class="description"><?php echo __( 'Example: (Days: 60, Fixed Date: 20.05.2018)' ); ?></p>
							</th>
							<td>
								<input name="voucher_expiry" type="text" id="voucher_expiry" value="<?php echo esc_html( $options->voucher_expiry ); ?>" class="regular-text" aria-required="true">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="voucher_terms_note"><?php echo __( 'Voucher Terms Note', 'gift-voucher'  ); ?></label>
								<p class="description"><?php echo __( 'Terms note in voucher order page', 'gift-voucher'  ); ?></p>
							</th>
							<td>
								<textarea name="voucher_terms_note" id="voucher_terms_note" class="regular-text" aria-required="true" rows="4"><?php echo esc_html( $options->voucher_terms_note ); ?></textarea>
							</td>
						</tr>
						<tr><th colspan="2" style="padding-bottom:0"><hr><h3><?php echo __( 'Payment Settings', 'gift-voucher'  ); ?></h3></th></tr>
						<tr>
							<th scope="row">
								<label for="paypal"><?php echo __( 'Paypal Enable', 'gift-voucher'  ); ?></label>
							</th>
							<td>
								<select name="paypal" id="paypal">
									<option value="1" <?php echo ($options->paypal == 1) ? 'selected' : ''; ?>>Yes</option>
									<option value="0" <?php echo ($options->paypal == 0) ? 'selected' : ''; ?>>No</option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="paypal_email"><?php echo __( 'Paypal Email', 'gift-voucher'  ); ?></label>
							</th>
							<td>
								<input name="paypal_email" type="email" id="paypal_email" value="<?php echo esc_html( $options->paypal_email ); ?>" class="regular-text" aria-describedby="paypal-description">
								<p class="description" id="paypal-description"><?php echo __( 'This address is used for paypal payment.' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="test_mode"><?php echo __( 'Paypal Testmode', 'gift-voucher'  ); ?></label>
							</th>
							<td>
								<select name="test_mode" id="test_mode">
									<option value="1" <?php echo ($options->test_mode == 1) ? 'selected' : ''; ?>>Yes</option>
									<option value="0" <?php echo ($options->test_mode == 0) ? 'selected' : ''; ?>>No</option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="sofort"><?php echo __( 'Sofort Enable', 'gift-voucher'  ); ?></label>
							</th>
							<td>
								<select name="sofort" id="sofort">
									<option value="1" <?php echo ($options->sofort == 1) ? 'selected' : ''; ?>>Yes</option>
									<option value="0" <?php echo ($options->sofort == 0) ? 'selected' : ''; ?>>No</option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="sofort_configure_key"><?php echo __( 'Sofort Configuration Key', 'gift-voucher' ); ?></label>
								<p class="description"><?php echo __( 'Enter your configuration key. you only can create a new configuration key by creating a new Gateway project in your account at sofort.com.', 'gift-voucher' ); ?></p>
							</th>
							<td>
								<input name="sofort_configure_key" type="text" id="sofort_configure_key" value="<?php echo esc_html( $options->sofort_configure_key ); ?>" class="regular-text" aria-describedby="paypal-description">
								<p class="description"><?php echo __( 'This key is used for Sofort Payment.', 'gift-voucher' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="reason_for_payment"><?php echo __( 'Reason for Payment', 'gift-voucher'  ); ?></label>
								<p class="description"><?php echo __( 'Reason for payment from Sofort.', 'gift-voucher'  ); ?></p>
							</th>
							<td>
								<input name="reason_for_payment" type="text" id="reason_for_payment" value="<?php echo esc_html( $options->reason_for_payment ); ?>" class="regular-text" aria-describedby="paypal-description">
							</td>
						</tr>
						<tr><th colspan="2" style="padding-bottom:0"><hr><h3><?php echo __( 'Email Settings', 'gift-voucher'  ); ?></h3></th></tr>
						<tr>
							<th scope="row">
								<label for="sender_name"><?php echo __( 'Sender Name', 'gift-voucher' ); ?></label>
								<p class="description"><?php echo __( 'For emails send by this plugin.', 'gift-voucher' ); ?></p>
							</th>
							<td>
								<input name="sender_name" type="text" id="sender_name" value="<?php echo esc_html( $options->sender_name ); ?>" class="regular-text" aria-describedby="sendername-description">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="sender_email"><?php echo __( 'Sender Email', 'gift-voucher' ); ?></label>
								<p class="description"><?php echo __( 'For emails send by this plugin.', 'gift-voucher' ); ?></p>
							</th>
							<td>
								<input name="sender_email" type="email" id="sender_email" value="<?php echo esc_html( $options->sender_email ); ?>" class="regular-text" aria-describedby="senderemail-description">
							</td>
						</tr>
					</tbody>
				</table>
				<p class="submit"><?php submit_button( __( 'Save Settings', 'gift-voucher'), 'primary', 'submit', false ); ?></p>
			</form></div></div>

		<div class="wpgiftv-col25">
			<div class="white-box rating-box">
				<h2>Rate Our Plugin</h2>
				<div class="star-ratings">
					<span class="dashicons dashicons-star-filled"></span>
					<span class="dashicons dashicons-star-filled"></span>
					<span class="dashicons dashicons-star-filled"></span>
					<span class="dashicons dashicons-star-filled"></span>
					<span class="dashicons dashicons-star-filled"></span>
				</div>
				<p>Did WordPress Gift Voucher help you out? Please leave a 5-star review. Thank you!</p>
				<a href="#" class="button button-primary">Write a review</a>
			</div>
			<div class="white-box">
				<h2>Try Premium</h2>
				<p>Do you want to discover all plugin features without any limitations? Would you like to try it?</p>
				<ul>
					<li><span class="dashicons dashicons-arrow-right"></span> Create unlimited gift voucher templates</li>
					<li><span class="dashicons dashicons-arrow-right"></span> No limit for customer orders</li>
					<li><span class="dashicons dashicons-arrow-right"></span> Set voucher maximum value</li>
					<li><span class="dashicons dashicons-arrow-right"></span> Set voucher expiry days</li>
					<li><span class="dashicons dashicons-arrow-right"></span> Order Email Template</li>
					<li><span class="dashicons dashicons-arrow-right"></span> 6 month support & updates included</li>
				</ul>
				<p>For more information about the PREMIUM version of WordPress Gift Voucher, visit the official page on <a href="http://www.codemenschen.at/wordpress-gift-voucher-plugin/" target="_blank">codemenschen.at</a></p>
				<a href="http://www.codemenschen.at/downloads/wordpress-gift-voucher-pro/" class="button button-primary" target="_blank">Buy Premium Plugin</a>
			</div>
			<div class="white-box">
				<h2>Having Issues?</h2>
				<p>Need a helping hand? Please ask for help on the <a href="http://www.codemenschen.at/submit-ticket/" target="_blank">Support forum</a>. Be sure to mention your WordPress version and give as much additional information as possible.</p>
				<a href="http://www.codemenschen.at/submit-ticket/?page=tickets&section=create-ticket" class="button button-primary" target="_blank">Submit your question</a>
			</div>
			<div class="white-box">
				<h2>Customization Service</h2>
				<p>We are a European Company. To hire our agency to help you with this plugin installation or any other customization or requirements please contact us through our site <a href="http://www.codemenschen.at/contact-us" target="_blank">contact form</a> or email <a href="mailto:office@telberia.com">office@telberia.com</a> directly.</p>
				<a href="http://www.codemenschen.at/contact-us" class="button button-primary" target="_blank">Hire Us Now</a>
			</div>
		</div>
		</div>
		<span class="wpgiftv-disclaimer">Thank you for using <b>WordPress Gift Voucher</b>.</span>
		</div>
		<?php
	}

	/**
	 * Voucher Template page
	 */
	public function voucher_template_page()
	{
		global $wpdb;
		$template_table = $wpdb->prefix . 'giftvouchers_template';
		$template_count = $wpdb->get_var( "SELECT COUNT(*) FROM $template_table" );

		?>
		<div class="wrap">
			<h1><?php echo __('Voucher Templates', 'gift-voucher' ); ?> <?php if($template_count <= 3) { ?><a class="page-title-action" id="add_new_template" href="<?php echo admin_url( 'admin.php' ); ?>?page=new-voucher-template"><?php echo __('Add New Template', 'gift-voucher' ) ?></a> <?php } ?></h1>
			<div class="content">
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->vouchers_obj->prepare_items();
								$this->vouchers_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php 
	}

	/**
	 * Add Voucher Template page
	 */
	public function new_voucher_template_page()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'giftvouchers_template';

		if ( !current_user_can( 'manage_options' ) )
		{
			wp_die( 'You are not allowed to be on this page.' );
   		}

		$template_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

		if($template_count > 3) {
			wp_die( 'Maximum 3 templates allowed in free version of this plugin. If you want more template upgrade to primium version.' );
		}

		$notice = 0;
		$pageTitle = __('Add New Template', 'gift-voucher');
		$btnText = __('Add Template', 'gift-voucher' );
		$options = (object) array();
		$options->title = $options->image = $options->active = $options->action = $options->template_id = '';

   		if(isset($_REQUEST['template_id'])) {
   			$template_id = $_REQUEST['template_id'];
   			$pageTitle = __('Edit Template', 'gift-voucher');
   			$btnText = __('Edit Template', 'gift-voucher');
   			$options->template_id = $template_id;
   		}
   		if(isset($_POST['title']) && $_REQUEST['action'] == 'edit_template')
   		{
			// Check that nonce field
			wp_verify_nonce( $_POST['new_template_verify'], 'new_template_verify' );

			$title 	= sanitize_text_field( $_POST['title'] );
			$image 	= sanitize_text_field( $_POST['image'] );
			$active = sanitize_text_field( $_POST['active'] );

			$wpdb->update(
				$table_name,
				array( 
					'title' 	=> $title,
					'image' 	=> $image,
					'active' 	=> $active,
				),
				array('id'=>$_REQUEST['template_id'])
			);
			$notice = 1;
			$templateMsg = __('Template Updated Successfully!', 'gift-voucher' );
			$options = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = $template_id" );
   			$options->action = 'edit_template';
   			$options->template_id = $template_id;
   			wpgv_get_image_url($image);

   		}
   		elseif ( isset($_POST['title']) )
		{
			// Check that nonce field
			wp_verify_nonce( $_POST['new_template_verify'], 'new_template_verify' );

			$title 	= sanitize_text_field( $_POST['title'] );
			$image 	= sanitize_text_field( $_POST['image'] );
			$active = sanitize_text_field( $_POST['active'] );

			$wpdb->insert(
				$table_name,
				array( 
					'title' 	=> $title,
					'image' 	=> $image,
					'active' 	=> $active,
				)
			);
			$notice = 1;
			$templateMsg = __('Template Added Successfully!', 'gift-voucher' );
			$lastid = $wpdb->insert_id;
			$options = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = $lastid" );
   			$options->action = 'edit_template';
   			$options->template_id = $template_id;
   			wpgv_get_image_url($image);
   		}
   		elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit_template')
   		{
   			$options = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = $template_id" );
   			$options->action = 'edit_template';
   			$options->template_id = $template_id;
   			wpgv_get_image_url($options->image);
   		}

   		if(function_exists( 'wp_enqueue_media' )){
    		wp_enqueue_media();
		} else{
    		wp_enqueue_style('thickbox');
    		wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
		}

		?>
		<div class="wrap">
			<h1><?php echo $pageTitle; ?></a></h1>
			<?php if($notice) { ?>
			<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
				<p><strong><?php echo $templateMsg; ?></strong></p>
				<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
			</div>
			<?php } ?>
			<form method="post" name="new-template" id="new-template" action="<?php echo admin_url( 'admin.php' ); ?>?page=new-voucher-template">
				<input type="hidden" name="action" value="save_voucher_settings_option" />
				<?php $nonce = wp_create_nonce( 'new_template_verify' ); ?>
				<input type="hidden" name="new_template_verify" value="<?php echo($nonce); ?>">
				<input type="hidden" name="action" value="<?php echo $options->action; ?>">
				<input type="hidden" name="template_id" value="<?php echo $options->template_id; ?>">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="title"><?php echo __('Title', 'gift-voucher' ) ?> <span class="description">(required)</span></label>
							</th>
							<td>
								<input name="title" type="text" id="title" value="<?php echo $options->title; ?>" class="regular-text" aria-required="true" required="required">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="image"><?php echo __('Image', 'gift-voucher' ) ?> <span class="description">(required)</span></label>
								<p class="description">(1235px x 939px)</p>
							</th>
							<td>
								<img class="image_src" src="" width="100" style="display: none;" /><br>
								<input class="image_url" type="hidden" name="image" size="60" value="<?php echo $options->image; ?>">
 								<button type="button" class="upload_image button"><?php echo __('Upload Image', 'gift-voucher' ) ?></button>
 								<button type="button" class="button button-primary remove_image" style="display: none;"><?php echo __('Remove Image', 'gift-voucher') ?></button>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="active"><?php echo __('Status', 'gift-voucher') ?></label>
							</th>
							<td>
								<select name="active" id="active">
									<option value="1" <?php echo ($options->active == 1) ? 'selected' : ''; ?>><?php echo __('Active', 'gift-voucher') ?></option>
									<option class="0" <?php echo ($options->active == 0) ? 'selected' : ''; ?>><?php echo __('Inactive', 'gift-voucher' ) ?></option>
								</select>
							</td>
						</tr>
					</tbody>
				</table>
				<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo $btnText; ?>"></p>
			</form>
		</div>
		<script>
    	jQuery(document).ready(function($) {
        	$('.upload_image').click(function(e) {
            	e.preventDefault();

            	var custom_uploader = wp.media({
                	title: 'Add Template Image',
                	button: {
                    	text: 'Upload Image'
                	},
                	multiple: false  // Set this to true to allow multiple files to be selected
            	})
            	.on('select', function() {
	                var attachment = custom_uploader.state().get('selection').first().toJSON();
    	            $('.image_src').attr('src', attachment.url).show();
        	        $('.image_url').val(attachment.id);
        	        $('.remove_image').show();
            	})
            	.open();
        	});
        	$('.remove_image').click(function () {
        		$('.image_src').attr('src','').hide();
        		$('.image_url').val('');
        	    $('.remove_image').hide();
        	});
    	});
		</script>
	<?php 
	}

	/**
	 * Screen options for voucher list
	 */
	public function screen_option_voucher()
	{
		$option = 'per_page';
		$args   = [
			'label'   => __('Gift Vouchers', 'gift-voucher'),
			'default' => 20,
			'option'  => 'vouchers_per_page'
		];

		add_screen_option( $option, $args );

		$this->vouchers_obj = new WPGV_Voucher_List();
	}

	/**
	 * Screen options for voucher templates
	 */
	public function screen_option_template()
	{
		$option = 'per_page';
		$args   = [
			'label'   => __('Voucher Templates', 'gift-voucher'),
			'default' => 20,
			'option'  => 'templates_per_page'
		];

		add_screen_option( $option, $args );

		$this->vouchers_obj = new WPGV_Voucher_Template();
	}

	/** Singleton instance */
	public static function get_instance() 
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

/**
 * Method for get image url by id (Only for Template Page)
 */
function wpgv_get_image_url($id)
{
	$image_attributes = wp_get_attachment_image_src( $id, 'voucher-thumb' );
	?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('.image_src').attr('src', '<?php echo $image_attributes[0]; ?>').show();
			$('.remove_image').show();
		});
	</script>
	<?php
}

add_action( 'admin_post_save_voucher_settings_option', 'process_voucher_settings_options' );