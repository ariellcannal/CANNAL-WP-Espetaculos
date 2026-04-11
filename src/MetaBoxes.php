<?php

/**
 * Gerencia os meta boxes e campos personalizados.
 *
 * @package    CANNALEspetaculos_Plugin
 * @subpackage CANNALEspetaculos_Plugin/includes
 */
class CANNALEspetaculos_MetaBoxes
{

    /**
     * Adiciona os meta boxes.
     */
    public function add_meta_boxes()
    {

        // Meta boxes para Espetáculos
        add_meta_box('espetaculo_detalhes', 'Detalhes do Espetáculo', array(
            $this,
            'render_espetaculo_detalhes_meta_box'
        ), 'espetaculo', 'normal', 'high');

        add_meta_box('espetaculo_galeria', 'Galeria de Fotos', array(
            $this,
            'render_espetaculo_galeria_meta_box'
        ), 'espetaculo', 'normal', 'default');

        add_meta_box('espetaculo_temporadas', 'Temporadas', array(
            $this,
            'render_espetaculo_temporadas_meta_box'
        ), 'espetaculo', 'normal', 'default');
    }

    /**
     * Renderiza o meta box de detalhes do espetáculo.
     */
    public function render_espetaculo_detalhes_meta_box($post)
    {
        wp_nonce_field('cannal_espetaculo_meta_box', 'cannal_espetaculo_meta_box_nonce');

        $autor = get_post_meta($post->ID, '_espetaculo_autor', true);
        $diretor = get_post_meta($post->ID, '_espetaculo_diretor', true);
        $elenco = get_post_meta($post->ID, '_espetaculo_elenco', true);
        $sinopse = get_post_meta($post->ID, '_espetaculo_sinopse', true);
        $ano_estreia = get_post_meta($post->ID, '_espetaculo_ano_estreia', true);
        $duracao = get_post_meta($post->ID, '_espetaculo_duracao', true);
        $classificacao = get_post_meta($post->ID, '_espetaculo_classificacao', true);
        $logotipo_id = get_post_meta($post->ID, '_espetaculo_logotipo', true);
        $logotipo_url = $logotipo_id ? wp_get_attachment_image_url($logotipo_id, 'medium') : '';
        $icone_id = (int) get_post_meta($post->ID, '_espetaculo_icone', true);
        $icone_url = $icone_id ? wp_get_attachment_image_url($icone_id, 'thumbnail') : '';
        $exibir_galeria = get_post_meta($post->ID, '_espetaculo_exibir_galeria', true);

        $template = dirname(dirname(__FILE__)) . '/templates/admin/metabox-detalhes-espetaculo.php';
        if (file_exists($template)) {
            include $template;
        }
    }

    /**
     * Renderiza o meta box de galeria do espetáculo.
     */
    public function render_espetaculo_galeria_meta_box($post)
    {
        wp_nonce_field('cannal_espetaculo_galeria_meta_box', 'cannal_espetaculo_galeria_meta_box_nonce');

        $galeria = get_post_meta($post->ID, '_espetaculo_galeria', true);
        $galeria_ids = ! empty($galeria) ? explode(',', $galeria) : array();

        $template = dirname(dirname(__FILE__)) . '/templates/admin/metabox-galeria-espetaculo.php';
        if (file_exists($template)) {
            include $template;
        }
    }

