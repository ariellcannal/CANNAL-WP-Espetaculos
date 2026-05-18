<?php

/**
 * Template: Widget CANNAL - Próximas Apresentações
 *
 * Variáveis disponíveis:
 * @var string      $titulo         Título do widget (h3)
 * @var int         $espetaculo_id  ID do espetáculo
 * @var WP_Post|null $temporada     Temporada ativa (ou null)
 * @var string      $autor
 * @var string      $diretor
 * @var string      $elenco
 * @var string      $duracao
 * @var string      $ano_estreia
 * @var string      $classificacao
 * @var string      $classificacao_text
 * @var string      $tipo           Tipo de apresentações (próximas ou últimas)
 * @var array       $temporadas     Array de WP_Post de temporadas
 */
if (! defined('ABSPATH'))
    exit();

if (empty($temporadas))
    return;
?>
<div class="espetaculo-temporadas">
    <?php foreach ($temporadas as $temporada) : ?>
        <?php
        $status = CANNALEspetaculos_DiasHorarios::get_status_temporada($temporada);
        if($status == __('Sem datas', 'cannal-espetaculos')) {
            continue; // pular espetáculos sem datas
        }
        $teatro_nome = get_post_meta($temporada->ID, '_temporada_teatro_nome', true);
        $link_vendas = get_post_meta($temporada->ID, '_temporada_link_vendas', true);
        $link_texto = get_post_meta($temporada->ID, '_temporada_link_texto', true);
        $tipo_sessao = get_post_meta($temporada->ID, '_temporada_tipo_sessao', true);
        $sessoes_data = get_post_meta($temporada->ID, '_temporada_sessoes_data', true);
        $dias_horarios = CANNALEspetaculos_DiasHorarios::gerar($tipo_sessao, $sessoes_data);
        $data_fim = get_post_meta($temporada->ID, '_temporada_data_fim', true);
        $data_fim_fmt = $data_fim ? date_i18n('d/m/Y', strtotime($data_fim)) : '';
        $data_inicio = get_post_meta($temporada->ID, '_temporada_data_inicio', true);
        $data_inicio_fmt = $data_inicio ? date_i18n('d/m/Y', strtotime($data_inicio)) : '';
        ?>
        <div class="info-item">
            <h3><?php echo esc_html($teatro_nome); ?></h3>

            <? if (!$status == __('Encerrada', 'cannal-espetaculos')): ?>
                <p><?php echo esc_html($dias_horarios); ?></p>
                <?php if ($link_vendas) : ?>
                    <div class="info-item-cta wp-block-button">
                        <a href="<?php echo esc_url($link_vendas); ?>" class="button button-small wp-block-button__link wp-element-button has-small-font-size" target="_blank" rel="noopener">
                            <?php echo esc_html($link_texto ? $link_texto : __('Comprar Ingressos', 'cannal-espetaculos')); ?>
                        </a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <?php if ($tipo_sessao == "temporada"): ?>
                    <p><?php echo sprintf(__('de %s até %s', 'cannal-espetaculos'), $data_inicio_fmt, $data_fim_fmt) ?></p>
                <?php elseif ($tipo_sessao == "avulsas"): ?>
                    <p><?php echo $data_fim_fmt ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>