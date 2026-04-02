<?php
/**
 * Template: Widget CANNAL - Últimas Apresentações
 *
 * Variáveis disponíveis:
 * @var string  $titulo     Título do widget (h3)
 * @var array   $temporadas Array de WP_Post de temporadas encerradas
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( empty( $temporadas ) ) return;
?>
<div class="widget cannal-widget-temporada">
    <h3 class="widget-title"><?php echo esc_html( $titulo ); ?></h3>
    <div class="cannal-temporada-info">
        <?php foreach ( $temporadas as $temporada ) : ?>
            <?php
            $teatro_nome     = get_post_meta( $temporada->ID, '_temporada_teatro_nome', true );
            $data_fim        = get_post_meta( $temporada->ID, '_temporada_data_fim', true );
            $data_fim_fmt    = $data_fim ? date_i18n( 'd/m/Y', strtotime( $data_fim ) ) : '';
            ?>
            <div class="cannal-info-box">
                <p>
                    <strong><?php echo esc_html( $teatro_nome ); ?></strong><br>
                    <?php if ( $data_fim_fmt ) : ?>
                        <?php esc_html_e( 'Término:', 'cannal-espetaculos' ); ?> <?php echo esc_html( $data_fim_fmt ); ?>
                    <?php endif; ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>
</div>
