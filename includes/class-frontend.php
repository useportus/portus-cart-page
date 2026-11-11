<?php
namespace Portus\CartPage;
defined('ABSPATH') || exit;

final class Frontend {
    private static $native_cart;
    private static bool $rendering = false;
    public static function rendering(): bool { return self::$rendering; }

    public static function active(): bool { return !empty(Settings::all()['enabled']); }

    /** Allow native quantity controls and extension metadata, without executable attributes. */
    public static function sanitize_quantity(string $html): string {
        $allowed = wp_kses_allowed_html('post');
        $attributes = ['id' => true, 'class' => true, 'name' => true, 'value' => true, 'type' => true, 'min' => true, 'max' => true, 'step' => true, 'size' => true, 'title' => true, 'placeholder' => true, 'pattern' => true, 'inputmode' => true, 'autocomplete' => true, 'disabled' => true, 'readonly' => true, 'required' => true, 'aria-*' => true, 'data-*' => true];
        foreach (['input', 'button', 'select', 'option', 'label'] as $tag) { $allowed[$tag] = array_merge($allowed[$tag] ?? [], $attributes); }
        $allowed['label']['for'] = true;
        $allowed['option']['selected'] = true;
        return wp_kses($html, $allowed);
    }

    public static function boot(): void {
        Summary::boot();
        add_action('init', function () {
            global $shortcode_tags;
            self::$native_cart = $shortcode_tags['woocommerce_cart'] ?? ['WC_Shortcodes', 'cart'];
            add_shortcode('woocommerce_cart', [self::class, 'classic']);
            add_shortcode('portus_cart_page', [self::class, 'shortcode']);
        }, 99);
        add_filter('pre_render_block', function ($pre, $block) {
            if ($pre !== null || is_admin() || !is_cart() || !self::active() || empty(Settings::all()['replace_blocks'])) { return $pre; }
            return ($block['blockName'] ?? '') === 'woocommerce/cart' ? self::render() : $pre;
        }, 10, 2);
        add_action('wp_enqueue_scripts', function () {
            if (is_cart() && self::active()) { self::assets(); }
        }, 30);
        add_filter('wc_get_template', function ($template, $name) {
            if (!self::$rendering) { return $template; }
            if ($name === 'cart/cart.php') { return PORTUS_CART_PAGE_DIR . 'templates/cart.php'; }
            return $name === 'cart/cart-empty.php' ? PORTUS_CART_PAGE_DIR . 'templates/cart-empty.php' : $template;
        }, PHP_INT_MAX, 2);
    }

    public static function classic($atts = [], $content = null, $tag = ''): string {
        if (!is_admin() && is_cart() && self::active()) { return self::render($atts); }
        return (string) call_user_func(self::$native_cart, $atts, $content, $tag);
    }

    public static function shortcode($atts = []): string {
        if (!is_cart() || !self::active()) { return (string) call_user_func(self::$native_cart, $atts); }
        return self::render($atts);
    }

    public static function assets(): void {
        $s = Settings::all(); $url = plugin_dir_url(PORTUS_CART_PAGE_FILE);
        wp_enqueue_style('portus-cart-page', $url . 'assets/cart.css', [], PORTUS_CART_PAGE_VERSION);
        wp_enqueue_script('wc-cart');
        wp_enqueue_script('portus-cart-page', $url . 'assets/cart.js', ['jquery', 'wc-cart'], PORTUS_CART_PAGE_VERSION, true);
        wp_localize_script('portus-cart-page', 'portusCartPage', ['updating' => __('Atualizando carrinho…', 'portus-cart-page'), 'updated' => __('Carrinho atualizado.', 'portus-cart-page')]);
        // All values below originate in bounded numeric / hex settings. Extensions receive the CSS through a filter.
        $css = '.pcp-cart{--pcp-accent:' . sanitize_hex_color($s['accent']) . ';--pcp-button-text:' . sanitize_hex_color($s['button_text']) . ';--pcp-radius:' . max(0, min(40, (int) $s['radius'])) . 'px;--pcp-gap:' . max(12, min(64, (int) $s['spacing'])) . 'px;}';
        wp_add_inline_style('portus-cart-page', self::sanitize_css(apply_filters('portus_cart_page_css', $css, $s)));
        $width = $s['container_mode'] === 'custom' ? max(640, min(1920, (int) $s['container_width'])) . 'px' : '100%';
        wp_add_inline_style('portus-cart-page', '.pcp-cart{--pcp-container-width:' . $width . ';--pcp-container-padding:' . max(0, min(48, (int) $s['container_padding'])) . 'px;}');
        do_action('portus_cart_page_enqueue', $s);
    }

