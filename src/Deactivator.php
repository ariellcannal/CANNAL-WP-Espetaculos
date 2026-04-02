<?php
/**
 * Disparado durante a desativação do plugin.
 *
 * @package    CANNALEspetaculos_Plugin
 * @subpackage CANNALEspetaculos_Plugin/includes
 */

class CANNALEspetaculos_Deactivator {

    /**
     * Ações executadas na desativação do plugin.
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
