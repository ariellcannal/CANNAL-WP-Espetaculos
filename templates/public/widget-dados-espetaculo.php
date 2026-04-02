<?php
/**
 * Template: Widget CANNAL - Dados do Espetáculo
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
 * @var string      $teatro_nome        (da temporada, se houver)
 * @var string      $teatro_endereco    (da temporada, se houver)
 * @var string      $dias_horarios      (gerado dinamicamente)
 * @var string      $tipo_sessao
 * @var string      $valores
 * @var string      $link_vendas
 * @var string      $link_texto
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="espetaculo-sidebar">
    <?php if ( $titulo ) : ?>
        <h3><?php echo esc_html( $titulo ); ?></h3>
    <?php endif; ?>

    <?php if ( $temporada ) : ?>
        <?php if ( $teatro_nome || $teatro_endereco ) : ?>
        <div class="info-item">
            <strong><?php esc_html_e( 'Teatro', 'cannal-espetaculos' ); ?></strong>
            <div>
                <?php if ( $teatro_nome ) : ?>
                    <?php echo esc_html( $teatro_nome ); ?><br />
                <?php endif; ?>
                <?php if ( $teatro_endereco ) : ?>
                    <?php echo esc_html( $teatro_endereco ); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ( $dias_horarios ) : ?>
        <div class="info-item">
            <strong><?php echo $tipo_sessao === 'avulsas' ? esc_html__( 'Apresentações', 'cannal-espetaculos' ) : esc_html__( 'Dias e Horários', 'cannal-espetaculos' ); ?></strong>
            <span><?php echo esc_html( $dias_horarios ); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ( $valores ) : ?>
        <div class="info-item">
            <strong><?php esc_html_e( 'Valores', 'cannal-espetaculos' ); ?></strong>
            <div><?php echo nl2br( esc_html( $valores ) ); ?></div>
        </div>
        <?php endif; ?>

    <?php endif; ?>
	
	<?php if ( $autor ) : ?>
    <div class="info-item">
        <strong><?php esc_html_e( 'Autor', 'cannal-espetaculos' ); ?></strong>
        <span><?php echo esc_html( $autor ); ?></span>
    </div>
    <?php endif; ?>

	<?php if ( $diretor ) : ?>
    <div class="info-item">
        <strong><?php esc_html_e( 'Direção', 'cannal-espetaculos' ); ?></strong>
        <span><?php echo esc_html( $diretor ); ?></span>
    </div>
    <?php endif; ?>
    
    <?php if ( $elenco ) : ?>
    <div class="info-item">
        <strong><?php esc_html_e( 'Elenco', 'cannal-espetaculos' ); ?></strong>
        <div><?php echo nl2br( esc_html( $elenco ) ); ?></div>
    </div>
    <?php endif; ?>

    <?php if ( $duracao ) : ?>
    <div class="info-item">
        <strong><?php esc_html_e( 'Duração', 'cannal-espetaculos' ); ?></strong>
        <span><?php echo esc_html( $duracao ); ?></span>
    </div>
    <?php endif; ?>

    <?php if ( $ano_estreia ) : ?>
    <div class="info-item">
        <strong><?php esc_html_e( 'Estreou em', 'cannal-espetaculos' ); ?></strong>
        <span><?php echo esc_html( $ano_estreia ); ?></span>
    </div>
    <?php endif; ?>

    <?php if ( $classificacao ) : ?>
    <div class="info-item">
        <strong><?php esc_html_e( 'Classificação Indicativa', 'cannal-espetaculos' ); ?></strong>
        <div class="classificacao-selo classificacao-<?php echo esc_attr( strtolower( str_replace( ' ', '-', $classificacao ) ) ); ?>">
            <?php echo esc_html( $classificacao_text ); ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ( $temporada && $link_vendas ) : ?>
    <div class="info-item-cta">
        <a href="<?php echo esc_url( $link_vendas ); ?>" class="btn-comprar-ingressos" target="_blank" rel="noopener">
            <?php echo esc_html( $link_texto ? $link_texto : __( 'Comprar Ingressos', 'cannal-espetaculos' ) ); ?>
        </a>
    </div>
    <?php endif; ?>

</div>
