<?php
/**
 * Widget Lista de Informações do Elementor.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/elementor-widgets
 */

class Cannal_Espetaculos_Widget_Lista_Informacoes extends \Elementor\Widget_Base {

    public function get_name() {
        return 'cannal_espetaculo_lista_informacoes';
    }

    public function get_title() {
        return 'Lista de Informações';
    }

    public function get_icon() {
        return 'eicon-bullet-list';
    }

    public function get_categories() {
        return array( 'cannal-espetaculos' );
    }

    protected function register_controls() {
        
        $this->start_controls_section(
            'content_section',
            array(
                'label' => 'Informações',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'info_type',
            array(
                'label' => 'Tipo',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => array(
                    'autor' => 'Autor',
                    'diretor' => 'Diretor',
                    'elenco' => 'Elenco',
                    'ano_estreia' => 'Ano de Estreia',
                    'duracao' => 'Duração',
                    'classificacao' => 'Classificação Indicativa',
                    'logotipo' => 'Logotipo',
                    'teatro' => 'Teatro',
                    'endereco' => 'Endereço',
                    'temporada' => 'Temporada',
                    'valores' => 'Valores',
                ),
                'default' => 'autor',
            )
        );

        $repeater->add_control(
            'custom_title',
            array(
                'label' => 'Título Personalizado',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
            )
        );

        $this->add_control(
            'info_list',
            array(
                'label' => 'Informações',
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => array(
                    array(
                        'info_type' => 'autor',
                        'custom_title' => 'Autor',
                    ),
                    array(
                        'info_type' => 'duracao',
                        'custom_title' => 'Duração',
                    ),
                ),
                'title_field' => '{{{ custom_title || info_type }}}',
            )
        );

        $this->end_controls_section();

        // Estilo
        $this->start_controls_section(
            'style_section',
            array(
                'label' => 'Estilo',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label' => 'Cor do Título',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .cannal-info-item-title' => 'color: {{VALUE}}',
                ),
            )
        );

        $this->add_control(
            'content_color',
            array(
                'label' => 'Cor do Conteúdo',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .cannal-info-item-content' => 'color: {{VALUE}}',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'title_typography',
                'label' => 'Tipografia do Título',
                'selector' => '{{WRAPPER}} .cannal-info-item-title',
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'content_typography',
                'label' => 'Tipografia do Conteúdo',
                'selector' => '{{WRAPPER}} .cannal-info-item-content',
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        global $post;

        if ( ! $post || $post->post_type !== 'espetaculo' ) {
            echo '<p>Este widget só funciona em páginas de espetáculos.</p>';
            return;
        }

        $settings = $this->get_settings_for_display();
        $temporada = $this->get_active_temporada( $post->ID );

        echo '<div class="cannal-lista-informacoes">';
        
        foreach ( $settings['info_list'] as $item ) {
            $content = $this->get_info_content( $post->ID, $temporada, $item['info_type'] );
            
            if ( ! empty( $content ) ) {
                $title = ! empty( $item['custom_title'] ) ? $item['custom_title'] : ucfirst( str_replace( '_', ' ', $item['info_type'] ) );
                
                echo '<div class="cannal-info-item">';
                echo '<div class="cannal-info-item-title">' . esc_html( $title ) . '</div>';
                echo '<div class="cannal-info-item-content">' . $content . '</div>';
                echo '</div>';
            }
        }
        
        echo '</div>';
    }

    private function get_info_content( $espetaculo_id, $temporada, $info_type ) {
        switch ( $info_type ) {
            case 'autor':
                return esc_html( cannal_get_field( $espetaculo_id, 'autor' ) );
            case 'diretor':
                return esc_html( cannal_get_field( $espetaculo_id, 'diretor' ) );
            case 'elenco':
                return nl2br( esc_html( cannal_get_field( $espetaculo_id, 'elenco' ) ) );
            case 'ano_estreia':
                return esc_html( cannal_get_field( $espetaculo_id, 'ano_estreia' ) );
            case 'duracao':
                return esc_html( cannal_get_field( $espetaculo_id, 'duracao' ) );
            case 'classificacao':
                $class = cannal_get_field( $espetaculo_id, 'classificacao' );
                if ( $class ) {
                    $texto = $class === 'livre' ? 'Livre' : $class . ' anos';
                    return '<div class="classificacao-selo classificacao-' . esc_attr( $class ) . '">' . esc_html( $texto ) . '</div>';
                }
                return '';
            case 'logotipo':
                $logo_id = cannal_get_field( $espetaculo_id, 'logotipo' );
                $logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
                if ( $logo_url ) {
                    return '<img src="' . esc_url( $logo_url ) . '" alt="Logotipo do espetáculo" />';
                }
                return '';
            case 'teatro':
                return $temporada ? esc_html( get_post_meta( $temporada->ID, '_temporada_teatro_nome', true ) ) : '';
            case 'endereco':
                return $temporada ? esc_html( get_post_meta( $temporada->ID, '_temporada_teatro_endereco', true ) ) : '';
            case 'temporada':
                if ( ! $temporada ) return '';
                $tipo_sessao = get_post_meta( $temporada->ID, '_temporada_tipo_sessao', true );
                $sessoes_data = get_post_meta( $temporada->ID, '_temporada_sessoes_data', true );
                return esc_html( Cannal_Espetaculos_Dias_Horarios::gerar( $tipo_sessao, $sessoes_data ) );
            case 'valores':
                return $temporada ? nl2br( esc_html( get_post_meta( $temporada->ID, '_temporada_valores', true ) ) ) : '';
            default:
                return '';
        }
    }

    private function get_active_temporada( $espetaculo_id ) {
        $hoje = current_time( 'Y-m-d' );
        $temporadas = get_posts( array(
            'post_type' => 'temporada',
            'posts_per_page' => 1,
            'meta_query' => array(
                'relation' => 'AND',
                array( 'key' => '_temporada_espetaculo_id', 'value' => $espetaculo_id, 'compare' => '=' ),
                array( 'key' => '_temporada_data_inicio', 'value' => $hoje, 'compare' => '<=', 'type' => 'DATE' ),
                array( 'key' => '_temporada_data_fim', 'value' => $hoje, 'compare' => '>=', 'type' => 'DATE' )
            )
        ) );
        return ! empty( $temporadas ) ? $temporadas[0] : null;
    }
}
