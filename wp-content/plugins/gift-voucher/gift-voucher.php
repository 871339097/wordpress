<?php
/*
Plugin Name: Gift Voucher
Description: Allow your users to buy and send gift cards via email, an easy and direct way to encourage new sales.
Plugin URI: http://www.telberia.com/
Author: codemenschen
Author URI: http://www.codemenschen.at/
Version: 1.0.5
Text Domain: gift-voucher
Domain Path:       /languages
*/

// plugin variable: wpgiftv

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

define( 'WPGIFT_VERSION', '1.0.5' );
define( 'WPGIFT__MINIMUM_WP_VERSION', '4.0' );
define( 'WPGIFT__PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WPGIFT__PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

if ( ! class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

function wpgiftv_plugin_init() {
  $langOK = load_plugin_textdomain( 'gift-voucher', false, dirname( plugin_basename(__FILE__) ) .'/languages' );
}
add_action('plugins_loaded', 'wpgiftv_plugin_init');

require_once( WPGIFT__PLUGIN_DIR .'/library/sofort/payment/sofortLibSofortueberweisung.inc.php');
require_once( WPGIFT__PLUGIN_DIR .'/library/fpdf/rotation.php');
require_once( WPGIFT__PLUGIN_DIR .'/admin.php');
require_once( WPGIFT__PLUGIN_DIR .'/front.php');
require_once( WPGIFT__PLUGIN_DIR .'/classes/voucher.php');
require_once( WPGIFT__PLUGIN_DIR .'/classes/template.php');
require_once( WPGIFT__PLUGIN_DIR .'/classes/page_template.php');
require_once( WPGIFT__PLUGIN_DIR .'/include/pdf.php');
require_once( WPGIFT__PLUGIN_DIR .'/include/voucher-shortcodes.php');

add_action( 'plugins_loaded', function () {
  WPGiftVoucherAdminPages::get_instance();
} );


class WPGV_PDF extends WPGV_PDF_Rotate
{
  function RotatedText($x,$y,$txt,$angle)
  {
      //Text rotated around its origin
      $this->Rotate($angle,$x,$y);
      $this->Text($x,$y,$txt);
      $this->Rotate(0);
  }
}

add_action( 'plugins_loaded', 'wpgv_voucher_imagesize_setup' );
function wpgv_voucher_imagesize_setup() {
    add_image_size( 'voucher-thumb', 300 );
    add_image_size( 'voucher-medium', 450 );
}

function wpgv_front_enqueue() {
  $translations = array( 'ajaxurl' => admin_url('admin-ajax.php'), 'select_template' => __('Please select voucher template'), 'accept_terms' => __('Please accept the terms and conditions'), 'finish' => __('Finish'), 'next' => __('Continue'), 'previous' => __('Back'), 'submitted' => __('Submitted!'), 'error_occur' => __('Error occurred'));
  wp_enqueue_script('jquery');
  wp_enqueue_style( 'voucher-style',  WPGIFT__PLUGIN_URL.'/assets/css/voucher-style.css');
  wp_enqueue_script('jquery-validate', WPGIFT__PLUGIN_URL  . '/assets/js/jquery.validate.min.js', array('jquery'), '1.0.0', true);
  wp_enqueue_script('jquery-steps', WPGIFT__PLUGIN_URL  . '/assets/js/jquery.steps.min.js', array('jquery'), '1.0.0', true);
  wp_enqueue_script('voucher-script', WPGIFT__PLUGIN_URL  . '/assets/js/voucher-script.js', array('jquery'), '1.0.0', true);
  wp_localize_script( 'voucher-script', 'frontend_ajax_object', $translations );
}

add_action( 'wp_enqueue_scripts','wpgv_front_enqueue');

function wpgv_plugin_activation() {
  global $wpdb;
  global $jal_db_version;

  $giftvouchers_setting = $wpdb->prefix . 'giftvouchers_setting';
  $giftvouchers_list = $wpdb->prefix . 'giftvouchers_list';
  $giftvouchers_template = $wpdb->prefix . 'giftvouchers_template';
  
  $charset_collate = $wpdb->get_charset_collate();

  $giftvouchers_setting_sql = "CREATE TABLE $giftvouchers_setting (
        id int(11) NOT NULL AUTO_INCREMENT,
        company_name varchar(255) DEFAULT NULL,
        currency_code varchar(3) DEFAULT NULL,
        currency varchar(2) DEFAULT NULL,
        currency_position varchar(10) DEFAULT NULL,
        voucher_bgcolor varchar(6) DEFAULT NULL,
        voucher_color varchar(6) DEFAULT NULL,
        template_col int(2) DEFAULT 3,
        voucher_min_value int(4) DEFAULT NULL,
        voucher_max_value int(6) DEFAULT NULL,
        voucher_expiry_type varchar(6) DEFAULT NULL,
        voucher_expiry varchar(10) DEFAULT NULL,
        voucher_terms_note text DEFAULT NULL,
        paypal int(11) DEFAULT NULL,
        sofort int(11) DEFAULT NULL,
        paypal_email varchar(100) DEFAULT NULL,
        sofort_configure_key varchar(100) DEFAULT NULL,
        reason_for_payment varchar(100) DEFAULT NULL,
        sender_name varchar(100) DEFAULT NULL,
        sender_email varchar(100) DEFAULT NULL,
        test_mode int(10) NOT NULL,
        PRIMARY KEY (id)
      ) $charset_collate;";

  $giftvouchers_list_sql = "CREATE TABLE $giftvouchers_list (
        id int(11) NOT NULL AUTO_INCREMENT,
        template_id int(11) NOT NULL,
        from_name varchar(255) NOT NULL,
        to_name varchar(255) NOT NULL,
        amount int(10) NOT NULL,
        message text NOT NULL,
        firstname varchar(255) NOT NULL,
        lastname varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        address text NOT NULL,
        postcode int(10) NOT NULL,
        pay_method varchar(255) NOT NULL,
        expiry varchar(100) NOT NULL,
        couponcode bigint(25) NOT NULL,
        voucherpdf_link text NOT NULL,
        voucheradd_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        status varchar(10) NOT NULL DEFAULT 'unused',
        payment_status varchar(10) NOT NULL DEFAULT 'Not Pay',
        PRIMARY KEY (id)
      ) $charset_collate;";

  $giftvouchers_template_sql = "CREATE TABLE $giftvouchers_template (
        id int(11) NOT NULL AUTO_INCREMENT,
        title text NOT NULL,
        image int(11) DEFAULT NULL,
        orderno int(11) NOT NULL DEFAULT '0',
        active int(11) NOT NULL DEFAULT '0',
        templateadd_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
      ) $charset_collate;";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $giftvouchers_setting_sql );
  dbDelta( $giftvouchers_list_sql );
  dbDelta( $giftvouchers_template_sql );

  add_option( 'jal_db_version', $jal_db_version );

  // Create Pages
  $voucherPage = array(
      'post_title'    => 'Gift Voucher',
      'post_content'  => '[wpgv_giftvoucher]',
      'post_status'   => 'publish',
      'post_author'   => get_current_user_id(),
      'post_type'     => 'page',
    );
  $voucherPDFPage = array(
      'post_title'    => 'Voucher PDF Preview',
      'post_content'  => ' ',
      'post_status'   => 'publish',
      'post_author'   => get_current_user_id(),
      'post_type'     => 'page',
      'comment_status' => 'closed',
      'ping_status'    => 'closed',
    );
  $voucherSuccessPage = array(
      'post_title'    => 'Voucher Payment Successful',
      'post_content'  => '[wpgv_giftvouchersuccesspage]',
      'post_status'   => 'publish',
      'post_author'   => get_current_user_id(),
      'post_type'     => 'page',
      'comment_status' => 'closed',
      'ping_status'    => 'closed',
    );
  $voucherCancelPage = array(
      'post_title'    => 'Voucher Payment Cancel',
      'post_content'  => '[wpgv_giftvouchercancelpage]',
      'post_status'   => 'publish',
      'post_author'   => get_current_user_id(),
      'post_type'     => 'page',
      'comment_status' => 'closed',
      'ping_status'    => 'closed',
    );
  wp_insert_post( $voucherPage, '' );
  $voucherPDFPage_id = wp_insert_post( $voucherPDFPage, '' );
  wp_insert_post( $voucherSuccessPage, '');
  wp_insert_post( $voucherCancelPage, '');

  if( !$voucherPDFPage_id )
      wp_die('Error creating template page');
  else
      update_post_meta( $voucherPDFPage_id, '_wp_page_template', 'pdf.php' );
}

