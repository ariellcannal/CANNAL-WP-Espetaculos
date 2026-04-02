<?php
/**
 * Widget: CANNAL - Dados do Espetáculo
 *
 * Exibe os dados do espetáculo na sidebar das páginas single-espetaculo.
 * Lógica:
 *  - Se há temporada ativa: exibe Teatro, Dias e Horários + bloco de dados do espetáculo.
 *  - Se há temporadas futuras: exibe bloco de dados + Widget Próximas Apresentações.
 *  - Caso contrário: exibe bloco de dados + Widget Últimas Apresentações.
 *
 * @package    CANNALEspetaculos_Plugin
 * @subpackage CANNALEspetaculos_Plugin/Widgets
 */
if (! defined('ABSPATH'))
    exit();

class CANNALEspetaculos_WidgetDados extends WP_Widget
{

    public function __construct()
    {
        parent::__construct('cannal_dados_espetaculo', __('CANNAL - Dados do Espetáculo', 'cannal-espetaculos'), array(
            'description' => __('Exibe os dados do espetáculo. Funciona apenas em páginas single-espetaculo.', 'cannal-espetaculos'),
            'classname' => 'cannal-widget-dados-espetaculo'
        ));
    }

    /**
     * Formulário de configuração no admin.
     */
    public function form($instance)
    {
        $titulo_opcao = ! empty($instance['titulo_opcao']) ? $instance['titulo_opcao'] : 'informacoes';
        $titulo_custom = ! empty($instance['titulo_custom']) ? $instance['titulo_custom'] : '';
        ?>
<p>
	<label for="<?php echo esc_attr( $this->get_field_id( 'titulo_opcao' ) ); ?>">
                <?php esc_html_e( 'Título do Widget:', 'cannal-espetaculos' ); ?>
            </label> <select id="<?php echo esc_attr( $this->get_field_id( 'titulo_opcao' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'titulo_opcao' ) ); ?>" class="widefat cannal-titulo-opcao-select">
		<option value="informacoes" <?php selected( $titulo_opcao, 'servico' ); ?>>
                    <?php esc_html_e( 'Serviço (padrão)', 'cannal-espetaculos' ); ?>
                </option>
		<option value="nome_espetaculo" <?php selected( $titulo_opcao, 'nome_espetaculo' ); ?>>
                    <?php esc_html_e( 'Nome do Espetáculo', 'cannal-espetaculos' ); ?>
                </option>
		<option value="custom" <?php selected( $titulo_opcao, 'custom' ); ?>>
                    <?php esc_html_e( 'Personalizado', 'cannal-espetaculos' ); ?>
                </option>
		<option value="sem_titulo" <?php selected( $titulo_opcao, 'sem_titulo' ); ?>>
                    <?php esc_html_e( 'Sem título', 'cannal-espetaculos' ); ?>
                </option>
	</select>
</p>
<p class="cannal-titulo-custom-wrap" <?php echo $titulo_opcao !== 'custom' ? 'style="display:none;"' : ''; ?>>
	<label for="<?php echo esc_attr( $this->get_field_id( 'titulo_custom' ) ); ?>">
                <?php esc_html_e( 'Título personalizado:', 'cannal-espetaculos' ); ?>
            </label> <input type="text" id="<?php echo esc_attr( $this->get_field_id( 'titulo_custom' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'titulo_custom' ) ); ?>" value="<?php echo esc_attr( $titulo_custom ); ?>" class="widefat" />
</p>
<script>
        (function($){
            $(document).on('change', '.cannal-titulo-opcao-select', function(){
                var wrap = $(this).closest('.widget-content, .widget-inside').find('.cannal-titulo-custom-wrap');
                if ($(this).val() === 'custom') {
                    wrap.show();
                } else {
                    wrap.hide();
                }
            });
        })(jQuery);
        </script>
<?php
    }

