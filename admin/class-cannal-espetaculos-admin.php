<?php
/**
 * A funcionalidade específica do admin do plugin.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/admin
 */

class Cannal_Espetaculos_Admin {

    /**
     * O ID deste plugin.
     */
    private $plugin_name;

    /**
     * A versão deste plugin.
     */
    private $version;

    /**
     * Inicializa a classe e define suas propriedades.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action( 'wp_ajax_get_espetaculo_content', array( $this, 'ajax_get_espetaculo_content' ) );
    }

    /**
     * Registra os arquivos de estilo para a área administrativa.
     */
    public function enqueue_styles() {
        $screen = get_current_screen();
        
        if ( in_array( $screen->post_type, array( 'espetaculo', 'temporada' ) ) ) {
            wp_enqueue_style( 
                $this->plugin_name, 
                CANNAL_ESPETACULOS_PLUGIN_URL . 'admin/css/cannal-espetaculos-admin.css', 
                array(), 
                $this->version, 
                'all' 
            );
        }
    }

    /**
     * Registra os arquivos JavaScript para a área administrativa.
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();
        
        if ( in_array( $screen->post_type, array( 'espetaculo', 'temporada' ) ) ) {
            wp_enqueue_media();
            wp_enqueue_script( 
                $this->plugin_name, 
                CANNAL_ESPETACULOS_PLUGIN_URL . 'admin/js/cannal-espetaculos-admin.js', 
                array( 'jquery', 'jquery-ui-sortable' ), 
                $this->version, 
                false 
            );
        }
    }

    /**
     * AJAX: Obtém o conteúdo de um espetáculo.
     */
    public function ajax_get_espetaculo_content() {
        check_ajax_referer( 'cannal_espetaculos_nonce', 'nonce' );

        $espetaculo_id = isset( $_POST['espetaculo_id'] ) ? intval( $_POST['espetaculo_id'] ) : 0;

        if ( ! $espetaculo_id ) {
            wp_send_json_error( array( 'message' => 'ID do espetáculo não fornecido.' ) );
        }

        $espetaculo = get_post( $espetaculo_id );

        if ( ! $espetaculo || $espetaculo->post_type !== 'espetaculo' ) {
            wp_send_json_error( array( 'message' => 'Espetáculo não encontrado.' ) );
        }

        wp_send_json_success( array( 'content' => $espetaculo->post_content ) );
    }
}
