<?php
/**
 * Widget Informação do Elementor.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/elementor-widgets
 */

class Cannal_Espetaculos_Widget_Informacao extends \Elementor\Widget_Base {

    public function get_name() {
        return 'cannal_espetaculo_informacao';
    }

    public function get_title() {
        return 'Informação';
    }

    public function get_icon() {
        return 'eicon-info-circle';
    }

    public function get_categories() {
        return array( 'cannal-espetaculos' );
    }

    protected function register_controls() {
        
        $this->start_controls_section(
            'content_section',
            array(
                'label' => 'Conteúdo',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'info_type',
            array(
                'label' => 'Tipo de Informação',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => array(
                    'autor' => 'Autor',
                    'ano_estreia' => 'Ano de Estreia',
                    'duracao' => 'Duração',
                    'classificacao' => 'Classificação Indicativa',
                    'teatro' => 'Teatro',
                    'endereco' => 'Endereço do Teatro',
                    'temporada' => 'Temporada/Apresentações',
                    'valores' => 'Valores',
                    'link_ingressos' => 'Link de Ingressos',
                ),
                'default' => 'autor',
            )
        );

        $this->add_control(
            'title',
            array(
                'label' => 'Título',
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'Informação',
            )
        );

        $this->add_control(
            'title_tag',
            array(
                'label' => 'Tag do Título',
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => array(
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'div',
                    'span' => 'span',
                    'p' => 'p',
                ),
                'default' => 'h3',
            )
        );

        $this->end_controls_section();

        // Estilo do título
        $this->start_controls_section(
            'title_style_section',
            array(
                'label' => 'Estilo do Título',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label' => 'Cor',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .cannal-info-title' => 'color: {{VALUE}}',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .cannal-info-title',
            )
        );

        $this->end_controls_section();

        // Estilo do conteúdo
        $this->start_controls_section(
            'content_style_section',
            array(
                'label' => 'Estilo do Conteúdo',
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'content_color',
            array(
                'label' => 'Cor',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .cannal-info-content' => 'color: {{VALUE}}',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'content_typography',
                'selector' => '{{WRAPPER}} .cannal-info-content',
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
        $info_type = $settings['info_type'];
        $title = $settings['title'];
        $title_tag = $settings['title_tag'];

        $content = $this->get_info_content( $post->ID, $info_type );

        if ( empty( $content ) ) {
            return;
        }

        echo '<div class="cannal-info-widget">';
        echo '<' . esc_attr( $title_tag ) . ' class="cannal-info-title">' . esc_html( $title ) . '</' . esc_attr( $title_tag ) . '>';
        echo '<div class="cannal-info-content">' . $content . '</div>';
        echo '</div>';
    }

    private function get_info_content( $espetaculo_id, $info_type ) {
        $temporada = $this->get_active_temporada( $espetaculo_id );

        switch ( $info_type ) {
            case 'autor':
                return esc_html( get_post_meta( $espetaculo_id, '_espetaculo_autor', true ) );

            case 'ano_estreia':
                return esc_html( get_post_meta( $espetaculo_id, '_espetaculo_ano_estreia', true ) );

            case 'duracao':
                return esc_html( get_post_meta( $espetaculo_id, '_espetaculo_duracao', true ) );

            case 'classificacao':
                $class = get_post_meta( $espetaculo_id, '_espetaculo_classificacao', true );
                if ( $class ) {
                    $texto = $class === 'livre' ? 'Livre' : $class . ' anos';
                    return '<div class="classificacao-selo classificacao-' . esc_attr( $class ) . '">' . esc_html( $texto ) . '</div>';
                }
                return '';

            case 'teatro':
                return $temporada ? esc_html( get_post_meta( $temporada->ID, '_temporada_teatro_nome', true ) ) : '';

            case 'endereco':
                return $temporada ? esc_html( get_post_meta( $temporada->ID, '_temporada_teatro_endereco', true ) ) : '';

            case 'temporada':
                return $temporada ? esc_html( get_post_meta( $temporada->ID, '_temporada_dias_horarios', true ) ) : '';

            case 'valores':
                return $temporada ? nl2br( esc_html( get_post_meta( $temporada->ID, '_temporada_valores', true ) ) ) : '';

            case 'link_ingressos':
                if ( ! $temporada ) return '';
                $link = get_post_meta( $temporada->ID, '_temporada_link_vendas', true );
                $texto = get_post_meta( $temporada->ID, '_temporada_link_texto', true );
                if ( $link ) {
                    $texto_botao = ! empty( $texto ) ? $texto : 'Ingressos Aqui';
                    return '<a href="' . esc_url( $link ) . '" class="button-ingressos" target="_blank">' . esc_html( $texto_botao ) . '</a>';
                }
                return '';

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
            )
        ) );

        if ( ! empty( $temporadas ) ) {
            return $temporadas[0];
        }

        $temporadas = get_posts( array(
            'post_type' => 'temporada',
            'posts_per_page' => 1,
            'meta_key' => '_temporada_espetaculo_id',
            'meta_value' => $espetaculo_id,
            'orderby' => 'date',
            'order' => 'DESC'
        ) );

        return ! empty( $temporadas ) ? $temporadas[0] : null;
    }
}
