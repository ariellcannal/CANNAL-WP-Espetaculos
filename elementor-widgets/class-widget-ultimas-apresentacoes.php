<?php
/**
 * Widget Últimas Apresentações do Elementor.
 */

class Cannal_Espetaculos_Widget_Ultimas_Apresentacoes extends \Elementor\Widget_Base {

    public function get_name() {
        return 'cannal_ultimas_apresentacoes';
    }

    public function get_title() {
        return 'Últimas Apresentações';
    }

    public function get_icon() {
        return 'eicon-archive';
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
            'limit',
            array(
                'label' => 'Limite',
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5,
                'min' => 1,
                'max' => 20,
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        global $post;
        if ( ! $post || $post->post_type !== 'espetaculo' ) return;

        $settings = $this->get_settings_for_display();
        $temporadas = Cannal_Espetaculos_Public::get_temporadas_by_status( $post->ID, 'encerradas' );
        $temporadas = array_slice( $temporadas, 0, $settings['limit'] );

        if ( empty( $temporadas ) ) {
            echo '<p>Nenhuma apresentação encerrada.</p>';
            return;
        }

        echo '<div class="cannal-temporadas-list">';
        foreach ( $temporadas as $temporada ) {
            $teatro = get_post_meta( $temporada->ID, '_temporada_teatro_nome', true );
            $data_fim = get_post_meta( $temporada->ID, '_temporada_data_fim', true );
            
            echo '<div class="cannal-temporada-item">';
            echo '<div class="temporada-teatro">' . esc_html( $teatro ) . '</div>';
            if ( $data_fim ) {
                echo '<div class="temporada-data">Encerrou em: ' . esc_html( date_i18n( 'd/m/Y', strtotime( $data_fim ) ) ) . '</div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }
}
