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

// Register shortcode for user registration form
function urp_register_form_shortcode()
{
    return urp_registration_form();
}
add_shortcode('angelfss_user_registration', 'urp_register_form_shortcode');

function urp_shortcode_instructions_page()
{
    ?>
    <div class="wrap">
        <h1>Shortcode Instructions</h1>
        <p>Welcome to the Shortcode Instructions page! Below is the shortcode that can be used on any page to display the
            user registration form.</p>

        <div style="border: 2px dashed #0073aa; padding: 20px; background-color: #f9f9f9;">
            <h2><strong>Registration Form Shortcode</strong></h2>
            <p>To display the user registration and payment form on any page, use the following shortcode:</p>
            <pre>[angelfss_user_registration]</pre>
            <p>This shortcode will show the registration form along with the payment gateway options. Simply paste this
                shortcode into any page or post, and the form will appear there.</p>
        </div>
    </div>
    <?php
}

// Plugin activation hook to set up options
function urp_activate_plugin()
{
    // Set default options for payment gateway, registration fee
    add_option('urp_payment_gateway', 'paystack');
    add_option('urp_registration_fee', 50000);  // Default registration fee
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

    $users = $wpdb->get_results("SELECT * FROM {$wpdb->users} ORDER BY user_registered DESC");

    echo '<div class="wrap">';
    echo '<h1>Registered Users</h1>';
    echo '<table class="wp-list-table widefat fixed striped users">';
    echo '<thead><tr><th>User ID</th><th>Username</th><th>Email</th><th>Payment Status</th></tr></thead>';
    echo '<tbody>';

    foreach ($users as $user) {
        $payment_status = get_user_meta($user->ID, '_payment_status', true);
        echo '<tr>';
        echo '<td>' . $user->ID . '</td>';
        echo '<td>' . $user->user_login . '</td>';
        echo '<td>' . $user->user_email . '</td>';
        echo '<td>' . ($payment_status ? 'Paid' : 'Pending') . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

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

// Enqueue the custom plugin styles
function urp_enqueue_styles()
{
    // Ensure we're only loading styles when the registration form is present
    if (is_page() && has_shortcode(get_post()->post_content, 'angelfss_user_registration')) {
        wp_enqueue_style('urp-registration-form-style', plugin_dir_url(__FILE__) . 'css/style.css?v=' . time());
    }
}
add_action('wp_enqueue_scripts', 'urp_enqueue_styles');

