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
        add_action( 'dynamic_sidebar_before', array( $this, 'inject_temporada_info' ) );
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
            
            // Usar template padrão de página
            $page_template_file = locate_template( array( 'page.php', 'singular.php', 'index.php' ) );
            if ( $page_template_file ) {
                return $page_template_file;
            }
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
     * Filtra o conteúdo do espetáculo para exibir galeria ao final.
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

        // Verificar se a galeria está ativada
        $exibir_galeria = get_post_meta( $post->ID, '_espetaculo_exibir_galeria', true );
        
        // Padrão é exibir (se campo não existe ou está vazio, exibe)
        if ( $exibir_galeria === '' || $exibir_galeria === '1' || $exibir_galeria === 'sim' ) {
            $galeria_ids = get_post_meta( $post->ID, '_espetaculo_galeria', true );
            
            if ( ! empty( $galeria_ids ) ) {
                $content .= $this->render_galeria( $galeria_ids );
            }
        }

        return $content;
    }

    /**
     * Renderiza a galeria de fotos em grid.
     */
    private function render_galeria( $galeria_ids ) {
        $ids = explode( ',', $galeria_ids );
        
        if ( empty( $ids ) ) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="cannal-galeria-fotos">
            <h3>Galeria de Fotos</h3>
            <div class="cannal-galeria-grid">
                <?php foreach ( $ids as $attachment_id ) : 
                    $attachment_id = trim( $attachment_id );
                    if ( empty( $attachment_id ) ) continue;
                    
                    $image_url = wp_get_attachment_image_url( $attachment_id, 'medium' );
                    $image_full = wp_get_attachment_image_url( $attachment_id, 'full' );
                    $image_alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
                    
                    if ( ! $image_url ) continue;
                ?>
                <div class="cannal-galeria-item">
                    <a href="<?php echo esc_url( $image_full ); ?>" data-lightbox="galeria-espetaculo">
                        <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" loading="lazy">
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <style>
        .cannal-galeria-fotos {
            margin: 40px 0;
        }
        
        .cannal-galeria-fotos h3 {
            margin-bottom: 20px;
            font-size: 1.5em;
        }
        
        .cannal-galeria-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .cannal-galeria-item {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            aspect-ratio: 1;
        }
        
        .cannal-galeria-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .cannal-galeria-item:hover img {
            transform: scale(1.05);
        }
        
        @media (max-width: 768px) {
            .cannal-galeria-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 10px;
            }
        }
        </style>
        <?php
        return ob_get_clean();
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

    /**
     * Injeta informações da temporada no início da sidebar.
     */
    public function inject_temporada_info() {
        // Verificar se é single de espetáculo
        if ( ! is_singular( 'espetaculo' ) ) {
            return;
        }

        global $post;
        $espetaculo_id = $post->ID;

        // Obter temporada ativa
        $temporada = $this->get_active_temporada( $espetaculo_id );

        if ( $temporada ) {
            // Exibir informações da temporada ativa
            $this->render_temporada_ativa( $espetaculo_id, $temporada );
        } else {
            // Buscar próximas temporadas
            $proximas = $this->get_proximas_temporadas( $espetaculo_id, 3 );
            
            if ( ! empty( $proximas ) ) {
                $this->render_proximas_temporadas( $proximas );
            } else {
                // Buscar últimas temporadas
                $ultimas = $this->get_ultimas_temporadas( $espetaculo_id, 3 );
                
                if ( ! empty( $ultimas ) ) {
                    $this->render_ultimas_temporadas( $ultimas );
                }
            }
        }
    }

    /**
     * Renderiza informações da temporada ativa.
     */
    private function render_temporada_ativa( $espetaculo_id, $temporada ) {
        $teatro_nome = get_post_meta( $temporada->ID, '_temporada_teatro_nome', true );
        $teatro_endereco = get_post_meta( $temporada->ID, '_temporada_teatro_endereco', true );
        $dias_horarios = get_post_meta( $temporada->ID, '_temporada_dias_horarios', true );
        $tipo_sessao = get_post_meta( $temporada->ID, '_temporada_tipo_sessao', true );
        $duracao = get_post_meta( $espetaculo_id, '_espetaculo_duracao', true );
        $classificacao = get_post_meta( $espetaculo_id, '_espetaculo_classificacao', true );
        $link_vendas = get_post_meta( $temporada->ID, '_temporada_link_vendas', true );
        $link_texto = get_post_meta( $temporada->ID, '_temporada_link_texto', true );
        ?>
        <div class="widget cannal-widget-temporada">
            <div class="cannal-temporada-info">
            <?php if ( $teatro_nome ) : ?>
            <div class="cannal-info-box">
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
            <div class="cannal-info-box">
                <h3><?php echo $tipo_sessao === 'avulsas' ? 'Apresentações' : 'Temporada'; ?></h3>
                <p><?php echo esc_html( $dias_horarios ); ?></p>
            </div>
            <?php endif; ?>

            <?php if ( $duracao ) : ?>
            <div class="cannal-info-box">
                <h3>Duração</h3>
                <p><?php echo esc_html( $duracao ); ?></p>
            </div>
            <?php endif; ?>

            <?php if ( $classificacao ) : ?>
            <div class="cannal-info-box">
                <h3>Classificação Indicativa</h3>
                <div class="classificacao-selo classificacao-<?php echo esc_attr( $classificacao ); ?>">
                    <?php echo esc_html( $classificacao === 'livre' ? 'Livre' : $classificacao . ' anos' ); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ( $link_vendas ) : ?>
            <div class="cannal-info-box">
                <a href="<?php echo esc_url( $link_vendas ); ?>" class="button-ingressos" target="_blank" rel="noopener">
                    <?php echo esc_html( ! empty( $link_texto ) ? $link_texto : 'Ingressos Aqui' ); ?>
                </a>
            </div>
            <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Renderiza próximas temporadas.
     */
    private function render_proximas_temporadas( $temporadas ) {
        ?>
        <div class="widget cannal-widget-temporada">
            <h3 class="widget-title">Próximas Temporadas</h3>
            <div class="cannal-temporada-info">
                <?php foreach ( $temporadas as $temporada ) : 
                    $teatro_nome = get_post_meta( $temporada->ID, '_temporada_teatro_nome', true );
                    $data_inicio = get_post_meta( $temporada->ID, '_temporada_data_inicio', true );
                    $data_inicio_formatada = date_i18n( 'd/m/Y', strtotime( $data_inicio ) );
                    $link_vendas = get_post_meta( $temporada->ID, '_temporada_link_vendas', true );
                    $link_texto = get_post_meta( $temporada->ID, '_temporada_link_texto', true );
                ?>
                <div class="cannal-info-box">
                    <p>
                        <strong><?php echo esc_html( $teatro_nome ); ?></strong><br>
                        Início: <?php echo esc_html( $data_inicio_formatada ); ?>
                    </p>
                    <?php if ( $link_vendas ) : ?>
                    <p>
                        <a href="<?php echo esc_url( $link_vendas ); ?>" class="button-ingressos" target="_blank" rel="noopener">
                            <?php echo esc_html( ! empty( $link_texto ) ? $link_texto : 'Ingressos Aqui' ); ?>
                        </a>
                    </p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Renderiza últimas temporadas.
     */
    private function render_ultimas_temporadas( $temporadas ) {
        ?>
        <div class="widget cannal-widget-temporada">
            <h3 class="widget-title">Últimas Temporadas</h3>
            <div class="cannal-temporada-info">
                <?php foreach ( $temporadas as $temporada ) : 
                    $teatro_nome = get_post_meta( $temporada->ID, '_temporada_teatro_nome', true );
                    $data_fim = get_post_meta( $temporada->ID, '_temporada_data_fim', true );
                    $data_fim_formatada = date_i18n( 'd/m/Y', strtotime( $data_fim ) );
                ?>
                <div class="cannal-info-box">
                    <p>
                        <strong><?php echo esc_html( $teatro_nome ); ?></strong><br>
                        Término: <?php echo esc_html( $data_fim_formatada ); ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Obtém as próximas temporadas.
     */
    private function get_proximas_temporadas( $espetaculo_id, $limit = 3 ) {
        $hoje = current_time( 'Y-m-d' );

        return get_posts( array(
            'post_type' => 'temporada',
            'posts_per_page' => $limit,
            'meta_query' => array(
                array(
                    'key' => '_temporada_espetaculo_id',
                    'value' => $espetaculo_id,
                    'compare' => '='
                ),
                array(
                    'key' => '_temporada_data_inicio',
                    'value' => $hoje,
                    'compare' => '>',
                    'type' => 'DATE'
                )
            ),
            'orderby' => 'meta_value',
            'meta_key' => '_temporada_data_inicio',
            'order' => 'ASC'
        ) );
    }

    /**
     * Obtém as últimas temporadas.
     */
    private function get_ultimas_temporadas( $espetaculo_id, $limit = 3 ) {
        $hoje = current_time( 'Y-m-d' );

        return get_posts( array(
            'post_type' => 'temporada',
            'posts_per_page' => $limit,
            'meta_query' => array(
                array(
                    'key' => '_temporada_espetaculo_id',
                    'value' => $espetaculo_id,
                    'compare' => '='
                ),
                array(
                    'key' => '_temporada_data_fim',
                    'value' => $hoje,
                    'compare' => '<',
                    'type' => 'DATE'
                )
            ),
            'orderby' => 'meta_value',
            'meta_key' => '_temporada_data_fim',
            'order' => 'DESC'
        ) );
    }
}
