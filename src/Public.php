<?php

/**
 * A funcionalidade pública do plugin.
 *
 * @package    CANNALEspetaculos_Plugin
 * @subpackage CANNALEspetaculos_Plugin/public
 */
class CANNALEspetaculos_Public
{

    /**
     * O ID deste plugin.
     */
    private $plugin_name;

    /**
     * A versão deste plugin.
     */
    private $version;

    /**
     * Inicializa a classe e define suas propriedades.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_filter('template_include', array(
            $this,
            'template_loader'
        ), 99);
        add_filter('the_content', array(
            $this,
            'espetaculo_content_filter'
        ));

        // Favicon personalizado por espetáculo
        add_action('wp_head', array(
            $this,
            'inject_espetaculo_favicon'
        ), 1);
    }

    /**
     * Injeta o favicon personalizado do espetáculo no <head>.
     * Fallback para o favicon padrão do site quando não houver ícone definido.
     */
    public function inject_espetaculo_favicon()
    {
        if (! is_singular('espetaculo')) {
            return;
        }

        $post_id = get_the_ID();
        $icone_id = (int) get_post_meta($post_id, '_espetaculo_icone', true);

        if (! $icone_id) {
            return; // Sem ícone: mantém o favicon padrão do tema
        }

        $icone_url = wp_get_attachment_image_url($icone_id, 'full');

        if (! $icone_url) {
            return;
        }

        // Sobrescrever o favicon padrão com o ícone do espetáculo
        // Usa priority=1 para rodar antes dos links de favicon do tema
        echo '<link rel="icon" type="image/png" href="' . esc_url($icone_url) . '" />' . "\n";
        echo '<link rel="shortcut icon" href="' . esc_url($icone_url) . '" />' . "\n";
        echo '<link rel="apple-touch-icon" href="' . esc_url($icone_url) . '" />' . "\n";
    }

    /**
     * Registra os arquivos de estilo para a área pública.
     */
    public function enqueue_styles()
    {
        wp_enqueue_style($this->plugin_name, CANNAL_ESPETACULOS_PLUGIN_URL . 'assets/css/cannal-espetaculos-public.css', array(), $this->version, 'all');
    }

