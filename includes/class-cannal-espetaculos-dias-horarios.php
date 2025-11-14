<?php
/**
 * Classe para geração inteligente de texto de Dias e Horários
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/includes
 */

class Cannal_Espetaculos_Dias_Horarios {

    /**
     * Gera texto formatado de dias e horários
     *
     * @param string $tipo_sessao Tipo: 'avulsas' ou 'temporada'
     * @param string $sessoes_data JSON com dados das sessões
     * @return string Texto formatado
     */
    public static function gerar( $tipo_sessao, $sessoes_data ) {
        if ( empty( $sessoes_data ) ) {
            return '';
        }

        $sessoes = json_decode( $sessoes_data, true );
        
        if ( ! $sessoes || ! isset( $sessoes['tipo'] ) ) {
            return '';
        }

        if ( $sessoes['tipo'] === 'avulsas' && ! empty( $sessoes['avulsas'] ) ) {
            return self::gerar_avulsas( $sessoes['avulsas'] );
        } elseif ( $sessoes['tipo'] === 'temporada' && ! empty( $sessoes['temporada'] ) ) {
            return self::gerar_temporada( $sessoes['temporada'] );
        }

        return '';
    }

    /**
     * Gera texto para sessões avulsas
     */
    private static function gerar_avulsas( $sessoes ) {
        // Ordenar por data e horário
        usort( $sessoes, function( $a, $b ) {
            $cmp = strcmp( $a['data'], $b['data'] );
            if ( $cmp === 0 ) {
                return strcmp( $a['horario'], $b['horario'] );
            }
            return $cmp;
        } );

        // Agrupar por mês e horário
        $grupos = self::agrupar_sessoes_avulsas( $sessoes );

        // Identificar padrão principal e sessões extras
        $resultado = self::formatar_grupos_avulsas( $grupos );

        return $resultado;
    }

    /**
     * Agrupa sessões avulsas por mês e horário
     */
    private static function agrupar_sessoes_avulsas( $sessoes ) {
        $grupos = array();

        foreach ( $sessoes as $sessao ) {
            $data = $sessao['data'];
            $horario = $sessao['horario'];
            
            $timestamp = strtotime( $data );
            $mes = date( 'n', $timestamp ); // Mês numérico
            $ano = date( 'Y', $timestamp );
            $dia = date( 'j', $timestamp );
            
            $chave = $ano . '-' . $mes . '-' . $horario;
            
            if ( ! isset( $grupos[ $chave ] ) ) {
                $grupos[ $chave ] = array(
                    'mes' => $mes,
                    'ano' => $ano,
                    'horario' => $horario,
                    'datas' => array()
                );
            }
            
            $grupos[ $chave ]['datas'][] = array(
                'dia' => $dia,
                'data_completa' => $data,
                'timestamp' => $timestamp
            );
        }

        return $grupos;
    }

    /**
     * Formata grupos de sessões avulsas
     */
    private static function formatar_grupos_avulsas( $grupos ) {
        if ( empty( $grupos ) ) {
            return '';
        }

        // Encontrar grupo principal (maior número de sessões)
        $grupo_principal = null;
        $max_sessoes = 0;

        foreach ( $grupos as $grupo ) {
            $count = count( $grupo['datas'] );
            if ( $count > $max_sessoes ) {
                $max_sessoes = $count;
                $grupo_principal = $grupo;
            }
        }

        $partes = array();
        $extras = array();

        // Processar grupo principal
        if ( $grupo_principal ) {
            $texto_principal = self::formatar_grupo_avulso( $grupo_principal );
            $partes[] = $texto_principal;

            // Remover grupo principal da lista
            $chave_principal = $grupo_principal['ano'] . '-' . $grupo_principal['mes'] . '-' . $grupo_principal['horario'];
            unset( $grupos[ $chave_principal ] );
        }

        // Processar sessões extras
        foreach ( $grupos as $grupo ) {
            $extras[] = self::formatar_grupo_avulso( $grupo );
        }

        // Montar texto final
        $texto = implode( '. ', $partes );

        if ( ! empty( $extras ) ) {
            $label_extra = count( $extras ) > 1 ? 'Sessões extras' : 'Sessão extra';
            $texto .= '. ' . $label_extra . ': ' . implode( ', e ', $extras );
        }

        return $texto . '.';
    }

    /**
     * Formata um grupo de sessões avulsas
     */
    private static function formatar_grupo_avulso( $grupo ) {
        $datas = $grupo['datas'];
        $mes_nome = self::get_mes_abreviado( $grupo['mes'] );
        $horario = substr( $grupo['horario'], 0, 5 ); // Remove segundos

        // Verificar se são dias consecutivos
        $dias = array_column( $datas, 'dia' );
        sort( $dias );

        if ( count( $dias ) >= 3 && self::sao_consecutivos( $dias ) ) {
            // Usar formato "De X a Y de mês"
            $primeiro = $dias[0];
            $ultimo = $dias[ count( $dias ) - 1 ];
            return "De {$primeiro} a {$ultimo} de {$mes_nome} às {$horario}h";
        } else {
            // Listar dias individualmente
            $dias_texto = self::formatar_lista_dias( $dias );
            
            if ( count( $dias ) === 1 ) {
                return "{$dias_texto} de {$mes_nome} às {$horario}h";
            } else {
                return "Dias {$dias_texto} de {$mes_nome} às {$horario}h";
            }
        }
    }

