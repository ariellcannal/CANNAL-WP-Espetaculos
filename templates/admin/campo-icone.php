<?php
/**
 * Template: Campo Ícone do Espetáculo
 *
 * Variáveis disponíveis:
 * @var string $icone_url  URL do ícone atual (pode ser vazia)
 * @var int    $icone_id   ID do attachment do ícone atual (pode ser 0)
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<tr>
    <th scope="row">
        <label for="espetaculo_icone_id"><?php esc_html_e( 'Ícone', 'cannal-espetaculos' ); ?></label>
    </th>
    <td>
        <div id="espetaculo-icone-wrap">
            <?php if ( $icone_url ) : ?>
                <div id="espetaculo-icone-preview">
                    <img src="<?php echo esc_url( $icone_url ); ?>" alt="<?php esc_attr_e( 'Ícone atual', 'cannal-espetaculos' ); ?>" />
                </div>
            <?php else : ?>
                <div id="espetaculo-icone-preview" class="hidden"></div>
            <?php endif; ?>

            <input type="hidden" id="espetaculo_icone_id" name="espetaculo_icone_id"
                value="<?php echo esc_attr( $icone_id ); ?>" />

            <button type="button" id="btn-upload-icone" class="button">
                <?php echo $icone_url ? esc_html__( 'Alterar Ícone', 'cannal-espetaculos' ) : esc_html__( 'Adicionar Ícone', 'cannal-espetaculos' ); ?>
            </button>

            <?php if ( $icone_url ) : ?>
                <button type="button" id="btn-remove-icone" class="button button-link-delete">
                    <?php esc_html_e( 'Remover', 'cannal-espetaculos' ); ?>
                </button>
            <?php else : ?>
                <button type="button" id="btn-remove-icone" class="button button-link-delete hidden">
                    <?php esc_html_e( 'Remover', 'cannal-espetaculos' ); ?>
                </button>
            <?php endif; ?>
        </div>
        <p class="description">
            <?php esc_html_e( 'Ícone da página do espetáculo (favicon). Obrigatoriamente quadrado, máximo 512×512px. Formatos aceitos: PNG, ICO, SVG.', 'cannal-espetaculos' ); ?>
        </p>
        <p id="espetaculo-icone-error" class="cannal-field-error hidden"></p>
    </td>
</tr>
