<?php
/**
 * Plugin Name: CANNAL Espetáculos
 * Plugin URI: https://github.com/ariellcannal/WP-CANNAL-Espetaculos
 * Description: Plugin completo para gerenciamento de espetáculos teatrais com temporadas, sessões, integração com Elementor e RevSlider.
 * Version: 2.4.0
 * Author: CANNAL
 * Author URI: https://cannal.com.br
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: cannal-espetaculos
 * Domain Path: /languages
 */

// Se este arquivo for chamado diretamente, abortar.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Versão atual do plugin.
 */
define( 'CANNAL_ESPETACULOS_VERSION', '2.5.0' );
define( 'CANNAL_ESPETACULOS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CANNAL_ESPETACULOS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Funções auxiliares do plugin.
 */
require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'src/cannal-helpers.php';

/**
 * O código que roda durante a ativação do plugin.
 */
function activate_cannal_espetaculos() {
    require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'src/class-cannal-espetaculos-activator.php';
    Cannal_Espetaculos_Activator::activate();
}

/**
 * O código que roda durante a desativação do plugin.
 */
function deactivate_cannal_espetaculos() {
    require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'src/class-cannal-espetaculos-deactivator.php';
    Cannal_Espetaculos_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cannal_espetaculos' );
register_deactivation_hook( __FILE__, 'deactivate_cannal_espetaculos' );

/**
 * A classe principal do plugin.
 */
require CANNAL_ESPETACULOS_PLUGIN_DIR . 'src/class-cannal-espetaculos.php';

/**
 * Widgets
 */
require CANNAL_ESPETACULOS_PLUGIN_DIR . 'src/Widgets/class-cannal-espetaculos-widget-lista.php';
require CANNAL_ESPETACULOS_PLUGIN_DIR . 'src/Widgets/class-widget-proximas-apresentacoes.php';
require CANNAL_ESPETACULOS_PLUGIN_DIR . 'src/Widgets/class-widget-ultimas-apresentacoes.php';
require CANNAL_ESPETACULOS_PLUGIN_DIR . 'src/Widgets/class-widget-dados-espetaculo.php';


/**
 * Inicia a execução do plugin.
 */
function run_cannal_espetaculos() {
    $plugin = new Cannal_Espetaculos();
    $plugin->run();
}
run_cannal_espetaculos();
