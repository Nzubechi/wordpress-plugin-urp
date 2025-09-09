<?php

// User registration form display function
function urp_registration_form()
{
    session_start(); // Start the session to store user data temporarily

    // Check if the user is logged in and if already registered
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();

        // Check if the user is already registered (email or username)
        if (email_exists($current_user->user_email)) {
            return '<div class="angelfss-urp-info">
            <p class="angelfss-urp-text-center">Hey! You\'re already registered.
            <a href="' . esc_url(home_url('/my-account')) . '">Go To Dashboard</a>
            </p> </div>';
        }
    }

    // Initialize error message variable
    $error_message = '';

    // Display the registration form if not registered
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Call the validation function
        $error_message = urp_validate_registration_form($_POST);

        // If there are errors, return the error message with the form
        if ($error_message) {
            return '<div class="angelfss-urp-error">'
                . $error_message . urp_registration_form_html()
                . '</div>';
        }

        // Capture and store user data temporarily in session if no errors
        $_SESSION['user_data'] = array(
            'username' => sanitize_text_field($_POST['username']),
            'email' => sanitize_email($_POST['email']),
            'password' => sanitize_text_field($_POST['password']),
            'payment_gateway' => sanitize_text_field($_POST['payment_gateway'])
        );

        $_SESSION['is_registered'] = true;
        // Process the payment (Paystack)
        urp_process_payment($_SESSION['user_data']);
    }

    // Show the registration form
    return urp_registration_form_html();
}

// Validation function for the registration form
function urp_validate_registration_form($data)
{
    $error_message = '';

    // Validate email
    $email = sanitize_email($data['email']);
    if (!is_email($email)) {
        $error_message .= '<p class="angelfss-urp-error">Invalid email format.</p>';
    } elseif (email_exists($email)) {
        $error_message .= '<p class="angelfss-urp-error">This email is already registered.</p>';
    }

    // Validate username
    $username = sanitize_text_field($data['username']);
    if (username_exists($username)) {
        $error_message .= '<p class="angelfss-urp-error">This username is already taken.</p>';
    }

    // Validate password
    $password = sanitize_text_field($data['password']);
    if (strlen($password) < 8) {
        $error_message .= '<p class="angelfss-urp-error">Password must be at least 8 characters long.</p>';
    }

    return $error_message;
}

// Helper function to generate the form HTML
function urp_registration_form_html()
{
    ob_start();
    ?>
    <form method="POST" class="angelfss-urp-form">
        <label for="username">Username:</label>
        <input type="text" name="username" required><br>

        <label for="email">Email:</label>
        <input type="email" name="email" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <label for="registration_fee">Registration Fee: $<?php echo get_option('urp_registration_fee'); ?></label><br>

        <label for="payment_gateway">Payment Gateway:</label>
        <select name="payment_gateway" required>
            <option value="paystack">Paystack</option>
        </select><br>

        <input type="submit" value="Register and Pay">
    </form>
    <?php
    return ob_get_clean();
}


// Handle the payment process and redirection
function urp_process_payment($user_data)
{
    // Get the registration fee and currency from plugin settings
    $registration_fee = get_option('urp_registration_fee');
    $currency = get_option('urp_payment_currency');

    // Process payment via Paystack (this will trigger the Paystack pop-up)
    $payment_url = urp_paystack_payment($user_data, $registration_fee, $currency);
    error_log($payment_url);

    if ($payment_url) {
        // Redirect to Paystack to complete the payment (this triggers the pop-up)
        wp_redirect($payment_url);
        exit();
    }
}


// This function will create the user only after payment is verified via the Paystack callback
function urp_create_user_after_payment($user_data)
{
    // Create user data array
    $userdata = array(
        'user_login' => $user_data['username'],
        'user_email' => $user_data['email'],
        'user_pass' => wp_hash_password($user_data['password']), // Always hash passwords
    );

    // Insert the user into WordPress
    $user_id = wp_insert_user($userdata);

    // Check for errors during user creation
    if (!is_wp_error($user_id)) {
        // Mark the user as "paid"
        update_user_meta($user_id, '_payment_status', 'paid');

        // Set the default role for the user (if specified in plugin settings)
        $default_role = get_option('urp_default_user_role', 'vendor'); // Default to 'subscriber' if not set
        $user = new WP_User($user_id);
        $user->set_role($default_role);

        // Log the user in automatically
        wp_set_current_user($user_id); // Set the current user to the newly created user
        wp_set_auth_cookie($user_id); // Set the authentication cookie so the user stays logged in

        // Clear the session data
        session_start();
        unset($_SESSION['user_data']); // Remove temporary session data

        // Redirect to the "My Account" page after login
        wp_redirect(home_url('/my-account'));
        exit(); // Ensure no further code execution
    } else {
        // Log any errors during user creation
        error_log('User creation failed: ' . $userdata['email']);
        wp_redirect(home_url('/payment-failed'));
        exit();
    }
}


