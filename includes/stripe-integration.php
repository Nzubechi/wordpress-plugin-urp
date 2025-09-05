<?php

function urp_stripe_payment($user_id, $amount, $currency = 'USD') {
    $stripe_test_key = get_option('stripe_test_key');
    $stripe_live_key = get_option('stripe_live_key');
    $stripe_env = get_option('stripe_env');

    // Choose the appropriate key based on the environment
    $stripe_key = ($stripe_env == 'test') ? $stripe_test_key : $stripe_live_key;

    \Stripe\Stripe::setApiKey($stripe_key);

    try {
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount * 100, // Convert to cents (smallest unit of USD)
            'currency' => $currency,
            'payment_method_types' => ['card'],
        ]);
        return $paymentIntent->client_secret; // Return the client secret for the payment
    } catch (Exception $e) {
        return false;
    }
}
