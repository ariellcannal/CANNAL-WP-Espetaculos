<?php
/**
 * Disparado durante a desativação do plugin.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/includes
 */

class Cannal_Espetaculos_Deactivator {

    /**
     * Ações executadas na desativação do plugin.
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
