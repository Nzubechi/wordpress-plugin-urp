<?php
/*
Plugin Name: Angel FSS User Registration and Payment Plugin
Description: A plugin for user registration with payment options.
Version: 1.1
Author: <a href="https://www.linkedin.com/in/patrick-angel/" target="_blank">Patrick Angel</a>
*/

// Ensure we don't access the file directly
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include necessary files
include_once(plugin_dir_path(__FILE__) . 'includes/registration-form.php');
include_once(plugin_dir_path(__FILE__) . 'includes/admin-menu.php');
include_once(plugin_dir_path(__FILE__) . 'includes/paystack-integration.php'); // Add Paystack integration
include_once(plugin_dir_path(__FILE__) . 'includes/login-form.php');

// Register shortcode for user registration form
function urp_register_form_shortcode()
{
    return urp_registration_form();
}
add_shortcode('angelfss_user_registration', 'urp_register_form_shortcode');

function urp_login_form_shortcode()
{
    return urp_login_form();
}
add_shortcode('angelfss_user_login', 'urp_login_form_shortcode');

function urp_shortcode_instructions_page()
{
    ?>
    <div class="wrap">
        <h1>Shortcode Instructions</h1>
        <p>Welcome to the Shortcode Instructions page! Below are the shortcodes you can use to display user forms on any
            page.</p>

        <!-- Registration Shortcode -->
        <div style="border: 2px dashed #0073aa; padding: 20px; margin-bottom:20px; background-color: #f9f9f9;">
            <h2><strong>Registration Form Shortcode</strong></h2>
            <p>To display the user registration and payment form on any page, use the following shortcode:</p>
            <pre>[angelfss_user_registration]</pre>
            <p>This shortcode will show the registration form along with the payment gateway options. Simply paste this
                shortcode into any page or post, and the form will appear there.</p>
        </div>

        <!-- Login Shortcode -->
        <div style="border: 2px dashed #46b450; padding: 20px; background-color: #f9f9f9;">
            <h2><strong>Login Form Shortcode</strong></h2>
            <p>To display the user login form on any page, use the following shortcode:</p>
            <pre>[angelfss_user_login]</pre>
            <p>This shortcode will display the login form for existing users. Once logged in, users will be redirected to
                their
                <code>/my-account</code> page.
            </p>
        </div>
    </div>
    <?php
}

// Plugin activation hook to set up options
function urp_activate_plugin()
{
    // Set default options for payment gateway, registration fee
    add_option('urp_payment_gateway', 'paystack');
    add_option('urp_registration_fee', 25000);  // Default registration fee
    add_option('urp_payment_currency', 'NGN'); // Default currency for payments (Paystack support for multiple currencies)
}
register_activation_hook(__FILE__, 'urp_activate_plugin');

