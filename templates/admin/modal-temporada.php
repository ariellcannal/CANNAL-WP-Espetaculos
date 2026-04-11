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

            <div class="cannal-espetaculos-temporada-form-grid">
                <div class="temporada-coluna">
                    <div class="form-group">
                        <label for="modal_teatro_nome"><?php esc_html_e( 'Nome do Teatro', 'cannal-espetaculos' ); ?></label>
                        <input type="text" id="modal_teatro_nome" name="teatro_nome" class="regular-text" required />
                    </div>
            
                    <div class="form-group">
                        <label for="teatro_endereco"><?php esc_html_e( 'Endereço do Teatro', 'cannal-espetaculos' ); ?></label>
                        <input type="text" id="teatro_endereco" name="teatro_endereco" class="regular-text" />
                    </div>
            
                    <div class="form-group">
                        <label for="modal_diretor"><?php esc_html_e( 'Diretor', 'cannal-espetaculos' ); ?></label>
                        <input type="text" id="modal_diretor" name="diretor" class="regular-text" />
                    </div>
            
                    <div class="form-group">
                        <label for="modal_elenco"><?php esc_html_e( 'Elenco', 'cannal-espetaculos' ); ?></label>
                        <input type="text" id="modal_elenco" name="elenco" class="regular-text" />
                    </div>
            
                    <div class="form-group">
                        <label for="modal_link_vendas"><?php esc_html_e( 'Link de Vendas', 'cannal-espetaculos' ); ?></label>
                        <input type="url" id="modal_link_vendas" name="link_vendas" class="regular-text" />
                    </div>
            
                    <div class="form-group">
                        <label for="modal_link_texto"><?php esc_html_e( 'Texto do Link', 'cannal-espetaculos' ); ?></label>
                        <input type="text" id="modal_link_texto" name="link_texto" class="regular-text" placeholder="<?php esc_attr_e( 'Ingressos Aqui', 'cannal-espetaculos' ); ?>" />
                    </div>
            
                    <div class="form-group">
                        <label for="modal_data_inicio"><?php esc_html_e( 'Período', 'cannal-espetaculos' ); ?></label>
                        <div style="display: flex; gap: 10px;">
                            <input type="date" id="modal_data_inicio" name="data_inicio" style="width: 100%;" />
                            <input type="date" id="modal_data_fim" name="data_fim" style="width: 100%;" />
                        </div>
                    </div>
            
                    <div class="form-group">
                        <label for="modal_valores"><?php esc_html_e( 'Valores', 'cannal-espetaculos' ); ?></label>
                        <textarea id="modal_valores" name="valores" rows="2" class="large-text"></textarea>
                    </div>
                </div>
            
                <div class="temporada-coluna">
                    <div class="form-group">
                        <label for="modal_data_banner"><?php esc_html_e( 'Início do Banner', 'cannal-espetaculos' ); ?></label>
                        <input type="date" id="modal_data_banner" name="data_banner" />
                    </div>
                    
                    <div class="form-group">
                        <label for="banner_destaque1"><?php esc_html_e( 'Destaque 1', 'cannal-espetaculos' ); ?></label>
                        <input type="text" id="banner_destaque1" name="banner_destaque1" class="regular-text" />
                    </div>
            
                    <div class="form-group">
                        <label for="banner_destaque2"><?php esc_html_e( 'Destaque 2', 'cannal-espetaculos' ); ?></label>
                        <input type="text" id="banner_destaque2" name="banner_destaque2" class="regular-text" />
                    </div>
            
                    <div class="form-group">
                        <label><?php esc_html_e( 'Tipo de Sessão', 'cannal-espetaculos' ); ?></label>
                        <div>
                            <label style="display: inline-block; font-weight: normal; margin-right: 15px; margin-top: 0; text-align: left;">
                                <input type="radio" name="modal_tipo_sessao" value="temporada" id="modal_tipo_sessao_temporada" checked />
                                <?php esc_html_e( 'Temporada', 'cannal-espetaculos' ); ?>
                            </label>
                            <label style="display: inline-block; font-weight: normal; margin-top: 0; text-align: left;">
                                <input type="radio" name="modal_tipo_sessao" value="avulsas" id="modal_tipo_sessao_avulsas" />
                                <?php esc_html_e( 'Avulsas', 'cannal-espetaculos' ); ?>
                            </label>
                        </div>
                    </div>
            
                    <div id="modal_sessoes_avulsas_container" class="modal-sessoes-container">
                        <div style="margin-bottom: 15px;">
                            <button type="button" class="button modal-add-sessao-avulsa">
                                <?php esc_html_e( 'Adicionar Sessão', 'cannal-espetaculos' ); ?>
                            </button>
                        </div>
                        <div id="modal_sessoes_avulsas_list" class="sessoes-lista-dinamica"></div>
                    </div>
            
                    <div id="modal_sessoes_temporada_container" class="modal-sessoes-container">
                        <div style="margin-bottom: 15px;">
                            <select id="select_add_dia_semana" style="width: auto;">
                                <option value=""><?php esc_html_e( 'Adicionar Dia da Semana', 'cannal-espetaculos' ); ?></option>
                                <?php foreach ( $dias_semana as $key => $label ) : ?>
                                    <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    
                        <div id="lista_dias_semana" class="sessoes-lista-dinamica">
                            <?php foreach ( $dias_semana as $key => $label ) : ?>
                            <div id="linha_dia_<?php echo esc_attr( $key ); ?>" class="sessao-linha-dinamica" style="display: none;">
                                <label><?php echo esc_html( $label ); ?></label>
                                <div class="modal-sessoes-horarios">
                                    <input type="time" name="modal_sessoes_<?php echo esc_attr( $key ); ?>_1" id="modal_sessoes_<?php echo esc_attr( $key ); ?>_1" />
                                    <input type="time" name="modal_sessoes_<?php echo esc_attr( $key ); ?>_2" id="modal_sessoes_<?php echo esc_attr( $key ); ?>_2" />
                                    <input type="time" name="modal_sessoes_<?php echo esc_attr( $key ); ?>_3" id="modal_sessoes_<?php echo esc_attr( $key ); ?>_3" />
                                    <button type="button" class="button btn-remove-dia" data-dia="<?php echo esc_attr( $key ); ?>" title="Remover Dia">&times;</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                </div>
            </div>
			<input type="hidden" id="modal_sessoes_data" name="sessoes_data" value="" />
			<div id="modal_conteudo_container" class="cannal-editor-container">
                <div class="cannal-editor-header">
                    <label for="modal_conteudo"><?php esc_html_e( 'Release da Temporada', 'cannal-espetaculos' ); ?></label>
                    <button type="button" id="modal_copiar_conteudo" class="button button-small modal-btn-copiar">
                        <?php esc_html_e( 'Copiar Release do Espetáculo', 'cannal-espetaculos' ); ?>
                    </button>
                </div>
                
                <?php
                wp_editor( '', 'modal_conteudo', array(
                    'textarea_name' => 'conteudo',
                    'textarea_rows' => 8,
                    'media_buttons' => false,
                    'teeny'         => true,
                    'quicktags'     => true,
                ) );
                ?>
            </div>
        
            <div class="cannal-form-actions">
                <button type="button" class="button temporada-modal-close">
                    <?php esc_html_e( 'Cancelar', 'cannal-espetaculos' ); ?>
                </button>
                <button type="submit" class="button button-primary">
                    <?php esc_html_e( 'Salvar Temporada', 'cannal-espetaculos' ); ?>
                </button>
            </div>
        </form>
    </div>
</div>
