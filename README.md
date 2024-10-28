# === BCL Payment Link for WordPress ===
Contributors: webimpian
Tags: payment, links, woocommerce, FPX, Malaysia, online payment, DuitNow
Requires at least: 5.6
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Generate BCL payment links for WordPress, with initial support for WooCommerce orders.

## == Description ==

BCL Payment Link for WordPress simplifies the process of generating payment links for your website content. Currently, it supports WooCommerce orders, with plans to expand to other WordPress data types in the future.

This plugin integrates with the BCL Payment Link service (https://bcl.my) to create payment links that can be easily shared with customers via various channels.

### = Key Features =

* Generate BCL payment links directly from WordPress
* Initial support for WooCommerce order dashboard
* Easy-to-use button for quick link creation
* Support for various payment methods through BCL:
  - FPX (Malaysian online banking)
  - DuitNow (Online Banking/Wallets)
  - DuitNow QR (including cross-border payments)
  - Direct Debit (for recurring payments)
* Sync payment status with BCL Payment Link dashboard (for WooCommerce orders)

### = How It Works =

1. For WooCommerce: A new button appears in your order dashboard.
2. Clicking this button generates a payment link via the BCL Payment Link API.
3. Copy the generated link and share it with your customer through social media or other channels.
4. The customer uses this link to complete the payment on the BCL platform.
5. For WooCommerce orders: Payment status is synced back to your order.

### = Important Notice =

This plugin relies on the BCL Payment Link service (https://bcl.my) to generate and process payments. By using this plugin, you are agreeing to share relevant information with BCL Payment Link for payment processing purposes. Please review BCL Payment Link's Terms of Service (https://bcl.my/info/terms) and Privacy Policy (https://bcl.my/info/privacy) before using this plugin.

### = Requirements =

* PHP 7.4 or higher
* WordPress 5.6 or higher
* WooCommerce plugin (for WooCommerce functionality)
* BCL Payment Link account (sign up at https://bcl.my)

## == Installation ==

1. Upload the BCL Payment Link plugin to your WordPress site.
2. Activate the BCL Payment Link plugin.
3. If using WooCommerce functionality, ensure you have the WooCommerce plugin installed and activated.

### = Configuration =

1. Go to WordPress Admin > BCL Payment Link Settings.
2. Enter your BCL Payment Link API credentials.
3. Configure any additional settings as needed.

## == Frequently Asked Questions ==

### = Does this plugin work with content types other than WooCommerce orders? =
Currently, the plugin only supports WooCommerce orders. We plan to expand support to other WordPress content types in future updates.

### = Does this plugin send data to external services? =
Yes, this plugin sends necessary information to BCL Payment Link (https://bcl.my) to generate payment links and process payments. Please review their Terms of Service (https://bcl.my/info/terms) and Privacy Policy (https://bcl.my/info/privacy) for more information on how your data is handled.

### = How do customers pay using this method? =
After you generate a payment link, you share it with the customer. They click the link and complete the payment on the BCL platform.

### = Can I use this for international payments? =
While primarily designed for the Malaysian market, BCL supports cross-border payments via DuitNow QR for select countries.

### = How do I get support? =
For inquiries or support, please email hai@bcl.my.

## == Screenshots ==

1. WooCommerce order dashboard with BCL Payment Link button
2. BCL Payment Link settings page

## == Changelog ==

### = 1.0.0 =
* Initial release of BCL Payment Link for WordPress
* Support for generating payment links from WooCommerce orders

## == Additional Information ==

For more details about BCL, including terms of use and privacy policy, visit:
* BCL Payment Link website: https://bcl.my
* Terms of Service: https://bcl.my/info/terms
* Privacy Policy: https://bcl.my/info/privacy

To register for a BCL Payment Link account, go to https://bcl.my/register