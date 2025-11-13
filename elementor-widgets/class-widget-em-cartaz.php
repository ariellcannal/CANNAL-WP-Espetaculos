<?php
/**
 * Widget Em Cartaz do Elementor.
 */

class Cannal_Espetaculos_Widget_Em_Cartaz extends \Elementor\Widget_Base {

    public function get_name() {
        return 'cannal_em_cartaz';
    }

    public function get_title() {
        return 'Em Cartaz';
    }

    public function get_icon() {
        return 'eicon-star';
    }

    public function get_categories() {
        return array( 'cannal-espetaculos' );
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => 'Configurações',
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_schedule',
            array(
                'label' => 'Mostrar Dias e Horários',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
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
            'background_color',
            array(
                'label' => 'Cor de Fundo',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .cannal-em-cartaz-box' => 'background-color: {{VALUE}}',
                ),
            )
        );

        $this->add_control(
            'text_color',
            array(
                'label' => 'Cor do Texto',
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .cannal-em-cartaz-box' => 'color: {{VALUE}}',
                ),
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        global $post;
        if ( ! $post || $post->post_type !== 'espetaculo' ) return;

        $settings = $this->get_settings_for_display();
        $temporadas = Cannal_Espetaculos_Public::get_temporadas_by_status( $post->ID, 'em_cartaz' );

        if ( empty( $temporadas ) ) {
            echo '<p>Não há apresentações em cartaz no momento.</p>';
            return;
        }

        echo '<div class="cannal-em-cartaz-container">';
        foreach ( $temporadas as $temporada ) {
            $teatro = get_post_meta( $temporada->ID, '_temporada_teatro_nome', true );
            $dias_horarios = get_post_meta( $temporada->ID, '_temporada_dias_horarios', true );
            
            echo '<div class="cannal-em-cartaz-box">';
            echo '<div class="em-cartaz-teatro"><strong>' . esc_html( $teatro ) . '</strong></div>';
            if ( $settings['show_schedule'] === 'yes' && $dias_horarios ) {
                echo '<div class="em-cartaz-horarios">' . esc_html( $dias_horarios ) . '</div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }
}
