<?php

// namespace Sofort\SofortLib;

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

function wpgv__doajax_pdf_save_func() {
	if ( ! wp_verify_nonce( $_POST['nonce'], 'voucher_form_verify' ) ) {
     	die( 'Security check' ); 
	}
	$template = sanitize_text_field(base64_decode($_POST['template']));
	$for = sanitize_text_field(base64_decode($_POST['for']));
	$from = sanitize_text_field(base64_decode($_POST['from']));
	$value = sanitize_text_field(base64_decode($_POST['value']));
	$message = sanitize_textarea_field(base64_decode($_POST['message']));
	$expiry = base64_decode($_POST['expiry']);
	$code = sanitize_text_field(base64_decode($_POST['code']));
	$firstname = sanitize_text_field(base64_decode($_POST['firstname']));
	$lastname = sanitize_text_field(base64_decode($_POST['lastname']));
	$email = sanitize_email(base64_decode($_POST['email']));
	$address = sanitize_text_field(base64_decode($_POST['address']));
	$pincode = sanitize_text_field(base64_decode($_POST['pincode']));
	$paymentmethod = sanitize_text_field(base64_decode($_POST['paymentmethod']));

	global $wpdb;
	$voucher_table 	= $wpdb->prefix . 'giftvouchers_list';
	$setting_table 	= $wpdb->prefix . 'giftvouchers_setting';
	$template_table = $wpdb->prefix . 'giftvouchers_template';
	$setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );
	$template_options = $wpdb->get_row( "SELECT * FROM $template_table WHERE id = $template" );
	$image_attributes = wp_get_attachment_image_src( $template_options->image, 'full' );
	$image_attributes = ($image_attributes) ? $image_attributes[0] : WPGIFT__PLUGIN_URL.'/assets/img/demo.png';
	$voucher_bgcolor = wpgv_hex2rgb($setting_options->voucher_bgcolor);
	$voucher_color = wpgv_hex2rgb($setting_options->voucher_color);
	$currency = ($setting_options->currency_position == 'Left') ? $setting_options->currency.''.$value : $value.''.$setting_options->currency;

	$upload = wp_upload_dir();
 	$upload_dir = $upload['basedir'];
 	$upload_dir = $upload_dir . '/voucherpdfuploads/'.$_POST['code'].'.pdf';
 	$upload_url = $upload['baseurl'];
 	$upload_url = $upload_url . '/voucherpdfuploads/'.$_POST['code'].'.pdf';

	$pdf = new WPGV_PDF('P','pt',array(595,900));
	$pdf->SetAutoPageBreak(0);
	$pdf->AddPage();
	$pdf->Image($image_attributes, 0, 0, 595, 453);
	$pdf->SetFont('Arial','',16);
	$pdf->SetXY(0, 453);
	$pdf->SetFillColor($voucher_bgcolor[0], $voucher_bgcolor[1], $voucher_bgcolor[2]);
	$pdf->Cell(595,450,'',0,1,'L',1);
	//Voucher
	$pdf->SetXY(0, 490);
	$pdf->SetFont('Arial','B',16);
	$pdf->SetTextColor(255,255,255);
	$pdf->SetFontSize(25);
	$pdf->Cell(0,0,$template_options->title,0,1,'C',0);
	//For
	$pdf->SetFont('Arial','');
	$pdf->SetXY(30, 520);
	$pdf->SetTextColor($voucher_color[0],$voucher_color[1],$voucher_color[2]);
	$pdf->SetFontSize(12);
	$pdf->Cell(0,0,__('For', 'gift-voucher'),0,1,'L',0);
	//For Input
	$pdf->SetXY(33, 530);
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor(85,85,85);
	$pdf->SetFontSize(15);
	$pdf->Cell(265,30,' '.$for,0,1,'L',1);
	//From
	$pdf->SetXY(310, 520);
	$pdf->SetTextColor($voucher_color[0],$voucher_color[1],$voucher_color[2]);
	$pdf->SetFontSize(12);
	$pdf->Cell(0,0,__('From', 'gift-voucher'),0,1,'L',0);
	//From Input
	$pdf->SetXY(313, 530);
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor(85,85,85);
	$pdf->SetFontSize(15);
	$pdf->Cell(265,30,' '.$from,0,1,'L',1);
	//Voucher Value
	$pdf->SetXY(30, 580);
	$pdf->SetTextColor($voucher_color[0],$voucher_color[1],$voucher_color[2]);
	$pdf->SetFontSize(12);
	$pdf->Cell(0,0,__('Voucher Value', 'gift-voucher'),0,1,'L',0);
	//Voucher Value Input
	$pdf->SetXY(33, 590);
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor(85,85,85);
	$pdf->SetFontSize(16);
	$pdf->Cell(265,30,' '.$currency,0,1,'L',1);
	//Personal Message
	$pdf->SetXY(30, 640);
	$pdf->SetTextColor($voucher_color[0],$voucher_color[1],$voucher_color[2]);
	$pdf->SetFontSize(12);
	$pdf->Cell(0,0,__('Personal Message', 'gift-voucher'),0,1,'L',0);
	//Personal Message Input
	$pdf->SetXY(33, 650);
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor(85,85,85);
	$pdf->SetFontSize(15);
	$pdf->Cell(543,100,'',0,1,'L',1);
	$pdf->SetXY(33, 650);
	$pdf->MultiCell(543,23,$message,0,1,'L',1);
	//Date of Expiry
	$pdf->SetXY(30, 770);
	$pdf->SetTextColor($voucher_color[0],$voucher_color[1],$voucher_color[2]);
	$pdf->SetFontSize(12);
	$pdf->Cell(0,0,__('Date of Expiry', 'gift-voucher'),0,1,'L',0);
	//Date of Expiry Input
	$pdf->SetXY(33, 780);
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor(85,85,85);
	$pdf->SetFontSize(16);
	$pdf->Cell(265,30,' '.$expiry,0,1,'L',1);
	//Coupon Code
	$pdf->SetXY(310, 770);
	$pdf->SetTextColor($voucher_color[0],$voucher_color[1],$voucher_color[2]);
	$pdf->SetFontSize(12);
	$pdf->Cell(0,0,__('Coupon Code', 'gift-voucher'),0,1,'L',0);
	//Coupon Code Input
	$pdf->SetXY(313, 780);
	$pdf->SetFillColor(255,255,255);
	$pdf->SetTextColor(85,85,85);
	$pdf->SetFontSize(16);
	$pdf->Cell(265,30,' '.$code,0,1,'L',1);
	//Company Details
	$pdf->SetXY(30, 840);
	$pdf->SetTextColor(255,255,255);
	$pdf->SetFontSize(11);
	$pdf->Cell(0,0,get_site_url().' | '.get_option('admin_email'),0,1,'C',0);
	//Terms
	$pdf->SetXY(0, 0);
	$pdf->SetTextColor(255,255,255);
	$pdf->SetFontSize(9);
	$pdf->RotatedText(20,850,'* '.__('Cash payment is not possible. The terms and conditions apply.', 'gift-voucher'),90);

	$pdf->Output('F',$upload_dir);

	$expiryCard = ($setting_options->voucher_expiry_type == 'days') ? date('d.m.Y',strtotime('+'.$setting_options->voucher_expiry.' days',time())) . PHP_EOL : $setting_options->voucher_expiry;

	$wpdb->insert(
		$voucher_table,
		array(
			'template_id' 		=> $template,
			'from_name' 		=> $for,
			'to_name' 			=> $from,
			'amount'			=> $value,
			'message'			=> $message,
			'firstname'			=> $firstname,
			'lastname'			=> $lastname,
			'email'				=> $email,
			'address'			=> $address,
			'postcode'			=> $pincode,
			'pay_method'		=> $paymentmethod,
			'expiry'			=> $expiryCard,
			'couponcode'		=> $code,
			'voucherpdf_link'	=> $upload_url,
			'payment_status'	=> 'Not Pay'
		)
	);

	$lastid = $wpdb->insert_id;
	$return_url = get_site_url() .'/voucher-payment-successful/?voucheritem='.$lastid;
	$cancel_url = get_site_url() .'/voucher-payment-cancel/?voucheritem='.$lastid;
	$notify_url = get_site_url() .'/voucher-payment-successful/?voucheritem='.$lastid;

	if ($paymentmethod == 'Paypal') {

		$paypal_email = $setting_options->paypal_email;

		$querystring = '';
		if($setting_options->test_mode) {
			$querystring .= 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_xclick';
		} else {
			$querystring .= 'https://www.paypal.com/cgi-bin/webscr?cmd=_xclick';
		}
		$querystring .= "&business=".urlencode($paypal_email)."&";
		$querystring .= "item_name=".urlencode($template_options->title.' Voucher')."&";
    	$querystring .= "item_number=".urlencode($lastid)."&";
    	$querystring .= "amount=".urlencode($value)."&";
    	$querystring .= "currency_code=$setting_options->currency_code&";
    	$querystring .= "first_name=".urlencode($firstname)."&";
    	$querystring .= "last_name=".urlencode($lastname)."&";
    	$querystring .= "email=".urlencode($email)."&";
    	$querystring .= "custom=".urlencode($lastid)."&";
    	$querystring .= "return=".urlencode(stripslashes($return_url))."&";
    	$querystring .= "cancel_return=".urlencode(stripslashes($cancel_url))."&";
    	$querystring .= "notify_url=".urlencode($notify_url);

	    echo $querystring;
		
	} else if($paymentmethod == 'Sofort') {

		$Sofortueberweisung = new Sofortueberweisung($setting_options->sofort_configure_key);

		$Sofortueberweisung->setAmount($value);
		$Sofortueberweisung->setCurrencyCode($setting_options->currency_code);

		$Sofortueberweisung->setReason($setting_options->reason_for_payment, $lastid);
		$Sofortueberweisung->setSuccessUrl($return_url, true);
		$Sofortueberweisung->setAbortUrl($cancel_url);
		$Sofortueberweisung->setNotificationUrl($notify_url);

		$Sofortueberweisung->sendRequest();

		if($Sofortueberweisung->isError()) {
			//SOFORT-API didn't accept the data
			echo $Sofortueberweisung->getError();
		} else {
			//buyer must be redirected to $paymentUrl else payment cannot be successfully completed!
			$paymentUrl = $Sofortueberweisung->getPaymentUrl();
			echo $paymentUrl;
		}
	}

	wp_die();
}
add_action('wp_ajax_nopriv_wpgv_doajax_pdf_save_func', 'wpgv__doajax_pdf_save_func');
add_action('wp_ajax_wpgv_doajax_pdf_save_func', 'wpgv__doajax_pdf_save_func');