    /**
     * Registra os arquivos JavaScript para a área pública.
     */
    public function enqueue_scripts()
    {
        if (is_singular('espetaculo') || is_post_type_archive('espetaculo') || is_tax('espetaculo_categoria')) {
            // Fancybox para galeria
            wp_enqueue_style('fancybox-css', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css', array(), '5.0');

            wp_enqueue_script('fancybox-js', 'https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js', array(), '5.0', true);

            wp_enqueue_script($this->plugin_name, CANNAL_ESPETACULOS_PLUGIN_URL . 'assets/js/cannal-espetaculos-public.js', array(
                'jquery',
                'fancybox-js'
            ), $this->version, true);
        }
    }

    /**
     * Carrega os templates personalizados.
     */
    public function template_loader($template)
    {
        // 1. VERIFICAÇÃO DO ELEMENTOR
        if (is_singular()) {
            // Checa se o post/espetáculo atual foi construído com o Elementor
            $is_built_with_elementor = get_post_meta(get_the_ID(), '_elementor_edit_mode', true) === 'builder';
            
            // Checa se estamos na tela de edição/preview do Elementor
            $is_elementor_preview = isset($_GET['elementor-preview']);
            
            // Se o Elementor estiver ativo para esta página, abortamos a nossa lógica
            if ($is_built_with_elementor || $is_elementor_preview) {
                return $template;
            }
        }
        
        if (is_singular('espetaculo')) {
            // Verificar se há um template de página personalizado definido
            $page_template = get_post_meta(get_the_ID(), '_wp_page_template', true);

            if ($page_template && $page_template !== 'default') {
                $custom_template = locate_template(array(
                    $page_template
                ));
                if ($custom_template) {
                    return $custom_template;
                }
            }

            // Verificar se o tema tem um template single-espetaculo.php
            $theme_template = locate_template(array(
                'single-espetaculo.php'
            ));

            if ($theme_template) {
                return $theme_template;
            }

            // Usar template padrão de página
            $page_template_file = locate_template(array(
                'page.php',
                'singular.php',
                'index.php'
            ));
            if ($page_template_file) {
                return $page_template_file;
            }
        }

        if (is_post_type_archive('espetaculo') || is_tax('espetaculo_categoria')) {
            // Usar templates do tema para archives
            $templates = array();

            if (is_tax('espetaculo_categoria')) {
                $term = get_queried_object();
                $templates[] = "taxonomy-espetaculo_categoria-{$term->slug}.php";
                $templates[] = 'taxonomy-espetaculo_categoria.php';
            }

            $templates[] = 'archive-espetaculo.php';
            $templates[] = 'archive.php';
            $templates[] = 'index.php';

            $theme_template = locate_template($templates);

            if ($theme_template) {
                return $theme_template;
            }
        }

        return $template;
    }

    /**
     * Filtra o conteúdo do espetáculo para exibir galeria ao final.
     */
    public function espetaculo_content_filter($content)
    {
        if (! is_singular('espetaculo') || ! in_the_loop() || ! is_main_query()) {
            return $content;
        }

        global $post;

        // Verificar se está usando Elementor
        if (class_exists('\Elementor\Plugin')) {
            $document = \Elementor\Plugin::$instance->documents->get($post->ID);
            if ($document && $document->is_built_with_elementor()) {
                return $content; // Deixar o Elementor gerenciar o conteúdo
            }
        }

        // Obter a temporada ativa ou mais recente
        $temporada = $this->get_active_temporada($post->ID);

        // Se houver temporada com conteúdo, usar o conteúdo da temporada
        if ($temporada && ! empty($temporada->post_content)) {
            $content = apply_filters('the_content', $temporada->post_content);
        }

        // Verificar se a galeria está ativada
        $exibir_galeria = get_post_meta($post->ID, '_espetaculo_exibir_galeria', true);

        // Padrão é exibir (se campo não existe ou está vazio, exibe)
        if ($exibir_galeria === '' || $exibir_galeria === '1' || $exibir_galeria === 'sim') {
            $galeria_ids = get_post_meta($post->ID, '_espetaculo_galeria', true);

            if (! empty($galeria_ids)) {
                $content .= $this->render_galeria($galeria_ids);
            }
        }

        return $content;
    }

    /**
     * Renderiza a galeria de fotos em grid.
     */
    private function render_galeria($galeria_ids)
    {
        $ids = explode(',', $galeria_ids);

        if (empty($ids)) {
            return '';
        }

        // Montar array de imagens para o template
        $imagens = array();
        foreach ($ids as $attachment_id) {
            $attachment_id = (int) trim($attachment_id);
            if (empty($attachment_id))
                continue;

            $url_thumb = wp_get_attachment_image_url($attachment_id, 'medium');
            $url_full = wp_get_attachment_image_url($attachment_id, 'full');
            $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);

            if (! $url_thumb)
                continue;

            $imagens[] = array(
                'url_thumb' => $url_thumb,
                'url_full' => $url_full,
                'alt' => $alt
            );
        }

        if (empty($imagens)) {
            return '';
        }

        ob_start();
        include CANNAL_ESPETACULOS_PLUGIN_DIR . 'templates/public/galeria-fotos.php';
        return ob_get_clean();
    }

