<?php

/**
 * A funcionalidade específica do admin do plugin.
 *
 * @package    CANNALEspetaculos_Plugin
 * @subpackage CANNALEspetaculos_Plugin/admin
 */
class CANNALEspetaculos_Admin
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
    }

    /**
     * Registra os arquivos de estilo para a área administrativa.
     */
    public function enqueue_styles()
    {
        $screen = get_current_screen();

        if (in_array($screen->post_type, array(
            'espetaculo',
            'temporada'
        ))) {
            wp_enqueue_style($this->plugin_name, CANNAL_ESPETACULOS_PLUGIN_URL . 'assets/css/cannal-espetaculos-admin.css', array(), $this->version, 'all');
        }
    }

    /**
     * Registra os arquivos JavaScript para a área administrativa.
     */
    public function enqueue_scripts()
    {
        $screen = get_current_screen();

        if (in_array($screen->post_type, array(
            'espetaculo',
            'temporada'
        ))) {
            // Enfileirar media library
            wp_enqueue_media();

            // Enfileirar script principal
            wp_enqueue_script($this->plugin_name, CANNAL_ESPETACULOS_PLUGIN_URL . 'assets/js/cannal-espetaculos-admin.js', array(
                'jquery',
                'jquery-ui-sortable',
                'wp-util'
            ), $this->version, true // Carregar no footer
            );

            // Passar variáveis para JavaScript
            wp_localize_script($this->plugin_name, 'cannalAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cannal_temporada_ajax'),
                'espetaculo_nonce' => wp_create_nonce('cannal_espetaculos_nonce')
            ));
        }
    }

    /**
     * AJAX: Obtém o conteúdo de um espetáculo.
     */
    public function ajax_get_espetaculo_content()
    {
        // Permitir sem nonce para compatibilidade, ou verificar se existe
        if (isset($_POST['nonce'])) {
            check_ajax_referer('cannal_espetaculos_nonce', 'nonce');
        }

        $espetaculo_id = isset($_POST['espetaculo_id']) ? intval($_POST['espetaculo_id']) : 0;

        if (! $espetaculo_id) {
            wp_send_json_error(array(
                'message' => 'ID do espetáculo não fornecido.'
            ));
        }

        $espetaculo = get_post($espetaculo_id);

        if (! $espetaculo || $espetaculo->post_type !== 'espetaculo') {
            wp_send_json_error(array(
                'message' => 'Espetáculo não encontrado.'
            ));
        }

        wp_send_json_success(array(
            'content' => $espetaculo->post_content
        ));
    }

    /**
     * AJAX: Salvar temporada.
     */
    public function ajax_save_temporada()
    {
        check_ajax_referer('cannal_temporada_ajax', 'nonce');

        if (! current_user_can('edit_posts')) {
            wp_send_json_error(array(
                'message' => 'Permissão negada.'
            ));
        }

        $temporada_id = isset($_POST['temporada_id']) ? intval($_POST['temporada_id']) : 0;
        $espetaculo_id = isset($_POST['espetaculo_id']) ? intval($_POST['espetaculo_id']) : 0;

        if (! $espetaculo_id) {
            wp_send_json_error(array(
                'message' => 'Espetáculo não especificado.'
            ));
        }

        $teatro_nome = isset($_POST['teatro_nome']) ? sanitize_text_field($_POST['teatro_nome']) : '';
        $espetaculo = get_post($espetaculo_id);
        $titulo = $teatro_nome . ' - ' . $espetaculo->post_title;

        // Criar ou atualizar temporada
        $post_data = array(
            'post_type' => 'temporada',
            'post_title' => $titulo,
            'post_content' => isset($_POST['conteudo']) ? wp_kses_post($_POST['conteudo']) : '',
            'post_status' => 'publish'
        );

        if ($temporada_id) {
            $post_data['ID'] = $temporada_id;
            $result = wp_update_post($post_data);
        } else {
            $result = wp_insert_post($post_data);
        }

        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => 'Erro ao salvar temporada.'
            ));
        }

        $temporada_id = $temporada_id ? $temporada_id : $result;

        // Salvar meta dados
        update_post_meta($temporada_id, '_temporada_espetaculo_id', $espetaculo_id);
        update_post_meta($temporada_id, '_temporada_teatro_nome', isset($_POST['teatro_nome']) ? sanitize_text_field($_POST['teatro_nome']) : '');
        update_post_meta($temporada_id, '_temporada_teatro_endereco', isset($_POST['teatro_endereco']) ? sanitize_text_field($_POST['teatro_endereco']) : '');
        update_post_meta($temporada_id, '_temporada_elenco', isset($_POST['elenco']) ? sanitize_textarea_field($_POST['elenco']) : '');
        // _temporada_data_inicio e _temporada_data_fim são salvos abaixo com lógica de avulsas
        update_post_meta($temporada_id, '_temporada_valores', isset($_POST['valores']) ? sanitize_textarea_field($_POST['valores']) : '');
        update_post_meta($temporada_id, '_temporada_link_vendas', isset($_POST['link_vendas']) ? esc_url_raw($_POST['link_vendas']) : '');
        update_post_meta($temporada_id, '_temporada_banner_destaque1', isset($_POST['banner_destaque1']) ? sanitize_text_field($_POST['banner_destaque1']) : '');
        update_post_meta($temporada_id, '_temporada_banner_destaque2', isset($_POST['banner_destaque2']) ? sanitize_text_field($_POST['banner_destaque2']) : '');
        update_post_meta($temporada_id, '_temporada_banner_data', isset($_POST['banner_data']) ? sanitize_text_field($_POST['banner_data']) : '');
        $tipo_sessao_input = isset($_POST['tipo_sessao']) ? sanitize_text_field($_POST['tipo_sessao']) : 'avulsas';
        // Usar wp_unslash para preservar o JSON intacto (sanitize_textarea_field corrompe aspas e chaves)
        $sessoes_data_raw = isset($_POST['sessoes_data']) ? wp_unslash($_POST['sessoes_data']) : '';

        update_post_meta($temporada_id, '_temporada_tipo_sessao', $tipo_sessao_input);
        update_post_meta($temporada_id, '_temporada_sessoes_data', $sessoes_data_raw);

        // Se tipo avulsas: preencher data_inicio/fim automaticamente com primeira e última sessão
        $data_inicio_post = isset($_POST['data_inicio']) ? sanitize_text_field($_POST['data_inicio']) : '';
        $data_fim_post = isset($_POST['data_fim']) ? sanitize_text_field($_POST['data_fim']) : '';

        if ($tipo_sessao_input === 'avulsas' && ! empty($sessoes_data_raw)) {
            $sessoes_obj = json_decode($sessoes_data_raw, true);
            if (! empty($sessoes_obj['avulsas']) && is_array($sessoes_obj['avulsas'])) {
                $datas = array_filter(array_column($sessoes_obj['avulsas'], 'data'));
                if (! empty($datas)) {
                    sort($datas);
                    $data_inicio_post = reset($datas);
                    $data_fim_post = end($datas);
                }
            }
        }

        update_post_meta($temporada_id, '_temporada_data_inicio', $data_inicio_post);
        update_post_meta($temporada_id, '_temporada_data_fim', $data_fim_post);

        // Calcular status da temporada para retornar ao JS
        $hoje = current_time('Y-m-d');
        $data_inicio = get_post_meta($temporada_id, '_temporada_data_inicio', true);
        $data_fim = get_post_meta($temporada_id, '_temporada_data_fim', true);
        $banner_data = get_post_meta($temporada_id, '_temporada_banner_data', true);
        $tipo_sessao = get_post_meta($temporada_id, '_temporada_tipo_sessao', true);
        $sessoes_raw = get_post_meta($temporada_id, '_temporada_sessoes_data', true);

        $status_label = CANNALEspetaculos_DiasHorarios::get_status_temporada($temporada_id);

        $periodo = CANNALEspetaculos_DiasHorarios::get_periodo_temporada($data_inicio, $data_fim);

        $dias_horarios = CANNALEspetaculos_DiasHorarios::gerar($tipo_sessao, $sessoes_raw);
        
        wp_send_json_success(array(
            'message' => 'Temporada salva com sucesso!',
            'temporada_id' => $temporada_id,
            'is_new' => isset($_POST['temporada_id']) && intval($_POST['temporada_id']) === 0,
            'teatro' => get_post_meta($temporada_id, '_temporada_teatro_nome', true),
            'periodo' => $periodo,
            'dias_horarios' => $dias_horarios,
            'status_label' => $status_label
        ));
    }

    /**
     * AJAX: Obter dados de uma temporada.
     */
    public function ajax_get_temporada()
    {
        check_ajax_referer('cannal_temporada_ajax', 'nonce');

        $temporada_id = isset($_POST['temporada_id']) ? intval($_POST['temporada_id']) : 0;

        if (! $temporada_id) {
            wp_send_json_error(array(
                'message' => 'ID da temporada não fornecido.'
            ));
        }

        $temporada = get_post($temporada_id);

        $espetaculo_id = get_post_meta($temporada_id, '_temporada_espetaculo_id', true);
        $espetaculo = get_post($espetaculo_id);

        if (! $temporada || $temporada->post_type !== 'temporada') {
            wp_send_json_error(array(
                'message' => 'Temporada não encontrada.'
            ));
        }

        $data = array(
            'espetaculo_nome' => $espetaculo->post_title,
            'teatro_nome' => get_post_meta($temporada_id, '_temporada_teatro_nome', true),
            'teatro_endereco' => get_post_meta($temporada_id, '_temporada_teatro_endereco', true),
            'elenco' => get_post_meta($temporada_id, '_temporada_elenco', true),
            'banner_destaque1' => get_post_meta($temporada_id, '_temporada_banner_destaque1', true),
            'banner_destaque2' => get_post_meta($temporada_id, '_temporada_banner_destaque2', true),
            'data_inicio' => get_post_meta($temporada_id, '_temporada_data_inicio', true),
            'data_fim' => get_post_meta($temporada_id, '_temporada_data_fim', true),
            'valores' => get_post_meta($temporada_id, '_temporada_valores', true),
            'link_vendas' => get_post_meta($temporada_id, '_temporada_link_vendas', true),
            'link_texto' => get_post_meta($temporada_id, '_temporada_link_texto', true),
            'banner_data' => get_post_meta($temporada_id, '_temporada_banner_data', true),
            'conteudo' => $temporada->post_content,
            'tipo_sessao' => get_post_meta($temporada_id, '_temporada_tipo_sessao', true),
            'sessoes_data' => get_post_meta($temporada_id, '_temporada_sessoes_data', true)
        );

        wp_send_json_success($data);
    }

    /**
     * AJAX: Salvar galeria.
     */
    public function ajax_save_galeria()
    {
        check_ajax_referer('cannal_espetaculos_nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $galeria_ids = isset($_POST['galeria_ids']) ? sanitize_text_field($_POST['galeria_ids']) : '';

        if (! $post_id) {
            wp_send_json_error(array(
                'message' => 'ID do post inválido'
            ));
        }

        // Salvar galeria
        $result = update_post_meta($post_id, '_espetaculo_galeria', $galeria_ids);

        error_log('[CANNAL AJAX GALERIA] Post ID: ' . $post_id);
        error_log('[CANNAL AJAX GALERIA] Galeria IDs recebidos: ' . $galeria_ids);
        error_log('[CANNAL AJAX GALERIA] Update result: ' . ($result ? 'SUCESSO' : 'FALHOU'));

        wp_send_json_success(array(
            'message' => 'Galeria salva com sucesso',
            'galeria_ids' => $galeria_ids,
            'result' => $result
        ));
    }

    /**
     * AJAX: Excluir temporada.
     */
    public function ajax_delete_temporada()
    {
        check_ajax_referer('cannal_temporada_ajax', 'nonce');

        if (! current_user_can('delete_posts')) {
            wp_send_json_error(array(
                'message' => 'Permissão negada.'
            ));
        }

        $temporada_id = isset($_POST['temporada_id']) ? intval($_POST['temporada_id']) : 0;

        if (! $temporada_id) {
            wp_send_json_error(array(
                'message' => 'ID da temporada não fornecido.'
            ));
        }

        $result = wp_delete_post($temporada_id, true);

        if (! $result) {
            wp_send_json_error(array(
                'message' => 'Erro ao excluir temporada.'
            ));
        }

        wp_send_json_success(array(
            'message' => 'Temporada excluída com sucesso!'
        ));
    }
}
