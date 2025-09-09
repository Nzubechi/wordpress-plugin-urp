<?php

// Add settings menu to WordPress admin
function urp_admin_menu() {
    // Add main menu page
    add_menu_page(
        'User Registration Payment', // Page title
        'Registration Payment',      // Menu title
        'manage_options',            // Capability
        'user-registration-payment', // Menu slug
        'urp_admin_page',         // Function to display the settings page content
        'dashicons-admin-users'      // Icon for the menu
    );

    // Add submenus
    add_submenu_page(
        'user-registration-payment',
        'Registered Users',          // Page title
        'Registered Users',          // Menu title
        'manage_options',            // Capability
        'user-registered-list',      // Menu slug
        'urp_user_registered_page'   // Function to display registered users page
    );

    // Add other submenus if needed
    add_submenu_page(
        'user-registration-payment',
        'Paystack Settings',         // Page title
        'Paystack',                  // Menu title
        'manage_options',            // Capability
        'paystack-settings',         // Menu slug
        'urp_paystack_settings_page' // Function to display the Paystack settings page
    );

    add_submenu_page(
        'user-registration-payment',
        'Shortcode Instructions',     // Page title
        'Shortcode Instructions',     // Menu title
        'manage_options',             // Capability
        'shortcode-instructions',     // Menu slug
        'urp_shortcode_instructions_page', // Callback function to display the content
        'dashicons-editor-help'       // Icon for the menu
    );

    // Register plugin settings for Paystack, Stripe, PayPal, etc.
    add_action('admin_init', 'urp_register_settings');
}
add_action('admin_menu', 'urp_admin_menu');

// Register plugin settings
function urp_register_settings() {
    // Paystack Settings
    register_setting('urp_settings_group', 'paystack_test_key');
    register_setting('urp_settings_group', 'paystack_live_key');
    register_setting('urp_settings_group', 'paystack_env');

    // Stripe Settings
    register_setting('urp_settings_group', 'stripe_test_key');
    register_setting('urp_settings_group', 'stripe_live_key');
    register_setting('urp_settings_group', 'stripe_env');

    // PayPal Settings
    register_setting('urp_settings_group', 'paypal_test_client_id');
    register_setting('urp_settings_group', 'paypal_live_client_id');
    register_setting('urp_settings_group', 'paypal_env');

    // Default User Role Setting
    register_setting('urp_settings_group', 'urp_default_user_role');

    // Registration Fee Setting
    register_setting('urp_settings_group', 'urp_registration_fee');  // Add this for registration fee
    register_setting('urp_settings_group', 'urp_payment_currency'); // Add this for payment currency (if needed)
    register_setting('urp_settings_group', 'urp_payment_gateway'); // Add this for payment gateway
}


?>
