<?php
/**
 * Integração com o Elementor.
 *
 * @package    CANNALEspetaculos_Plugin
 * @subpackage CANNALEspetaculos_Plugin/includes
 */

class CANNALEspetaculos_Elementor {

    /**
     * Registra os widgets do Elementor.
     */
    public function register_widgets( $widgets_manager ) {
        
        if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
            return;
        }

        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'elementor-widgets/Release.php';
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'elementor-widgets/Galeria.php';
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'elementor-widgets/Informacao.php';
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'elementor-widgets/ListaInformacoes.php';
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'elementor-widgets/Proximas.php';
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'elementor-widgets/Ultimas.php';
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'elementor-widgets/EmCartaz.php';

        $widgets_manager->register( new \CANNALEspetaculos_ElementorRelease() );
        $widgets_manager->register( new \CANNALEspetaculos_ElementorGaleria() );
        $widgets_manager->register( new \CANNALEspetaculos_ElementorInformacao() );
        $widgets_manager->register( new \CANNALEspetaculos_ElementorListaInformacoes() );
        $widgets_manager->register( new \CANNALEspetaculos_ElementorProximas() );
        $widgets_manager->register( new \CANNALEspetaculos_ElementorUltimas() );
        $widgets_manager->register( new \CANNALEspetaculos_ElementorEmCartaz() );
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
