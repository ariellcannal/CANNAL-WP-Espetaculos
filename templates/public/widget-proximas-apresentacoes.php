<?php
/**
 * Template: Widget CANNAL - Próximas Apresentações
 *
 * Variáveis disponíveis:
 * @var string  $titulo     Título do widget (h3)
 * @var array   $temporadas Array de WP_Post de temporadas futuras
 */
if (! defined('ABSPATH'))
    exit();

if (empty($temporadas))
    return;
?>
<div class="espetaculo-sidebar">
	<h3><?php echo esc_html( $titulo ); ?></h3>
    <?php foreach ( $temporadas as $temporada ) : ?>
        <?php
        $teatro_nome = get_post_meta($temporada->ID, '_temporada_teatro_nome', true);
        $link_vendas = get_post_meta($temporada->ID, '_temporada_link_vendas', true);
        $link_texto = get_post_meta($temporada->ID, '_temporada_link_texto', true);
        $tipo_sessao = get_post_meta($temporada->ID, '_temporada_tipo_sessao', true);
        $sessoes_data = get_post_meta($temporada->ID, '_temporada_sessoes_data', true);
        $dias_horarios = CANNALEspetaculos_DiasHorarios::gerar($tipo_sessao,$sessoes_data);
        ?>
        <div class="info-item">
        	<strong><?php echo esc_html( $teatro_nome ); ?></strong>
        	<span><?php echo esc_html( $dias_horarios ); ?></span>
        	<?php if ( $link_vendas) : ?>
    	    <div class="info-item-cta wp-block-button">
                <a href="<?php echo esc_url( $link_vendas ); ?>" class="button button-small wp-block-button__link wp-element-button has-small-font-size" target="_blank" rel="noopener">
                    <?php echo esc_html( $link_texto ? $link_texto : __( 'Comprar Ingressos', 'cannal-espetaculos' ) ); ?>
                </a>
            </div>
            <?php endif; ?>
		</div>        
    <?php endforeach; ?>
</div>
