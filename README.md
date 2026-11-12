# Portus Cart Page

Portus Cart Page is a responsive, customizable cart page plugin for WooCommerce.

Give the cart page a clearer presentation while keeping WooCommerce responsible for prices, stock, coupons, shipping, taxes, and checkout validation.

[WordPress.org Plugin](https://wordpress.org/plugins/portus-cart-page/) · [Product Page](https://useportus.com/portus-cart-page-pro/) · [Documentation](https://useportus.com/docs/portus-cart-page/) · [Support](mailto:plugins@useportus.com)

## Features

- Responsive product list and order summary inside a padded container.
- Follow the theme container or set a custom maximum width and horizontal padding.
- Optional subtle container shadow.
- Stock-aware plus/minus quantity controls, including individually sold products and permitted backorders.
- Native WooCommerce item removal and undo notices.
- Native coupons, discount savings, shipping controls, and totals.
- Native WooCommerce cross-sell recommendations, available in Free without a license key.
- Configurable continue-shopping button text and destination.
- Responsive empty-cart presentation.
- Settings for primary color, button text color, corners, spacing, cart heading, and description.
- Local SVG icons with WebKit-compatible masks and image fallbacks.
- A settings panel with the Portus logo, four dynamic overview cards, and an illustrative appearance preview.
- Scoped frontend styles and supported WooCommerce extension hooks.

This plugin changes the cart page only. It does not add a side cart, replace checkout, create orders, or process payments. It can run alongside the separate [Portus Cart](https://github.com/useportus/portus-cart) side-cart plugin.

## Requirements

- WordPress 6.6 or newer.
- PHP 8.1 or newer.
- WooCommerce 9.0 or newer, installed and active.
- A cart page configured in WooCommerce.

## Installation

The recommended installation method is through WordPress.org:

1. Open **Plugins > Add New** in WordPress.
2. Search for **Portus Cart Page** by Portus.
3. Install and activate the plugin with WooCommerce active.
4. Open **WooCommerce > Portus Cart Page**.
5. Configure the presentation, colors, text, container width, and spacing.
6. Add a product and open the store's cart page to verify the result.

For a ready-to-install ZIP, use the [WordPress.org download](https://wordpress.org/plugins/portus-cart-page/). GitHub source archives include repository documentation and directory assets in addition to the plugin source.

## Shortcodes and Page Builders

Use one `[woocommerce_cart]` or `[portus_cart_page]` shortcode on the page configured as the WooCommerce cart. In Elementor, use a Shortcode widget and edit the content surrounding it. Configure the cart's internal presentation in the Portus settings panel.

An optional compatibility setting replaces the WooCommerce Cart Block presentation with the classic Portus renderer at runtime. Saved page content is not rewritten. This is not a native Cart Block extension; integrations that depend exclusively on the block-based cart require individual testing.

Exclude the cart and checkout from page caching. Theme overrides and third-party pricing, shipping, subscription, or product-bundle integrations may require store-specific validation.

## Free and Pro

This public repository mirrors the Free WordPress.org edition of Portus Cart Page, currently version **1.4.13**.

All functionality included here works without a license key, payment, trial period, or usage limit. Native cross-sell recommendations are included in Free.

The commercial Pro extension is distributed separately outside WordPress.org and is not included in this repository. It adds layouts, sticky summaries, an optional mobile checkout bar, advanced presentation settings, shipping progress, informational payment details, and configuration import/export.

## Privacy

The Free plugin does not send telemetry, license checks, customer data, order data, or payment data to Portus. Standard Free updates are distributed through WordPress.org. Cart sessions, calculations, and payment/shipping integrations remain under WooCommerce and the store's configured extensions.

## WordPress.org Assets

The `wordpress-org-assets/` directory contains the public icon, banners, and screenshots used by the WordPress.org plugin page. Runtime styles, scripts, and icons are in `assets/`.

## Development Notes

This repository mirrors the public Free edition. Releases to WordPress.org are published through the [official SVN repository](https://plugins.svn.wordpress.org/portus-cart-page/).

Private Pro packages, license-server code, credentials, customer data, and internal validation reports do not belong in this repository.

## Maintainer

Portus Cart Page is created and maintained by [Daniel Portela](https://github.com/daniel-portela) under [Portus](https://github.com/useportus).

## License

Portus Cart Page is licensed under the GPLv2 or later. See [LICENSE](LICENSE) for details.

Included Bootstrap Icons are distributed under the MIT license. The original notice is preserved in [LICENSE-Bootstrap-Icons.txt](LICENSE-Bootstrap-Icons.txt).
