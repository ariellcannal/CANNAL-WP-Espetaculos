<?php
/**
 * Template: Modal de Adicionar/Editar Temporada
 *
 * Variáveis disponíveis:
 * @var int    $espetaculo_id  ID do espetáculo pai
 * @var array  $dias_semana    Array de dias da semana (key => label)
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div id="temporada-modal" class="temporada-modal">
    <div class="temporada-modal-content">
        <span class="temporada-modal-close">&times;</span>
        <h2 id="temporada-modal-title"><?php esc_html_e( 'Nova Temporada', 'cannal-espetaculos' ); ?></h2>

        <form id="temporada-form">
            <input type="hidden" id="modal_temporada_id" name="temporada_id" value="" />
            <input type="hidden" id="modal_espetaculo_id" name="espetaculo_id" value="<?php echo esc_attr( $espetaculo_id ); ?>" />

            <table class="form-table">
                <tr>
                    <th><label for="modal_teatro_nome"><?php esc_html_e( 'Nome do Teatro', 'cannal-espetaculos' ); ?> *</label></th>
                    <td><input type="text" id="modal_teatro_nome" name="teatro_nome" class="regular-text" required /></td>
                </tr>
                <tr>
                    <th><label for="modal_teatro_endereco"><?php esc_html_e( 'Endereço do Teatro', 'cannal-espetaculos' ); ?></label></th>
                    <td><input type="text" id="modal_teatro_endereco" name="teatro_endereco" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="modal_diretor"><?php esc_html_e( 'Diretor', 'cannal-espetaculos' ); ?></label></th>
                    <td><input type="text" id="modal_diretor" name="diretor" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="modal_elenco"><?php esc_html_e( 'Elenco', 'cannal-espetaculos' ); ?></label></th>
                    <td><textarea id="modal_elenco" name="elenco" rows="3" class="large-text"></textarea></td>
                </tr>
                <tr>
                    <th><label for="modal_data_inicio"><?php esc_html_e( 'Data de Início', 'cannal-espetaculos' ); ?> *</label></th>
                    <td><input type="date" id="modal_data_inicio" name="data_inicio" required /></td>
                </tr>
                <tr>
                    <th><label for="modal_data_fim"><?php esc_html_e( 'Data Final', 'cannal-espetaculos' ); ?> *</label></th>
                    <td><input type="date" id="modal_data_fim" name="data_fim" required /></td>
                </tr>
                <tr>
                    <th><label for="modal_valores"><?php esc_html_e( 'Valores', 'cannal-espetaculos' ); ?></label></th>
                    <td><textarea id="modal_valores" name="valores" rows="3" class="large-text"></textarea></td>
                </tr>
                <tr>
                    <th><label for="modal_link_vendas"><?php esc_html_e( 'Link de Vendas', 'cannal-espetaculos' ); ?></label></th>
                    <td><input type="url" id="modal_link_vendas" name="link_vendas" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="modal_link_texto"><?php esc_html_e( 'Texto do Link', 'cannal-espetaculos' ); ?></label></th>
                    <td><input type="text" id="modal_link_texto" name="link_texto" class="regular-text" placeholder="<?php esc_attr_e( 'Ingressos Aqui', 'cannal-espetaculos' ); ?>" /></td>
                </tr>
                <tr>
                    <th><label for="modal_data_inicio_cartaz"><?php esc_html_e( 'Data de Início do Cartaz', 'cannal-espetaculos' ); ?></label></th>
                    <td><input type="date" id="modal_data_inicio_cartaz" name="data_inicio_cartaz" /></td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e( 'Tipo de Sessão', 'cannal-espetaculos' ); ?></label></th>
                    <td>
                        <label>
                            <input type="radio" name="modal_tipo_sessao" value="avulsas" id="modal_tipo_sessao_avulsas" checked />
                            <?php esc_html_e( 'Sessões Avulsas', 'cannal-espetaculos' ); ?>
                        </label>
                        &nbsp;&nbsp;
                        <label>
                            <input type="radio" name="modal_tipo_sessao" value="temporada" id="modal_tipo_sessao_temporada" />
                            <?php esc_html_e( 'Temporada (dias da semana)', 'cannal-espetaculos' ); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <div id="modal_sessoes_avulsas_container" class="modal-sessoes-container">
                <p><strong><?php esc_html_e( 'Sessões Avulsas:', 'cannal-espetaculos' ); ?></strong></p>
                <div id="modal_sessoes_avulsas_list"></div>
                <button type="button" class="button modal-add-sessao-avulsa">
                    <?php esc_html_e( 'Adicionar Sessão', 'cannal-espetaculos' ); ?>
                </button>
            </div>

            <div id="modal_sessoes_temporada_container" class="modal-sessoes-container modal-sessoes-temporada">
                <p><strong><?php esc_html_e( 'Dias da Semana e Horários:', 'cannal-espetaculos' ); ?></strong></p>
                <table class="form-table">
                    <?php foreach ( $dias_semana as $key => $label ) : ?>
                    <tr>
                        <th><label><?php echo esc_html( $label ); ?></label></th>
                        <td class="modal-sessoes-horarios">
                            <input type="time" name="modal_sessoes_<?php echo esc_attr( $key ); ?>_1" id="modal_sessoes_<?php echo esc_attr( $key ); ?>_1" />
                            <input type="time" name="modal_sessoes_<?php echo esc_attr( $key ); ?>_2" id="modal_sessoes_<?php echo esc_attr( $key ); ?>_2" />
                            <input type="time" name="modal_sessoes_<?php echo esc_attr( $key ); ?>_3" id="modal_sessoes_<?php echo esc_attr( $key ); ?>_3" />
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <input type="hidden" id="modal_sessoes_data" name="sessoes_data" value="" />

            <table class="form-table">
                <tr>
                    <th><label for="modal_conteudo"><?php esc_html_e( 'Conteúdo', 'cannal-espetaculos' ); ?></label></th>
                    <td>
                        <button type="button" id="modal_copiar_conteudo" class="button modal-btn-copiar">
                            <?php esc_html_e( 'Copiar conteúdo do espetáculo', 'cannal-espetaculos' ); ?>
                        </button>
                        <?php
                        wp_editor( '', 'modal_conteudo', array(
                            'textarea_name' => 'conteudo',
                            'textarea_rows' => 8,
                            'media_buttons' => false,
                            'teeny'         => true,
                            'quicktags'     => true,
                        ) );
                        ?>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e( 'Salvar Temporada', 'cannal-espetaculos' ); ?>
                </button>
                <button type="button" class="button temporada-modal-close">
                    <?php esc_html_e( 'Cancelar', 'cannal-espetaculos' ); ?>
                </button>
            </p>
        </form>
    </div>
</div>
