<?php
/**
 * A funcionalidade pública do plugin.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/public
 */

class Cannal_Espetaculos_Public {

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

        add_filter( 'template_include', array( $this, 'template_loader' ), 99 );
        add_filter( 'the_content', array( $this, 'espetaculo_content_filter' ) );
    }

    /**
     * Registra os arquivos de estilo para a área pública.
     */
    public function enqueue_styles() {
        if ( is_singular( 'espetaculo' ) || is_post_type_archive( 'espetaculo' ) || is_tax( 'espetaculo_categoria' ) ) {
            wp_enqueue_style( 
                $this->plugin_name, 
                CANNAL_ESPETACULOS_PLUGIN_URL . 'public/css/cannal-espetaculos-public.css', 
                array(), 
                $this->version, 
                'all' 
            );
        }
    }

    /**
     * Registra os arquivos JavaScript para a área pública.
     */
    public function enqueue_scripts() {
        if ( is_singular( 'espetaculo' ) || is_post_type_archive( 'espetaculo' ) || is_tax( 'espetaculo_categoria' ) ) {
            wp_enqueue_script( 
                $this->plugin_name, 
                CANNAL_ESPETACULOS_PLUGIN_URL . 'public/js/cannal-espetaculos-public.js', 
                array( 'jquery' ), 
                $this->version, 
                false 
            );
        }
    }

    /**
     * Carrega os templates personalizados.
     */
    public function template_loader( $template ) {
        
        if ( is_singular( 'espetaculo' ) ) {
            // Verificar se há um template de página personalizado definido
            $page_template = get_post_meta( get_the_ID(), '_wp_page_template', true );
            
            if ( $page_template && $page_template !== 'default' ) {
                $custom_template = locate_template( array( $page_template ) );
                if ( $custom_template ) {
                    return $custom_template;
                }
            }
            
            // Verificar se o tema tem um template single-espetaculo.php
            $theme_template = locate_template( array( 'single-espetaculo.php' ) );
            
            if ( $theme_template ) {
                return $theme_template;
            }
            
            // Usar o template do plugin
            return CANNAL_ESPETACULOS_PLUGIN_DIR . 'templates/single-espetaculo.php';
        }

        if ( is_post_type_archive( 'espetaculo' ) || is_tax( 'espetaculo_categoria' ) ) {
            // Verificar se é arquivo de categorias
            if ( get_query_var( 'espetaculos_archive' ) === 'categories' ) {
                $theme_template = locate_template( array( 'archive-espetaculos-categories.php' ) );
                
                if ( $theme_template ) {
                    return $theme_template;
                }
                
                return CANNAL_ESPETACULOS_PLUGIN_DIR . 'templates/archive-espetaculos-categories.php';
            }

            // Arquivo normal de espetáculos
            $theme_template = locate_template( array( 'archive-espetaculo.php' ) );
            
            if ( $theme_template ) {
                return $theme_template;
            }
            
            return CANNAL_ESPETACULOS_PLUGIN_DIR . 'templates/archive-espetaculo.php';
        }

        return $template;
    }

    /**
     * Filtra o conteúdo do espetáculo para exibir informações adicionais.
     */
    public function espetaculo_content_filter( $content ) {
        
        if ( ! is_singular( 'espetaculo' ) || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }

        global $post;

        // Verificar se está usando Elementor
        if ( class_exists( '\Elementor\Plugin' ) ) {
            $document = \Elementor\Plugin::$instance->documents->get( $post->ID );
            if ( $document && $document->is_built_with_elementor() ) {
                return $content; // Deixar o Elementor gerenciar o conteúdo
            }
        }

        // Obter a temporada ativa ou mais recente
        $temporada = $this->get_active_temporada( $post->ID );

        // Se houver temporada com conteúdo, usar o conteúdo da temporada
        if ( $temporada && ! empty( $temporada->post_content ) ) {
            $content = apply_filters( 'the_content', $temporada->post_content );
        }

        // Adicionar sidebar com informações
        $sidebar = $this->get_espetaculo_sidebar( $post->ID, $temporada );

        return '<div class="espetaculo-content-wrapper"><div class="espetaculo-main-content">' . $content . '</div>' . $sidebar . '</div>';
    }

    /**
     * Obtém a temporada ativa ou mais recente de um espetáculo.
     */
    private function get_active_temporada( $espetaculo_id ) {
        $hoje = current_time( 'Y-m-d' );

        // Buscar temporada em cartaz
        $temporadas = get_posts( array(
            'post_type' => 'temporada',
            'posts_per_page' => 1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_temporada_espetaculo_id',
                    'value' => $espetaculo_id,
                    'compare' => '='
                ),
                array(
                    'key' => '_temporada_data_inicio',
                    'value' => $hoje,
                    'compare' => '<=',
                    'type' => 'DATE'
                ),
                array(
                    'key' => '_temporada_data_fim',
                    'value' => $hoje,
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            ),
            'orderby' => 'meta_value',
            'meta_key' => '_temporada_data_inicio',
            'order' => 'DESC'
        ) );

        if ( ! empty( $temporadas ) ) {
            return $temporadas[0];
        }

        // Se não houver em cartaz, buscar a mais recente
        $temporadas = get_posts( array(
            'post_type' => 'temporada',
            'posts_per_page' => 1,
            'meta_key' => '_temporada_espetaculo_id',
            'meta_value' => $espetaculo_id,
            'orderby' => 'meta_value',
            'meta_key' => '_temporada_data_inicio',
            'order' => 'DESC'
        ) );

        return ! empty( $temporadas ) ? $temporadas[0] : null;
    }

    /**
     * Gera a sidebar com informações do espetáculo.
     */
    private function get_espetaculo_sidebar( $espetaculo_id, $temporada ) {
        ob_start();
        ?>
        <aside class="espetaculo-sidebar">
            <?php if ( $temporada ) : 
                $teatro_nome = get_post_meta( $temporada->ID, '_temporada_teatro_nome', true );
                $teatro_endereco = get_post_meta( $temporada->ID, '_temporada_teatro_endereco', true );
                $dias_horarios = get_post_meta( $temporada->ID, '_temporada_dias_horarios', true );
                $tipo_sessao = get_post_meta( $temporada->ID, '_temporada_tipo_sessao', true );
            ?>

            <?php if ( $teatro_nome ) : ?>
            <div class="espetaculo-info-box">
                <h3>Teatro</h3>
                <p>
                    <strong><?php echo esc_html( $teatro_nome ); ?></strong><br>
                    <?php if ( $teatro_endereco ) : ?>
                        <?php echo esc_html( $teatro_endereco ); ?>
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>

            <?php if ( $dias_horarios ) : ?>
            <div class="espetaculo-info-box">
                <h3><?php echo $tipo_sessao === 'avulsas' ? 'Apresentações' : 'Temporada'; ?></h3>
                <p><?php echo esc_html( $dias_horarios ); ?></p>
            </div>
            <?php endif; ?>

            <?php endif; ?>

            <?php
            $duracao = get_post_meta( $espetaculo_id, '_espetaculo_duracao', true );
            if ( $duracao ) :
            ?>
            <div class="espetaculo-info-box">
                <h3>Duração</h3>
                <p><?php echo esc_html( $duracao ); ?></p>
            </div>
            <?php endif; ?>

            <?php
            $classificacao = get_post_meta( $espetaculo_id, '_espetaculo_classificacao', true );
            if ( $classificacao ) :
            ?>
            <div class="espetaculo-info-box">
                <h3>Classificação Indicativa</h3>
                <div class="classificacao-selo classificacao-<?php echo esc_attr( $classificacao ); ?>">
                    <?php echo esc_html( $classificacao === 'livre' ? 'Livre' : $classificacao . ' anos' ); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php
            if ( $temporada ) {
                $link_vendas = get_post_meta( $temporada->ID, '_temporada_link_vendas', true );
                $link_texto = get_post_meta( $temporada->ID, '_temporada_link_texto', true );
                
                if ( $link_vendas ) :
                    $texto_botao = ! empty( $link_texto ) ? $link_texto : 'Ingressos Aqui';
            ?>
            <div class="espetaculo-info-box">
                <a href="<?php echo esc_url( $link_vendas ); ?>" class="button-ingressos" target="_blank" rel="noopener">
                    <?php echo esc_html( $texto_botao ); ?>
                </a>
            </div>
            <?php 
                endif;
            }
            ?>
        </aside>
        <?php
        return ob_get_clean();
    }

    /**
     * Obtém temporadas de um espetáculo por status.
     */
    public static function get_temporadas_by_status( $espetaculo_id, $status = 'em_cartaz' ) {
        $hoje = current_time( 'Y-m-d' );
        $meta_query = array(
            array(
                'key' => '_temporada_espetaculo_id',
                'value' => $espetaculo_id,
                'compare' => '='
            )
        );

        switch ( $status ) {
            case 'em_cartaz':
                $meta_query[] = array(
                    'key' => '_temporada_data_inicio',
                    'value' => $hoje,
                    'compare' => '<=',
                    'type' => 'DATE'
                );
                $meta_query[] = array(
                    'key' => '_temporada_data_fim',
                    'value' => $hoje,
                    'compare' => '>=',
                    'type' => 'DATE'
                );
                break;

            case 'futuras':
                $meta_query[] = array(
                    'key' => '_temporada_data_inicio',
                    'value' => $hoje,
                    'compare' => '>',
                    'type' => 'DATE'
                );
                break;

            case 'encerradas':
                $meta_query[] = array(
                    'key' => '_temporada_data_fim',
                    'value' => $hoje,
                    'compare' => '<',
                    'type' => 'DATE'
                );
                break;
        }

        return get_posts( array(
            'post_type' => 'temporada',
            'posts_per_page' => -1,
            'meta_query' => $meta_query,
            'orderby' => 'meta_value',
            'meta_key' => '_temporada_data_inicio',
            'order' => $status === 'encerradas' ? 'DESC' : 'ASC'
        ) );
    }
}
