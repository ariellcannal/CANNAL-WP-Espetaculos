<?php
/**
 * Template: Lista de Temporadas no Meta Box do Espetáculo
 *
 * Variáveis disponíveis:
 * @var WP_Post $post        Post do espetáculo
 * @var array   $temporadas  Array de WP_Post enriquecidos com propriedades:
 *                           ->teatro, ->periodo, ->dias_horarios, ->status_label
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="espetaculo-temporadas-list">
    <div id="cannal-admin-notices"></div>

    <?php if ( ! empty( $temporadas ) ) : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Teatro', 'cannal-espetaculos' ); ?></th>
                    <th><?php esc_html_e( 'Período', 'cannal-espetaculos' ); ?></th>
                    <th><?php esc_html_e( 'Dias e Horários', 'cannal-espetaculos' ); ?></th>
                    <th><?php esc_html_e( 'Status', 'cannal-espetaculos' ); ?></th>
                    <th><?php esc_html_e( 'Ações', 'cannal-espetaculos' ); ?></th>
                </tr>
            </thead>
            <tbody id="temporadas-tbody">
                <?php foreach ( $temporadas as $temporada ) : ?>
                <tr id="temporada-row-<?php echo esc_attr( $temporada->ID ); ?>">
                    <td><?php echo esc_html( $temporada->teatro ); ?></td>
                    <td><?php echo esc_html( $temporada->periodo ); ?></td>
                    <td><?php echo esc_html( $temporada->dias_horarios ); ?></td>
                    <td><?php echo esc_html( $temporada->status_label ); ?></td>
                    <td>
                        <button type="button" class="button button-small edit-temporada-btn"
                            data-temporada-id="<?php echo esc_attr( $temporada->ID ); ?>">
                            <?php esc_html_e( 'Editar', 'cannal-espetaculos' ); ?>
                        </button>
                        <button type="button" class="button button-small duplicate-temporada-btn"
                            data-temporada-id="<?php echo esc_attr( $temporada->ID ); ?>">
                            <?php esc_html_e( 'Duplicar', 'cannal-espetaculos' ); ?>
                        </button>
                        <button type="button" class="button button-small button-link-delete delete-temporada-btn"
                            data-temporada-id="<?php echo esc_attr( $temporada->ID ); ?>">
                            <?php esc_html_e( 'Excluir', 'cannal-espetaculos' ); ?>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p id="no-temporadas-msg"><?php esc_html_e( 'Nenhuma temporada cadastrada ainda.', 'cannal-espetaculos' ); ?></p>
    <?php endif; ?>

    <p class="temporadas-actions">
        <button type="button" class="button button-primary open-temporada-modal"
            data-espetaculo-id="<?php echo esc_attr( $post->ID ); ?>">
            <?php esc_html_e( 'Adicionar Nova Temporada', 'cannal-espetaculos' ); ?>
        </button>
    </p>
</div>
