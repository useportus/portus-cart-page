=== Portus Cart Page ===
Contributors: portusoficial
Tags: woocommerce, cart, cart page, customization
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 8.1
Requires Plugins: woocommerce
Stable tag: 1.4.13
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Customize your WooCommerce cart page with a responsive layout, native coupons, quantity controls and an easy-to-use settings panel.

== Description ==

Portus Cart Page gives the WooCommerce cart page a clean, responsive presentation while keeping WooCommerce responsible for prices, stock, coupons, shipping, taxes and checkout validation.

This plugin changes the cart page only. It does not add a side cart, replace checkout, create orders or process payments. It can be used alongside Portus Cart for WooCommerce, the separate Portus side-cart plugin.

= Free features =

* Refined order summary with local Bootstrap Icons, highlighted coupons and native discount savings.
* Responsive product list and order summary inside a padded container with an optional subtle shadow.
* Native WooCommerce quantity, removal, coupon and shipping controls.
* Native WooCommerce cross-sell recommendations, available without Pro or a license key.
* Automatic quantity updates through the WooCommerce cart script.
* Plus/minus controls for products that support quantity changes.
* Cart and empty-cart continue-shopping buttons with configurable text and destination.
* Use the configured WooCommerce shop page, a relative path such as /loja, or a full HTTP(S) URL.
* Follow the site container or set a maximum width and horizontal padding.
* Customize the primary color, button text color, corners and spacing.
* Change the cart heading and description.
* Settings under WooCommerce > Portus Cart Page, with an illustrative visual preview.
* Four read-only dashboard cards show installed version, cart-page setup, edition/license display status and active theme.
* Keep WooCommerce notices and supported extension hooks.
* Compatibility handling for the Royal Addons cart widget's duplicate wrappers and coupon field, scoped to Portus rendering.

All functionality included in this WordPress.org package works without a license key. There is no premium implementation embedded in this package. Additional layouts, advanced styling, shipping progress, import/export and private updates are provided by the separately distributed Portus Cart Page Pro extension.

= Cart shortcode and Cart Block =

On the cart page configured in WooCommerce, Portus presents [woocommerce_cart] using its own classic cart template. You can also use [portus_cart_page] on that page.

When the corresponding option is enabled, an existing WooCommerce Cart Block is presented using the classic Portus renderer at runtime. This is not a native Cart Block extension. Extensions that require the block-based cart must be tested separately; disable the replacement option to keep the original Cart Block.

The saved page content is not rewritten. Turning Portus off restores the original presentation.

= Compatibility =

WooCommerce 9.0 or later is required. Theme and page-builder overrides, dynamic pricing, subscriptions, bundles and carrier-specific rules may require individual testing. The plugin preserves the native checkout and payment extension hooks but does not promise universal compatibility with every extension.

Exclude the cart and checkout from page caches. Custom width stays within the available parent container; it does not force an Elementor column beyond its own width.

= External services and privacy =

The Free plugin does not send telemetry, license checks or update requests to Portus. Standard Free updates are managed by WordPress.org. No customer, order or payment data is sent by this plugin to a Portus service.

The Pro extension is a separate download and documents its optional license/update service in its own readme.

== Installation ==

1. Install and activate WooCommerce and configure the cart, checkout and shop pages.
2. Install and activate Portus Cart Page.
3. Open WooCommerce > Portus Cart Page.
4. Enable the presentation, set your colors, text and width, and save.
5. Add a product and open the configured cart page to verify the result.

== Frequently Asked Questions ==

= Does this replace my side cart? =

No. It changes the cart page only. Portus Cart for WooCommerce remains a separate plugin with its own settings and triggers.

= Do I need Pro or a license key? =

No. The Free plugin works on its own. Pro is an optional, separate extension.

= Can I use Elementor? =

Yes. Edit the cart page selected under WooCommerce > Settings > Advanced and add one Shortcode widget containing [portus_cart_page]. Elementor can edit surrounding sections. Configure the cart internals in the Portus panel; product rows and totals are not separate Elementor widgets. Avoid placing two cart widgets or shortcodes on the same page.

= How do quantity buttons work? =

