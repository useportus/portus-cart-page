<?php
namespace Portus\CartPage;
defined('ABSPATH') || exit;

final class Settings {
    const OPTION = 'portus_cart_page_settings';

    public static function defaults(): array {
        return [
            'enabled' => 1, 'replace_blocks' => 1, 'accent' => '#00053a',
            'button_text' => '#ffffff', 'radius' => 8, 'spacing' => 32, 'shadow' => 'soft',
            'title' => __('Seu carrinho', 'portus-cart-page'), 'subtitle' => __('Revise seus produtos e siga para finalizar a compra.', 'portus-cart-page'),
            'continue_text' => __('Continuar comprando', 'portus-cart-page'), 'continue_url' => '', 'upgrade_url' => '',
            'container_mode' => 'theme', 'container_width' => 1200, 'container_padding' => 0,
        ];
    }

    public static function schema(): array {
        return apply_filters('portus_cart_page_settings_schema', [
            'enabled' => ['checkbox', __('Ativar apresentação Portus', 'portus-cart-page'), 'general'],
            'replace_blocks' => ['checkbox', __('Usar Portus também no bloco de carrinho', 'portus-cart-page'), 'general'],
            'accent' => ['color', __('Cor principal', 'portus-cart-page'), 'appearance'],
            'button_text' => ['color', __('Texto dos botões', 'portus-cart-page'), 'appearance'],
            'radius' => ['number', __('Arredondamento (px)', 'portus-cart-page'), 'appearance', 0, 40],
            'spacing' => ['number', __('Espaçamento (px)', 'portus-cart-page'), 'appearance', 12, 64],
            'shadow' => ['select', __('Sombra do contêiner', 'portus-cart-page'), 'appearance', ['soft' => __('Suave', 'portus-cart-page'), 'none' => __('Sem sombra', 'portus-cart-page')]],
            'container_mode' => ['select', __('Largura do carrinho', 'portus-cart-page'), 'appearance', ['theme' => __('Acompanhar o contêiner do site', 'portus-cart-page'), 'custom' => __('Largura máxima personalizada', 'portus-cart-page')]],
            'container_width' => ['number', __('Largura máxima personalizada (px)', 'portus-cart-page'), 'appearance', 640, 1920],
            'container_padding' => ['number', __('Margem interna lateral (px)', 'portus-cart-page'), 'appearance', 0, 48],
            'title' => ['text', __('Título do carrinho', 'portus-cart-page'), 'content'],
            'subtitle' => ['text', __('Descrição', 'portus-cart-page'), 'content'],
            'continue_text' => ['text', __('Texto para continuar comprando', 'portus-cart-page'), 'content'],
            'continue_url' => ['destination', __('URL do botão Continuar comprando (ex.: /loja)', 'portus-cart-page'), 'content'],
            'upgrade_url' => ['url', __('URL comercial do Pro (opcional)', 'portus-cart-page'), 'general'],
        ]);
    }

    public static function all(): array {
        return array_merge(apply_filters('portus_cart_page_defaults', self::defaults()), (array) get_option(self::OPTION, []));
    }

    public static function sanitize_destination($value): string {
        $value = is_scalar($value) ? trim((string) $value) : '';
        if ($value === '' || preg_match('/[\x00-\x20\\\\]/', $value)) { return ''; }
        if (strpos($value, '/') === 0) {
            return strpos($value, '//') === 0 ? '' : esc_url_raw($value, ['http', 'https']);
        }
        $parts = wp_parse_url($value);
        return is_array($parts) && !empty($parts['host']) && in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            ? esc_url_raw($value, ['http', 'https']) : '';
    }

