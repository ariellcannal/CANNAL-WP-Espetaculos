<?php
/**
 * Gerencia as rewrite rules e URLs personalizadas.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/includes
 */

class Cannal_Espetaculos_Rewrites {

    /**
     * Adiciona as rewrite rules personalizadas.
     */
    public function add_rewrite_rules() {
        // O WordPress já gerencia automaticamente as URLs de taxonomia
        // Estrutura: /espetaculos/{categoria}/{espetaculo}/
        
        // Garantir que o arquivo de espetáculos funcione
        add_rewrite_rule(
            '^espetaculos/?$',
            'index.php?post_type=espetaculo',
            'top'
        );
    }

    /**
     * Adiciona query vars personalizadas.
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'espetaculos_archive';
        return $vars;
    }

    /**
     * Gerencia redirecionamentos.
     * (Removido - WordPress gerencia automaticamente)
     */
    public function handle_redirects() {
        // Não é mais necessário
    }

    /**
     * Callback quando uma categoria é criada, editada ou deletada.
     */
    public function on_category_change() {
        // Flush rewrite rules para atualizar permalinks
        flush_rewrite_rules();
    }

    /**
     * Obtém a URL de um espetáculo.
     */
    public static function get_espetaculo_url( $post_id ) {
        // Usar permalink padrão do WordPress
        return get_permalink( $post_id );
    }

    /**
     * Obtém a URL do arquivo de espetáculos.
     */
    public static function get_espetaculos_archive_url() {
        return get_post_type_archive_link( 'espetaculo' );
    }

    /**
     * Obtém a URL de uma categoria de espetáculos.
     */
    public static function get_categoria_url( $term_slug_or_id ) {
        if ( is_numeric( $term_slug_or_id ) ) {
            return get_term_link( (int) $term_slug_or_id, 'espetaculo_categoria' );
        }
        
        $term = get_term_by( 'slug', $term_slug_or_id, 'espetaculo_categoria' );
        if ( $term && ! is_wp_error( $term ) ) {
            return get_term_link( $term, 'espetaculo_categoria' );
        }
        
        return home_url( '/espetaculos/' );
    }
}
