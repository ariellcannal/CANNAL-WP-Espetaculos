<?php
/**
 * Registra os custom post types e taxonomias.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/includes
 */

class Cannal_Espetaculos_Post_Types {

    /**
     * Registra o custom post type de Espetáculos.
     */
    public function register_post_types() {
        
        // Registrar Espetáculos
        $labels_espetaculo = array(
            'name'                  => 'Espetáculos',
            'singular_name'         => 'Espetáculo',
            'menu_name'             => 'CANNAL Espetáculos',
            'name_admin_bar'        => 'Espetáculo',
            'add_new'               => 'Adicionar Novo',
            'add_new_item'          => 'Adicionar Novo Espetáculo',
            'new_item'              => 'Novo Espetáculo',
            'edit_item'             => 'Editar Espetáculo',
            'view_item'             => 'Ver Espetáculo',
            'all_items'             => 'Espetáculos',
            'search_items'          => 'Buscar Espetáculos',
            'parent_item_colon'     => 'Espetáculo Pai:',
            'not_found'             => 'Nenhum espetáculo encontrado.',
            'not_found_in_trash'    => 'Nenhum espetáculo encontrado na lixeira.'
        );

        $args_espetaculo = array(
            'labels'                => $labels_espetaculo,
            'description'           => 'Espetáculos teatrais',
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'query_var'             => 'espetaculo',
            'rewrite'               => array(
                'slug'       => 'espetaculos',
                'with_front' => false,
                'feeds'      => false,
                'pages'      => false
            ),
            'capability_type'       => 'post',
            'has_archive'           => 'espetaculos',
            'hierarchical'          => false,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-tickets-alt',
            'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
            'show_in_rest'          => true,
            'taxonomies'            => array( 'post_tag' ),
            'permalink_epmask'      => EP_PERMALINK
        );

        register_post_type( 'espetaculo', $args_espetaculo );

        // Registrar Temporadas
        $labels_temporada = array(
            'name'                  => 'Temporadas',
            'singular_name'         => 'Temporada',
            'menu_name'             => 'Temporadas',
            'name_admin_bar'        => 'Temporada',
            'add_new'               => 'Adicionar Nova',
            'add_new_item'          => 'Adicionar Nova Temporada',
            'new_item'              => 'Nova Temporada',
            'edit_item'             => 'Editar Temporada',
            'view_item'             => 'Ver Temporada',
            'all_items'             => 'Temporadas',
            'search_items'          => 'Buscar Temporadas',
            'parent_item_colon'     => 'Temporada Pai:',
            'not_found'             => 'Nenhuma temporada encontrada.',
            'not_found_in_trash'    => 'Nenhuma temporada encontrada na lixeira.'
        );

        $args_temporada = array(
            'labels'                => $labels_temporada,
            'description'           => 'Temporadas de espetáculos',
            'public'                => false,
            'publicly_queryable'    => false,
            'show_ui'               => true,
            'show_in_menu'          => 'edit.php?post_type=espetaculo',
            'query_var'             => false,
            'rewrite'               => false,
            'capability_type'       => 'post',
            'has_archive'           => false,
            'hierarchical'          => false,
            'supports'              => array( 'title', 'editor' ),
            'show_in_rest'          => true
        );

        register_post_type( 'temporada', $args_temporada );
    }

    /**
     * Registra as taxonomias personalizadas.
     */
    public function register_taxonomies() {
        
        // Registrar Categorias de Espetáculo
        $labels_categoria = array(
            'name'                       => 'Categorias',
            'singular_name'              => 'Categoria',
            'search_items'               => 'Buscar Categorias',
            'popular_items'              => 'Categorias Populares',
            'all_items'                  => 'Todas as Categorias',
            'parent_item'                => 'Categoria Pai',
            'parent_item_colon'          => 'Categoria Pai:',
            'edit_item'                  => 'Editar Categoria',
            'update_item'                => 'Atualizar Categoria',
            'add_new_item'               => 'Adicionar Nova Categoria',
            'new_item_name'              => 'Nome da Nova Categoria',
            'separate_items_with_commas' => 'Separe categorias com vírgulas',
            'add_or_remove_items'        => 'Adicionar ou remover categorias',
            'choose_from_most_used'      => 'Escolher das categorias mais usadas',
            'not_found'                  => 'Nenhuma categoria encontrada.',
            'menu_name'                  => 'Categorias',
        );

        $args_categoria = array(
            'hierarchical'          => true,
            'labels'                => $labels_categoria,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'query_var'             => true,
            'rewrite'               => false, // Será tratado manualmente
            'show_in_rest'          => true,
            'show_in_menu'          => true
        );

        register_taxonomy( 'espetaculo_categoria', array( 'espetaculo' ), $args_categoria );
    }

    /**
     * Redireciona tentativas de acessar temporadas publicamente para 404.
     */
    public function redirect_temporada_to_404() {
        if ( is_singular( 'temporada' ) ) {
            global $wp_query;
            $wp_query->set_404();
            status_header( 404 );
            get_template_part( 404 );
            exit;
        }
    }
    
    /**
     * Renomeia "Imagem Destacada" para "Banner" no post type Espetáculo.
     */
    public function rename_featured_image( $content ) {
        global $post_type;
        
        if ( $post_type === 'espetaculo' ) {
            // Todas as variações possíveis
            $content = str_replace( 'Imagem destacada', 'Banner', $content );
            $content = str_replace( 'imagem destacada', 'banner', $content );
            $content = str_replace( 'Imagem Destacada', 'Banner', $content );
            $content = str_replace( 'IMAGEM DESTACADA', 'BANNER', $content );
            $content = str_replace( 'Definir imagem destacada', 'Definir banner', $content );
            $content = str_replace( 'Remover imagem destacada', 'Remover banner', $content );
            $content = str_replace( 'Alterar imagem destacada', 'Alterar banner', $content );
            $content = str_replace( 'Featured Image', 'Banner', $content );
            $content = str_replace( 'Set featured image', 'Definir banner', $content );
            $content = str_replace( 'Remove featured image', 'Remover banner', $content );
        }
        
        return $content;
    }
}
