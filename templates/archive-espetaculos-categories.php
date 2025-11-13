<?php
/**
 * Template para exibir o arquivo de categorias de espetáculos.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/templates
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <header class="page-header">
            <h1 class="page-title">Categorias de Espetáculos</h1>
        </header>

        <?php
        $terms = get_terms( array(
            'taxonomy' => 'espetaculo_categoria',
            'hide_empty' => true,
        ) );

        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) :
        ?>

            <div class="categorias-grid">
                <?php foreach ( $terms as $term ) : ?>

                    <div class="categoria-card">
                        <h2 class="categoria-title">
                            <a href="<?php echo esc_url( Cannal_Espetaculos_Rewrites::get_categoria_url( $term->slug ) ); ?>">
                                <?php echo esc_html( $term->name ); ?>
                            </a>
                        </h2>

                        <?php if ( ! empty( $term->description ) ) : ?>
                        <div class="categoria-description">
                            <?php echo wp_kses_post( $term->description ); ?>
                        </div>
                        <?php endif; ?>

                        <p class="categoria-count">
                            <?php printf( _n( '%s espetáculo', '%s espetáculos', $term->count, 'cannal-espetaculos' ), number_format_i18n( $term->count ) ); ?>
                        </p>

                        <a href="<?php echo esc_url( Cannal_Espetaculos_Rewrites::get_categoria_url( $term->slug ) ); ?>" class="categoria-link">
                            Ver espetáculos
                        </a>
                    </div>

                <?php endforeach; ?>
            </div>

        <?php else : ?>

            <p>Nenhuma categoria encontrada.</p>

        <?php endif; ?>

    </main>
</div>

<?php
get_footer();
