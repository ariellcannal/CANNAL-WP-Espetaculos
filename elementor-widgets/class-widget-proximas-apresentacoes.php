<?php
/**
 * Widget Próximas Apresentações do Elementor.
 */

class Cannal_Espetaculos_Widget_Proximas_Apresentacoes extends \Elementor\Widget_Base {

    public function get_name() {
        return 'cannal_proximas_apresentacoes';
    }

    public function get_title() {
        return 'Próximas Apresentações';
    }

    public function get_icon() {
        return 'eicon-calendar';
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
            'show_theater',
            array(
                'label' => 'Mostrar Teatro',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_date',
            array(
                'label' => 'Mostrar Data de Início',
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        global $post;
        if ( ! $post || $post->post_type !== 'espetaculo' ) return;

        $settings = $this->get_settings_for_display();
        $temporadas = Cannal_Espetaculos_Public::get_temporadas_by_status( $post->ID, 'futuras' );

        if ( empty( $temporadas ) ) {
            echo '<p>Nenhuma apresentação futura agendada.</p>';
            return;
        }

        echo '<div class="cannal-temporadas-list">';
        foreach ( $temporadas as $temporada ) {
            $teatro = get_post_meta( $temporada->ID, '_temporada_teatro_nome', true );
            $data_inicio = get_post_meta( $temporada->ID, '_temporada_data_inicio', true );
            
            echo '<div class="cannal-temporada-item">';
            if ( $settings['show_theater'] === 'yes' && $teatro ) {
                echo '<div class="temporada-teatro">' . esc_html( $teatro ) . '</div>';
            }
            if ( $settings['show_date'] === 'yes' && $data_inicio ) {
                echo '<div class="temporada-data">Início: ' . esc_html( date_i18n( 'd/m/Y', strtotime( $data_inicio ) ) ) . '</div>';
            }
            echo '</div>';
        }
        echo '</div>';
    }
}
