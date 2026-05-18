<?php
/**
 * Gerencia as rewrite rules e URLs personalizadas.
 *
 * @package    CANNALEspetaculos_Plugin
 * @subpackage CANNALEspetaculos_Plugin/includes
 */

class CANNALEspetaculos_Rewrites {

    /**
     * Adiciona as rewrite rules personalizadas.
     */
    public function add_rewrite_rules() {
        // O WordPress já gerencia automaticamente as URLs de taxonomia
        // Estrutura: /espetaculos/{categoria}/{espetaculo}/
        
        // Garantir que o arquivo de espetáculos funcione
        add_rewrite_rule(
            '^espetaculos/?$',
            'index.php?post_type=espetaculo',
            'top'
        );
    }

    /**
     * Adiciona query vars personalizadas.
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'espetaculos_archive';
        return $vars;
    }

    /**
     * Callback quando uma categoria é criada, editada ou deletada.
     */
    public function on_category_change() {
        // Flush rewrite rules para atualizar permalinks
        flush_rewrite_rules();
    }

}
