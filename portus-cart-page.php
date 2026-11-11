<?php
/**
 * Plugin Name: Portus Cart Page
 * Description: Uma página de carrinho moderna e personalizável, com as regras nativas do WooCommerce.
 * Version: 1.4.12
 * Author: Portus
 * Author URI: https://useportus.com/
 * Domain Path: /languages
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Requires Plugins: woocommerce
 * WC requires at least: 9.0
 * Text Domain: portus-cart-page
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
namespace Portus\CartPage;
defined('ABSPATH') || exit;
define('PORTUS_CART_PAGE_VERSION', '1.4.12');
define('PORTUS_CART_PAGE_FILE', __FILE__);
define('PORTUS_CART_PAGE_DIR', plugin_dir_path(__FILE__));
require_once __DIR__ . '/includes/class-settings.php';
require_once __DIR__ . '/includes/class-frontend.php';
require_once __DIR__ . '/includes/class-summary.php';
add_action('before_woocommerce_init', function () {
    if (class_exists('Automattic\\WooCommerce\\Utilities\\FeaturesUtil')) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

add_action('plugins_loaded', function () {
    Settings::boot();
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function () {
            if (current_user_can('activate_plugins')) {
                echo '<div class="notice notice-warning"><p>' . esc_html__('Portus Cart Page precisa do WooCommerce ativo.', 'portus-cart-page') . '</p></div>';
            }
        });
        return;
    }
    Frontend::boot();
    do_action('portus_cart_page_ready');
}, 20);

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    array_unshift($links, '<a href="' . esc_url(admin_url('admin.php?page=portus-cart-page')) . '">' . esc_html__('Configurar', 'portus-cart-page') . '</a>');
    return $links;
});
