<?php
/**
 * Widget de Informações da Temporada
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/includes
 */

class Cannal_Espetaculos_Widget extends WP_Widget {

    /**
     * Construtor do widget.
     */
    public function __construct() {
        parent::__construct(
            'cannal_espetaculos_temporada',
            'CANNAL: Informações da Temporada',
            array(
                'description' => 'Exibe informações da temporada ativa do espetáculo'
            )
        );
    }

    /**
     * Front-end do widget.
     */
    public function widget( $args, $instance ) {
        // Verificar se estamos em uma página de espetáculo
        if ( ! is_singular( 'espetaculo' ) ) {
            return;
        }

        // Buscar temporada ativa
        $temporada_ativa = $this->get_temporada_ativa( get_the_ID() );
        
        if ( ! $temporada_ativa ) {
            return;
        }

        // Obter dados da temporada
        $teatro_nome = get_post_meta( $temporada_ativa->ID, '_temporada_teatro_nome', true );
        $teatro_endereco = get_post_meta( $temporada_ativa->ID, '_temporada_teatro_endereco', true );
        $valores = get_post_meta( $temporada_ativa->ID, '_temporada_valores', true );
        $link_vendas = get_post_meta( $temporada_ativa->ID, '_temporada_link_vendas', true );
        $link_texto = get_post_meta( $temporada_ativa->ID, '_temporada_link_texto', true );
        $sessoes_data = get_post_meta( $temporada_ativa->ID, '_temporada_sessoes_data', true );
        
        // Obter dados do espetáculo
        $duracao = get_post_meta( get_the_ID(), '_espetaculo_duracao', true );
        $classificacao = get_post_meta( get_the_ID(), '_espetaculo_classificacao', true );
        
        // Decodificar sessões
        $sessoes = ! empty( $sessoes_data ) ? json_decode( $sessoes_data, true ) : null;
        $dias_horarios = $this->format_dias_horarios_legivel( $sessoes );

        // Renderizar widget
        echo $args['before_widget'];
        
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }
        
        ?>
        <div class="cannal-temporada-info">
            
            <?php if ( $teatro_nome || $teatro_endereco ) : ?>
            <div class="info-item">
                <strong>Teatro:</strong>
                <div>
                    <?php if ( $teatro_nome ) : ?>
                        <?php echo esc_html( $teatro_nome ); ?><br/>
                    <?php endif; ?>
                    <?php if ( $teatro_endereco ) : ?>
                        <?php echo esc_html( $teatro_endereco ); ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ( $dias_horarios ) : ?>
            <div class="info-item">
                <strong>Dias e Horários:</strong>
                <span><?php echo esc_html( $dias_horarios ); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ( $duracao ) : ?>
            <div class="info-item">
                <strong>Duração:</strong>
                <span><?php echo esc_html( $duracao ); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ( $classificacao ) : ?>
            <div class="info-item classificacao-item">
                <strong>Classificação Indicativa:</strong>
                <div class="classificacao-selo classificacao-<?php echo esc_attr( strtolower( str_replace( ' ', '-', $classificacao ) ) ); ?>">
                    <?php echo esc_html( $classificacao ); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ( $valores ) : ?>
            <div class="info-item">
                <strong>Valores:</strong>
                <div><?php echo nl2br( esc_html( $valores ) ); ?></div>
            </div>
            <?php endif; ?>
            
            <?php if ( $link_vendas ) : ?>
            <div class="info-item-cta">
                <a href="<?php echo esc_url( $link_vendas ); ?>" class="btn-comprar-ingressos" target="_blank" rel="noopener">
                    <?php echo esc_html( $link_texto ? $link_texto : 'Comprar Ingressos' ); ?>
                </a>
            </div>
            <?php endif; ?>
            
        </div>
        
        <style>
        .cannal-temporada-info {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }
        
