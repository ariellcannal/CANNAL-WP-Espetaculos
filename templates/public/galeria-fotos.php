<?php
/**
 * Template: Galeria de Fotos do Espetáculo
 *
 * Variáveis disponíveis:
 * @var array $imagens  Array de arrays com chaves: url_thumb, url_full, alt
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="cannal-galeria-fotos">
    <h3><?php esc_html_e( 'Galeria de Fotos', 'cannal-espetaculos' ); ?></h3>
    <div class="cannal-galeria-grid">
        <?php foreach ( $imagens as $imagem ) : ?>
            <div class="cannal-galeria-item">
                <a href="<?php echo esc_url( $imagem['url_full'] ); ?>"
                   data-fancybox="galeria-espetaculo"
                   data-caption="<?php echo esc_attr( $imagem['alt'] ); ?>">
                    <img src="<?php echo esc_url( $imagem['url_thumb'] ); ?>"
                         alt="<?php echo esc_attr( $imagem['alt'] ); ?>"
                         loading="lazy">
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
