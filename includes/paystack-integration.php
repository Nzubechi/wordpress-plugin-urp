<?php

// Paystack Settings page content
function urp_paystack_settings_page()
{
    ?>
    <div class="wrap">
        <h1>Paystack Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('urp_paystack_settings_group'); ?>
            <table class="form-table">

                <!-- Test Secret Key -->
                <tr valign="top">
                    <th scope="row">Test Secret Key</th>
                    <td><input type="text" name="paystack_test_secret_key"
                            value="<?php echo esc_attr(get_option('paystack_test_secret_key')); ?>" required /></td>
                </tr>

                <!-- Test Public Key -->
                <tr valign="top">
                    <th scope="row">Test Public Key</th>
                    <td><input type="text" name="paystack_test_public_key"
                            value="<?php echo esc_attr(get_option('paystack_test_public_key')); ?>" required /></td>
                </tr>

                <!-- Live Secret Key -->
                <tr valign="top">
                    <th scope="row">Live Secret Key</th>
                    <td><input type="text" name="paystack_live_secret_key"
                            value="<?php echo esc_attr(get_option('paystack_live_secret_key')); ?>" required /></td>
                </tr>

                <!-- Live Public Key -->
                <tr valign="top">
                    <th scope="row">Live Public Key</th>
                    <td><input type="text" name="paystack_live_public_key"
                            value="<?php echo esc_attr(get_option('paystack_live_public_key')); ?>" required /></td>
                </tr>

                <!-- Environment -->
                <tr valign="top">
                    <th scope="row">Environment</th>
                    <td>
                        <select name="paystack_env">
                            <option value="test" <?php selected(get_option('paystack_env'), 'test'); ?>>Test</option>
                            <option value="live" <?php selected(get_option('paystack_env'), 'live'); ?>>Live</option>
                        </select>
                    </td>
                </tr>

            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}


// Function to initiate Paystack payment
function urp_paystack_payment($user_data, $amount, $currency = 'NGN')
{
    // Get the Paystack keys from settings
    $paystack_test_secret_key = get_option('paystack_test_secret_key');
    $paystack_test_public_key = get_option('paystack_test_public_key');
    $paystack_live_secret_key = get_option('paystack_live_secret_key');
    $paystack_live_public_key = get_option('paystack_live_public_key');
    $paystack_env = get_option('paystack_env');

    // Choose the appropriate keys based on the environment
    if ($paystack_env == 'test') {
        $paystack_secret_key = $paystack_test_secret_key;
        $paystack_public_key = $paystack_test_public_key;
    } else {
        $paystack_secret_key = $paystack_live_secret_key;
        $paystack_public_key = $paystack_live_public_key;
    }

    // Paystack API endpoint to initialize the payment
    $payment_url = 'https://api.paystack.co/transaction/initialize';
    $headers = array(
        'Authorization' => 'Bearer ' . $paystack_secret_key,
        'Content-Type' => 'application/json',
    );

    // Prepare the data to send to Paystack for initialization
    $data = array(
        'email' => $user_data['email'],
        'amount' => $amount * 100, // Paystack works in kobo (smallest NGN unit)
        'currency' => $currency,
        'callback_url' => site_url('paystack-callback'),  // Callback URL after payment
    );

    // Send the request to Paystack to initialize the payment
    $response = wp_remote_post($payment_url, array(
        'method' => 'POST',
        'headers' => $headers,
        'body' => json_encode($data),
    ));

    // Decode the Paystack response
    $body = wp_remote_retrieve_body($response);
    $json = json_decode($body);

    if ($json->status) {
        // Return the authorization URL to redirect the user
        return $json->data->authorization_url;
    } else {
        return false;
    }
}

function urp_handle_paystack_callback()
{
    session_start(); // Start session to access session data

    // Ensure that the callback is only processed if the user submitted the registration form
    if (!isset($_SESSION['is_registered']) || $_SESSION['is_registered'] !== true) {
        // If the registration flag is not set, skip the callback processing
        error_log('Session not registered or callback not valid.');
        clear_all_sessions_on_urp_form_load();
        return;
    }
    error_log('Received Callback Params: ' . print_r($_GET, true));
    // Clear session data after failed or canceled payment
    if (isset($_SESSION['user_data'])) {
        unset($_SESSION['user_data']);  // Remove user data if exists
    }

    // Check if either reference or trxref is available in the URL
    if (isset($_GET['reference'])) {
        $reference = sanitize_text_field($_GET['reference']);
    } elseif (isset($_GET['trxref'])) {
        $reference = sanitize_text_field($_GET['trxref']);
    } else {
        // If neither parameter is found, log the error and redirect
        error_log('No reference or trxref found in Paystack callback');
        if (isset($_SESSION['is_registered'])) {
            unset($_SESSION['is_registered']); // Remove the registration flag if it exists
        }
        wp_redirect(home_url('/payment-failed'));
        exit();
    }
    // Get the Paystack environment setting (test or live)
    $paystack_env = get_option('paystack_env');  // Retrieve environment from settings (test/live)

    // Determine the correct Paystack secret key based on the environment
    if ($paystack_env == 'test') {
        // Use the test secret key
        $paystack_secret_key = get_option('paystack_test_secret_key');
    } else {
        // Use the live secret key
        $paystack_secret_key = get_option('paystack_live_secret_key');
    }

    // Log the verify URL and secret key used for debugging
    // Verify the payment using Paystack's API
    $verify_url = 'https://api.paystack.co/transaction/verify/' . $reference;
    error_log($verify_url);
    $headers = array(
        'Authorization' => 'Bearer ' . $paystack_secret_key, // Use the correct secret key based on environment
    );

    // Send request to verify the payment
    $response = wp_remote_get($verify_url, array('headers' => $headers));

    // Log the API response for debugging
    $body = wp_remote_retrieve_body($response);
    $json = json_decode($body);
    error_log('Paystack Callback Response: ' . print_r($json, true));

    // Check if payment is successful
    if ($json->status && $json->data->status == 'success') {
        // Payment successful, now create the user
        if (isset($_SESSION['user_data'])) {
            urp_create_user_after_payment($_SESSION['user_data']);
            // Clear session data after user is created
            unset($_SESSION['user_data']);
        }
    } else {
        // Payment failed, log the failure and redirect to failure page
        error_log('Payment Failed: ' . print_r($json, true));
        wp_redirect(home_url('/payment-failed')); // Redirect to a payment failure page
        exit();
    }
}
// Add action to handle the callback (URL: /paystack-callback)
add_action('init', 'urp_handle_paystack_callback');

function urp_register_paystack_settings()
{
    // Register Paystack settings for both environments (test/live keys)
    register_setting('urp_paystack_settings_group', 'paystack_test_secret_key');
    register_setting('urp_paystack_settings_group', 'paystack_test_public_key');
    register_setting('urp_paystack_settings_group', 'paystack_live_secret_key');
    register_setting('urp_paystack_settings_group', 'paystack_live_public_key');
    register_setting('urp_paystack_settings_group', 'paystack_env');
}
add_action('admin_init', 'urp_register_paystack_settings');



