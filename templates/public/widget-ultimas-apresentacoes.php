<?php
/**
 * Template: Widget CANNAL - Próximas Apresentações
 *
 * Variáveis disponíveis:
 * @var string  $titulo         Título do widget (h3)
 * @var array   $temporadas     Array de WP_Post de temporadas futuras
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
        $data_fim = get_post_meta($temporada->ID, '_temporada_data_fim', true);
        $data_fim_fmt = $data_fim ? date_i18n('d/m/Y', strtotime($data_fim)) : '';
        $data_inicio = get_post_meta($temporada->ID, '_temporada_data_inicio', true);
        $data_inicio_fmt = $data_inicio ? date_i18n('d/m/Y', strtotime($data_inicio)) : '';
        $tipo_sessao = get_post_meta($temporada->ID, '_temporada_tipo_sessao', true);
        ?>
        <div class="info-item">
        	<strong><?php echo esc_html( $teatro_nome ); ?></strong>
        	<?php if($tipo_sessao == "temporada"):?>
        	<span><?php echo sprintf(__('de %s até %s','cannal-espetaculos'),$data_inicio_fmt, $data_fim_fmt)?></span>
        	<?php elseif ($tipo_sessao == "avulsas"):?>
        	<span><?php echo $data_fim_fmt?></span>
        	<?php endif;?>
		</div>        
    <?php endforeach; ?>
</div>