    /**
     * Renderiza o meta box de temporadas do espetáculo.
     */
    public function render_espetaculo_temporadas_meta_box($post)
    {
        $temporadas_raw = get_posts(array(
            'post_type' => 'temporada',
            'posts_per_page' => - 1,
            'meta_key' => '_temporada_espetaculo_id',
            'meta_value' => $post->ID,
            'orderby' => 'meta_value',
            'order' => 'DESC'
        ));

        $temporadas = array();

        foreach ($temporadas_raw as $t) {
            $data_inicio = get_post_meta($t->ID, '_temporada_data_inicio', true);
            $data_fim = get_post_meta($t->ID, '_temporada_data_fim', true);
            $tipo_sessao = get_post_meta($t->ID, '_temporada_tipo_sessao', true);
            $sessoes_data = get_post_meta($t->ID, '_temporada_sessoes_data', true);

            // Calcular status
            $status_label = CANNALEspetaculos_DiasHorarios::get_status_temporada($t);

            // Formatar período
            $periodo = '';
            if ($data_inicio) {
                $periodo = date_i18n('d/m/Y', strtotime($data_inicio));
                if ($data_fim) {
                    $periodo .= ' – ' . date_i18n('d/m/Y', strtotime($data_fim));
                }
            }

            // Gerar dias e horários
            $dias_horarios = '';
            if (class_exists('CANNALEspetaculos_DiasHorarios') && ! empty($sessoes_data)) {
                $dias_horarios = CANNALEspetaculos_DiasHorarios::gerar($tipo_sessao, $sessoes_data);
            }

            // Adicionar propriedades ao objeto para o template
            $t->teatro = get_post_meta($t->ID, '_temporada_teatro_nome', true);
            $t->data_inicio = $data_inicio;
            $t->data_fim = $data_fim;
            $t->dias_horarios = $dias_horarios;
            $t->status_label = $status_label;
            $t->periodo = $periodo;

            $temporadas[] = $t;
        }

        // Usar template (MetaBoxes.php está em src/, então sobe um nível para a raiz do plugin)
        $template = dirname(dirname(__FILE__)) . '/templates/admin/lista-temporadas.php';
        if (file_exists($template)) {
            include $template;
        }

        // Registrar modal no footer
        add_action('admin_footer', function () use ($post) {
            $this->render_temporada_modal($post->ID);
        });
    }

    /**
     * Renderiza o modal de temporadas no admin footer.
     */
    public function render_temporada_modal($espetaculo_id)
    {
        // Verificar se estamos na tela de edição de espetáculo
        $screen = get_current_screen();
        if (! $screen || $screen->post_type !== 'espetaculo' || $screen->base !== 'post') {
            return;
        }

        $dias_semana = array(
            'segunda' => 'Segunda-feira',
            'terca' => 'Terça-feira',
            'quarta' => 'Quarta-feira',
            'quinta' => 'Quinta-feira',
            'sexta' => 'Sexta-feira',
            'sabado' => 'Sábado',
            'domingo' => 'Domingo'
        );

        $template = dirname(dirname(__FILE__)) . '/templates/admin/modal-temporada.php';
        if (file_exists($template)) {
            include $template;
        }
    }

