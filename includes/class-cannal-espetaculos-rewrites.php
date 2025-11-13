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
        $has_categories = $this->has_categories();

        if ( $has_categories ) {
            // COM CATEGORIAS
            
            // Arquivo de categorias
            add_rewrite_rule(
                '^espetaculos/?$',
                'index.php?post_type=espetaculo&espetaculos_archive=categories',
                'top'
            );

            // Arquivo de espetáculos de uma categoria
            add_rewrite_rule(
                '^espetaculos/([^/]+)/?$',
                'index.php?post_type=espetaculo&espetaculo_categoria=$matches[1]',
                'top'
            );

            // Single de espetáculo com categoria
            add_rewrite_rule(
                '^espetaculos/([^/]+)/([^/]+)/?$',
                'index.php?post_type=espetaculo&espetaculo_categoria=$matches[1]&name=$matches[2]',
                'top'
            );

        } else {
            // SEM CATEGORIAS
            
            // Arquivo de espetáculos
            add_rewrite_rule(
                '^espetaculos/?$',
                'index.php?post_type=espetaculo',
                'top'
            );

            // Single de espetáculo
            add_rewrite_rule(
                '^espetaculos/([^/]+)/?$',
                'index.php?post_type=espetaculo&name=$matches[1]',
                'top'
            );
        }
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
     */
    public function handle_redirects() {
        global $wp_query;

        $has_categories = $this->has_categories();

        // Se tem categorias e acessar /espetaculos/{slug} sem categoria, redirecionar
        if ( $has_categories && is_singular( 'espetaculo' ) ) {
            $post_id = get_queried_object_id();
            $terms = get_the_terms( $post_id, 'espetaculo_categoria' );
            
            if ( $terms && ! is_wp_error( $terms ) ) {
                $term = array_shift( $terms );
                $current_url = $_SERVER['REQUEST_URI'];
                $expected_url = '/espetaculos/' . $term->slug . '/' . get_post_field( 'post_name', $post_id ) . '/';
                
                // Se a URL atual não contém a categoria, redirecionar
                if ( strpos( $current_url, '/espetaculos/' . $term->slug . '/' ) === false ) {
                    wp_redirect( home_url( $expected_url ), 301 );
                    exit;
                }
            }
        }
    }

    /**
     * Verifica se existem categorias cadastradas.
     */
    private function has_categories() {
        $terms = get_terms( array(
            'taxonomy' => 'espetaculo_categoria',
            'hide_empty' => false,
            'number' => 1
        ) );

        $has_categories = ! empty( $terms ) && ! is_wp_error( $terms );
        update_option( 'cannal_espetaculos_has_categories', $has_categories );

        return $has_categories;
    }

    /**
     * Callback quando uma categoria é criada, editada ou deletada.
     */
    public function on_category_change() {
        // Atualizar a opção
        $this->has_categories();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Obtém a URL de um espetáculo.
     */
    public static function get_espetaculo_url( $post_id ) {
        $has_categories = get_option( 'cannal_espetaculos_has_categories', false );
        $slug = get_post_field( 'post_name', $post_id );

        if ( $has_categories ) {
            $terms = get_the_terms( $post_id, 'espetaculo_categoria' );
            if ( $terms && ! is_wp_error( $terms ) ) {
                $term = array_shift( $terms );
                return home_url( '/espetaculos/' . $term->slug . '/' . $slug . '/' );
            }
        }

        return home_url( '/espetaculos/' . $slug . '/' );
    }

    /**
     * Obtém a URL do arquivo de espetáculos.
     */
    public static function get_espetaculos_archive_url() {
        return home_url( '/espetaculos/' );
    }

    /**
     * Obtém a URL de uma categoria de espetáculos.
     */
    public static function get_categoria_url( $term_slug ) {
        return home_url( '/espetaculos/' . $term_slug . '/' );
    }
}
