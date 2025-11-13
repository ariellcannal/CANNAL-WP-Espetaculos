<?php
/**
 * Integração com o Elementor.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/includes
 */

class Cannal_Espetaculos_Elementor {

    /**
     * Registra os widgets do Elementor.
     */
    public function register_widgets( $widgets_manager ) {
        
        if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
            return;
        }

        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'elementor-widgets/class-widget-release.php';
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'elementor-widgets/class-widget-galeria.php';
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'elementor-widgets/class-widget-informacao.php';
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'elementor-widgets/class-widget-lista-informacoes.php';
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'elementor-widgets/class-widget-proximas-apresentacoes.php';
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'elementor-widgets/class-widget-ultimas-apresentacoes.php';
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'elementor-widgets/class-widget-em-cartaz.php';

        $widgets_manager->register( new \Cannal_Espetaculos_Widget_Release() );
        $widgets_manager->register( new \Cannal_Espetaculos_Widget_Galeria() );
        $widgets_manager->register( new \Cannal_Espetaculos_Widget_Informacao() );
        $widgets_manager->register( new \Cannal_Espetaculos_Widget_Lista_Informacoes() );
        $widgets_manager->register( new \Cannal_Espetaculos_Widget_Proximas_Apresentacoes() );
        $widgets_manager->register( new \Cannal_Espetaculos_Widget_Ultimas_Apresentacoes() );
        $widgets_manager->register( new \Cannal_Espetaculos_Widget_Em_Cartaz() );
    }

    /**
     * Adiciona categoria personalizada no Elementor.
     */
    public function add_elementor_category( $elements_manager ) {
        $elements_manager->add_category(
            'cannal-espetaculos',
            array(
                'title' => 'CANNAL Espetáculos',
                'icon' => 'fa fa-plug',
            )
        );
    }
}