        .cannal-temporada-info .info-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .cannal-temporada-info .info-item:last-of-type {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .cannal-temporada-info .info-item strong {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .cannal-temporada-info .info-item-cta {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #333;
        }
        
        .cannal-temporada-info .btn-comprar-ingressos {
            display: block;
            background: #e74c3c;
            color: white !important;
            text-align: center;
            padding: 15px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1em;
            transition: background 0.3s;
        }
        
        .cannal-temporada-info .btn-comprar-ingressos:hover {
            background: #c0392b;
        }
        
        .cannal-temporada-info .classificacao-item {
            display: flex;
            flex-direction: column;
        }
        
        .cannal-temporada-info .classificacao-selo {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin-top: 5px;
            color: white;
            background: #2c3e50;
        }
        
        .cannal-temporada-info .classificacao-livre {
            background: #27ae60;
        }
        
        .cannal-temporada-info .classificacao-10-anos {
            background: #3498db;
        }
        
        .cannal-temporada-info .classificacao-12-anos {
            background: #f39c12;
        }
        
        .cannal-temporada-info .classificacao-14-anos {
            background: #e67e22;
        }
        
        .cannal-temporada-info .classificacao-16-anos {
            background: #e74c3c;
        }
        
        .cannal-temporada-info .classificacao-18-anos {
            background: #c0392b;
        }
        </style>
        <?php
        
        echo $args['after_widget'];
    }

    /**
     * Back-end do widget (formulário de configuração).
     */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : 'Informações';
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">Título:</label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p><em>Este widget só aparece em páginas de espetáculos que tenham uma temporada ativa.</em></p>
        <?php
    }

    /**
     * Atualiza as configurações do widget.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
        return $instance;
    }

    /**
     * Busca a temporada ativa do espetáculo.
     */
    private function get_temporada_ativa( $espetaculo_id ) {
        $temporadas = get_posts( array(
            'post_type' => 'temporada',
            'posts_per_page' => 1,
            'meta_key' => '_temporada_espetaculo_id',
            'meta_value' => $espetaculo_id,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => '_temporada_data_fim',
                    'value' => date('Y-m-d'),
                    'compare' => '>=',
                    'type' => 'DATE'
                ),
                array(
                    'key' => '_temporada_data_fim',
                    'value' => '',
                    'compare' => '='
                )
            ),
            'orderby' => 'meta_value',
            'order' => 'ASC'
        ) );

        return ! empty( $temporadas ) ? $temporadas[0] : null;
    }

    /**
     * Formata dias e horários de forma legível.
     */
    private function format_dias_horarios_legivel( $sessoes ) {
        if ( empty( $sessoes ) ) return '';
        
        $output = '';
        
        if ( $sessoes['tipo'] === 'avulsas' && ! empty( $sessoes['avulsas'] ) ) {
            // Agrupar por mês
            $por_mes = array();
            foreach ( $sessoes['avulsas'] as $sessao ) {
                $mes = date_i18n( 'F', strtotime( $sessao['data'] ) );
                $dia = date_i18n( 'j', strtotime( $sessao['data'] ) );
                
                if ( ! isset( $por_mes[$mes] ) ) {
                    $por_mes[$mes] = array();
                }
                
                $por_mes[$mes][] = $dia;
            }
            
            $partes = array();
            foreach ( $por_mes as $mes => $dias ) {
                $dias = array_unique( $dias );
                sort( $dias );
                
                if ( count( $dias ) === 1 ) {
                    $dias_texto = $dias[0];
                } elseif ( count( $dias ) === 2 ) {
                    $dias_texto = $dias[0] . ' e ' . $dias[1];
                } else {
                    $ultimo = array_pop( $dias );
                    $dias_texto = implode( ', ', $dias ) . ' e ' . $ultimo;
                }
                
                $partes[] = $dias_texto . ' de ' . $mes;
            }
            
            $output = implode( ', ', $partes );
            
        } elseif ( $sessoes['tipo'] === 'temporada' && ! empty( $sessoes['temporada'] ) ) {
            // Agrupar por horário
            $por_horario = array();
            $dias_semana_map = array(
                'domingo' => 'dom',
                'segunda' => 'seg',
                'terca' => 'ter',
                'quarta' => 'qua',
                'quinta' => 'qui',
                'sexta' => 'sex',
                'sabado' => 'sáb'
            );
            
            foreach ( $sessoes['temporada'] as $dia => $horarios_str ) {
                if ( empty( $horarios_str ) ) continue;
                
                $dia_abrev = isset( $dias_semana_map[$dia] ) ? $dias_semana_map[$dia] : $dia;
                
                if ( ! isset( $por_horario[$horarios_str] ) ) {
                    $por_horario[$horarios_str] = array();
                }
                
                $por_horario[$horarios_str][] = $dia_abrev;
            }
            
            $partes = array();
            foreach ( $por_horario as $horarios => $dias ) {
                if ( count( $dias ) === 1 ) {
                    $dias_texto = $dias[0];
                } elseif ( count( $dias ) === 2 ) {
                    $dias_texto = $dias[0] . ' e ' . $dias[1];
                } else {
                    // Verificar se são dias consecutivos
                    $dias_ordem = array( 'dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sáb' );
                    $indices = array();
                    foreach ( $dias as $dia ) {
                        $indices[] = array_search( $dia, $dias_ordem );
                    }
                    sort( $indices );
                    
                    $consecutivos = true;
                    for ( $i = 1; $i < count( $indices ); $i++ ) {
                        if ( $indices[$i] !== $indices[$i-1] + 1 ) {
                            $consecutivos = false;
                            break;
                        }
                    }
                    
                    if ( $consecutivos ) {
                        $dias_texto = $dias_ordem[$indices[0]] . ' a ' . $dias_ordem[$indices[count($indices)-1]];
                    } else {
                        $ultimo = array_pop( $dias );
                        $dias_texto = implode( ', ', $dias ) . ' e ' . $ultimo;
                    }
                }
                
                $partes[] = $dias_texto . ' às ' . $horarios;
            }
            
            $output = implode( ', ', $partes );
        }
        
        return $output;
    }
}

