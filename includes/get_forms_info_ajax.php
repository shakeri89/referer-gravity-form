<?php
function get_brr_forms_data()
{
	// Ensure this is an AJAX call and nonce is valid. If constants are not defined, fall back safely.
	if ( ( defined('DOING_AJAX') && DOING_AJAX ) === false ) {
		// Not necessarily fatal; continue but return an error for non-AJAX callers.
		wp_send_json_error( 'not_ajax' );
	}

	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'brr_form_info_nonce_secured' ) ) {
		wp_send_json_error( 'invalid_nonce' );
	}
	$target_form_id = get_option('brr_gravity_form_id');
	$inputParams = [
		'brr_referer' => 'brrReferer',
		'brr_source' => 'brrSource',
		'brr_medium' => 'brrMedium',
		'brr_campaign' => 'brrCampaign',
		'ad_group' => 'brrAdGroup',
		'matchtype' => 'brrMatchType',
		'keyword' => 'brrKeyword',
		'campaign_content' => 'brrCampaignContent',
		'campaign_term' => 'brrCampaignTerm',
		'campaign_id' => 'brrCampaignId'
	];

	$forms = [];

	if (!empty($target_form_id)) {

		// Ensure Gravity Forms API is available
		if ( ! class_exists( 'GFAPI' ) ) {
			wp_send_json_error( 'gravityforms_not_active' );
		}

		$target_form_id_array_temp = explode( ',', $target_form_id );
		$target_form_id_array = [];

		foreach ( $target_form_id_array_temp as $target_form_id_val ){
			$target_form_id_array []= intval( trim( $target_form_id_val ) );
		}

		$counter = 0;

		foreach ( $target_form_id_array as $target_id ){
			$forms[$counter] = [
				'id' => $target_id,
				'fields' => []
			];
			$form_data = GFAPI::get_form($target_id);
			foreach ( $form_data['fields'] as $field ){
				foreach ( $inputParams as $key => $url_param ){
					if( $field->inputName === $url_param ){
						$forms [$counter]['fields'][$key] = $field->id;
					}
				}
			}
			$counter++;
		}
	}
	wp_send_json_success( $forms );
}
add_action( 'wp_ajax_get-brr-forms-info', 'get_brr_forms_data' );
add_action( 'wp_ajax_nopriv_get-brr-forms-info', 'get_brr_forms_data' );