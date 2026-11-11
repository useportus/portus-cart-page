<?php
/** Portus cart presentation. WooCommerce remains responsible for all cart actions and totals. */
defined('ABSPATH') || exit;
$portus_settings = \Portus\CartPage\Settings::all();
do_action('woocommerce_before_cart');
?>
<div class="pcp-cart-grid">
    <form class="woocommerce-cart-form pcp-items-panel" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
        <div class="pcp-list-heading"><h3><?php esc_html_e('Produtos no carrinho', 'portus-cart-page'); ?></h3><span><?php
        $portus_count = WC()->cart->get_cart_contents_count();
        /* translators: %d is the quantity of items. */
        echo esc_html(sprintf(_n('%d item', '%d itens', $portus_count, 'portus-cart-page'), $portus_count));
        ?></span></div>
        <?php do_action('woocommerce_before_cart_table'); ?>
        <table class="woocommerce-cart-form__contents pcp-product-list" role="table">
            <thead class="screen-reader-text"><tr><th><?php esc_html_e('Remover', 'portus-cart-page'); ?></th><th><?php esc_html_e('Imagem', 'portus-cart-page'); ?></th><th><?php esc_html_e('Produto', 'portus-cart-page'); ?></th><th><?php esc_html_e('Preço', 'portus-cart-page'); ?></th><th><?php esc_html_e('Quantidade', 'portus-cart-page'); ?></th><th><?php esc_html_e('Subtotal', 'portus-cart-page'); ?></th></tr></thead>
            <tbody role="rowgroup">
            <?php do_action('woocommerce_before_cart_contents'); ?>
            <?php foreach (WC()->cart->get_cart() as $portus_key => $portus_item) :
                $portus_product = apply_filters('woocommerce_cart_item_product', $portus_item['data'], $portus_item, $portus_key);
                $portus_product_id = apply_filters('woocommerce_cart_item_product_id', $portus_item['product_id'], $portus_item, $portus_key);
                if (!$portus_product || !$portus_product->exists() || $portus_item['quantity'] <= 0 || !apply_filters('woocommerce_cart_item_visible', true, $portus_item, $portus_key)) { continue; }
                $portus_base_name = method_exists(WC()->cart, 'get_item_product_name') ? WC()->cart->get_item_product_name($portus_item, $portus_product) : $portus_product->get_name();
                $portus_name = apply_filters('woocommerce_cart_item_name', $portus_base_name, $portus_item, $portus_key);
                $portus_url = apply_filters('woocommerce_cart_item_permalink', $portus_product->is_visible() ? $portus_product->get_permalink($portus_item) : '', $portus_item, $portus_key);
                ?>
                <tr class="woocommerce-cart-form__cart-item pcp-product-row <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $portus_item, $portus_key)); ?>" role="row">
                    <td class="product-remove pcp-remove" role="cell"><?php
                        echo wp_kses_post(apply_filters('woocommerce_cart_item_remove_link', sprintf('<a role="button" href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>', esc_url(wc_get_cart_remove_url($portus_key)), esc_attr(sprintf(/* translators: %s is the product name. */
                            __('Remover %s do carrinho', 'portus-cart-page'), wp_strip_all_tags($portus_name))), esc_attr($portus_product_id), esc_attr($portus_product->get_sku())), $portus_key));
                    ?></td>
                    <td class="product-thumbnail pcp-image" role="cell"><?php
                        $portus_image = apply_filters('woocommerce_cart_item_thumbnail', $portus_product->get_image(), $portus_item, $portus_key);
                        echo $portus_url ? '<a href="' . esc_url($portus_url) . '">' . wp_kses_post($portus_image) . '</a>' : wp_kses_post($portus_image); // Native WooCommerce image HTML.
                    ?></td>
                    <td class="product-name pcp-name" role="cell"><?php
                        echo $portus_url ? wp_kses_post(apply_filters('woocommerce_cart_item_name', '<a href="' . esc_url($portus_url) . '">' . $portus_base_name . '</a>', $portus_item, $portus_key)) : wp_kses_post($portus_name);
                        do_action('woocommerce_after_cart_item_name', $portus_item, $portus_key);
                        echo wp_kses_post(wc_get_formatted_cart_item_data($portus_item)); // WooCommerce formats and escapes metadata.
                        if ($portus_product->backorders_require_notification() && $portus_product->is_on_backorder($portus_item['quantity'])) {
                            echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Disponível por encomenda', 'portus-cart-page') . '</p>', $portus_product_id, $portus_item, $portus_key));
                        }
                    ?></td>
                    <td class="product-price pcp-price" role="cell"><?php echo wp_kses_post(apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($portus_product), $portus_item, $portus_key)); ?></td>
                    <td class="product-quantity pcp-quantity" role="cell"><?php $portus_max_quantity = $portus_product->is_sold_individually() ? 1 : $portus_product->get_max_purchase_quantity(); ?><div class="pcp-quantity-control" data-max="<?php echo esc_attr($portus_max_quantity); ?>">
                        <button type="button" class="pcp-quantity-step" data-step="-1" aria-label="<?php echo esc_attr(sprintf(/* translators: %s is the product name. */
                            __('Diminuir quantidade de %s', 'portus-cart-page'), wp_strip_all_tags($portus_name))); ?>">−</button>
                        <?php
                        $portus_quantity = woocommerce_quantity_input(['input_name' => "cart[{$portus_key}][qty]", 'input_value' => $portus_item['quantity'], 'max_value' => $portus_product->is_sold_individually() ? 1 : $portus_product->get_max_purchase_quantity(), 'min_value' => $portus_product->is_sold_individually() ? 1 : 0, 'product_name' => wp_strip_all_tags($portus_name)], $portus_product, false);
                        echo \Portus\CartPage\Frontend::sanitize_quantity(apply_filters('woocommerce_cart_item_quantity', $portus_quantity, $portus_key, $portus_item)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by the quantity control HTML allowlist.
                        if ($portus_product->is_sold_individually()) { echo '<span class="pcp-single-quantity" aria-hidden="true">1</span>'; }
                        ?>
                        <button type="button" class="pcp-quantity-step" data-step="1" <?php disabled($portus_max_quantity >= 0 && (float) $portus_item['quantity'] >= $portus_max_quantity); ?> aria-label="<?php echo esc_attr(sprintf(/* translators: %s is the product name. */
                            __('Aumentar quantidade de %s', 'portus-cart-page'), wp_strip_all_tags($portus_name))); ?>">+</button>
                    </div></td>
                    <td class="product-subtotal pcp-subtotal" role="cell"><span class="pcp-subtotal-label"><?php esc_html_e('Subtotal', 'portus-cart-page'); ?></span><?php echo wp_kses_post(apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($portus_product, $portus_item['quantity']), $portus_item, $portus_key)); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php do_action('woocommerce_cart_contents'); ?>
            <?php do_action('woocommerce_after_cart_contents'); ?>
            </tbody>
        </table>
        <?php do_action('woocommerce_after_cart_table'); ?>
        <div class="pcp-form-actions">
            <?php if (wc_coupons_enabled()) : ?>
                <div class="coupon pcp-coupon"><label for="coupon_code"><?php esc_html_e('Tem um cupom de desconto?', 'portus-cart-page'); ?></label><div class="pcp-coupon-fields"><input type="text" id="coupon_code" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e('Código do cupom', 'portus-cart-page'); ?>" value=""><button type="submit" name="apply_coupon" value="<?php esc_attr_e('Aplicar cupom', 'portus-cart-page'); ?>" class="button"><?php esc_html_e('Aplicar', 'portus-cart-page'); ?></button></div><?php do_action('woocommerce_cart_coupon'); ?></div>
            <?php endif; ?>
            <button type="submit" name="update_cart" value="<?php esc_attr_e('Atualizar carrinho', 'portus-cart-page'); ?>" class="button pcp-update-cart"><?php esc_html_e('Atualizar carrinho', 'portus-cart-page'); ?></button>
            <?php do_action('woocommerce_cart_actions'); ?>
            <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>
        </div>
    </form>
    <?php do_action('woocommerce_before_cart_collaterals'); ?>
    <aside class="cart-collaterals pcp-summary-panel" aria-label="<?php esc_attr_e('Resumo do pedido', 'portus-cart-page'); ?>"><h3 class="pcp-summary-heading"><?php esc_html_e('Resumo do pedido', 'portus-cart-page'); ?></h3><?php do_action('woocommerce_cart_collaterals'); ?></aside>
    <?php if (apply_filters('portus_cart_page_show_cross_sells', true, $portus_settings)) : ?>
        <?php ob_start(); woocommerce_cross_sell_display(); $portus_related = trim(ob_get_clean()); ?>
        <?php if ($portus_related !== '') : ?><div class="pcp-related"><?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Native WooCommerce cross-sell template, including extension hooks.
            echo $portus_related;
        ?></div><?php endif; ?>
    <?php endif; ?>
</div>
<?php do_action('woocommerce_after_cart'); ?>
