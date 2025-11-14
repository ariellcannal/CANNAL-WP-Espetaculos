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
define( 'CANNAL_ESPETACULOS_VERSION', '2.4.0' );
define( 'CANNAL_ESPETACULOS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CANNAL_ESPETACULOS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * O código que roda durante a ativação do plugin.
 */
function activate_cannal_espetaculos() {
    require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'includes/class-cannal-espetaculos-activator.php';
    Cannal_Espetaculos_Activator::activate();
}

/**
 * O código que roda durante a desativação do plugin.
 */
function deactivate_cannal_espetaculos() {
    require_once CANNAL_ESPETACULOS_PLUGIN_DIR . 'includes/class-cannal-espetaculos-deactivator.php';
    Cannal_Espetaculos_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cannal_espetaculos' );
register_deactivation_hook( __FILE__, 'deactivate_cannal_espetaculos' );

/**
 * A classe principal do plugin.
 */
require CANNAL_ESPETACULOS_PLUGIN_DIR . 'includes/class-cannal-espetaculos.php';

/**
 * Widget de lista de espetáculos.
 */
require CANNAL_ESPETACULOS_PLUGIN_DIR . 'includes/class-cannal-espetaculos-widget-lista.php';

/**
 * Classe para geração inteligente de dias e horários.
 */
require CANNAL_ESPETACULOS_PLUGIN_DIR . 'includes/class-cannal-espetaculos-dias-horarios.php';

/**
 * Inicia a execução do plugin.
 */
function run_cannal_espetaculos() {
    $plugin = new Cannal_Espetaculos();
    $plugin->run();
}
run_cannal_espetaculos();
