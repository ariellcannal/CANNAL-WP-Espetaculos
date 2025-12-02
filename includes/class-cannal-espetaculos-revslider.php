<?php
/**
 * Integração com RevSlider para banners de espetáculos.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/includes
 */

class Cannal_Espetaculos_RevSlider {

    /**
     * Obtém os espetáculos para exibição no banner.
     * 
     * Ordem: Em cartaz (por data de estreia), depois futuros (por data de estreia)
     */
    public static function get_banner_espetaculos() {
        $hoje = current_time( 'Y-m-d' );

        // Buscar espetáculos em cartaz
        $em_cartaz = get_posts( array(
            'post_type' => 'espetaculo',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'EXISTS',
                array(
                    'key' => '_espetaculo_banner_temporada_id',
                    'compare' => 'EXISTS'
                )
            )
        ) );

        $espetaculos_ordenados = array();

        foreach ( $em_cartaz as $espetaculo ) {
            $temporada_id = get_post_meta( $espetaculo->ID, '_espetaculo_banner_temporada_id', true );
            
            if ( ! $temporada_id ) {
                continue;
            }

            $data_inicio = get_post_meta( $temporada_id, '_temporada_data_inicio', true );
            $data_fim = get_post_meta( $temporada_id, '_temporada_data_fim', true );
            $data_inicio_cartaz = get_post_meta( $temporada_id, '_temporada_data_inicio_cartaz', true );

            // Verificar se o banner deve ser exibido
            if ( $data_inicio_cartaz && $hoje < $data_inicio_cartaz ) {
                continue;
            }

            // Classificar por status
            if ( $data_inicio && $data_fim ) {
                if ( $hoje >= $data_inicio && $hoje <= $data_fim ) {
                    $status = 'em_cartaz';
                } elseif ( $hoje < $data_inicio ) {
                    $status = 'futuro';
                } else {
                    continue; // Não exibir encerrados
                }

                $espetaculos_ordenados[] = array(
                    'post' => $espetaculo,
                    'temporada_id' => $temporada_id,
                    'status' => $status,
                    'data_inicio' => $data_inicio
                );
            }
        }

        // Ordenar: em cartaz primeiro, depois futuros, ambos por data de estreia
        usort( $espetaculos_ordenados, function( $a, $b ) {
            if ( $a['status'] !== $b['status'] ) {
                return $a['status'] === 'em_cartaz' ? -1 : 1;
            }
            return strcmp( $a['data_inicio'], $b['data_inicio'] );
        } );

        return $espetaculos_ordenados;
    }

    /**
     * Atualiza a temporada ativa para o banner de um espetáculo.
     */
    public static function update_banner_temporada( $espetaculo_id ) {
        $hoje = current_time( 'Y-m-d' );

        // Buscar temporada em cartaz ou futura mais próxima
        $temporadas = get_posts( array(
            'post_type' => 'temporada',
            'posts_per_page' => 1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_temporada_espetaculo_id',
                    'value' => $espetaculo_id,
                    'compare' => '='
                ),
                array(
                    'key' => '_temporada_data_fim',
                    'value' => $hoje,
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            ),
            'orderby' => 'meta_value',
            'meta_key' => '_temporada_data_inicio',
            'order' => 'ASC'
        ) );

        if ( ! empty( $temporadas ) ) {
            update_post_meta( $espetaculo_id, '_espetaculo_banner_temporada_id', $temporadas[0]->ID );
        } else {
            delete_post_meta( $espetaculo_id, '_espetaculo_banner_temporada_id' );
        }
    }

    /**
     * Gera dados para uso no RevSlider.
     */
    public static function get_slide_data( $espetaculo_id, $temporada_id ) {
        $espetaculo = get_post( $espetaculo_id );
        $temporada = get_post( $temporada_id );

        if ( ! $espetaculo || ! $temporada ) {
            return null;
        }

        $teatro_nome = get_post_meta( $temporada_id, '_temporada_teatro_nome', true );
        $dias_horarios = get_post_meta( $temporada_id, '_temporada_dias_horarios', true );
        $link_vendas = get_post_meta( $temporada_id, '_temporada_link_vendas', true );
        $link_texto = get_post_meta( $temporada_id, '_temporada_link_texto', true );
        $espetaculo_url = Cannal_Espetaculos_Rewrites::get_espetaculo_url( $espetaculo_id );

        // Obter imagem destaque (banner)
        $image_id = get_post_thumbnail_id( $espetaculo_id );
        $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';

        return array(
            'titulo' => $espetaculo->post_title,
            'teatro' => $teatro_nome,
            'dias_horarios' => $dias_horarios,
            'link_vendas' => $link_vendas,
            'link_texto' => ! empty( $link_texto ) ? $link_texto : 'Ingressos Aqui',
            'espetaculo_url' => $espetaculo_url,
            'image_url' => $image_url
        );
    }

    /**
     * Shortcode para exibir dados de espetáculos no RevSlider.
     * Uso: [cannal_banner_espetaculos]
     */
    public static function shortcode_banner_espetaculos( $atts ) {
        $atts = shortcode_atts( array(
            'limit' => 10
        ), $atts );

        $espetaculos = self::get_banner_espetaculos();
        $espetaculos = array_slice( $espetaculos, 0, intval( $atts['limit'] ) );

        $output = '<div class="cannal-banner-espetaculos">';
        
        foreach ( $espetaculos as $item ) {
            $data = self::get_slide_data( $item['post']->ID, $item['temporada_id'] );
            
            if ( ! $data ) {
                continue;
            }

            $output .= '<div class="cannal-banner-slide" style="background-image: url(' . esc_url( $data['image_url'] ) . ');">';
            $output .= '<div class="cannal-banner-content">';
            $output .= '<h2 class="banner-titulo">' . esc_html( $data['titulo'] ) . '</h2>';
            $output .= '<p class="banner-teatro">' . esc_html( $data['teatro'] ) . '</p>';
            $output .= '<p class="banner-horarios">' . esc_html( $data['dias_horarios'] ) . '</p>';
            
            if ( $data['link_vendas'] ) {
                $output .= '<a href="' . esc_url( $data['link_vendas'] ) . '" class="banner-button-ingressos" target="_blank">' . esc_html( $data['link_texto'] ) . '</a>';
            }
            
            $output .= '<a href="' . esc_url( $data['espetaculo_url'] ) . '" class="banner-link-espetaculo">Ver mais</a>';
            $output .= '</div>';
            $output .= '</div>';
        }
        
        $output .= '</div>';

        return $output;
    }

    /**
     * Registra o shortcode.
     */
    public static function register_shortcode() {
        add_shortcode( 'cannal_banner_espetaculos', array( __CLASS__, 'shortcode_banner_espetaculos' ) );
    }

    /**
     * Hook para atualizar automaticamente a temporada do banner quando uma temporada é salva.
     */
    public static function on_temporada_save( $post_id ) {
        if ( get_post_type( $post_id ) !== 'temporada' ) {
            return;
        }

        $espetaculo_id = get_post_meta( $post_id, '_temporada_espetaculo_id', true );
        
        if ( $espetaculo_id ) {
            self::update_banner_temporada( $espetaculo_id );
        }
    }
}

// Registrar hooks e shortcodes
add_action( 'init', array( 'Cannal_Espetaculos_RevSlider', 'register_shortcode' ) );
add_action( 'save_post', array( 'Cannal_Espetaculos_RevSlider', 'on_temporada_save' ) );
