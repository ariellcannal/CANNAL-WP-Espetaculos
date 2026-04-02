<?php
/**
 * Template: Widget CANNAL - Próximas Apresentações
 *
 * Variáveis disponíveis:
 * @var string  $titulo     Título do widget (h3)
 * @var array   $temporadas Array de WP_Post de temporadas futuras
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( empty( $temporadas ) ) return;
?>
<div class="widget cannal-widget-temporada">
    <h3 class="widget-title"><?php echo esc_html( $titulo ); ?></h3>
    <div class="cannal-temporada-info">
        <?php foreach ( $temporadas as $temporada ) : ?>
            <?php
            $teatro_nome         = get_post_meta( $temporada->ID, '_temporada_teatro_nome', true );
            $data_inicio         = get_post_meta( $temporada->ID, '_temporada_data_inicio', true );
            $data_inicio_fmt     = $data_inicio ? date_i18n( 'd/m/Y', strtotime( $data_inicio ) ) : '';
            $link_vendas         = get_post_meta( $temporada->ID, '_temporada_link_vendas', true );
            $link_texto          = get_post_meta( $temporada->ID, '_temporada_link_texto', true );
            ?>
            <div class="cannal-info-box">
                <p>
                    <strong><?php echo esc_html( $teatro_nome ); ?></strong><br>
                    <?php if ( $data_inicio_fmt ) : ?>
                        <?php esc_html_e( 'Início:', 'cannal-espetaculos' ); ?> <?php echo esc_html( $data_inicio_fmt ); ?>
                    <?php endif; ?>
                </p>
                <?php if ( $link_vendas ) : ?>
                <p>
                    <a href="<?php echo esc_url( $link_vendas ); ?>" class="button-ingressos" target="_blank" rel="noopener">
                        <?php echo esc_html( ! empty( $link_texto ) ? $link_texto : __( 'Ingressos Aqui', 'cannal-espetaculos' ) ); ?>
                    </a>
                </p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
