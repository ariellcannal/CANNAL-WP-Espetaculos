<?php
/**
 * Template para exibir um único espetáculo.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/templates
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php
        while ( have_posts() ) :
            the_post();
            ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                
                <header class="entry-header">
                    <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                    
                    <?php
                    $autor = get_post_meta( get_the_ID(), '_espetaculo_autor', true );
                    $ano_estreia = get_post_meta( get_the_ID(), '_espetaculo_ano_estreia', true );
                    
                    if ( $autor || $ano_estreia ) :
                    ?>
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
                    <?php the_content(); ?>
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

            </article>

        <?php endwhile; ?>

    </main>
</div>

<?php
get_footer();
