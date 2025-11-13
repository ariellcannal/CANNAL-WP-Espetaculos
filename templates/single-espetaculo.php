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
                            $data_inicio = get_post_meta( $temporada_ativa->ID, '_temporada_data_inicio', true );
                            $data_fim = get_post_meta( $temporada_ativa->ID, '_temporada_data_fim', true );
                            $valores = get_post_meta( $temporada_ativa->ID, '_temporada_valores', true );
                            $link_vendas = get_post_meta( $temporada_ativa->ID, '_temporada_link_vendas', true );
                            $link_texto = get_post_meta( $temporada_ativa->ID, '_temporada_link_texto', true );
                            $sessoes_data = get_post_meta( $temporada_ativa->ID, '_temporada_sessoes_data', true );
                            
                            // Decodificar sessões
                            $sessoes = ! empty( $sessoes_data ) ? json_decode( $sessoes_data, true ) : null;
                            $dias_horarios = '';
                            
                            if ( $sessoes ) {
                                if ( $sessoes['tipo'] === 'avulsas' && ! empty( $sessoes['avulsas'] ) ) {
                                    $dias_horarios = '<ul>';
                                    foreach ( $sessoes['avulsas'] as $sessao ) {
                                        $data_formatada = date_i18n( 'd/m/Y', strtotime( $sessao['data'] ) );
                                        $dias_horarios .= '<li>' . $data_formatada . ' às ' . $sessao['horario'] . '</li>';
                                    }
                                    $dias_horarios .= '</ul>';
                                } elseif ( $sessoes['tipo'] === 'temporada' && ! empty( $sessoes['temporada'] ) ) {
                                    $dias_semana_labels = array(
                                        'domingo' => 'Domingo',
                                        'segunda' => 'Segunda',
                                        'terca' => 'Terça',
                                        'quarta' => 'Quarta',
                                        'quinta' => 'Quinta',
                                        'sexta' => 'Sexta',
                                        'sabado' => 'Sábado'
                                    );
                                    $dias_horarios = '<ul>';
                                    foreach ( $sessoes['temporada'] as $dia => $horarios ) {
                                        if ( ! empty( $horarios ) ) {
                                            $label = isset( $dias_semana_labels[$dia] ) ? $dias_semana_labels[$dia] : ucfirst($dia);
                                            $dias_horarios .= '<li>' . $label . ': ' . esc_html( $horarios ) . '</li>';
                                        }
                                    }
                                    $dias_horarios .= '</ul>';
                                }
                            }
                        ?>
                        
                        <div class="temporada-info-box">
                            <h3>Informações da Temporada</h3>
                            
                            <?php if ( $teatro_nome ) : ?>
                            <div class="info-item">
                                <strong>Teatro:</strong>
                                <span><?php echo esc_html( $teatro_nome ); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ( $teatro_endereco ) : ?>
                            <div class="info-item">
                                <strong>Endereço:</strong>
                                <span><?php echo esc_html( $teatro_endereco ); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ( $data_inicio || $data_fim ) : ?>
                            <div class="info-item">
                                <strong>Período:</strong>
                                <span>
                                    <?php 
                                    if ( $data_inicio ) echo date_i18n( 'd/m/Y', strtotime( $data_inicio ) );
                                    if ( $data_inicio && $data_fim ) echo ' a ';
                                    if ( $data_fim ) echo date_i18n( 'd/m/Y', strtotime( $data_fim ) );
                                    ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ( $dias_horarios ) : ?>
                            <div class="info-item">
                                <strong>Dias e Horários:</strong>
                                <?php echo $dias_horarios; ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ( $duracao ) : ?>
                            <div class="info-item">
                                <strong>Duração:</strong>
                                <span><?php echo esc_html( $duracao ); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ( $classificacao ) : ?>
                            <div class="info-item">
                                <strong>Classificação:</strong>
                                <span><?php echo esc_html( $classificacao ); ?></span>
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
                                <a href="<?php echo esc_url( $link_vendas ); ?>" class="btn-comprar" target="_blank" rel="noopener">
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
                            <div class="info-item">
                                <strong>Classificação:</strong>
                                <span><?php echo esc_html( $classificacao ); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php endif; ?>
                    </aside>
                </div>

            </article>

        <?php endwhile; ?>

    </main>
</div>

<style>
.espetaculo-layout {
    display: flex;
    gap: 40px;
    margin: 20px 0;
}

.espetaculo-content {
    flex: 1;
    min-width: 0;
}

.espetaculo-sidebar {
    width: 350px;
    flex-shrink: 0;
}

.temporada-info-box,
.espetaculo-info-box {
    background: #f9f9f9;
    border: 1px solid #ddd;
    padding: 20px;
    border-radius: 8px;
    position: sticky;
    top: 20px;
}

.temporada-info-box h3,
.espetaculo-info-box h3 {
    margin: 0 0 20px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #333;
    font-size: 1.2em;
}

.info-item {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.info-item:last-child {
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

.info-item ul {
    margin: 5px 0 0 0;
    padding-left: 20px;
}

.info-item ul li {
    margin-bottom: 5px;
}

.info-item-cta {
    margin-top: 20px;
    text-align: center;
}

.btn-comprar {
    display: inline-block;
    background: #e74c3c;
    color: white !important;
    padding: 15px 30px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    transition: background 0.3s;
}

.btn-comprar:hover {
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
    }
    
    .temporada-info-box,
    .espetaculo-info-box {
        position: static;
    }
}
</style>

<?php
get_footer();