The plus button is disabled at WooCommerce's purchase limit. This includes available stock and individually sold products. Backorders remain available when the merchant allows them. At quantity one, minus uses WooCommerce's remove-item link, including its undo notice. Native WooCommerce validation remains authoritative.

= Can I choose the continue-shopping URL? =

Yes. Leave it empty to use the WooCommerce shop page, enter a site-relative path such as /loja, or provide a full HTTP(S) URL.

= Does this edit my cart page content? =

No. The presentation is applied at runtime. Deactivation leaves your saved page content and WooCommerce settings intact.

= Why is the admin preview different from the store? =

The preview contains illustrative products and totals. Use the View cart button to verify actual product data, layout, shipping, discounts and theme integration.

= What happens to settings on uninstall? =

Settings are retained to avoid accidental loss. To remove them permanently after deactivation, delete the portus_cart_page_settings and portus_cart_page_design_version options with an administrative database tool or WP-CLI.

== Screenshots ==

1. Free cart page with native product quantities, coupons and order summary.
2. Mobile cart with touch-friendly controls.
3. Cart appearance settings in the Portus panel.
4. Refined empty-cart state with a return-to-shop button.

== Changelog ==

= 1.4.13 =
* Add four responsive overview cards below the settings header with installed version, cart-page setup, edition/license display status and active theme.
* Use live local configuration and the separate Pro add-on's cached display status without extra license requests.

= 1.4.12 =
* Display the product logo beside the plugin name in the shared Free and Pro settings header.
* Round the product icon background and use a versioned local image in the admin panel.

= 1.4.11 =
* Enable the native cross-sell recommendations in Free without a license key or Pro add-on.
* Sanitize filtered inline styles with scoped selectors, primitive design values and WordPress safe CSS filtering.

= 1.4.10 =
* Add WebKit-compatible icon masks for mobile and tablet browsers.
* Use local SVG image fallbacks when CSS masking is unavailable, avoiding solid square placeholders.

= 1.4.9 =
* Use filled Bootstrap Icons and keep related products within the cart grid for extension layouts.

= 1.4.7 =
* Refine the native shipping calculator with a delivery icon and accessible touch targets.
* Explain native postcode, shipping and virtual-only cart behavior in setup help.

= 1.4.6 =
* Compact summary spacing and total typography; add icons to subtotal, total and coupon field labels.

= 1.4.5 =
* Refine the order summary with local Bootstrap Icons, highlighted coupons and a clearer checkout button.
* Show coupon savings using WooCommerce discount and display-tax settings.
* Keep native coupon removal, shipping refreshes and payment extension hooks.

= 1.4.3 =
* Align the public plugin name with the Portus Cart Page product and intended directory slug.
* Finalize release documentation and retain the approved product identity.

= 1.4.2 =
* Refine the white page-and-bag product mark and favicon with more surrounding navy space.

= 1.4.1 =
* White administrative header with navy typography.
* Replace the product mark with a white Bootstrap Icons cart on Portus navy.
* Include the original Bootstrap Icons MIT license.

= 1.4.0 =
* Stock-aware plus/minus controls, including individually sold products.
* Remove an item at quantity one through the native WooCommerce removal flow.
* Improve touch targets, keyboard focus and empty-cart presentation.
* Expand setup instructions for Elementor, classic shortcodes and Cart Block.

= 1.3.1 =
* Refined cart container, subtle shadow, spacing and button presentation.
* Avoid Elementor flex-height inflation while preserving container-responsive layouts.


= 1.3.0 =
* Release audit, stricter output escaping and safe quantity-control markup.
* Import/export implementation moved entirely to the separate Pro extension.
* Automatic shop-page fallback when no continue-shopping URL is configured.
* Improved extension points for Pro administration panels.

= 1.2.3 =
* Prevent duplicate Royal Addons cart wrappers and coupon fields during Portus rendering.

= 1.2.2 =
* Place coupon validation messages below the controls and show them in red.

= 1.2.1 =
* Configurable continue-shopping button destination.

= 1.2.0 =
* Dedicated cart template and configurable container width.

== Upgrade Notice ==

= 1.3.0 =
Update the Free plugin before updating the separate Pro extension. Existing cart settings and saved page content are preserved.