    public static function continue_url(): string {
        $destination = self::sanitize_destination(self::all()['continue_url']);
        if ($destination === '') { return function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/'); }
        return strpos($destination, '/') === 0 ? home_url($destination) : $destination;
    }

    public static function sanitize($raw): array {
        $raw = is_array($raw) ? $raw : [];
        // Preserve extension settings while Pro is inactive.
        $clean = self::all();
        foreach (self::schema() as $key => $field) {
            $value = $raw[$key] ?? '';
            if (!is_scalar($value)) { $value = ''; }
            switch ($field[0]) {
                case 'checkbox': $clean[$key] = empty($value) ? 0 : 1; break;
                case 'color': $clean[$key] = sanitize_hex_color((string) $value) ?: ($clean[$key] ?? '#155e50'); break;
                case 'number': $clean[$key] = max($field[3], min($field[4], (int) $value)); break;
                case 'select': $clean[$key] = array_key_exists((string) $value, $field[3]) ? (string) $value : array_key_first($field[3]); break;
                case 'url': $clean[$key] = esc_url_raw((string) $value, ['https', 'http']); break;
                case 'destination': $clean[$key] = self::sanitize_destination($value); break;
                default: $clean[$key] = sanitize_text_field(mb_substr((string) $value, 0, 500));
            }
        }
        return $clean;
    }

    public static function boot(): void {
        add_action('init', function () {
            if (get_option('portus_cart_page_design_version') === '1.2.0') { return; }
            $saved = (array) get_option(self::OPTION, []);
            foreach (['accent' => ['#155e50', '#00053a'], 'background' => ['#f5f7f6', '#f7f8fc'], 'text_color' => ['#192b26', '#161b35']] as $key => $values) {
                if (isset($saved[$key]) && is_string($saved[$key]) && strtolower($saved[$key]) === $values[0]) { $saved[$key] = $values[1]; }
            }
            if (($saved['background'] ?? '') === '#f7f8fc') { $saved['background'] = '#ffffff'; }
            if (($saved['text_color'] ?? '') === '#161b35') { $saved['text_color'] = '#222222'; }
            if (isset($saved['radius']) && (int) $saved['radius'] === 16) { $saved['radius'] = 2; }
            if (isset($saved['spacing']) && (int) $saved['spacing'] === 28) { $saved['spacing'] = 32; }
            if ($saved) { update_option(self::OPTION, $saved, false); }
            update_option('portus_cart_page_design_version', '1.2.0', false);
        }, 20);
        add_action('admin_menu', function () {
            add_submenu_page('woocommerce', 'Portus Cart Page', 'Portus Cart Page', 'manage_woocommerce', 'portus-cart-page', [self::class, 'page']);
        });
        add_filter('option_page_capability_portus_cart_page', function () { return 'manage_woocommerce'; });
        add_action('admin_init', function () {
            register_setting('portus_cart_page', self::OPTION, ['sanitize_callback' => [self::class, 'sanitize'], 'type' => 'array', 'default' => self::defaults()]);
        });
        add_action('admin_enqueue_scripts', function ($hook) {
            if (strpos($hook, 'portus-cart-page') === false) { return; }
            $url = plugin_dir_url(PORTUS_CART_PAGE_FILE);
            wp_enqueue_style('portus-cart-page-admin', $url . 'assets/admin.css', [], PORTUS_CART_PAGE_VERSION);
            wp_enqueue_script('portus-cart-page-admin', $url . 'assets/admin.js', [], PORTUS_CART_PAGE_VERSION, true);
        });
        add_action('admin_post_portus_cart_page_reset', [self::class, 'reset']);
    }

    private static function authorize(string $action): void {
        if (!current_user_can('manage_woocommerce')) { wp_die(esc_html__('Acesso negado.', 'portus-cart-page'), '', ['response' => 403]); }
        check_admin_referer($action);
    }

    public static function reset(): void {
        self::authorize('portus_cart_page_reset');
        $defaults = apply_filters('portus_cart_page_defaults', self::defaults());
        // Reset inactive Pro fields too, without retaining hidden customizations.
        delete_option(self::OPTION);
        update_option(self::OPTION, $defaults, false);
        wp_safe_redirect(admin_url('admin.php?page=portus-cart-page&reset=1')); exit;
    }

    public static function page(): void {
        if (!current_user_can('manage_woocommerce')) { return; }
        $s = self::all(); $pro = defined('PORTUS_CART_PAGE_PRO_VERSION');
        $tabs = apply_filters('portus_cart_page_admin_tabs', ['general' => __('Geral', 'portus-cart-page'), 'appearance' => __('Aparência', 'portus-cart-page'), 'content' => __('Conteúdo', 'portus-cart-page'), 'pro' => __('Recursos Pro', 'portus-cart-page'), 'help' => __('Ajuda', 'portus-cart-page')]);
        ?>
        <div class="wrap pcp-admin">
            <h1 class="screen-reader-text">Portus Cart Page</h1><header class="pcp-admin-header"><div class="pcp-admin-brand"><img class="pcp-admin-logo" src="<?php echo esc_url(plugin_dir_url(PORTUS_CART_PAGE_FILE) . 'assets/cart-page-icon.svg?ver=' . PORTUS_CART_PAGE_VERSION); ?>" alt="" aria-hidden="true" width="64" height="64"><div class="pcp-admin-brand-copy"><span class="pcp-eyebrow">PORTUS / WOOCOMMERCE</span><h2>Portus Cart Page <span class="pcp-badge"><?php echo $pro ? 'PRO' : 'FREE'; ?></span></h2><p><?php esc_html_e('Um carrinho à altura da sua loja.', 'portus-cart-page'); ?></p></div></div><a class="button" href="<?php echo esc_url(function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url()); ?>" target="_blank" rel="noopener"><?php esc_html_e('Ver carrinho ↗', 'portus-cart-page'); ?></a></header>
            <?php settings_errors(); ?>
            <?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only success indicator; does not process or modify settings.
            if (isset($_GET['reset']) || isset($_GET['imported'])) : ?><div class="notice notice-success"><p><?php esc_html_e('Configurações atualizadas.', 'portus-cart-page'); ?></p></div><?php endif; ?>
            <nav class="pcp-tabs" aria-label="<?php esc_attr_e('Configurações', 'portus-cart-page'); ?>"><?php foreach ($tabs as $key => $label) : ?><button type="button" data-tab="<?php echo esc_attr($key); ?>" aria-pressed="<?php echo $key === 'general' ? 'true' : 'false'; ?>"><?php echo esc_html($label); ?></button><?php endforeach; ?></nav>
            <div class="pcp-admin-columns"><div>
            <form action="options.php" method="post" id="pcp-settings">
                <?php settings_fields('portus_cart_page'); ?>
                <?php foreach (['general', 'appearance', 'content', 'pro'] as $tab) : ?>
                <section class="pcp-panel" data-panel="<?php echo esc_attr($tab); ?>" <?php echo $tab !== 'general' ? 'hidden' : ''; ?>><h2><?php echo esc_html($tabs[$tab]); ?></h2>
                <?php foreach (self::schema() as $key => $field) : if ($field[2] !== $tab) { continue; } ?>
                    <div class="pcp-field"><label for="pcp-<?php echo esc_attr($key); ?>"><?php echo esc_html($field[1]); ?></label>
                    <?php if ($field[0] === 'select') : ?><select id="pcp-<?php echo esc_attr($key); ?>" name="<?php echo esc_attr(self::OPTION . '[' . $key . ']'); ?>"><?php foreach ($field[3] as $value => $label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($s[$key] ?? '', $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select>
                    <?php else : ?><input id="pcp-<?php echo esc_attr($key); ?>" type="<?php echo esc_attr($field[0] === 'destination' ? 'text' : $field[0]); ?>" name="<?php echo esc_attr(self::OPTION . '[' . $key . ']'); ?>" value="<?php echo esc_attr($field[0] === 'checkbox' ? '1' : ($s[$key] ?? '')); ?>" <?php if ($field[0] === 'checkbox') { checked(!empty($s[$key])); } ?> <?php if ($field[0] === 'number') { echo 'min="' . esc_attr($field[3]) . '" max="' . esc_attr($field[4]) . '"'; } ?> <?php echo $field[0] === 'text' ? 'maxlength="500"' : ''; ?>><?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if ($tab === 'general') : ?><p class="description"><?php esc_html_e('A apresentação é aplicada à página definida como carrinho no WooCommerce. O conteúdo salvo da página é preservado. Desative a substituição do bloco se quiser manter o Cart Block original.', 'portus-cart-page'); ?></p><?php endif; ?>
                <?php if ($tab === 'pro' && !$pro) : ?><p><?php esc_html_e('A extensão Pro adiciona layouts, resumo fixo, barra de ação no celular, progresso para frete grátis e personalização avançada.', 'portus-cart-page'); ?></p><?php $upgrade = apply_filters('portus_cart_page_upgrade_url', $s['upgrade_url']); if ($upgrade) : ?><a class="button button-primary" href="<?php echo esc_url($upgrade); ?>" target="_blank" rel="noopener"><?php esc_html_e('Conhecer o Pro', 'portus-cart-page'); ?></a><?php else : ?><p class="description"><?php esc_html_e('A URL comercial pode ser configurada na aba Geral.', 'portus-cart-page'); ?></p><?php endif; endif; ?>
                </section><?php endforeach; ?>
                <?php submit_button(__('Salvar configurações', 'portus-cart-page')); ?>
            </form>
            <?php do_action('portus_cart_page_admin_extra_panels'); ?>
            <section class="pcp-panel" data-panel="help" hidden><h2><?php esc_html_e('Como começar', 'portus-cart-page'); ?></h2><ol><li><?php esc_html_e('Ative o WooCommerce e configure as páginas de carrinho e checkout.', 'portus-cart-page'); ?></li><li><?php esc_html_e('Ative a apresentação Portus em Geral. O shortcode e o bloco de carrinho serão apresentados com o layout Portus.', 'portus-cart-page'); ?></li><li><?php esc_html_e('Você também pode usar [portus_cart_page] na página de carrinho.', 'portus-cart-page'); ?></li><li><?php esc_html_e('Ajuste o visual e verifique compras, cupons e frete em uma loja de testes antes de publicar.', 'portus-cart-page'); ?></li></ol><p><?php esc_html_e('A prévia ao lado usa produtos e valores ilustrativos. Frete, impostos, cupons e validações da loja são executados pelo WooCommerce. O plugin não modifica o checkout.', 'portus-cart-page'); ?></p><p><?php esc_html_e('Evite cache de página no carrinho e no checkout. A substituição do Cart Block usa a renderização clássica: extensões exclusivas do bloco precisam ser verificadas separadamente.', 'portus-cart-page'); ?></p>
            <h3><?php esc_html_e('Usar com Elementor', 'portus-cart-page'); ?></h3>
            <p><?php esc_html_e('Edite a página definida como carrinho em WooCommerce → Configurações → Avançado. Adicione um único widget Shortcode com [portus_cart_page]. Você pode colocar banners e textos ao redor dele. Configure o interior do carrinho neste painel; os produtos e o resumo não são widgets individuais do Elementor.', 'portus-cart-page'); ?></p>
            <h3><?php esc_html_e('Shortcode e bloco de carrinho', 'portus-cart-page'); ?></h3>
            <p><?php esc_html_e('No editor clássico, use [woocommerce_cart] ou [portus_cart_page], apenas uma vez. No editor de blocos, mantenha o Cart Block existente e ative sua substituição em Geral. O Portus apresenta o carrinho clássico sem reescrever a página. Para usar extensões exclusivas do Cart Block, desative essa substituição.', 'portus-cart-page'); ?></p>
            <h3><?php esc_html_e('Estoque e quantidades', 'portus-cart-page'); ?></h3>
            <p><?php esc_html_e('O botão + respeita o limite de compra e o estoque do WooCommerce, incluindo vendas sob encomenda quando permitidas. Produtos vendidos individualmente têm limite de uma unidade. Ao usar − com uma unidade, o item é removido pelo WooCommerce, que oferece a opção de desfazer. Sem JavaScript, use o campo de quantidade, Atualizar carrinho e o botão de remoção.', 'portus-cart-page'); ?></p>
            <p><?php esc_html_e('CEP, modalidades e valores de entrega seguem as zonas e integrações de frete do WooCommerce. Habilite a calculadora de entrega nas configurações do WooCommerce para permitir o cálculo no carrinho. Pedidos somente com produtos virtuais não exibem frete. Não são criados prazos ou opções de entrega fictícios.', 'portus-cart-page'); ?></p>
            <?php do_action('portus_cart_page_admin_help'); ?>
            <form method="post" data-confirm="<?php esc_attr_e('Restaurar todas as configurações Portus?', 'portus-cart-page'); ?>" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="portus_cart_page_reset"><?php wp_nonce_field('portus_cart_page_reset'); ?><button class="button"><?php esc_html_e('Restaurar padrões', 'portus-cart-page'); ?></button></form></section>
            </div><aside class="pcp-preview" aria-label="<?php esc_attr_e('Prévia ilustrativa', 'portus-cart-page'); ?>"><span class="pcp-eyebrow"><?php esc_html_e('PRÉVIA ILUSTRATIVA', 'portus-cart-page'); ?></span><h2 id="pcp-preview-title"><?php echo esc_html($s['title']); ?></h2><p id="pcp-preview-subtitle"><?php echo esc_html($s['subtitle']); ?></p><div class="pcp-preview-product"><span class="pcp-preview-thumb">P</span><div><strong><?php esc_html_e('Produto de exemplo', 'portus-cart-page'); ?></strong><small><?php esc_html_e('Quantidade: 1', 'portus-cart-page'); ?></small></div><b>R$ 149</b></div><div class="pcp-preview-total"><span><?php esc_html_e('Total ilustrativo', 'portus-cart-page'); ?></span><strong>R$ 149</strong></div><span class="pcp-preview-cta"><?php esc_html_e('Finalizar compra →', 'portus-cart-page'); ?></span><small><?php esc_html_e('Cores, bordas, fontes e espaçamento acompanham os controles. Para conferir layouts e dados reais, abra o carrinho da loja.', 'portus-cart-page'); ?></small></aside></div>
        </div><?php
    }
}
