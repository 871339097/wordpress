<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

// Add Voucher Payment Successful Shortcode
function wpgv_voucher_successful_shortcode() 
{
	global $wpdb;
	$voucher_table 	= $wpdb->prefix . 'giftvouchers_list';
	$setting_table 	= $wpdb->prefix . 'giftvouchers_setting';
	$setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );
	if (isset($_GET['voucheritem'])) {
		$voucheritem = sanitize_text_field($_GET['voucheritem']);
		$voucher_options = $wpdb->get_row( "SELECT * FROM $voucher_table WHERE id = $voucheritem" );

		$wpdb->update(
			$voucher_table,
			array( 
				'payment_status' => __('Paid'),
			),
			array('id'=>$voucheritem)
		);

		$sub = 'Order Confirmation - Your Order with '.$setting_options->company_name.' (Voucher Order No: '.$voucher_options->id.') has been successfully placed!';
		$msg = 'Dear '.$voucher_options->firstname.' '. $voucher_options->lastname.', <br/><br/>Order successfully placed.<br>We are pleased to confirm your order no '.$voucheritem.'<br>Thank you for shopping with '.$setting_options->company_name.'! <br><br>- For any clarifications please feel free to email us at '.$setting_options->sender_email.'<br><br>Thanking you once again for your patronage.<br><br><b>Warm Regards,<br/>'.$setting_options->company_name.'</b>';

	$upload = wp_upload_dir();
 	$upload_dir = $upload['basedir'];
		$attachments = $upload_dir.'/voucherpdfuploads/'.base64_encode($voucher_options->couponcode).'.pdf';

		$to = $voucher_options->firstname.' '. $voucher_options->lastname .'<'.$voucher_options->email.'>';
		$subject = $sub;
		$body = $msg;
		$headers = 'Content-type: text/html;charset=utf-8' . "\r\n";
		$headers .= 'From: '.$setting_options->sender_name.' <'.$setting_options->sender_email.'>' . "\r\n";
		$headers .= 'Reply-to: '.$setting_options->sender_name.' <'.$setting_options->sender_email.'>' . "\r\n";
		$mail_sent = wp_mail( $to, $subject, $body, $headers, $attachments );

		if($mail_sent == true){
			echo '<div class="success">'.__('We have got your order!', 'gift-voucher').'<br/>'.__('E-Mail Sent Successfully to', 'gift-voucher').' <b><i>'.$voucher_options->email.'</i> </b>.</div>';

$toadmin = $setting_options->sender_name.' <'.$setting_options->sender_email.'>';
			$bodyadmin = "Hello,<br><br>New Voucher Order received.<br><br>Order Id: $voucher_options->id<br><br>Name: $voucher_options->firstname $voucher_options->lastname<br>Email: $voucher_options->email<br>Address: $voucher_options->address<br>Pincode: $voucher_options->postcode<br>";
			$headersadmin = 'Content-type: text/html;charset=utf-8' . "\r\n";
			$headersadmin .= 'From: '.$setting_options->sender_name.' <'.$setting_options->sender_email.'>' . "\r\n";
			$headersadmin .= 'Reply-to: '.$voucher_options->firstname.' '. $voucher_options->lastname.' <'.$voucher_options->email.'>' . "\r\n";

			wp_mail( $toadmin, "New Voucher Order Received from $voucher_options->firstname $voucher_options->lastname (Order No: $voucher_options->id)!", $bodyadmin, $headersadmin );
}
		else{
			echo '<div class="error"><p>'.__('Some Error Occurred From Sending this Email! <br>(Reload and Retry Again!)', 'gift-voucher').'</p></div>';}

	}
}
add_shortcode( 'wpgv_giftvouchersuccesspage', 'wpgv_voucher_successful_shortcode' );

// Add Voucher Payment Cancel Shortcode
function wpgv_voucher_cancel_shortcode() 
{
	global $wpdb;
	$voucher_table 	= $wpdb->prefix . 'giftvouchers_list';
	if (isset($_GET['voucheritem'])) {
		$voucheritem = sanitize_text_field($_GET['voucheritem']);
		$wpdb->delete( $voucher_table, array( 'id' => $voucheritem ), array( '%d' ) );
		echo sprintf('You cancel your order. Please again place your order from <a href="%s/gift-voucher">here</a>.', get_site_url());
	}
}
add_shortcode( 'wpgv_giftvouchercancelpage', 'wpgv_voucher_cancel_shortcode' );