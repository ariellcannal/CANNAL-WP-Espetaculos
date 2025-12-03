<?php
/**
 * Funções utilitárias do plugin.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/includes
 */

if ( ! function_exists( 'cannal_get_field' ) ) {
    /**
     * Recupera um meta field procurando primeiro na temporada mais recente.
     *
     * @param int    $espetaculo_id ID do espetáculo base para a busca.
     * @param string $field         Slug do campo sem prefixo ou com prefixo de meta.
     * @return mixed Valor encontrado ou string vazia.
     */
    function cannal_get_field( $espetaculo_id, $field ) {
        $espetaculo_id = intval( $espetaculo_id );

        if ( ! $espetaculo_id || empty( $field ) ) {
            return '';
        }

        $normalized_field = ltrim( $field, '_' );

        if ( strpos( $field, '_temporada_' ) === 0 ) {
            $temporada_key = $field;
            $espetaculo_key = str_replace( '_temporada_', '_espetaculo_', $field );
        } elseif ( strpos( $field, '_espetaculo_' ) === 0 ) {
            $temporada_key = str_replace( '_espetaculo_', '_temporada_', $field );
            $espetaculo_key = $field;
        } else {
            $temporada_key = '_temporada_' . $normalized_field;
            $espetaculo_key = '_espetaculo_' . $normalized_field;
        }

        $temporadas = get_posts(
            array(
                'post_type'      => 'temporada',
                'posts_per_page' => 1,
                'meta_query'     => array(
                    array(
                        'key'     => '_temporada_espetaculo_id',
                        'value'   => $espetaculo_id,
                        'compare' => '=',
                    ),
                ),
                'meta_key'       => '_temporada_data_inicio',
                'orderby'        => 'meta_value',
                'order'          => 'DESC',
            )
        );

        if ( ! empty( $temporadas ) ) {
            $temporada_value = get_post_meta( $temporadas[0]->ID, $temporada_key, true );

            if ( '' !== $temporada_value && null !== $temporada_value ) {
                return $temporada_value;
            }
        }

        return get_post_meta( $espetaculo_id, $espetaculo_key, true );
    }
}