    /**
     * Salva os meta dados do espetáculo.
     */
    public function save_espetaculo_meta($post_id)
    {

        // Verificar se pelo menos um dos nonces está presente
        $has_detalhes_nonce = isset($_POST['cannal_espetaculo_meta_box_nonce']);
        $has_galeria_nonce = isset($_POST['cannal_espetaculo_galeria_meta_box_nonce']);

        if (! $has_detalhes_nonce && ! $has_galeria_nonce) {
            return;
        }

        // Verificar nonces
        $detalhes_valid = $has_detalhes_nonce && wp_verify_nonce($_POST['cannal_espetaculo_meta_box_nonce'], 'cannal_espetaculo_meta_box');
        $galeria_valid = $has_galeria_nonce && wp_verify_nonce($_POST['cannal_espetaculo_galeria_meta_box_nonce'], 'cannal_espetaculo_galeria_meta_box');

        if (! $detalhes_valid && ! $galeria_valid) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (! current_user_can('edit_post', $post_id)) {
            return;
        }

        if (get_post_type($post_id) !== 'espetaculo') {
            return;
        }

        $post_data = [];

        // Salvar campos
        if (isset($_POST['espetaculo_autor'])) {
            update_post_meta($post_id, '_espetaculo_autor', sanitize_text_field($_POST['espetaculo_autor']));
        }

        if (isset($_POST['espetaculo_diretor'])) {
            update_post_meta($post_id, '_espetaculo_diretor', sanitize_text_field($_POST['espetaculo_diretor']));
        }

        if (isset($_POST['espetaculo_elenco'])) {
            update_post_meta($post_id, '_espetaculo_elenco', sanitize_textarea_field($_POST['espetaculo_elenco']));
        }

        if (isset($_POST['espetaculo_sinopse'])) {
            update_post_meta($post_id, '_espetaculo_sinopse', sanitize_textarea_field($_POST['espetaculo_sinopse']));
            $post_data['post_excerpt'] = sanitize_textarea_field($_POST['espetaculo_sinopse']);
        }

        if (isset($_POST['espetaculo_ano_estreia'])) {
            update_post_meta($post_id, '_espetaculo_ano_estreia', sanitize_text_field($_POST['espetaculo_ano_estreia']));
        }

        if (isset($_POST['espetaculo_duracao'])) {
            update_post_meta($post_id, '_espetaculo_duracao', sanitize_text_field($_POST['espetaculo_duracao']));
        }

        if (isset($_POST['espetaculo_classificacao'])) {
            update_post_meta($post_id, '_espetaculo_classificacao', sanitize_text_field($_POST['espetaculo_classificacao']));
        }

        if (isset($_POST['espetaculo_logotipo'])) {
            update_post_meta($post_id, '_espetaculo_logotipo', sanitize_text_field($_POST['espetaculo_logotipo']));
        }

        // Salvar ícone (favicon da single page)
        if (isset($_POST['espetaculo_icone_id'])) {
            $icone_id = absint($_POST['espetaculo_icone_id']);
            if ($icone_id > 0) {
                // Validar proporção quadrada e tamanho máximo no servidor
                $meta = wp_get_attachment_metadata($icone_id);
                if ($meta && isset($meta['width'], $meta['height'])) {
                    if ($meta['width'] === $meta['height'] && $meta['width'] <= 512) {
                        update_post_meta($post_id, '_espetaculo_icone', $icone_id);
                    }
                } else {
                    // Sem metadados (ex: SVG) — salva assim mesmo
                    update_post_meta($post_id, '_espetaculo_icone', $icone_id);
                }
            } else {
                delete_post_meta($post_id, '_espetaculo_icone');
            }
        }

        // Exibir galeria (checkbox)
        $exibir_galeria = isset($_POST['espetaculo_exibir_galeria']) ? '1' : '0';
        update_post_meta($post_id, '_espetaculo_exibir_galeria', $exibir_galeria);

        if (count($post_data)) {
            $post_data['ID'] = $post_id;
            wp_update_post($post_data);
        }

        // GALERIA: Agora é salva via AJAX, não pelo formulário
        // O salvamento via formulário foi removido para evitar sobrescrever o valor salvo via AJAX
    }

    /**
     * Processa as sessões e gera o campo "Dias e Horários".
     */
    private function process_sessoes($post_id)
    {
        $tipo_sessao = get_post_meta($post_id, '_temporada_tipo_sessao', true);
        $sessoes_data = get_post_meta($post_id, '_temporada_sessoes_data', true);

        if (empty($sessoes_data)) {
            return;
        }

        $sessoes = json_decode($sessoes_data, true);
        $dias_horarios = '';

        if ($tipo_sessao === 'avulsas' && ! empty($sessoes['avulsas'])) {
            $dias_horarios = $this->format_sessoes_avulsas($sessoes['avulsas']);
        } elseif ($tipo_sessao === 'temporada' && ! empty($sessoes['temporada'])) {
            $dias_horarios = $this->format_sessoes_temporada($sessoes['temporada']);
        }

        update_post_meta($post_id, '_temporada_dias_horarios', $dias_horarios);
    }
}
