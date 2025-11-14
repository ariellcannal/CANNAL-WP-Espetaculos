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
        
        // Criar categoria padrão "Espetáculo" se não existir
        self::create_default_category();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Definir transient para exibir aviso
        set_transient( 'cannal_espetaculos_flush_rewrite_rules', true, 60 );
    }
    
    /**
     * Cria a categoria padrão "Espetáculo" se não existir.
     */
    private static function create_default_category() {
        // Verificar se já existe
        $term = get_term_by( 'slug', 'espetaculo', 'espetaculo_categoria' );
        
        if ( ! $term ) {
            // Criar categoria padrão
            $result = wp_insert_term(
                'Espetáculo',
                'espetaculo_categoria',
                array(
                    'slug' => 'espetaculo',
                    'description' => 'Categoria padrão para espetáculos'
                )
            );
            
            if ( ! is_wp_error( $result ) ) {
                // Salvar ID da categoria padrão
                update_option( 'cannal_espetaculos_default_category', $result['term_id'] );
            }
        } else {
            // Atualizar opção com ID da categoria existente
            update_option( 'cannal_espetaculos_default_category', $term->term_id );
        }
    }
}