function wpgv_plugin_install_data() {
  global $wpdb;
  
  $company_name = get_bloginfo( 'name' );
  $paypal_email = get_option('admin_email');
  
  $setting_table_name = $wpdb->prefix . 'giftvouchers_setting';
  $template_table_name = $wpdb->prefix . 'giftvouchers_template';
  
  if(!$wpdb->get_var( "SELECT * FROM $setting_table_name WHERE id = 1" )) {
    $wpdb->insert( 
      $setting_table_name,
      array( 
        'company_name'       => $company_name,
        'paypal_email'       => $paypal_email,
        'reason_for_payment' => 'Payment for Gift Voucher',
        'sender_name'        => $company_name,
        'sender_email'       => $paypal_email,
        'currency_code'      => 'USD',
        'currency'           => '$',
        'paypal'             => 1,
        'sofort'             => 0,
        'voucher_bgcolor'    => '81c6a9',
        'voucher_color'      => '555555',
        'template_col'       => 4,
        'voucher_min_value'  => 0,
        'voucher_max_value'  => 10000,
        'voucher_expiry_type'=> 'days',
        'voucher_expiry'     => 60,
        'voucher_terms_note' => 'Note: The voucher is valid for 60 days and can be redeemed at '.$company_name.'. A cash payment is not possible.',
        'currency_position'  => 'Left',
        'test_mode'          => 0,
      )
    );
    $wpdb->insert( 
      $template_table_name,
      array( 
        'title'  => "Demo Template",
        'active' => 1,
      )
    );
  }

  $upload = wp_upload_dir();
  $upload_dir = $upload['basedir'];
  $upload_dir = $upload_dir . '/voucherpdfuploads';
  if (! is_dir($upload_dir)) {
      mkdir( $upload_dir, 0755 );
  }
}

