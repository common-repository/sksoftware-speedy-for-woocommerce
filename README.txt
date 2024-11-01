=== SKSoftware Speedy for WooCommerce ===
Contributors: sksoft
Donate link: https://sk-soft.net/
Tags: shipping, speedy, speedy shipping, woocommerce
Requires at least: 4.7
Tested up to: 6.4
Stable tag: 1.1.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The SKSoftware Speedy for WooCommerce plugin calculates rates for shipping dynamically using Speedy API during cart/checkout.

== Description ==

SKSoftware Speedy for WooCommerce is a plugin which enables your WooCommerce store to deliver goods using Speedy shipping method.
It does automatic calculation of the shipping price based on country, ZIP code, weight and volume for the products in
the cart directly in the checkout. The administrator of the website can then create orders in Speedy directly from the
store itself and print shipping labels provided by Speedy or their partners.

#### Features
* Modern design, accompanied by WooCommerce styling
* Manage orders
* Print shipping labels
* Set default values for weight, volume and any others that may be required by Speedy when the product has none
* Overwrite defined values by default when creating a shipment, even if the product has them set
* Configure global defaults or single (instance) method defaults
* Bulk shipment creation
* Blazing fast office/APT picker with typo corrections and transliteration
* No added fields in checkout for Deliver to Address method
* Track shipments and delivery status
* Quick links to review the shipment on Speedy's website

#### Speedy services support:

* Speedy to the address
* Speedy to the office
* Speedy to the APT

#### 3rd party services
This plugin relies on a 3rd party services to send data required for shipping calculations and generating shipments in Speedy. For more information about the data and its usage, please refer to the following links:

* [SK Software](https://sk-soft.net/license-agreement/)
* [Speedy](https://www.speedy.bg/bg/terms-and-conditions-cookies)

== Installation ==

First, go to [sk-soft.net](https://sk-soft.net/plugins/speedy-for-woocommerce/) and get your license key. You can request 14 days trial period to test the plugin.

#### Method 1: Get directly from WordPress repository
1. Navigate to Plugins -> Add New and search for SKSoftware Speedy for WooCommerce
2. Click the install button on the plugin with the corresponding name and activate it
3. Navigate to WooCommerce -> Shipping -> Speedy and enter your credentials. Instructions are provided below the input fields.
4. Setup your shipping methods and zones on WooCommerce -> Shipping section

#### Method 2: Upload via WordPress plugins:
1. Download the plugin from the WordPress plugins repo
2. Navigate to Plugins -> Add New -> Upload Plugin and select the 'sksoftware-speedy-for-woocommerce.zip' archive
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to WooCommerce -> Shipping -> Speedy and enter your credentials. Instructions are provided below the input fields.
5. Setup your shipping methods and zones on WooCommerce -> Shipping section

#### Method 3: Upload via FTP:
1. Download the plugin from the WordPress plugins repo
2. Upload 'sksoftware-speedy-for-woocommerce.zip' to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to WooCommerce -> Shipping -> Speedy and enter your credentials. Instructions are provided below the input fields.
5. Setup your shipping methods and zones on WooCommerce -> Shipping section

== Frequently Asked Questions ==

= Does the plugin provide free trial? =

Yes, it does. We provide 14 days free trial which can be started on [our website](https://sk-soft.net/plugins/speedy-for-woocommerce/).

= Can my clients see the price for shipping in checkout? =

Yes, your clients can see the calculated price based on their filled shipping info.

= Can I create shipment directly on Speedy via my WordPress dashboard? =

Yes, you can.

= Can I print a label for my shipment from my WordPress dashboard? =

Yes, you can.

= Can I override the calculated prices (e.g. to make free shipping or promotions)? =

Yes, you can via the "Pricing override" settings table.

= Do you add any additional fields in checkout? =

We only add an office picker for Deliver to Office and APT methods.

= I have a modified checkout (e.g. with a plugin). Will the plugin work? =

If you have modified the default shipping/billing fields - probably not. We need all the default fields in order to calculate the shipping price properly. If you have modified checkout fields, please contact us for assistance.

= How is tax for shipping price calculated? =

Tax for shipping price is calculated if you use WooCommerce Taxes. Otherwise, it is VAT inclusive.

= Is there any included support? =

Yes, there is. You can write us [in our contact form](https://sk-soft.net/contacts/) or [email us](mailto:office@sk-soft.net).

= Can you install the plugin for me? =

We offer free installation if you struggle to do it yourself. [Contact us for assistance](https://sk-soft.net/contacts/)

= Does the plugin collect any data? =

No, we do not track you or your clients.

== Changelog ==

= 1.1.1 =
* Bugfix - Shipping - Fix an issue that was preventing to send you shipments from address.

= 1.1.0 =
* Add - Enhancement - Add filter for default shipment parameters.

= 1.0.0 =
* Initial upload.
