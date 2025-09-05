````markdown
# Angel FSS User Registration and Payment Plugin

A WordPress plugin that allows user registration with integrated payment options using **PayPal**, **Stripe**, and **Paystack**. This plugin enables users to register on your site, pay a registration fee, and get assigned a specific role upon successful payment.

## Features

- **User Registration**: Users can register on your site via a simple registration form.
- **Multiple Payment Gateways**: Supports PayPal, Stripe, and Paystack as payment methods.
- **Payment Confirmation**: Users are only added to the site upon successful payment confirmation.
- **Customizable Registration Fee**: Admin can set a fixed registration fee.
- **Default User Role**: Admin can configure the default user role assigned upon successful registration.
- **Shortcode**: Easy-to-use shortcode to display the registration form on any page.

## Requirements

- WordPress 5.0 or higher.
- PHP 7.0 or higher.
- PayPal, Stripe, or Paystack accounts for payment integration.

## Installation

### 1. **Download the Plugin**

- Download or clone this repository to your local machine.

### 2. **Install the Plugin on WordPress**

- Go to your WordPress Admin Dashboard.
- Navigate to **Plugins** > **Add New**.
- Click on **Upload Plugin** and upload the `angel-fss-user-registration-payment-plugin.zip` file.
- Activate the plugin.

### 3. **Configure the Plugin Settings**

- After activation, go to **Registration Payment** in the admin menu.
- Set up your payment gateway (PayPal, Stripe, or Paystack) and enter the required credentials (Test or Live keys).
- Set the **registration fee** and choose the **default user role**.
- Save the settings.

### 4. **Using the Shortcode**

To display the registration and payment form, add the following shortcode to any page or post:

```plaintext
[angelfss_user_registration_and_payment_form]
````

The registration form will display with the option to choose the payment gateway (PayPal, Stripe, or Paystack). Once the user completes the registration and payment, they will be added to the WordPress site.

## Payment Gateway Configuration

The plugin supports **PayPal**, **Stripe**, and **Paystack** as payment gateways.

### **PayPal Integration**

* Obtain your **PayPal Client ID** and **Secret Key** from your PayPal Developer Dashboard.
* Enter these credentials in the plugin settings to enable PayPal payments.

### **Stripe Integration**

* Obtain your **Stripe API keys** (Test and Live) from the Stripe Dashboard.
* Enter these credentials in the plugin settings to enable Stripe payments.

### **Paystack Integration**

* Obtain your **Paystack Public and Secret keys** from the Paystack Developer Dashboard.
* Enter these credentials in the plugin settings to enable Paystack payments.

## Admin Settings

1. **Registration Fee**: Set the registration fee users must pay to complete registration.
2. **Payment Gateway**: Choose between PayPal, Stripe, or Paystack as the payment method.
3. **Default User Role**: Choose the role that will be assigned to new users upon successful payment (e.g., Subscriber, Editor, etc.).

## Admin Dashboard Pages

* **Shortcode Instructions**: A dedicated page in the admin dashboard that provides details about how to use the registration form shortcode.
* **Registered Users**: View a list of all registered users and their payment status.
* **Paystack Settings**: Configure the Paystack payment gateway settings.

## Contribution

If you’d like to contribute to this project, feel free to open a pull request or submit an issue. Contributions are always welcome!

1. Fork this repository.
2. Create a new branch (`git checkout -b feature-branch`).
3. Commit your changes (`git commit -am 'Add feature'`).
4. Push to your branch (`git push origin feature-branch`).
5. Create a new pull request.

## Contact

For any inquiries, please contact [Patrick Angel](https://www.angelfss.com).

---

Thank you for using **Angel FSS User Registration and Payment Plugin**!
```
