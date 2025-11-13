<?php
/**
 * Disparado durante a ativação do plugin.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/includes
 */

class Cannal_Espetaculos_Activator {

    /**
     * Ações executadas na ativação do plugin.
     */
    public static function activate() {
        // Registrar os post types e taxonomias
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cannal-espetaculos-post-types.php';
        $post_types = new Cannal_Espetaculos_Post_Types();
        $post_types->register_post_types();
        $post_types->register_taxonomies();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Criar opção para controlar estrutura de URLs
        add_option( 'cannal_espetaculos_has_categories', false );
    }
}
