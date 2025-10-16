<?php
/**
Plugin Name: Referer Recogition
Plugin URI: https://github.com/shakeri89
Description: A brief description of the Plugin.
Version: 1.0
Author: shakeri
Author URI: https://github.com/shakeri89/referer-gravity-form
License: GPLv2 or above
 */

function activate_brr_plugin()
{
	flush_rewrite_rules();
}
register_activation_hook(__FILE__,'activate_brr_plugin');

function deactivate_brr_plugin()
{
	flush_rewrite_rules();
}
register_deactivation_hook(__FILE__,'deactivate_brr_plugin');

require_once __DIR__.'/includes/set_referer_cookies.php';
require_once __DIR__.'/includes/add_settings_fields.php';
require_once __DIR__.'/includes/add_gravity_form_fields.php';
require_once __DIR__.'/includes/get_forms_info_ajax.php';

// Admin notice: warn if Gravity Forms is not active (GFAPI missing)
function brr_check_gravityforms_dependency() {
	if ( ! is_admin() ) {
		return;
	}

	// Only show to users who can manage options
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! class_exists( 'GFAPI' ) ) {
		add_action( 'admin_notices', function() {
			echo '<div class="notice notice-warning is-dismissible">';
			echo '<p><strong>Baransys Referer Recogition:</strong> Gravity Forms plugin is not active or GFAPI is unavailable. Some features of this plugin require Gravity Forms.</p>';
			echo '</div>';
		} );
	}
}
add_action( 'admin_init', 'brr_check_gravityforms_dependency' );

function add_brr_scripts()
{
	wp_register_script( 'brr_js_to_add_cookies', plugin_dir_url( __FILE__ ).'assets/baran_referer.js', array('jquery'), '1.1.5', true );
	wp_localize_script( 'brr_js_to_add_cookies', 'brrSettings', [
		'ajaxUrl' => admin_url('admin-ajax.php'),
		'getFormsInfoAction' => 'get-brr-forms-info',
		'getFormsInfoNonce' => wp_create_nonce( 'brr_form_info_nonce_secured' )
	] );
	wp_script_add_data( 'brr_js_to_add_cookies', 'defer', true );
	wp_enqueue_script( 'brr_js_to_add_cookies' );
}
add_action( 'wp_enqueue_scripts', 'add_brr_scripts' );