    /**
     * Verifica se os dias são consecutivos
     */
    private static function sao_consecutivos( $dias ) {
        for ( $i = 1; $i < count( $dias ); $i++ ) {
            if ( $dias[ $i ] !== $dias[ $i - 1 ] + 1 ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Formata lista de dias com vírgulas e "e"
     */
    private static function formatar_lista_dias( $dias ) {
        if ( count( $dias ) === 1 ) {
            return (string) $dias[0];
        }

        if ( count( $dias ) === 2 ) {
            return $dias[0] . ' e ' . $dias[1];
        }

        $ultimos_dois = array_slice( $dias, -2 );
        $primeiros = array_slice( $dias, 0, -2 );

        $texto = implode( ', ', $primeiros );
        $texto .= ', ' . $ultimos_dois[0] . ' e ' . $ultimos_dois[1];

        return $texto;
    }

    /**
     * Gera texto para temporada (dias da semana recorrentes)
     */
    private static function gerar_temporada( $temporada_data ) {
        $dias_semana = array(
            'domingo' => 'Dom',
            'segunda' => 'Seg',
            'terca' => 'Ter',
            'quarta' => 'Qua',
            'quinta' => 'Qui',
            'sexta' => 'Sex',
            'sabado' => 'Sáb'
        );

        // Agrupar dias por horário
        $horarios_grupos = array();

        foreach ( $temporada_data as $dia => $horarios_str ) {
            $horarios = array_map( 'trim', explode( ',', $horarios_str ) );
            
            foreach ( $horarios as $horario ) {
                $horario = substr( $horario, 0, 5 ); // Remove segundos
                
                if ( ! isset( $horarios_grupos[ $horario ] ) ) {
                    $horarios_grupos[ $horario ] = array();
                }
                
                $horarios_grupos[ $horario ][] = $dia;
            }
        }

        // Formatar grupos
        $partes = array();

        foreach ( $horarios_grupos as $horario => $dias ) {
            $dias_abrev = array();
            
            foreach ( $dias as $dia ) {
                if ( isset( $dias_semana[ $dia ] ) ) {
                    $dias_abrev[] = $dias_semana[ $dia ];
                }
            }

            if ( empty( $dias_abrev ) ) {
                continue;
            }

            // Verificar se são dias consecutivos da semana
            $texto_dias = self::formatar_dias_semana( $dias, $dias_abrev );
            $partes[] = $texto_dias . ' às ' . $horario . 'h';
        }

        return implode( ', ', $partes ) . '.';
    }

    /**
     * Formata dias da semana (detecta sequências)
     */
    private static function formatar_dias_semana( $dias_completos, $dias_abrev ) {
        $ordem_semana = array( 'domingo', 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado' );
        
        // Ordenar dias pela ordem da semana
        $dias_ordenados = array();
        foreach ( $ordem_semana as $dia ) {
            if ( in_array( $dia, $dias_completos ) ) {
                $dias_ordenados[] = $dia;
            }
        }

        // Mapear para abreviações
        $dias_semana_map = array(
            'domingo' => 'Dom',
            'segunda' => 'Seg',
            'terca' => 'Ter',
            'quarta' => 'Qua',
            'quinta' => 'Qui',
            'sexta' => 'Sex',
            'sabado' => 'Sáb'
        );

        $abrev_ordenadas = array();
        foreach ( $dias_ordenados as $dia ) {
            $abrev_ordenadas[] = $dias_semana_map[ $dia ];
        }

        // Detectar sequências (3 ou mais dias consecutivos)
        if ( count( $abrev_ordenadas ) >= 3 && self::sao_dias_semana_consecutivos( $dias_ordenados, $ordem_semana ) ) {
            $primeiro = $abrev_ordenadas[0];
            $ultimo = $abrev_ordenadas[ count( $abrev_ordenadas ) - 1 ];
            return "De {$primeiro} a {$ultimo}";
        }

        // Listar individualmente
        if ( count( $abrev_ordenadas ) === 1 ) {
            return $abrev_ordenadas[0];
        }

        if ( count( $abrev_ordenadas ) === 2 ) {
            return $abrev_ordenadas[0] . ' e ' . $abrev_ordenadas[1];
        }

        $ultimos_dois = array_slice( $abrev_ordenadas, -2 );
        $primeiros = array_slice( $abrev_ordenadas, 0, -2 );

        return implode( ', ', $primeiros ) . ', ' . $ultimos_dois[0] . ' e ' . $ultimos_dois[1];
    }

    /**
     * Verifica se dias da semana são consecutivos
     */
    private static function sao_dias_semana_consecutivos( $dias, $ordem_semana ) {
        $indices = array();
        
        foreach ( $dias as $dia ) {
            $indices[] = array_search( $dia, $ordem_semana );
        }

        sort( $indices );

        for ( $i = 1; $i < count( $indices ); $i++ ) {
            if ( $indices[ $i ] !== $indices[ $i - 1 ] + 1 ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retorna nome do mês abreviado
     */
    private static function get_mes_abreviado( $mes ) {
        $meses = array(
            1 => 'jan',
            2 => 'fev',
            3 => 'mar',
            4 => 'abr',
            5 => 'mai',
            6 => 'jun',
            7 => 'jul',
            8 => 'ago',
            9 => 'set',
            10 => 'out',
            11 => 'nov',
            12 => 'dez'
        );

        return isset( $meses[ $mes ] ) ? $meses[ $mes ] : '';
    }
}
