<?php
// Display login form
// Display login form
function urp_login_form() {
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        return
            '<div class="angelfss-urp-info">'
            .'<div class="angelfss-urp-center">'
            . '<p class="angelfss-urp-msg">Hey ' . esc_html($current_user->display_name) . '! You\'re already logged in...</p>'
            . '<a class="angelfss-urp-btn" href="' . esc_url(home_url('/my-account')) . '">Go To Dashboard</a>'
            . '</div>'
            . '</div>';
    }

    // Handle login form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['urp_login_nonce']) && wp_verify_nonce($_POST['urp_login_nonce'], 'urp_login_action')) {
        // Sanitize and validate the form fields
        $user_login = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
        $password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';

        // Check if both fields are filled
        if (empty($user_login) || empty($password)) {
            echo '<div class="angelfss-urp-error">'
            . '<strong>Error:</strong> Both fields are required.'
            . '</div>';
        } else {
            // Attempt to log the user in
            $creds = array(
                'user_login' => $user_login, // Can be email or username
                'user_password' => $password,
                'remember' => true
            );

            $user = wp_signon($creds, false);

            if (is_wp_error($user)) {
                echo '<div class="angelfss-urp-error">'
                . $user->get_error_message()
                . '</div>';
            } else {
                // Redirect to the 'my account' page on successful login
                wp_safe_redirect(home_url('/my-account'));
                exit;
            }
        }
    }

    // Show the form
    ob_start();
    ?>
    <form method="POST" class="angelfss-urp-form">
        <?php wp_nonce_field('urp_login_action', 'urp_login_nonce'); ?>

        <label for="email">Email or Username:</label>
        <input type="text" name="email" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <input type="submit" value="Login">
    </form>
    <?php
    return ob_get_clean();
}