    /**
     * Accept only cart-scoped rules containing primitive design values.
     *
     * This legacy extension filter is not an arbitrary stylesheet editor. Reject
     * markup, URLs, functions, escapes, at-rules and unsupported selectors before
     * applying WordPress's declaration sanitizer at the inline-style boundary.
     */
    public static function sanitize_css($css): string {
        if (!is_string($css)) { return ''; }
        $safe = '';
        preg_match_all('/(?:\A|(?<=}))\s*(\.pcp-cart(?:\.pcp-pro)?)\s*\{([^{}]*)\}/', $css, $rules, PREG_SET_ORDER);
        foreach ($rules as $rule) {
            $declarations = [];
            foreach (explode(';', $rule[2]) as $declaration) {
                $parts = explode(':', $declaration, 2);
                if (count($parts) !== 2) { continue; }
                $property = trim($parts[0]);
                $value = trim($parts[1]);
                $variable = preg_match('/^--pcp-[a-z][a-z-]*$/D', $property);
                $primitive = preg_match('/^(?:#[a-fA-F0-9]{3}|#[a-fA-F0-9]{6}|[0-9]+(?:\.[0-9]+)?(?:px|%|rem|em)?|inherit)$/D', $value);
                $font = $property === 'font-family' && in_array($value, ['inherit', 'system-ui,sans-serif', 'Georgia,serif'], true);
                if (($variable && $primitive) || $font) {
                    $declarations[] = safecss_filter_attr($property . ':' . $value);
                }
            }
            $declarations = array_filter($declarations);
            if ($declarations) { $safe .= $rule[1] . '{' . implode(';', $declarations) . ';}'; }
        }
        return $safe;
    }

    public static function render($atts = []): string {
        if (self::$rendering) { return ''; }
        $s = Settings::all();
        $builder_layout_actions = self::suspend_builder_layout_actions();
        $cross_sell_priority = has_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display');
        if ($cross_sell_priority !== false) {
            remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', $cross_sell_priority);
        }
        self::$rendering = true;
        try {
            // Use the native cart engine with Portus's scoped template. Builder shortcode overrides
            // must not nest a second cart layout inside this presentation.
            $cart = (string) \WC_Shortcodes::cart($atts);
        } finally {
            self::$rendering = false;
            foreach ($builder_layout_actions as $action) {
                add_action($action['hook'], $action['callback'], $action['priority'], $action['accepted_args']);
            }
            if ($cross_sell_priority !== false && !has_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display')) {
                add_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display', $cross_sell_priority);
            }
        }
        $classes = apply_filters('portus_cart_page_classes', ['pcp-cart', $s['shadow'] === 'none' ? 'pcp-no-shadow' : 'pcp-soft-shadow'], $s);
        $classes = implode(' ', array_map('sanitize_html_class', $classes));
        ob_start(); ?>
        <section class="<?php echo esc_attr($classes); ?>" aria-label="<?php echo esc_attr($s['title']); ?>">
            <header class="pcp-heading"><div><h2><?php echo esc_html($s['title']); ?></h2><p><?php echo esc_html($s['subtitle']); ?></p></div><a class="pcp-continue" href="<?php echo esc_url(Settings::continue_url()); ?>"><?php echo esc_html($s['continue_text']); ?></a></header>
            <div class="pcp-layout"><?php
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered by WooCommerce and the scoped, escaped Portus templates; includes forms and extension payment controls.
                echo $cart;
            ?></div>
            <span class="screen-reader-text pcp-status" role="status" aria-live="polite" aria-atomic="true"></span>
            <?php do_action('portus_cart_page_after_layout', $s); ?>
        </section><?php return ob_get_clean();
    }

    /** Royal's cart widget also adds wrappers and a second coupon through actions, outside its template. */
    private static function suspend_builder_layout_actions(): array {
        global $wp_filter;
        $suspended = [];
        $hooks = ['woocommerce_before_cart', 'woocommerce_after_cart', 'woocommerce_before_cart_table', 'woocommerce_after_cart_table', 'woocommerce_cart_contents', 'woocommerce_after_cart_contents'];
        foreach ($hooks as $hook) {
            if (!isset($wp_filter[$hook]) || !($wp_filter[$hook] instanceof \WP_Hook)) { continue; }
            $registered = $wp_filter[$hook]->callbacks;
            foreach ($registered as $priority => $callbacks) {
                foreach ($callbacks as $entry) {
                    $callback = $entry['function'];
                    if (!is_array($callback) || !is_object($callback[0]) || !is_a($callback[0], 'WprAddons\\Modules\\ThemeBuilder\\Woocommerce\\PageCart\\Widgets\\Wpr_Page_Cart') || $callback[1] !== $hook) { continue; }
                    $suspended[] = ['hook' => $hook, 'callback' => $callback, 'priority' => $priority, 'accepted_args' => $entry['accepted_args']];
                    remove_action($hook, $callback, $priority);
                }
            }
        }
        return $suspended;
    }
}
