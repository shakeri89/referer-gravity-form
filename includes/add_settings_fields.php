<?php
// Register a settings page for the plugin
add_action( 'admin_menu', 'register_brr_settings_page' );

function register_brr_settings_page() {
    add_options_page( 'تنظیمات آی دی گراویتی فرم باران', 'تنظیمات آی دی گراویتی فرم باران', 'manage_options', 'brr_settings', 'brr_settings_page_callback' );
}

// Add section and field to the settings page
add_action( 'admin_init', 'register_brr_settings' );
function register_brr_settings() {
    register_setting( 'brr_settings_group', 'brr_gravity_form_id' );
    add_settings_section( 'brr_settings_section', 'تنظیمات آی دی گراویتی فرم باران', 'brr_settings_section_callback', 'brr_settings' );
    add_settings_field( 'brr_gravity_form_id', 'آی دی گراویتی فرم', 'brr_gravity_form_id_field_callback', 'brr_settings', 'brr_settings_section' );
}

// Section callback function
function brr_settings_section_callback() {
    echo 'آی دی گراویتی فرم مورد نظر را وارد کنید :';
}

// Field callback function
function brr_gravity_form_id_field_callback() {
    $brr_gravity_form_id = get_option( 'brr_gravity_form_id' );
    echo '<input type="text" id="brr_gravity_form_id" name="brr_gravity_form_id" value="' . esc_attr( $brr_gravity_form_id ) . '" />';
    echo '<p class="description">هر مقدار را با , از هم جدا کنید.</p>';
}

// Settings page callback function
function brr_settings_page_callback() {
    ?>
    <div class="wrap">
        <h2>تنظیمات آی دی گراویتی فرم باران</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'brr_settings_group' );
            do_settings_sections( 'brr_settings' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}