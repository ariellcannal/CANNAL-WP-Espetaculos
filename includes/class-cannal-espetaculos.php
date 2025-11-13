<?php
/**
 * A classe principal do plugin.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/includes
 */

class Cannal_Espetaculos {

    /**
     * O loader responsável por manter e registrar todos os hooks do plugin.
     */
    protected $loader;

    /**
     * O identificador único deste plugin.
     */
    protected $plugin_name;

    /**
     * A versão atual do plugin.
     */
    protected $version;

    /**
     * Define a funcionalidade principal do plugin.
     */
    public function __construct() {
        $this->version = CANNAL_ESPETACULOS_VERSION;
        $this->plugin_name = 'cannal-espetaculos';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Carrega as dependências necessárias para este plugin.
     */
    private function load_dependencies() {
        
        /**
         * A classe responsável por orquestrar as ações e filtros do plugin.
         */
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'includes/class-cannal-espetaculos-loader.php';

        /**
         * A classe responsável por registrar os post types personalizados.
         */
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'includes/class-cannal-espetaculos-post-types.php';

        /**
         * A classe responsável pelos campos personalizados.
         */
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'includes/class-cannal-espetaculos-meta-boxes.php';

        /**
         * A classe responsável pelas rewrite rules.
         */
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'includes/class-cannal-espetaculos-rewrites.php';

        /**
         * A classe responsável pela funcionalidade administrativa.
         */
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'admin/class-cannal-espetaculos-admin.php';

        /**
         * A classe responsável pela funcionalidade pública.
         */
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'public/class-cannal-espetaculos-public.php';

        /**
         * A classe responsável pelos widgets do Elementor.
         */
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'includes/class-cannal-espetaculos-elementor.php';

        /**
         * A classe responsável pela integração com RevSlider.
         */
        require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'includes/class-cannal-espetaculos-revslider.php';

        $this->loader = new Cannal_Espetaculos_Loader();
    }

    /**
     * Registra todos os hooks relacionados à área administrativa.
     */
    private function define_admin_hooks() {
        
        $plugin_admin = new Cannal_Espetaculos_Admin( $this->get_plugin_name(), $this->get_version() );
        
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        // Registrar post types e taxonomias
        $post_types = new Cannal_Espetaculos_Post_Types();
        $this->loader->add_action( 'init', $post_types, 'register_post_types' );
        $this->loader->add_action( 'init', $post_types, 'register_taxonomies' );

        // Registrar meta boxes
        $meta_boxes = new Cannal_Espetaculos_Meta_Boxes();
        $this->loader->add_action( 'add_meta_boxes', $meta_boxes, 'add_meta_boxes' );
        $this->loader->add_action( 'save_post', $meta_boxes, 'save_espetaculo_meta' );
        $this->loader->add_action( 'save_post', $meta_boxes, 'save_temporada_meta' );

        // Registrar rewrite rules
        $rewrites = new Cannal_Espetaculos_Rewrites();
        $this->loader->add_action( 'init', $rewrites, 'add_rewrite_rules' );
        $this->loader->add_filter( 'query_vars', $rewrites, 'add_query_vars' );
        $this->loader->add_action( 'template_redirect', $rewrites, 'handle_redirects' );

        // Monitorar mudanças em categorias
        $this->loader->add_action( 'created_espetaculo_categoria', $rewrites, 'on_category_change' );
        $this->loader->add_action( 'edited_espetaculo_categoria', $rewrites, 'on_category_change' );
        $this->loader->add_action( 'delete_espetaculo_categoria', $rewrites, 'on_category_change' );
    }

    /**
     * Registra todos os hooks relacionados à área pública.
     */
    private function define_public_hooks() {
        
        $plugin_public = new Cannal_Espetaculos_Public( $this->get_plugin_name(), $this->get_version() );
        
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        // Registrar widgets do Elementor
        $elementor = new Cannal_Espetaculos_Elementor();
        $this->loader->add_action( 'elementor/widgets/register', $elementor, 'register_widgets' );
        $this->loader->add_action( 'elementor/elements/categories_registered', $elementor, 'add_elementor_category' );
    }

    /**
     * Executa o loader para executar todos os hooks com o WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * O nome do plugin usado para identificá-lo exclusivamente no contexto do WordPress.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * A referência à classe que orquestra os hooks com o plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Recupera o número da versão do plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
