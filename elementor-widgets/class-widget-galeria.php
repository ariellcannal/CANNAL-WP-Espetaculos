<?php
/**
 * Widget Galeria do Elementor.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/elementor-widgets
 */

class Cannal_Espetaculos_Widget_Galeria extends \Elementor\Widget_Base {

    public function get_name() {
        return 'cannal_espetaculo_galeria';
    }

    public function get_title() {
        return 'Galeria de Fotos';
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
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
            'columns',
            array(
                'label' => 'Colunas',
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 3,
                'min' => 1,
                'max' => 6,
            )
        );

        $this->add_control(
            'gap',
            array(
                'label' => 'Espaçamento',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px' ),
                'range' => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 50,
                    ),
                ),
                'default' => array(
                    'unit' => 'px',
                    'size' => 15,
                ),
                'selectors' => array(
                    '{{WRAPPER}} .cannal-galeria-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ),
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
            'border_radius',
            array(
                'label' => 'Raio da Borda',
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px', '%' ),
                'range' => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 50,
                    ),
                ),
                'selectors' => array(
                    '{{WRAPPER}} .cannal-galeria-item img' => 'border-radius: {{SIZE}}{{UNIT}};',
                ),
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
        $galeria = get_post_meta( $post->ID, '_espetaculo_galeria', true );

        if ( empty( $galeria ) ) {
            echo '<p>Nenhuma imagem na galeria.</p>';
            return;
        }

        $galeria_ids = explode( ',', $galeria );
        $columns = $settings['columns'];

        echo '<div class="cannal-galeria-grid" style="grid-template-columns: repeat(' . esc_attr( $columns ) . ', 1fr);">';
        
        foreach ( $galeria_ids as $image_id ) {
            $image_url = wp_get_attachment_image_url( $image_id, 'medium' );
            $image_full = wp_get_attachment_image_url( $image_id, 'full' );
            
            if ( $image_url ) {
                echo '<a href="' . esc_url( $image_full ) . '" class="cannal-galeria-item">';
                echo '<img src="' . esc_url( $image_url ) . '" alt="" />';
                echo '</a>';
            }
        }
        
        echo '</div>';
    }
}