register_activation_hook( __FILE__, 'wpgv_plugin_activation' );
register_activation_hook( __FILE__, 'wpgv_plugin_install_data' );

add_action('init', 'wpgv_do_output_buffer');
function wpgv_do_output_buffer() {
        ob_start();
}

// Filter page template
add_filter('page_template', 'wpgv_catch_plugin_template');

// Page template filter callback
function wpgv_catch_plugin_template($template) {
    if( is_page_template('pdf.php') ) {
        $template = WPGIFT__PLUGIN_DIR .'/templates/pdf.php';
    }

    return $template;
}

// Admin Notice if orders are more than 10
function wpgv_moreOrdersAdminNotice() {

  global $wpdb;

  $order_table = $wpdb->prefix . 'giftvouchers_list';
  $order_count = $wpdb->get_var( "SELECT COUNT(*) FROM $order_table" );
  
  $class = 'notice notice-error';
  $message = sprintf('Voucher Orders are more than 10. Please Upgrade Wordpress Gift Voucher Plugin to <a href="%s" target="_blank">premium</a> for more features.', 'http://www.codemenschen.at/wordpress-gift-voucher-plugin/');
  if($order_count > 10)
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message ); 
}
add_action( 'admin_notices', 'wpgv_moreOrdersAdminNotice' );

function wpgv_hex2rgb($color)
{
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
}
