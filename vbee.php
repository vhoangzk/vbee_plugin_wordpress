<?php 
/*
Plugin Name: Vbee Plugin
Plugin URI: https://vbee.vn
Description: Text to audio plugin
Author: Vbee
Version: 1.0
Author URI: https://vbee.vn
Text Domain: vbee
*/

if ( !function_exists( 'add_action' ) ) {
  echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
  exit;
}

define('VBEE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('VBEE_PLUGIN_RIR', plugin_dir_path(__FILE__));
define('FOLDER_AUDIO', 'vbee-audios');

// load textdomain
add_action('plugins_loaded', 'vbee_init');
function vbee_init() {
    load_plugin_textdomain( 'vbee', false, VBEE_PLUGIN_URL . 'languages');
}

// active plugin
register_activation_hook( __FILE__, 'vbee_activate' );
function vbee_activate(){
    $uploads_dir = trailingslashit( wp_upload_dir()['basedir'] ) . FOLDER_AUDIO;
    wp_mkdir_p( $uploads_dir );
}

// inlude class
require_once(VBEE_PLUGIN_RIR . 'includes/admin-setting-class.php');
require_once(VBEE_PLUGIN_RIR . 'includes/vbee-api-connect-class.php');
require_once(VBEE_PLUGIN_RIR . 'includes/frontend-setting-class.php');
require_once(VBEE_PLUGIN_RIR . 'includes/admin-convert-class.php');

// add style play audio
add_action( 'wp_enqueue_scripts', 'vbee_enqueue_fontend_styles');
add_action( 'admin_enqueue_scripts', 'vbee_enqueue_styles');
function vbee_enqueue_styles() {
	// add jquery
	wp_deregister_script( 'jquery' );
    wp_register_script( 'jquery', includes_url( '/js/jquery/jquery.js' ), false, NULL, true );
    wp_enqueue_script( 'jquery' );

    // jquery dialog
    wp_enqueue_style('wp-jquery-ui-dialog');

    // add vbee scripts
    wp_enqueue_script( 'vbee-scripts', VBEE_PLUGIN_URL . 'assets/js/vbee.js', array('jquery-ui-core','jquery-ui-dialog'), '', true );
    // add vbee style
	wp_enqueue_style( 'vbee-style', VBEE_PLUGIN_URL . 'assets/css/vbee.play.css', array());
}

function vbee_enqueue_fontend_styles() {
    wp_enqueue_style( 'vbee-style', VBEE_PLUGIN_URL . 'assets/css/vbee.play.css', array());
}

// deactivation plugin
register_deactivation_hook( __FILE__, 'vbee_deactivate' );
function vbee_deactivate(){
    global $wpdb;
    $table = $wpdb->prefix.'postmeta';
    $wpdb->delete($table, array('meta_key' => 'audio'));
    $wpdb->delete($table, array('meta_key' => 'check_audio'));
}