    /**
     * Sanitiza as opções ao salvar.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['titulo_opcao'] = sanitize_text_field($new_instance['titulo_opcao']);
        $instance['titulo_custom'] = sanitize_text_field($new_instance['titulo_custom']);
        return $instance;
    }

    /**
     * Renderiza o widget no front-end.
     */
    public function widget($args, $instance)
    {
        if (! is_singular('espetaculo')) {
            return;
        }

        global $post;
        $espetaculo_id = $post->ID;

        // --- Dados do espetáculo ---
        $autor = get_post_meta($espetaculo_id, '_espetaculo_autor', true);
        $diretor = get_post_meta($espetaculo_id, '_espetaculo_diretor', true);
        $elenco = get_post_meta($espetaculo_id, '_espetaculo_elenco', true);
        $duracao = get_post_meta($espetaculo_id, '_espetaculo_duracao', true);
        $ano_estreia = get_post_meta($espetaculo_id, '_espetaculo_ano_estreia', true);
        $classificacao = get_post_meta($espetaculo_id, '_espetaculo_classificacao', true);
        $classificacao_text = '';
        if ($classificacao) {
            $classificacao_text = strtolower($classificacao) === 'livre' ? 'L' : $classificacao;
        }

        // --- Temporada ativa ---
        $temporada = CANNALEspetaculos_Public::get_active_temporada_static($espetaculo_id);

        // --- Dados da temporada (com fallback para metas do espetáculo) ---
        $teatro_nome = '';
        $teatro_endereco = '';
        $dias_horarios = '';
        $tipo_sessao = '';
        $valores = '';
        $link_vendas = '';
        $link_texto = '';

        // Se não há temporada ativa, tenta usar dados da última ou próxima temporada como fallback
        if (! $temporada) {
            // Tenta próximas primeiro, depois últimas
            $proximas_temp = CANNALEspetaculos_Public::get_proximas_temporadas_static($espetaculo_id, 1);
            if (! empty($proximas_temp)) {
                $temporada_fallback = $proximas_temp[0];
            } else {
                $ultimas_temp = CANNALEspetaculos_Public::get_ultimas_temporadas_static($espetaculo_id, 1);
                $temporada_fallback = ! empty($ultimas_temp) ? $ultimas_temp[0] : null;
            }

            if ($temporada_fallback) {
                $teatro_nome     = get_post_meta($temporada_fallback->ID, '_temporada_teatro_nome', true);
                $teatro_endereco = get_post_meta($temporada_fallback->ID, '_temporada_teatro_endereco', true);
                $valores         = get_post_meta($temporada_fallback->ID, '_temporada_valores', true);
                $link_vendas     = get_post_meta($temporada_fallback->ID, '_temporada_link_vendas', true);
                $link_texto      = get_post_meta($temporada_fallback->ID, '_temporada_link_texto', true);
                $tipo_sessao     = get_post_meta($temporada_fallback->ID, '_temporada_tipo_sessao', true);
                $sessoes_data    = get_post_meta($temporada_fallback->ID, '_temporada_sessoes_data', true);
                $sessoes         = ! empty($sessoes_data) ? json_decode($sessoes_data, true) : null;
                $dias_horarios   = CANNALEspetaculos_DiasHorarios::format_dias_horarios_legivel($sessoes);

                $diretor_temp = get_post_meta($temporada_fallback->ID, '_temporada_diretor', true);
                $elenco_temp  = get_post_meta($temporada_fallback->ID, '_temporada_elenco', true);
                if ($diretor_temp) $diretor = $diretor_temp;
                if ($elenco_temp)  $elenco  = $elenco_temp;
            }
        }

        if ($temporada) {
            $teatro_nome = get_post_meta($temporada->ID, '_temporada_teatro_nome', true);
            $teatro_endereco = get_post_meta($temporada->ID, '_temporada_teatro_endereco', true);
            $valores = get_post_meta($temporada->ID, '_temporada_valores', true);
            $link_vendas = get_post_meta($temporada->ID, '_temporada_link_vendas', true);
            $link_texto = get_post_meta($temporada->ID, '_temporada_link_texto', true);
            $tipo_sessao = get_post_meta($temporada->ID, '_temporada_tipo_sessao', true);
            $sessoes_data = get_post_meta($temporada->ID, '_temporada_sessoes_data', true);
            $sessoes = ! empty($sessoes_data) ? json_decode($sessoes_data, true) : null;
            $dias_horarios = CANNALEspetaculos_DiasHorarios::format_dias_horarios_legivel($sessoes);

            // Fallback: diretor e elenco da temporada sobrescrevem os do espetáculo
            $diretor_temp = get_post_meta($temporada->ID, '_temporada_diretor', true);
            $elenco_temp = get_post_meta($temporada->ID, '_temporada_elenco', true);
            if ($diretor_temp)
                $diretor = $diretor_temp;
            if ($elenco_temp)
                $elenco = $elenco_temp;
        }

        // --- Título do widget ---
        $titulo_opcao = ! empty($instance['titulo_opcao']) ? $instance['titulo_opcao'] : 'servico';
        $titulo_custom = ! empty($instance['titulo_custom']) ? $instance['titulo_custom'] : '';

        switch ($titulo_opcao) {
            case 'informacoes':
                $titulo = __('Serviço', 'cannal-espetaculos');
                break;
            case 'nome_espetaculo':
                $titulo = get_the_title($espetaculo_id);
                break;
            case 'custom':
                $titulo = $titulo_custom ?: __('Serviço', 'cannal-espetaculos');
                break;
            case 'sem_titulo':
            default:
                $titulo = '';
                break;
        }

        // --- Renderizar Widget de Dados ---
        echo $args['before_widget'];

        $template_vars = compact('titulo', 'espetaculo_id', 'temporada', 'autor', 'diretor', 'elenco', 'duracao', 'ano_estreia', 'classificacao', 'classificacao_text', 'teatro_nome', 'teatro_endereco', 'dias_horarios', 'tipo_sessao', 'valores', 'link_vendas', 'link_texto');
        extract($template_vars);

        include CANNAL_ESPETACULOS_PLUGIN_DIR . 'templates/public/widget-dados-espetaculo.php';

        echo $args['after_widget'];

        // --- Widgets complementares: renderizados como widgets independentes, FORA do before/after_widget ---
        if (! $temporada) {
            $proximas = CANNALEspetaculos_Public::get_proximas_temporadas_static($espetaculo_id, 5);

            if (! empty($proximas)) {
                $widget_proximas = new CANNALEspetaculos_WidgetProximas();
                $widget_proximas->widget($args, array('titulo' => ''));
            } else {
                $ultimas = CANNALEspetaculos_Public::get_ultimas_temporadas_static($espetaculo_id, 5);
                if (! empty($ultimas)) {
                    $widget_ultimas = new CANNALEspetaculos_WidgetUltimas();
                    $widget_ultimas->widget($args, array('titulo' => ''));
                }
            }
        }
    }
}
