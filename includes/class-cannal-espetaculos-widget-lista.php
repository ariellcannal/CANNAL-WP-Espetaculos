<?php
/**
 * Widget de Lista de Espetáculos
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/includes
 */

class Cannal_Espetaculos_Widget_Lista extends WP_Widget {

    /**
     * Construtor do widget.
     */
    public function __construct() {
        parent::__construct(
            'cannal_espetaculos_lista',
            'CANNAL - Lista de Espetáculos',
            array(
                'description' => 'Exibe uma lista de espetáculos filtrados por status de temporada.'
            )
        );
    }

    /**
     * Front-end do widget.
     */
    public function widget( $args, $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
        $filtro = ! empty( $instance['filtro'] ) ? $instance['filtro'] : 'em_cartaz';
        $limite = ! empty( $instance['limite'] ) ? intval( $instance['limite'] ) : 5;

        echo $args['before_widget'];

        if ( ! empty( $title ) ) {
            echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
        }

        // Buscar espetáculos com temporadas no status selecionado
        $espetaculos = $this->get_espetaculos_by_temporada_status( $filtro, $limite );

        if ( ! empty( $espetaculos ) ) {
            echo '<div class="cannal-widget-lista-espetaculos">';
            echo '<ul class="widget-list">';
            
            foreach ( $espetaculos as $espetaculo_data ) {
                $espetaculo_id = $espetaculo_data['espetaculo_id'];
                $temporada = $espetaculo_data['temporada'];
                $espetaculo = get_post( $espetaculo_id );
                
                if ( ! $espetaculo ) {
                    continue;
                }
                
                $permalink = get_permalink( $espetaculo_id );
                $thumbnail = get_the_post_thumbnail( $espetaculo_id, 'thumbnail', array( 'class' => 'alignleft' ) );
                $teatro = get_post_meta( $temporada->ID, '_temporada_teatro_nome', true );
                $data_inicio = get_post_meta( $temporada->ID, '_temporada_data_inicio', true );
                
                // Gerar texto de dias e horários
                $tipo_sessao = get_post_meta( $temporada->ID, '_temporada_tipo_sessao', true );
                $sessoes_data = get_post_meta( $temporada->ID, '_temporada_sessoes_data', true );
                $dias_horarios = $this->gerar_dias_horarios( $tipo_sessao, $sessoes_data );
                
                echo '<li class="cannal-espetaculo-item">';
                
                if ( $thumbnail ) {
                    echo '<a href="' . esc_url( $permalink ) . '">' . $thumbnail . '</a>';
                }
                
                echo '<div class="cannal-espetaculo-info">';
                echo '<h4><a href="' . esc_url( $permalink ) . '">' . esc_html( $espetaculo->post_title ) . '</a></h4>';
                
                if ( $teatro ) {
                    echo '<p class="cannal-teatro"><strong>' . esc_html( $teatro ) . '</strong></p>';
                }
                
                if ( $dias_horarios ) {
                    echo '<p class="cannal-dias-horarios">' . esc_html( $dias_horarios ) . '</p>';
                }
                
                echo '</div>';
                echo '<div class="clear"></div>';
                echo '</li>';
            }
            
            echo '</ul>';
            echo '</div>';
        } else {
            echo '<p>Nenhum espetáculo encontrado.</p>';
        }

        echo $args['after_widget'];
    }

    /**
     * Formulário de configuração do widget no admin.
     */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : 'Espetáculos';
        $filtro = ! empty( $instance['filtro'] ) ? $instance['filtro'] : 'em_cartaz';
        $limite = ! empty( $instance['limite'] ) ? $instance['limite'] : 5;
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Título:</label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'filtro' ) ); ?>">Filtro:</label>
            <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'filtro' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'filtro' ) ); ?>">
                <option value="em_cartaz" <?php selected( $filtro, 'em_cartaz' ); ?>>Em Cartaz</option>
                <option value="futuras" <?php selected( $filtro, 'futuras' ); ?>>Próximas Temporadas</option>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'limite' ) ); ?>">Limite de espetáculos:</label>
            <input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'limite' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limite' ) ); ?>" type="number" step="1" min="1" value="<?php echo esc_attr( $limite ); ?>" size="3">
        </p>
        <?php
    }

    /**
     * Atualiza as configurações do widget.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['filtro'] = ! empty( $new_instance['filtro'] ) ? sanitize_text_field( $new_instance['filtro'] ) : 'em_cartaz';
        $instance['limite'] = ! empty( $new_instance['limite'] ) ? intval( $new_instance['limite'] ) : 5;
        return $instance;
    }

    /**
     * Busca espetáculos com temporadas no status especificado.
     */
    private function get_espetaculos_by_temporada_status( $status, $limite ) {
        $hoje = current_time( 'Y-m-d' );
        $meta_query = array();

        switch ( $status ) {
            case 'em_cartaz':
                $meta_query = array(
                    'relation' => 'AND',
                    array(
                        'key' => '_temporada_data_inicio',
                        'value' => $hoje,
                        'compare' => '<=',
                        'type' => 'DATE'
                    ),
                    array(
                        'key' => '_temporada_data_fim',
                        'value' => $hoje,
                        'compare' => '>=',
                        'type' => 'DATE'
                    )
                );
                $order = 'ASC';
                break;

            case 'futuras':
                $meta_query = array(
                    array(
                        'key' => '_temporada_data_inicio',
                        'value' => $hoje,
                        'compare' => '>',
                        'type' => 'DATE'
                    )
                );
                $order = 'ASC';
                break;

            default:
                $order = 'ASC';
                break;
        }

        // Buscar temporadas
        $temporadas = get_posts( array(
            'post_type' => 'temporada',
            'posts_per_page' => $limite,
            'meta_query' => $meta_query,
            'orderby' => 'meta_value',
            'meta_key' => '_temporada_data_inicio',
            'order' => $order
        ) );

        // Agrupar por espetáculo
        $espetaculos = array();
        $espetaculos_ids = array();

        foreach ( $temporadas as $temporada ) {
            $espetaculo_id = get_post_meta( $temporada->ID, '_temporada_espetaculo_id', true );
            
            // Evitar duplicatas (pegar apenas a primeira temporada de cada espetáculo)
            if ( ! in_array( $espetaculo_id, $espetaculos_ids ) ) {
                $espetaculos_ids[] = $espetaculo_id;
                $espetaculos[] = array(
                    'espetaculo_id' => $espetaculo_id,
                    'temporada' => $temporada
                );
            }
        }

        return $espetaculos;
    }

    /**
     * Gera texto de dias e horários usando classe inteligente.
     */
    private function gerar_dias_horarios( $tipo_sessao, $sessoes_data ) {
        return Cannal_Espetaculos_Dias_Horarios::gerar( $tipo_sessao, $sessoes_data );
    }
}

/**
 * Registra o widget.
 */
function cannal_register_widget_lista() {
    register_widget( 'Cannal_Espetaculos_Widget_Lista' );
}
add_action( 'widgets_init', 'cannal_register_widget_lista' );
