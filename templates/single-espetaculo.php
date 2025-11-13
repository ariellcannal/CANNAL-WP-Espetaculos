<?php
/**
 * Template para exibir um único espetáculo.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/templates
 */

get_header();

// Buscar temporada ativa (em cartaz ou futura mais próxima)
$temporadas = get_posts( array(
    'post_type' => 'temporada',
    'posts_per_page' => 1,
    'meta_key' => '_temporada_espetaculo_id',
    'meta_value' => get_the_ID(),
    'meta_query' => array(
        'relation' => 'OR',
        array(
            'key' => '_temporada_data_fim',
            'value' => date('Y-m-d'),
            'compare' => '>=',
            'type' => 'DATE'
        ),
        array(
            'key' => '_temporada_data_fim',
            'value' => '',
            'compare' => '='
        )
    ),
    'orderby' => 'meta_value',
    'order' => 'ASC'
) );

$temporada_ativa = ! empty( $temporadas ) ? $temporadas[0] : null;

// Função para formatar dias e horários de forma legível
function format_dias_horarios_legivel( $sessoes ) {
    if ( empty( $sessoes ) ) return '';
    
    $output = '';
    
    if ( $sessoes['tipo'] === 'avulsas' && ! empty( $sessoes['avulsas'] ) ) {
        // Agrupar por mês
        $por_mes = array();
        foreach ( $sessoes['avulsas'] as $sessao ) {
            $mes = date_i18n( 'F', strtotime( $sessao['data'] ) ); // Nome do mês
            $dia = date_i18n( 'j', strtotime( $sessao['data'] ) ); // Dia sem zero à esquerda
            $horario = $sessao['horario'];
            
            if ( ! isset( $por_mes[$mes] ) ) {
                $por_mes[$mes] = array();
            }
            
            $por_mes[$mes][] = array( 'dia' => $dia, 'horario' => $horario );
        }
        
        $partes = array();
        foreach ( $por_mes as $mes => $datas ) {
            $dias = array_unique( array_column( $datas, 'dia' ) );
            sort( $dias );
            
            if ( count( $dias ) === 1 ) {
                $dias_texto = $dias[0];
            } elseif ( count( $dias ) === 2 ) {
                $dias_texto = $dias[0] . ' e ' . $dias[1];
            } else {
                $ultimo = array_pop( $dias );
                $dias_texto = implode( ', ', $dias ) . ' e ' . $ultimo;
            }
            
            $partes[] = $dias_texto . ' de ' . $mes;
        }
        
        $output = implode( ', ', $partes );
        
    } elseif ( $sessoes['tipo'] === 'temporada' && ! empty( $sessoes['temporada'] ) ) {
        // Agrupar por horário
        $por_horario = array();
        $dias_semana_map = array(
            'domingo' => 'dom',
            'segunda' => 'seg',
            'terca' => 'ter',
            'quarta' => 'qua',
            'quinta' => 'qui',
            'sexta' => 'sex',
            'sabado' => 'sáb'
        );
        
        foreach ( $sessoes['temporada'] as $dia => $horarios_str ) {
            if ( empty( $horarios_str ) ) continue;
            
            $dia_abrev = isset( $dias_semana_map[$dia] ) ? $dias_semana_map[$dia] : $dia;
            
            if ( ! isset( $por_horario[$horarios_str] ) ) {
                $por_horario[$horarios_str] = array();
            }
            
            $por_horario[$horarios_str][] = $dia_abrev;
        }
        
        $partes = array();
        foreach ( $por_horario as $horarios => $dias ) {
            if ( count( $dias ) === 1 ) {
                $dias_texto = $dias[0];
            } elseif ( count( $dias ) === 2 ) {
                $dias_texto = $dias[0] . ' e ' . $dias[1];
            } else {
                // Verificar se são dias consecutivos
                $dias_ordem = array( 'dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sáb' );
                $indices = array();
                foreach ( $dias as $dia ) {
                    $indices[] = array_search( $dia, $dias_ordem );
                }
                sort( $indices );
                
                $consecutivos = true;
                for ( $i = 1; $i < count( $indices ); $i++ ) {
                    if ( $indices[$i] !== $indices[$i-1] + 1 ) {
                        $consecutivos = false;
                        break;
                    }
                }
                
                if ( $consecutivos ) {
                    $dias_texto = $dias_ordem[$indices[0]] . ' a ' . $dias_ordem[$indices[count($indices)-1]];
                } else {
                    $ultimo = array_pop( $dias );
                    $dias_texto = implode( ', ', $dias ) . ' e ' . $ultimo;
                }
            }
            
            $partes[] = $dias_texto . ' às ' . $horarios;
        }
        
        $output = implode( ', ', $partes );
    }
    
    return $output;
}
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main espetaculo-single">

        <?php
        while ( have_posts() ) :
            the_post();
            
            $autor = get_post_meta( get_the_ID(), '_espetaculo_autor', true );
            $ano_estreia = get_post_meta( get_the_ID(), '_espetaculo_ano_estreia', true );
            $duracao = get_post_meta( get_the_ID(), '_espetaculo_duracao', true );
            $classificacao = get_post_meta( get_the_ID(), '_espetaculo_classificacao', true );
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                
                <div class="espetaculo-layout">
                    <!-- Conteúdo Principal -->
                    <div class="espetaculo-content">
                        
                        <header class="entry-header">
                            <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                            
                            <?php if ( $autor || $ano_estreia ) : ?>
                            <div class="espetaculo-meta">
                                <?php if ( $autor ) : ?>
                                    <span class="espetaculo-autor">Por <?php echo esc_html( $autor ); ?></span>
                                <?php endif; ?>
                                <?php if ( $ano_estreia ) : ?>
                                    <span class="espetaculo-ano"><?php echo esc_html( $ano_estreia ); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </header>

                        <?php if ( has_post_thumbnail() ) : ?>
                        <div class="espetaculo-featured-image">
                            <?php the_post_thumbnail( 'large' ); ?>
                        </div>
                        <?php endif; ?>

                        <div class="entry-content">
                            <?php 
                            // Priorizar release da temporada se existir
                            if ( $temporada_ativa && ! empty( $temporada_ativa->post_content ) ) {
                                echo apply_filters( 'the_content', $temporada_ativa->post_content );
                            } else {
                                the_content();
                            }
                            ?>
                        </div>

                        <?php
                        // Exibir galeria de fotos
                        $galeria = get_post_meta( get_the_ID(), '_espetaculo_galeria', true );
                        if ( ! empty( $galeria ) ) :
                            $galeria_ids = explode( ',', $galeria );
                        ?>
                        <div class="espetaculo-galeria">
                            <h2>Galeria de Fotos</h2>
                            <div class="espetaculo-galeria-grid">
                                <?php foreach ( $galeria_ids as $image_id ) : 
                                    $image_url = wp_get_attachment_image_url( $image_id, 'medium' );
                                    $image_full = wp_get_attachment_image_url( $image_id, 'full' );
                                    if ( $image_url ) :
                                ?>
                                <a href="<?php echo esc_url( $image_full ); ?>" class="espetaculo-galeria-item" data-lightbox="galeria">
                                    <img src="<?php echo esc_url( $image_url ); ?>" alt="" />
                                </a>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php
                        // Exibir tags
                        $tags = get_the_tags();
                        if ( $tags ) :
                        ?>
                        <div class="espetaculo-tags">
                            <h3>Tags</h3>
                            <?php the_tags( '<ul class="tag-list"><li>', '</li><li>', '</li></ul>' ); ?>
                        </div>
                        <?php endif; ?>

                    </div>

                    <!-- Sidebar com Informações da Temporada -->
                    <aside class="espetaculo-sidebar">
                        <?php if ( $temporada_ativa ) : 
                            $teatro_nome = get_post_meta( $temporada_ativa->ID, '_temporada_teatro_nome', true );
                            $teatro_endereco = get_post_meta( $temporada_ativa->ID, '_temporada_teatro_endereco', true );
                            $valores = get_post_meta( $temporada_ativa->ID, '_temporada_valores', true );
                            $link_vendas = get_post_meta( $temporada_ativa->ID, '_temporada_link_vendas', true );
                            $link_texto = get_post_meta( $temporada_ativa->ID, '_temporada_link_texto', true );
                            $sessoes_data = get_post_meta( $temporada_ativa->ID, '_temporada_sessoes_data', true );
                            
                            // Decodificar sessões
                            $sessoes = ! empty( $sessoes_data ) ? json_decode( $sessoes_data, true ) : null;
                            $dias_horarios = format_dias_horarios_legivel( $sessoes );
                        ?>
                        
                        <!-- Informações da Temporada -->
                        <div class="espetaculo-info-box">
                            <h3>Informações</h3>
                            
                            <?php if ( $teatro_nome || $teatro_endereco ) : ?>
                            <div class="info-item">
                                <strong>Teatro:</strong>
                                <div>
                                    <?php if ( $teatro_nome ) : ?>
                                        <?php echo esc_html( $teatro_nome ); ?><br/>
                                    <?php endif; ?>
                                    <?php if ( $teatro_endereco ) : ?>
                                        <?php echo esc_html( $teatro_endereco ); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ( $dias_horarios ) : ?>
                            <div class="info-item">
                                <strong>Dias e Horários:</strong>
                                <span><?php echo esc_html( $dias_horarios ); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ( $duracao ) : ?>
                            <div class="info-item">
                                <strong>Duração:</strong>
                                <span><?php echo esc_html( $duracao ); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ( $classificacao ) : ?>
                            <div class="info-item classificacao-item">
                                <strong>Classificação Indicativa:</strong>
                                <div class="classificacao-selo classificacao-<?php echo esc_attr( strtolower( str_replace( ' ', '-', $classificacao ) ) ); ?>">
                                    <?php echo esc_html( $classificacao ); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ( $valores ) : ?>
                            <div class="info-item">
                                <strong>Valores:</strong>
                                <div><?php echo nl2br( esc_html( $valores ) ); ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ( $link_vendas ) : ?>
                            <div class="info-item-cta">
                                <a href="<?php echo esc_url( $link_vendas ); ?>" class="btn-comprar-ingressos" target="_blank" rel="noopener">
                                    <?php echo esc_html( $link_texto ? $link_texto : 'Comprar Ingressos' ); ?>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php else : ?>
                        
                        <!-- Informações do Espetáculo (quando não há temporada ativa) -->
                        <div class="espetaculo-info-box">
                            <h3>Informações</h3>
                            
                            <?php if ( $duracao ) : ?>
                            <div class="info-item">
                                <strong>Duração:</strong>
                                <span><?php echo esc_html( $duracao ); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ( $classificacao ) : ?>
                            <div class="info-item classificacao-item">
                                <strong>Classificação Indicativa:</strong>
                                <div class="classificacao-selo classificacao-<?php echo esc_attr( strtolower( str_replace( ' ', '-', $classificacao ) ) ); ?>">
                                    <?php echo esc_html( $classificacao ); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php endif; ?>
                    </aside>
                </div>

            </article>

        <?php
        endwhile;
        ?>

    </main>
