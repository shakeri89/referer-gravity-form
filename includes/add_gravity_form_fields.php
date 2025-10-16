<?php
add_filter('gform_pre_render', 'add_brr_hidden_field_to_form', 999 );
function add_brr_hidden_field_to_form($form)
{
    // Specify the ID of the form to which you want to add the hidden field

    $target_form_id = get_option('brr_gravity_form_id');

    // Build url/cookie params map safely
    $urlParams = [
        'brr_referer' => ( isset( $_COOKIE['brr_referer'] ) && trim( $_COOKIE['brr_referer'] ) !== '' ) ? $_COOKIE['brr_referer'] : ( isset( $_GET['brr_referer'] ) ? $_GET['brr_referer'] : '' ),
        'brr_source' => ( isset( $_COOKIE['brr_source'] ) && trim( $_COOKIE['brr_source'] ) !== '' ) ? $_COOKIE['brr_source'] : ( isset( $_GET['brr_source'] ) ? $_GET['brr_source'] : '' ),
        'brr_medium' => ( isset( $_COOKIE['brr_medium'] ) && trim( $_COOKIE['brr_medium'] ) !== '' ) ? $_COOKIE['brr_medium'] : ( isset( $_GET['brr_medium'] ) ? $_GET['brr_medium'] : '' ),
        'brr_campaign' => ( isset( $_COOKIE['brr_campaign'] ) && trim( $_COOKIE['brr_campaign'] ) !== '' ) ? $_COOKIE['brr_campaign'] : ( isset( $_GET['brr_campaign'] ) ? $_GET['brr_campaign'] : '' ),
        // normalize ad group key
        'ad_group' => ( isset( $_COOKIE['ad_group'] ) && trim( $_COOKIE['ad_group'] ) !== '' ) ? $_COOKIE['ad_group'] : ( isset( $_GET['ad_group'] ) ? $_GET['ad_group'] : '' ),
        'matchtype' => ( isset( $_COOKIE['matchtype'] ) && trim( $_COOKIE['matchtype'] ) !== '' ) ? $_COOKIE['matchtype'] : ( isset( $_GET['matchtype'] ) ? $_GET['matchtype'] : '' ),
        'keyword' => ( isset( $_COOKIE['keyword'] ) && trim( $_COOKIE['keyword'] ) !== '' ) ? $_COOKIE['keyword'] : ( isset( $_GET['keyword'] ) ? $_GET['keyword'] : '' ),
        'campaign_content' => ( isset( $_COOKIE['campaign_content'] ) && trim( $_COOKIE['campaign_content'] ) !== '' ) ? $_COOKIE['campaign_content'] : ( isset( $_GET['campaign_content'] ) ? $_GET['campaign_content'] : '' ),
        'campaign_term' => ( isset( $_COOKIE['campaign_term'] ) && trim( $_COOKIE['campaign_term'] ) !== '' ) ? $_COOKIE['campaign_term'] : ( isset( $_GET['campaign_term'] ) ? $_GET['campaign_term'] : '' ),
        'campaign_id' => ( isset( $_COOKIE['campaign_id'] ) && trim( $_COOKIE['campaign_id'] ) !== '' ) ? $_COOKIE['campaign_id'] : ( isset( $_GET['campaign_id'] ) ? $_GET['campaign_id'] : '' ),
    ];

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

    if (!empty($target_form_id)) {

        $target_form_id_array_temp = explode( ',', $target_form_id );
        $target_form_id_array = [];

        foreach ( $target_form_id_array_temp as $target_form_id_val ){
            $target_form_id_array []= intval( trim( $target_form_id_val ) );
        }


        // Check if the current form matches the target form
        if ( in_array( intval( $form['id'] ), $target_form_id_array ) ) {

            // Iterate fields and set defaultValue when a mapped inputName exists on a hidden field
            foreach ( $form['fields'] as &$field ) {
                // Only target hidden fields to avoid overwriting visible inputs
                if ( ! is_object( $field ) ) {
                    continue;
                }
                if ( ! property_exists( $field, 'type' ) || $field->type !== 'hidden' ) {
                    continue;
                }

                // Single-input fields with inputName
                if ( property_exists( $field, 'inputName' ) && ! empty( $field->inputName ) ) {
                    foreach ( $inputParams as $key => $inputName ) {
                        if ( $field->inputName === $inputName && isset( $urlParams[ $key ] ) && $urlParams[ $key ] !== '' ) {
                            $field->defaultValue = sanitize_text_field( $urlParams[ $key ] );
                            break;
                        }
                    }
                }

                // Multi-input fields: check nested inputs if present (rare for hidden fields but handled)
                if ( property_exists( $field, 'inputs' ) && is_array( $field->inputs ) ) {
                    foreach ( $field->inputs as $input ) {
                        $inputNameVal = null;
                        if ( is_array( $input ) && isset( $input['inputName'] ) ) {
                            $inputNameVal = $input['inputName'];
                        } elseif ( is_object( $input ) && property_exists( $input, 'inputName' ) ) {
                            $inputNameVal = $input->inputName;
                        }
                        if ( $inputNameVal ) {
                            foreach ( $inputParams as $key => $inputName ) {
                                if ( $inputNameVal === $inputName && isset( $urlParams[ $key ] ) && $urlParams[ $key ] !== '' ) {
                                    // set top-level field default - Gravity Forms uses field->defaultValue
                                    $field->defaultValue = sanitize_text_field( $urlParams[ $key ] );
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
            unset( $field );

            /*// Define the hidden field settings
            $source_field = array(
                'type' => 'hidden',
                'id' => 'brr_source_field', // Replace 1000 with a unique field ID
                'label' => 'Source Field',
                'defaultValue' => (($referer !== false) ? $referer : ''), // Replace with your desired default value
                'isRequired' => false,
            );

            $medium_field = array(
                'type' => 'hidden',
                'id' => 'brr_medium_field', // Replace 1000 with a unique field ID
                'label' => 'Medium Field',
                'defaultValue' => (($medium !== false) ? $medium : ''), // Replace with your desired default value
                'isRequired' => false,
            );

            $campaign_field = array(
                'type' => 'hidden',
                'id' => 'brr_campaign_field', // Replace 1000 with a unique field ID
                'label' => 'Campaign Field',
                'defaultValue' => (($campaign !== false) ? $campaign : ''), // Replace with your desired default value
                'isRequired' => false,
            );

            if (!in_array('brr_source_field', array_column($form['fields'], 'id'))) {
                $field = GF_Fields::create($source_field);
                array_push($form['fields'], $field);
                $field2 = GF_Fields::create($medium_field);
                array_push($form['fields'], $field2);
                $field3 = GF_Fields::create($campaign_field);
                array_push($form['fields'], $field3);
            }*/

            // Add the hidden field to the form
            //$form['fields'][] = $hidden_field;
        }

    }

    return $form;
}