/**
 * Registra o widget.
 */
function cannal_espetaculos_register_widget() {
    register_widget( 'Cannal_Espetaculos_Widget' );
}
add_action( 'widgets_init', 'cannal_espetaculos_register_widget' );

/**
 * Adiciona o widget automaticamente na sidebar em páginas de espetáculos.
 */
function cannal_espetaculos_auto_add_widget( $sidebars_widgets ) {
    // Verificar se estamos em uma página de espetáculo
    if ( ! is_singular( 'espetaculo' ) ) {
        return $sidebars_widgets;
    }
    
    // Verificar se há temporada ativa
    $temporadas = get_posts( array(
        'post_type' => 'temporada',
        'posts_per_page' => 1,
        'meta_key' => '_temporada_espetaculo_id',
        'meta_value' => get_the_ID(),
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key' => '_temporada_data_fim',
                'value' => date('Y-m-d'),
                'compare' => '>=',
                'type' => 'DATE'
            ),
            array(
                'key' => '_temporada_data_fim',
                'value' => '',
                'compare' => '='
            )
        )
    ) );
    
    if ( empty( $temporadas ) ) {
        return $sidebars_widgets;
    }
    
    // Obter a primeira sidebar ativa
    $active_sidebars = array_keys( $sidebars_widgets );
    $target_sidebar = null;
    
    foreach ( $active_sidebars as $sidebar_id ) {
        if ( $sidebar_id !== 'wp_inactive_widgets' && ! empty( $sidebars_widgets[$sidebar_id] ) ) {
            $target_sidebar = $sidebar_id;
            break;
        }
    }
    
    if ( ! $target_sidebar ) {
        return $sidebars_widgets;
    }
    
    // Adicionar o widget no início da sidebar
    array_unshift( $sidebars_widgets[$target_sidebar], 'cannal_espetaculos_temporada-1' );
    
    return $sidebars_widgets;
}
add_filter( 'sidebars_widgets', 'cannal_espetaculos_auto_add_widget' );
