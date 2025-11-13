<?php
/**
 * Registra todas as ações e filtros para o plugin.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/includes
 */

class Cannal_Espetaculos_Loader {

    /**
     * O array de ações registradas com o WordPress.
     */
    protected $actions;

    /**
     * O array de filtros registrados com o WordPress.
     */
    protected $filters;

    /**
     * Inicializa as coleções de ações e filtros.
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
    }

    /**
     * Adiciona uma nova ação à coleção a ser registrada com o WordPress.
     */
    public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * Adiciona um novo filtro à coleção a ser registrada com o WordPress.
     */
    public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * Uma função utilitária que é usada para registrar as ações e hooks em um único array.
     */
    private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
        $hooks[] = array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );

        return $hooks;
    }

    /**
     * Registra os filtros e ações com o WordPress.
     */
    public function run() {
        foreach ( $this->filters as $hook ) {
            add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
        }

        foreach ( $this->actions as $hook ) {
            add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
        }
    }
}
