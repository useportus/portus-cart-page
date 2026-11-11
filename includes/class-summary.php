<?php
namespace Portus\CartPage;
defined('ABSPATH') || exit;

/** Presentation only: WooCommerce owns all discounts, totals and removal actions. */
final class Summary {
    public static function boot(): void {
        add_filter('woocommerce_cart_totals_coupon_label', [self::class, 'coupon_label'], 20, 2);
        add_filter('woocommerce_cart_totals_coupon_html', [self::class, 'coupon_html'], 20, 3);
        add_action('woocommerce_cart_totals_before_order_total', [self::class, 'savings']);
    }

    private static function active(): bool {
        if (!Frontend::active()) { return false; }
        if (Frontend::rendering()) { return true; }
        // Shipping refreshes render only native cart totals, outside the page wrapper.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only routing context; WooCommerce validates the request.
        $action = isset($_GET['wc-ajax']) ? sanitize_key(wp_unslash($_GET['wc-ajax'])) : '';
        return defined('WC_DOING_AJAX') && WC_DOING_AJAX && in_array($action, ['get_cart_totals', 'update_shipping_method'], true);
    }

    public static function coupon_label($label, $coupon): string {
        return self::active() ? esc_html__('Cupom aplicado', 'portus-cart-page') : $label;
    }

    public static function coupon_html($html, $coupon, $discount): string {
        if (!self::active()) { return $html; }
        // Keep the native remove link, aria-label, filters and data-coupon for AJAX.
        return '<span class="pcp-discount-value">' . $html . '</span><small class="pcp-coupon-code">' . esc_html($coupon->get_code()) . '</small>';
    }

    public static function savings(): void {
        if (!self::active() || !WC()->cart) { return; }
        $saving = (float) WC()->cart->get_discount_total();
        if (WC()->cart->display_prices_including_tax()) { $saving += (float) WC()->cart->get_discount_tax(); }
        if ($saving <= 0) { return; }
        echo '<tr class="pcp-savings"><td colspan="2"><span>' . esc_html__('Você economizou', 'portus-cart-page') . '</span> <strong>' . wp_kses_post(wc_price($saving)) . '</strong></td></tr>';
    }
}