// Admin page content (Settings page)
function urp_admin_page()
{
    ?>
    <div class="wrap">
        <h1>User Registration and Payment Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('urp_settings_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Registration Fee</th>
                    <td><input type="number" name="urp_registration_fee"
                            value="<?php echo esc_attr(get_option('urp_registration_fee')); ?>" required /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Payment Gateway</th>
                    <td>
                        <select name="urp_payment_gateway" required>
                            <option value="paypal" <?php selected(get_option('urp_payment_gateway'), 'paypal'); ?>>PayPal
                            </option>
                            <option value="stripe" <?php selected(get_option('urp_payment_gateway'), 'stripe'); ?>>Stripe
                            </option>
                            <option value="paystack" <?php selected(get_option('urp_payment_gateway'), 'paystack'); ?>>
                                Paystack</option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Payment Currency</th>
                    <td>
                        <select name="urp_payment_currency">
                            <option value="NGN" <?php selected(get_option('urp_payment_currency'), 'NGN'); ?>>NGN</option>
                            <option value="USD" <?php selected(get_option('urp_payment_currency'), 'USD'); ?>>USD</option>
                            <option value="EUR" <?php selected(get_option('urp_payment_currency'), 'EUR'); ?>>EUR</option>
                            <!-- Add other currencies supported by Paystack -->
                        </select>
                    </td>
                </tr>
                <!-- Default User Role -->
                <tr valign="top">
                    <th scope="row">Default User Role</th>
                    <td>
                        <select name="urp_default_user_role">
                            <?php
                            // Get available user roles in WordPress
                            $roles = get_editable_roles();
                            foreach ($roles as $role_key => $role) {
                                echo '<option value="' . esc_attr($role_key) . '" ' . selected(get_option('urp_default_user_role'), $role_key, false) . '>' . esc_html($role['name']) . '</option>';
                            }
                            ?>
                        </select>
                        <p class="description">Select the default role for newly registered users.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Registered users page
function urp_user_registered_page()
{
    global $wpdb;

    // Fetch the users from the database
    $users = $wpdb->get_results("SELECT * FROM {$wpdb->users} ORDER BY user_registered DESC");

    // Display the table
    echo '<div class="wrap">';
    echo '<h1>Registered Users</h1>';
    echo '<table class="wp-list-table widefat fixed striped users">';
    echo '<thead><tr><th>User ID</th><th>Username</th><th>Email</th><th>Role</th><th>Payment Status</th><th>Registration Date</th><th>Actions</th></tr></thead>';
    echo '<tbody>';

    // Loop through each user and display the data
    foreach ($users as $user) {
        // Get user data
        $userdata = get_userdata($user->ID);
        $roles = $userdata->roles; // Array of roles assigned to the user
        $role_list = implode(', ', $roles); // Convert the array into a comma-separated string


        $payment_status = get_user_meta($user->ID, '_payment_status', true);
        $registration_date = $user->user_registered; // User registration date

        echo '<tr>';
        echo '<td>' . $user->ID . '</td>';
        echo '<td>' . $user->user_login . '</td>';
        echo '<td>' . $user->user_email . '</td>';
        echo '<td>' . ucwords($role_list) . '</td>';
        echo '<td>' . ($payment_status ? 'Paid' : 'Pending') . '</td>';
        echo '<td>' . date('F j, Y, g:i a', strtotime($registration_date)) . '</td>'; // Format the registration date
        echo '<td><a href="' . esc_url(admin_url('admin-post.php?action=delete_user&id=' . $user->ID)) . '" class="button" onclick="return confirm(\'Are you sure you want to delete this user?\')">Delete</a></td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

// Handle the deletion of a user
function urp_handle_user_deletion()
{
    // Ensure that the user ID is passed and it's a valid number
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $user_id = intval($_GET['id']);

        // Delete the user
        require_once(ABSPATH . 'wp-admin/includes/user.php');
        wp_delete_user($user_id);

        // Redirect back to the user list page
        wp_redirect(admin_url('admin.php?page=user-registered-list'));
        exit();
    }
}
add_action('admin_post_delete_user', 'urp_handle_user_deletion');

function clear_all_sessions_on_urp_form_load()
{
    // Clear the session data after processing (after payment is complete or failed)
    unset($_SESSION['is_registered']);  // Clear registration flag
    unset($_SESSION['user_data']);      // Clear user data for security purposes
    // Optionally, clear the $_GET variables to reset the page state
    unset($_GET['reference']);
    unset($_GET['trxref']);
    error_log('Cleared all sessions and reference and trxref from $_GET');
}

function urp_redirect_guest_users()
{
    // Check if user is NOT logged in
    if (!is_user_logged_in()) {
        // Check if current URL is the "my-account" page
        if (is_page('my-account')) {
            wp_redirect(site_url('/login'));
            exit;
        }
    }
}

// Enqueue the custom plugin styles
function urp_enqueue_styles()
{
    // Ensure we're only loading styles when the registration form is present
    if (
        is_page()
        && has_shortcode(get_post()->post_content, 'angelfss_user_registration')
        || has_shortcode(get_post()->post_content, 'angelfss_user_login')
    ) {
        wp_enqueue_style('urp-registration-form-style', plugin_dir_url(__FILE__) . 'css/urp_style.css?ver=' . time());
    }
}
add_action('wp_enqueue_scripts', 'urp_enqueue_styles');
add_action('template_redirect', 'urp_redirect_guest_users');