</div>

<style>
.espetaculo-layout {
    display: flex;
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.espetaculo-content {
    flex: 1;
    min-width: 0;
}

.espetaculo-sidebar {
    width: 350px;
    flex-shrink: 0;
}

.espetaculo-info-box {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    position: sticky;
    top: 20px;
}

.espetaculo-info-box h3 {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 1.4em;
    border-bottom: 2px solid #333;
    padding-bottom: 10px;
}

.info-item {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.info-item:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.info-item strong {
    display: block;
    margin-bottom: 5px;
    color: #666;
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-item-cta {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 2px solid #333;
}

.btn-comprar-ingressos {
    display: block;
    background: #e74c3c;
    color: white !important;
    text-align: center;
    padding: 15px 20px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    font-size: 1.1em;
    transition: background 0.3s;
}

.btn-comprar-ingressos:hover {
    background: #c0392b;
}

.classificacao-item {
    display: flex;
    flex-direction: column;
}

.classificacao-selo {
    display: inline-block;
    padding: 8px 15px;
    border-radius: 5px;
    font-weight: bold;
    text-align: center;
    margin-top: 5px;
    color: white;
    background: #2c3e50;
}

.classificacao-livre {
    background: #27ae60;
}

.classificacao-10-anos {
    background: #3498db;
}

.classificacao-12-anos {
    background: #f39c12;
}

.classificacao-14-anos {
    background: #e67e22;
}

.classificacao-16-anos {
    background: #e74c3c;
}

.classificacao-18-anos {
    background: #c0392b;
}

.espetaculo-galeria-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.espetaculo-galeria-item img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 5px;
    transition: transform 0.3s;
}

.espetaculo-galeria-item:hover img {
    transform: scale(1.05);
}

@media (max-width: 768px) {
    .espetaculo-layout {
        flex-direction: column;
    }
    
    .espetaculo-sidebar {
        width: 100%;
        position: static;
    }
}
</style>

<?php
get_footer();
