<?php
/*
 * Template Name: PDF Viewer Page Template
 * Description: A Page Template for pdf viewer.
 */

if(!isset($_GET['action'])) {
	exit();
}
if ( ! wp_verify_nonce( $_GET['nonce'], 'voucher_form_verify' ) ) {
     die( 'Security check' ); 
}

$watermark = 'This is a preview voucher.';
if(sanitize_text_field($_GET['action']) == 'preview') {
	$watermark = 'This is a preview voucher.';
} else {
	exit();
}

$template = sanitize_text_field(base64_decode($_GET['template']));
$for = sanitize_text_field(base64_decode($_GET['for']));
$from = sanitize_text_field(base64_decode($_GET['from']));
$value = sanitize_text_field(base64_decode($_GET['value']));
$message = sanitize_textarea_field(base64_decode($_GET['message']));
$expiry = base64_decode($_GET['expiry']);
$code = sanitize_text_field(base64_decode($_GET['code']));

global $wpdb;
$setting_table 	= $wpdb->prefix . 'giftvouchers_setting';
$template_table = $wpdb->prefix . 'giftvouchers_template';
$setting_options = $wpdb->get_row( "SELECT * FROM $setting_table WHERE id = 1" );
$template_options = $wpdb->get_row( "SELECT * FROM $template_table WHERE id = $template" );
$image_attributes = wp_get_attachment_image_src( $template_options->image, 'full' );
$image = ($image_attributes) ? $image_attributes[0] : WPGIFT__PLUGIN_URL.'/assets/img/demo.png';
$voucher_bgcolor = wpgv_hex2rgb($setting_options->voucher_bgcolor);
$voucher_color = wpgv_hex2rgb($setting_options->voucher_color);
$currency = ($setting_options->currency_position == 'Left') ? $setting_options->currency.''.$value : $value.''.$setting_options->currency;

$pdf = new WPGV_PDF('P','pt',array(595,900));
$pdf->SetAutoPageBreak(0);
$pdf->AddPage();
$pdf->Image($image, 0, 0, 595, 453);
$pdf->SetFont('Arial','',16);
$pdf->SetXY(0, 453);
$pdf->SetFillColor($voucher_bgcolor[0], $voucher_bgcolor[1], $voucher_bgcolor[2]);
$pdf->Cell(595,450,'',0,1,'L',1);
//Put the watermark
$pdf->SetXY(0, 0);
$pdf->SetFont('Arial','B',55);
$pdf->SetTextColor(215,215,215);
$pdf->RotatedText(75,700,$watermark,45);
//Voucher
$pdf->SetXY(0, 490);
$pdf->SetFont('Arial','B',16);
$pdf->SetTextColor(255,255,255);
$pdf->SetFontSize(25);
$pdf->Cell(0,0, $template_options->title,0,1,'C',0);
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

$pdf->Output();
?>