    /**
     * Obtém temporadas de um espetáculo por status.
     */
    public static function get_temporadas_by_status($espetaculo_id, $status = 'em_cartaz')
    {
        $hoje = current_time('Y-m-d');
        $meta_query = array(
            array(
                'key' => '_temporada_espetaculo_id',
                'value' => $espetaculo_id,
                'compare' => '='
            )
        );

        switch ($status) {
            case 'em_cartaz':
                $meta_query[] = array(
                    'key' => '_temporada_data_inicio',
                    'value' => $hoje,
                    'compare' => '<=',
                    'type' => 'DATE'
                );
                $meta_query[] = array(
                    'key' => '_temporada_data_fim',
                    'value' => $hoje,
                    'compare' => '>=',
                    'type' => 'DATE'
                );
                break;

            case 'futuras':
                $meta_query[] = array(
                    'key' => '_temporada_data_inicio',
                    'value' => $hoje,
                    'compare' => '>',
                    'type' => 'DATE'
                );
                break;

            case 'encerradas':
                $meta_query[] = array(
                    'key' => '_temporada_data_fim',
                    'value' => $hoje,
                    'compare' => '<',
                    'type' => 'DATE'
                );
                break;
        }

        return get_posts(array(
            'post_type' => 'temporada',
            'posts_per_page' => - 1,
            'meta_query' => $meta_query,
            'orderby' => 'meta_value',
            'meta_key' => '_temporada_data_inicio',
            'order' => $status === 'encerradas' ? 'DESC' : 'ASC'
        ));
    }

    /**
     * Obtém a temporada ativa de um espetáculo (instancia)
     */
    private function get_active_temporada($espetaculo_id)
    {
        return self::get_active_temporada_static($espetaculo_id);
    }

    /**
     * Obtém a temporada ativa de um espetáculo (estático — uso pelos widgets)
     */
    public static function get_active_temporada_static($espetaculo_id)
    {
        $hoje = current_time('Y-m-d');

        $temporadas = get_posts(array(
            'post_type' => 'temporada',
            'posts_per_page' => 1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_temporada_espetaculo_id',
                    'value' => $espetaculo_id,
                    'compare' => '='
                ),
                array(
                    'key' => '_temporada_data_inicio',
                    'value' => $hoje,
                    'compare' => '<=',
                    'type' => 'DATE'
                ),
                array(
                    'key' => '_temporada_data_fim',
                    'value' => $hoje,
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            ),
            'orderby' => 'meta_value',
            'meta_key' => '_temporada_data_inicio',
            'order' => 'DESC'
        ));

        if (! empty($temporadas)) {
            return $temporadas[0];
        }
    }

    /**
     * Obtém as próximas temporadas (instância).
     */
    private function get_proximas_temporadas($espetaculo_id, $limit = 3)
    {
        return self::get_proximas_temporadas_static($espetaculo_id, $limit);
    }

    /**
     * Obtém as próximas temporadas (estático — uso pelos widgets).
     */
    public static function get_proximas_temporadas_static($espetaculo_id, $limit = 3)
    {
        $hoje = current_time('Y-m-d');

        return get_posts(array(
            'post_type' => 'temporada',
            'posts_per_page' => $limit,
            'meta_query' => array(
                array(
                    'key' => '_temporada_espetaculo_id',
                    'value' => $espetaculo_id,
                    'compare' => '='
                ),
                array(
                    'key' => '_temporada_data_inicio',
                    'value' => $hoje,
                    'compare' => '>',
                    'type' => 'DATE'
                )
            ),
            'orderby' => 'meta_value',
            'meta_key' => '_temporada_data_inicio',
            'order' => 'ASC'
        ));
    }

    /**
     * Obtém as últimas temporadas (instância).
     */
    private function get_ultimas_temporadas($espetaculo_id, $limit = 3)
    {
        return self::get_ultimas_temporadas_static($espetaculo_id, $limit);
    }

    /**
     * Obtém as últimas temporadas (estático — uso pelos widgets).
     */
    public static function get_ultimas_temporadas_static($espetaculo_id, $limit = 3)
    {
        $hoje = current_time('Y-m-d');

        return get_posts(array(
            'post_type' => 'temporada',
            'posts_per_page' => $limit,
            'meta_query' => array(
                array(
                    'key' => '_temporada_espetaculo_id',
                    'value' => $espetaculo_id,
                    'compare' => '='
                ),
                array(
                    'key' => '_temporada_data_fim',
                    'value' => $hoje,
                    'compare' => '<',
                    'type' => 'DATE'
                )
            ),
            'orderby' => 'meta_value',
            'meta_key' => '_temporada_data_fim',
            'order' => 'DESC'
        ));
    }
}
