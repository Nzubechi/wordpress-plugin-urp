<?php 

function urp_paypal_payment($user_id, $amount, $currency = 'USD') {
    $paypal_test_client_id = get_option('paypal_test_client_id');
    $paypal_live_client_id = get_option('paypal_live_client_id');
    $paypal_env = get_option('paypal_env');

    // Choose the appropriate client ID based on the environment
    $paypal_client_id = ($paypal_env == 'test') ? $paypal_test_client_id : $paypal_live_client_id;

    // PayPal API integration would go here (using $paypal_client_id for API requests)
    // Return the PayPal payment URL or token to redirect the user for payment
    return 'https://www.paypal.com/checkoutnow?client_id=' . $paypal_client_id . '&amount=' . $amount . '&currency=' . $currency;
}
