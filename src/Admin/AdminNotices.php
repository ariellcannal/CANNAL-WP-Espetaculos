<?php
/**
 * Avisos administrativos do plugin.
 *
 * @package    CANNALEspetaculos_Plugin
 * @subpackage CANNALEspetaculos_Plugin/admin
 */

class CANNALEspetaculos_AdminNotices {

    /**
     * Exibe aviso se os permalinks não estiverem configurados corretamente.
     */
    public static function check_permalinks() {
        $permalink_structure = get_option( 'permalink_structure' );
        
        // Se estiver usando permalinks padrão (vazio), exibir aviso
        if ( empty( $permalink_structure ) ) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong>CANNAL Espetáculos:</strong> 
                    Para que as URLs dos espetáculos funcionem corretamente, você precisa configurar os 
                    <a href="<?php echo admin_url( 'options-permalink.php' ); ?>">Links Permanentes</a> 
                    para qualquer opção diferente de "Simples".
                </p>
                <p>
                    Recomendamos usar <strong>Nome do post</strong> ou <strong>Estrutura personalizada: /%postname%/</strong>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Exibe aviso para recarregar permalinks após ativação.
     */
    public static function flush_rewrite_rules_notice() {
        if ( get_transient( 'cannal_espetaculos_flush_rewrite_rules' ) ) {
            ?>
            <div class="notice notice-info is-dismissible">
                <p>
                    <strong>CANNAL Espetáculos:</strong> 
                    Por favor, vá em 
                    <a href="<?php echo admin_url( 'options-permalink.php' ); ?>">Configurações > Links Permanentes</a> 
                    e clique em "Salvar alterações" para atualizar as regras de URL.
                </p>
            </div>
            <?php
            delete_transient( 'cannal_espetaculos_flush_rewrite_rules' );
        }
    }
}

// Registrar avisos
add_action( 'admin_notices', array( 'CANNALEspetaculos_AdminNotices', 'check_permalinks' ) );
add_action( 'admin_notices', array( 'CANNALEspetaculos_AdminNotices', 'flush_rewrite_rules_notice' ) );
