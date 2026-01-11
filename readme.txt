=== Swiver for WooCommerce ===
Contributors: ghabri, swiver
Tags: woocommerce, invoices, invoicing, swiver, order sync
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

The Swiver extension for WordPress is a powerful tool designed to seamlessly integrate your company's website with the Swiver invoicing platform.

== Description ==

The Swiver extension for WordPress is a powerful tool designed to seamlessly
integrate your company’s website with the Swiver invoicing platform. This integration
allows you to effortlessly sync customer orders from your website directly into your
Swiver account, enabling smooth conversion into delivery notes or final invoices,
depending on your workflow.

== Features ==

* **Easy Setup** - No developer needed for installation or configuration
* **Automatic Order Sync** - Orders are automatically sent to Swiver on checkout
* **Automatic Product Creation** - Each new order automatically generates the corresponding product in your Swiver account
* **Automatic Customer Creation** - New customers are automatically added with each order
* **Tax Rate Management** - View Swiver taxes and add missing ones to WooCommerce with one click
* **Bulk Tax Import** - Add all unmatched taxes to WooCommerce at once
* **Connection Status** - Visual indicator showing connection state, company name, and last sync time
* **Resync Support** - Refresh data from Swiver without disconnecting
* **Inventory Impact** - Stock levels are only updated once an order is converted into a final invoice
* **HPOS Compatible** - Works with WooCommerce High Performance Order Storage
* **Multi-Site Support** - Multiple websites can connect to a single Swiver account

== Installation ==

1. Log in to your Swiver account.
2. Go to the Settings section and navigate to Integration.
3. Generate an API key for your website and copy it.
4. On your WordPress admin panel, search for "Swiver e-commerce extension"
in the Extensions section.
5. Install the plugin, paste the API key, and click "Synchronize".

== Important Notes ==

• First Order Setup: If no orders exist in your Swiver account, you must manually create the first order and set the initial serial number. After this, all incoming orders will be synced automatically.
• Multi-Site Support: You can link multiple websites to a single Swiver account.
• Product Identification: Products are matched using the SKU. If no SKU exists, a new product will be created in Swiver.

== Screenshots ==

1. Settings page

== Changelog ==

= 1.0.0 =
* Initial release
* AJAX-based sync/disconnect with loading spinners and feedback
* Tax management with match status display
* Add individual taxes to WooCommerce with one click
* Bulk "Add all" button to import all unmatched taxes
* WooCommerce tax name display for matched taxes
* Resync button to refresh data without disconnecting
* Connection status indicator showing company name and last sync time
* Company details, brands, warehouses, categories, units display
* Order synchronization on checkout
* Customer and product creation/matching in Swiver
* Unit tests with PHPUnit and Brain Monkey
* GitHub Actions CI pipeline for automated testing
* WooCommerce HPOS compatibility
* Tested up to: WooCommerce 9.5.2
* Tested up to: WordPress 6.7.1

== External Services ==

This plugin connects to the Swiver invoicing platform API to synchronize your WooCommerce data.

= What data is sent? =

When you use this plugin, the following data may be transmitted to Swiver's servers:

* **Order Information** - Order details, totals, discounts, and notes
* **Customer Data** - Customer names, addresses, phone numbers, and email addresses
* **Product Information** - Product names, descriptions, SKUs, and prices
* **Tax Rates** - Tax configuration from your Swiver account

= Service Details =

* **Service Provider:** Swiver
* **API Endpoint:** https://server.swiver.io/open_api/
* **Website:** https://swiver.io
* **Terms of Service:** https://swiver.io/terms
* **Privacy Policy:** https://swiver.io/privacy

Data is only transmitted when:
1. You enter your API token and click "Synchronize"
2. A customer completes checkout (order sync)
3. You manually trigger a resync

No data is sent without your explicit action. Your API token is stored securely in your WordPress database.

== Frequently Asked Questions ==

= How do I get an API key? =

Log in to your Swiver account, go to Settings > Integration, and generate an API key for your website.

= Why do I need to create the first order manually in Swiver? =

Swiver requires an initial serial number to be set for document numbering. Creating the first order manually allows you to set this starting number according to your business needs.

= How are products matched between WooCommerce and Swiver? =

Products are matched using the SKU (Stock Keeping Unit). If a product in your WooCommerce order has a SKU that matches a product in Swiver, they will be linked. If no matching SKU is found, a new product will be created in Swiver.

= Can I connect multiple WooCommerce stores to one Swiver account? =

Yes, you can connect multiple websites to a single Swiver account. Each site will sync orders independently.

= When are stock levels updated in Swiver? =

Stock levels in Swiver are only updated when an order is converted into a final invoice, not when the draft document is created.

= What happens if I disconnect the plugin? =

Disconnecting clears your API token and all cached Swiver data from WordPress. Your data in Swiver remains unchanged. You can reconnect at any time with the same or a different API key.

= Does this plugin work with WooCommerce HPOS? =

Yes, this plugin is fully compatible with WooCommerce High Performance Order Storage (HPOS).

= How do I add missing tax rates to WooCommerce? =

After syncing with Swiver, go to the Swiver settings page. Tax rates that exist in Swiver but not in WooCommerce will show a "Not in WooCommerce" badge. Click "Add to WooCommerce" to create the tax rate, or use "Add all" to import all missing taxes at once.

== Upgrade Notice ==

= 1.0.0 =
Initial release of Swiver for WooCommerce.