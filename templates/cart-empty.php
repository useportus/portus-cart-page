<?php
defined('ABSPATH') || exit;
$portus_settings = \Portus\CartPage\Settings::all();
$portus_empty = apply_filters('portus_cart_page_empty_content', [
    'title' => __('Seu próximo favorito está esperando.', 'portus-cart-page'),
    'description' => __('Explore a loja e encontre algo para chamar de seu.', 'portus-cart-page'),
]);
// Replace only the core empty message; preserve third-party hooks and notices.
$portus_empty_priority = has_action('woocommerce_cart_is_empty', 'wc_empty_cart_message');
if ($portus_empty_priority !== false) { remove_action('woocommerce_cart_is_empty', 'wc_empty_cart_message', $portus_empty_priority); }
try { do_action('woocommerce_cart_is_empty'); }
finally { if ($portus_empty_priority !== false) { add_action('woocommerce_cart_is_empty', 'wc_empty_cart_message', $portus_empty_priority); } }
?>
<div class="wc-empty-cart-message pcp-empty">
    <span class="pcp-empty-icon" aria-hidden="true"><svg viewBox="0 0 48 48" width="48" height="48" fill="none" focusable="false"><path d="M11 15h26l3 27H8l3-27Z" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"/><path d="M17 18V11a7 7 0 0 1 14 0v7M18 28h12M21 34h6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg></span>
    <h2><?php echo esc_html($portus_empty['title']); ?></h2>
    <p><?php echo esc_html($portus_empty['description']); ?></p>
    <a class="button pcp-continue pcp-continue-empty" href="<?php echo esc_url(\Portus\CartPage\Settings::continue_url()); ?>"><?php echo esc_html($portus_settings['continue_text']); ?></a>
</div>
