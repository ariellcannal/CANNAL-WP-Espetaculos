<?php
/**
 * Template para exibir o arquivo de espetáculos.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/templates
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <header class="page-header">
            <?php
            if ( is_tax( 'espetaculo_categoria' ) ) {
                single_term_title( '<h1 class="page-title">', '</h1>' );
                the_archive_description( '<div class="archive-description">', '</div>' );
            } else {
                echo '<h1 class="page-title">Espetáculos</h1>';
            }
            ?>
        </header>

        <?php if ( have_posts() ) : ?>

            <div class="espetaculos-grid">
                <?php
                while ( have_posts() ) :
                    the_post();
                ?>

                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'espetaculo-card' ); ?>>
                        
                        <?php if ( has_post_thumbnail() ) : ?>
                        <div class="espetaculo-card-image">
                            <a href="<?php echo esc_url( Cannal_Espetaculos_Rewrites::get_espetaculo_url( get_the_ID() ) ); ?>">
                                <?php the_post_thumbnail( 'medium' ); ?>
                            </a>
                        </div>
                        <?php endif; ?>

                        <div class="espetaculo-card-content">
                            <h2 class="espetaculo-card-title">
                                <a href="<?php echo esc_url( Cannal_Espetaculos_Rewrites::get_espetaculo_url( get_the_ID() ) ); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h2>

                            <?php
                            $autor = get_post_meta( get_the_ID(), '_espetaculo_autor', true );
                            if ( $autor ) :
                            ?>
                            <p class="espetaculo-card-autor">Por <?php echo esc_html( $autor ); ?></p>
                            <?php endif; ?>

                            <?php if ( has_excerpt() ) : ?>
                            <div class="espetaculo-card-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            <?php endif; ?>

                            <?php
                            // Verificar se está em cartaz
                            $temporadas_em_cartaz = Cannal_Espetaculos_Public::get_temporadas_by_status( get_the_ID(), 'em_cartaz' );
                            if ( ! empty( $temporadas_em_cartaz ) ) :
                            ?>
                            <span class="espetaculo-badge em-cartaz">Em Cartaz</span>
                            <?php endif; ?>

                            <a href="<?php echo esc_url( Cannal_Espetaculos_Rewrites::get_espetaculo_url( get_the_ID() ) ); ?>" class="espetaculo-card-link">
                                Ver mais
                            </a>
                        </div>

                    </article>

                <?php endwhile; ?>
            </div>

            <?php
            the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => __( '&laquo; Anterior', 'cannal-espetaculos' ),
                'next_text' => __( 'Próximo &raquo;', 'cannal-espetaculos' ),
            ) );
            ?>

        <?php else : ?>

            <p>Nenhum espetáculo encontrado.</p>

        <?php endif; ?>

    </main>
</div>

<?php
get_footer();
