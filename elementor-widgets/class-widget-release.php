<?php
/**
 * Widget Release do Elementor.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/elementor-widgets
 */

class Cannal_Espetaculos_Widget_Release extends \Elementor\Widget_Base {

    public function get_name() {
        return 'cannal_espetaculo_release';
    }

    public function get_title() {
        return 'Release';
    }

    public function get_icon() {
        return 'eicon-text-area';
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
            'important_note',
            array(
                'label' => '',
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<div style="background: #f0f0f0; padding: 15px; border-radius: 5px;">Este widget exibe automaticamente o conteúdo da temporada ativa ou do espetáculo.</div>',
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
            'text_color',
            array(
                'label' => 'Cor do Texto',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .cannal-release-content' => 'color: {{VALUE}}',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'content_typography',
                'selector' => '{{WRAPPER}} .cannal-release-content',
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

        // Obter temporada ativa
        $temporada = $this->get_active_temporada( $post->ID );

        // Exibir conteúdo da temporada ou do espetáculo
        $content = '';
        if ( $temporada && ! empty( $temporada->post_content ) ) {
            $content = apply_filters( 'the_content', $temporada->post_content );
        } else {
            $content = apply_filters( 'the_content', $post->post_content );
        }

        echo '<div class="cannal-release-content">' . $content . '</div>';
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
            ),
            'orderby' => 'meta_value',
            'meta_key' => '_temporada_data_inicio',
            'order' => 'DESC'
        ) );

        if ( ! empty( $temporadas ) ) {
            return $temporadas[0];
        }

        $temporadas = get_posts( array(
            'post_type' => 'temporada',
            'posts_per_page' => 1,
            'meta_key' => '_temporada_espetaculo_id',
            'meta_value' => $espetaculo_id,
            'orderby' => 'meta_value',
            'meta_key' => '_temporada_data_inicio',
            'order' => 'DESC'
        ) );

        return ! empty( $temporadas ) ? $temporadas[0] : null;
    }
}
