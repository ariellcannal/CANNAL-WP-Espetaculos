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
        if (class_exists('OT_Loader')) {
            $espetaculo_detalhes = array(
                'id' => 'espetaculo_detalhes',
                'title' => esc_html__('Detalhes do Espetáculo', 'cannal-espetaculos'),
                'pages' => array(
                    'espetaculo'
                ),
                'context' => 'normal',
                'priority' => 'high',
                'fields' => array(
                    array(
                        'type' => 'tab',
                        'id' => 'cannal-espetaculos-detalhes',
                        'label' => esc_html__('Detalhes', 'cannal-espetaculos')
                    ),
                    array(
                        'type' => 'text',
                        'id' => '_espetaculo_autor',
                        'label' => esc_html__('Autor', 'cannal-espetaculos')
                    ),
                    array(
                        'type' => 'text',
                        'id' => '_espetaculo_diretor',
                        'label' => esc_html__('Diretor', 'cannal-espetaculos')
                    ),
                    array(
                        'type' => 'text',
                        'id' => '_espetaculo_elenco',
                        'label' => esc_html__('Elenco', 'cannal-espetaculos'),
                        'desc' => esc_html__('Nomes separados por vírgula', 'cannal-espetaculos')
                    ),
                    array(
                        'type' => 'textarea-simple',
                        'id' => '_espetaculo_sinopse',
                        'label' => esc_html__('Sinopse', 'cannal-espetaculos'),
                        'rows' => 3
                    ),
                    array(
                        'type' => 'number',
                        'id' => '_espetaculo_ano_estreia',
                        'label' => esc_html__('Ano de Estreia', 'cannal-espetaculos'),
                        'desc' => esc_html__('O ano em que o espetáculo estreou', 'cannal-espetaculos')
                    ),
                    array(
                        'type' => 'number',
                        'id' => '_espetaculo_duracao',
                        'label' => esc_html__('Duração', 'cannal-espetaculos'),
                        'desc' => esc_html__('Duração do espetáculo em minutos', 'cannal-espetaculos')
                    ),
                    array(
                        'type' => 'select',
                        'id' => '_espetaculo_classificacao',
                        'label' => esc_html__('Classificação Indicativa', 'cannal-espetaculos'),
                        'std' => 'default',
                        'choices' => array(
                            array(
                                'label' => esc_html__('Livre', 'cannal-espetaculos'),
                                'value' => 'livre'
                            ),
                            array(
                                'label' => esc_html__('12 anos', 'cannal-espetaculos'),
                                'value' => '12'
                            ),
                            array(
                                'label' => esc_html__('14 anos', 'cannal-espetaculos'),
                                'value' => '14'
                            ),
                            array(
                                'label' => esc_html__('16 anos', 'cannal-espetaculos'),
                                'value' => '16 anos'
                            ),
                            array(
                                'label' => esc_html__('18 anos', 'cannal-espetaculos'),
                                'value' => '18'
                            )
                        )
                    ),
                    array(
                        'type' => 'tab',
                        'id' => 'cannal-espetaculos-midia',
                        'label' => esc_html__('Mídia', 'cannal-espetaculos')
                    ),
                    array(
                        'type' => 'upload',
                        'id' => '_espetaculo_logotipo',
                        'label' => esc_html__('Logotipo do Espetáculo', 'cannal-espetaculos'),
                        'desc' => esc_html__('Logotipo do espetáculo', 'cannal-espetaculos')
                    ),
                    array(
                        'type' => 'upload',
                        'id' => '_espetaculo_logotipo_preto',
                        'label' => esc_html__('Logotipo do Espetáculo', 'cannal-espetaculos'),
                        'desc' => esc_html__('Logotipo para fundos claros', 'cannal-espetaculos')
                    ),
                    array(
                        'type' => 'upload',
                        'id' => '_espetaculo_logotipo_branco',
                        'label' => esc_html__('Logotipo do Espetáculo', 'cannal-espetaculos'),
                        'desc' => esc_html__('Logotipo para fundos escuros', 'cannal-espetaculos')
                    ),
                    array(
                        'type' => 'upload',
                        'id' => '_espetaculo_icone',
                        'label' => esc_html__('Ícone do Espetáculo', 'cannal-espetaculos'),
                        'desc' => esc_html__('Ícone da página do espetáculo (favicon). Obrigatoriamente quadrado, máximo 512×512px. Formatos aceitos: PNG, ICO, SVG.', 'cannal-espetaculos')
                    ),
                    array(
                        'type' => 'on_off',
                        'id' => '_espetaculo_exibir_galeria',
                        'label' => esc_html__('Exibir galeria', 'cannal-espetaculos'),
                        'desc' => esc_html__('Exibir galeria de fotos ao final da página', 'cannal-espetaculos')
                    ),
                    array(
                        'type' => 'gallery',
                        'id' => '_espetaculo_galeria',
                        'label' => esc_html__('Galeria de Imagens', 'cannal-espetaculos')
                    ),
                    array(
                        'type' => 'tab',
                        'id' => 'cannal-espetaculos-temporada',
                        'label' => esc_html__('Temporadas', 'cannal-espetaculos')
                    ),
                    array(
                        'type' => 'custom_box',
                        'id' => '_espetaculo_temporadas',
                        'label' => esc_html__('Temporadas do Espetáculo', 'cannal-espetaculos'),
                        'callback' => array(
                            $this,
                            'render_espetaculo_temporadas_meta_box'
                        )
                    )
                )
            );
            ot_register_meta_box($espetaculo_detalhes);
        }
    }

    /**
     * Renderiza o meta box de temporadas do espetáculo.
     */
    public function render_espetaculo_temporadas_meta_box($args = array())
    {
        extract($args); // phpcs:ignore
        
        $post = get_post($post_id);
        
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

        if (isset($_POST['espetaculo_sinopse'])) {
            $post_data['post_excerpt'] = sanitize_textarea_field($_POST['espetaculo_sinopse']);
        }
        
        if (count($post_data)) {
            $post_data['ID'] = $post_id;
            wp_update_post($post_data);
        }
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
