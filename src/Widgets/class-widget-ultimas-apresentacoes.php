<?php
/**
 * Widget: CANNAL - Últimas Apresentações
 *
 * Exibe as últimas temporadas encerradas do espetáculo na sidebar.
 * Funciona apenas em páginas single-espetaculo.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/Widgets
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Cannal_Widget_Ultimas_Apresentacoes extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'cannal_ultimas_apresentacoes',
            __( 'CANNAL - Últimas Apresentações', 'cannal-espetaculos' ),
            array(
                'description' => __( 'Exibe as últimas temporadas encerradas do espetáculo. Funciona apenas em páginas single-espetaculo.', 'cannal-espetaculos' ),
                'classname'   => 'cannal-widget-ultimas-apresentacoes',
            )
        );
    }

    /**
     * Formulário de configuração no admin.
     */
    public function form( $instance ) {
        $titulo = ! empty( $instance['titulo'] ) ? $instance['titulo'] : '';
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'titulo' ) ); ?>">
                <?php esc_html_e( 'Título do Widget:', 'cannal-espetaculos' ); ?>
            </label>
            <input type="text"
                   id="<?php echo esc_attr( $this->get_field_id( 'titulo' ) ); ?>"
                   name="<?php echo esc_attr( $this->get_field_name( 'titulo' ) ); ?>"
                   value="<?php echo esc_attr( $titulo ); ?>"
                   class="widefat"
                   placeholder="<?php esc_attr_e( 'Últimas Apresentações', 'cannal-espetaculos' ); ?>" />
            <small><?php esc_html_e( 'Deixe em branco para usar "Últimas Apresentações".', 'cannal-espetaculos' ); ?></small>
        </p>
        <?php
    }

    /**
     * Sanitiza as opções ao salvar.
     */
    public function update( $new_instance, $old_instance ) {
        $instance           = array();
        $instance['titulo'] = sanitize_text_field( $new_instance['titulo'] );
        return $instance;
    }

    /**
     * Renderiza o widget no front-end.
     */
    public function widget( $args, $instance ) {
        if ( ! is_singular( 'espetaculo' ) ) {
            return;
        }

        global $post;
        $espetaculo_id = $post->ID;

        $temporadas = Cannal_Espetaculos_Public::get_ultimas_temporadas_static( $espetaculo_id, 5 );

        if ( empty( $temporadas ) ) {
            return;
        }

        $titulo = ! empty( $instance['titulo'] )
            ? $instance['titulo']
            : __( 'Últimas Apresentações', 'cannal-espetaculos' );

        echo $args['before_widget'];

        include CANNAL_ESPETACULOS_PLUGIN_DIR . 'templates/public/widget-ultimas-apresentacoes.php';

        echo $args['after_widget'];
    }
}
