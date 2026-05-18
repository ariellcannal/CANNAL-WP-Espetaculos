<?php

/**
 * Classe para geração inteligente de texto de Dias e Horários
 *
 * @package    CANNALEspetaculos_Plugin
 * @subpackage CANNALEspetaculos_Plugin/src
 */
class CANNALEspetaculos_DiasHorarios
{
    public static function gerar($tipo_sessao, $sessoes_data)
    {
        if (empty($sessoes_data)) return '';
        
        $sessoes = json_decode($sessoes_data, true);
        if (!$sessoes || !isset($sessoes['tipo'])) return '';
        
        $out = '';
        if ($sessoes['tipo'] === 'avulsas' && !empty($sessoes['avulsas'])) {
            $out = self::gerar_avulsas($sessoes['avulsas']);
        } elseif ($sessoes['tipo'] === 'temporada' && !empty($sessoes['temporada'])) {
            $out = self::gerar_temporada($sessoes['temporada']);
        }
        
        // Mantido para as sessões avulsas, a nova temporada lida com isso nativamente
        return str_replace(':00', '', $out);
    }
    
    public static function get_status_temporada($temporada)
    {
        $temporada_id = ($temporada instanceof WP_Post) ? $temporada->ID : (is_numeric($temporada) ? (int) $temporada : 0);
        if (!$temporada_id) return null;
        
        $data_inicio            = get_post_meta($temporada_id, '_temporada_data_inicio', true);
        $data_fim               = get_post_meta($temporada_id, '_temporada_data_fim', true);
        $tipo_sessao            = get_post_meta($temporada_id, '_temporada_tipo_sessao', true);
        $hoje                   = wp_date('Y-m-d');
        $fim_penultimo_ciclo    = !empty($data_fim)? date('Y-m-d', strtotime('-7 days', strtotime($data_fim))):null;
        
        if ($data_fim && $data_fim < $hoje) {
            return __('Encerrada', 'cannal-espetaculos');
        } elseif ($tipo_sessao == "temporada" && $hoje > $fim_penultimo_ciclo) {
            return __('Última Semana', 'cannal-espetaculos');
        } elseif ($data_inicio && $data_inicio <= $hoje && (!$data_fim || $data_fim >= $hoje)) {
            return __('Em Cartaz', 'cannal-espetaculos');
        } elseif ($data_inicio && $data_inicio > $hoje) {
            return __('Em Breve', 'cannal-espetaculos');
        }
        
        return __('Sem datas', 'cannal-espetaculos');
    }
    
    public static function get_data_formatada($data)
    {
        if (!$data) return null;
        
        $ts = strtotime($data);
        $formato = (wp_date('Y', $ts) === wp_date('Y'))
        ? __('d \d\e M', 'cannal-espetaculos')
        : __('d \d\e M \d\e Y', 'cannal-espetaculos');
        
        return mb_strtolower(wp_date($formato, $ts), 'UTF-8');
    }
    
    public static function get_periodo_temporada($data_inicio, $data_fim)
    {
        $inicio = self::get_data_formatada($data_inicio);
        $fim    = self::get_data_formatada($data_fim);
        
        if ($inicio && $fim) return sprintf(__('de %1$s até %2$s', 'cannal-espetaculos'), $inicio, $fim);
        if ($inicio)         return sprintf(__('a partir de %s', 'cannal-espetaculos'), $inicio);
        if ($fim)            return sprintf(__('até %s', 'cannal-espetaculos'), $fim);
        
        return null;
    }
    
    /* =========================================================
     * TEMPORADAS REGULARES (Com Ciclo Teatral Avançado)
     * ========================================================= */
    
    private static function gerar_temporada($temporada_data)
    {
        global $wp_locale;
        
        $mapa_legado = array(
            'domingo' => 0, 'segunda' => 1, 'terca' => 2,
            'quarta' => 3, 'quinta' => 4, 'sexta' => 5, 'sabado' => 6
        );
        
        $dias_ativos   = array();
        $dias_horarios = array();
        
        // 1. Normalização e Agrupamento Bruto
        foreach ($temporada_data as $dia_key => $horarios_str) {
            $dia_num = isset($mapa_legado[$dia_key]) ? $mapa_legado[$dia_key] : (int) $dia_key;
            if ($dia_num < 0 || $dia_num > 6) continue;
            
            $horarios = array();
            foreach (explode(',', $horarios_str) as $h) {
                $h = trim($h);
                if ($h) $horarios[] = $h;
            }
            
            if (!empty($horarios)) {
                sort($horarios); // Garante que pacotes iguais sejam idênticos independente da ordem salva
                $dias_ativos[] = $dia_num;
                $dias_horarios[$dia_num] = $horarios;
            }
        }
        
        if (empty($dias_ativos)) return '';
        
        // 2. A Bússola: Descobrir o Início do Ciclo Teatral
        $dias_ativos = array_unique($dias_ativos);
        sort($dias_ativos);
        
        $start_day = 1; // Padrão: Segunda-feira
        $total_dias = count($dias_ativos);
        
        if ($total_dias > 1) {
            $max_gap = 0;
            $start_day_candidate = $dias_ativos[0];
            
            for ($i = 0; $i < $total_dias; $i++) {
                $next_i = ($i + 1) % $total_dias;
                // Calcula a distância do buraco (circular)
                $gap = ($dias_ativos[$next_i] - $dias_ativos[$i] + 7) % 7;
                
                if ($gap > $max_gap) {
                    $max_gap = $gap;
                    $start_day_candidate = $dias_ativos[$next_i];
                }
            }
            $start_day = $start_day_candidate;
        } elseif ($total_dias === 1) {
            $start_day = $dias_ativos[0];
        }
        
        // Constrói a régua da semana baseada no Início do Ciclo
        $week_order = array();
        for ($i = 0; $i < 7; $i++) {
            $week_order[] = ($start_day + $i) % 7;
        }
        
        // 3. Agrupar Dias por Pacotes de Horários Exatos
        $grupos = array();
        foreach ($dias_horarios as $dia_num => $horarios) {
            $time_str = self::format_time_package($horarios);
            if (!isset($grupos[$time_str])) {
                $grupos[$time_str] = array();
            }
            $grupos[$time_str][] = $dia_num;
        }
        
        // 4. Processar e Formatar Textos
        $partes_grupo = array();
        foreach ($grupos as $time_str => $dias) {
            // Ordena os dias respeitando a régua do ciclo teatral
            usort($dias, function($a, $b) use ($week_order) {
                return array_search($a, $week_order) - array_search($b, $week_order);
            });
                
                // Guarda qual o primeiro dia desse grupo para ordenação final
                $first_day_idx = array_search($dias[0], $week_order);
                
                // Fatiamento inteligente dos dias
                $dias_formatados = self::format_days_chunked($dias, $week_order, $wp_locale);
                
                $texto = sprintf(__('%1$s <span class="horarios">%2$s</span>', 'cannal-espetaculos'), $dias_formatados, $time_str);
                
                $partes_grupo[] = array(
                    'texto'         => $texto,
                    'first_day_idx' => $first_day_idx
                );
        }
        
        // 5. Ordenar grupos cronologicamente na frase
        usort($partes_grupo, function($a, $b) {
            return $a['first_day_idx'] - $b['first_day_idx'];
        });
            
            // Extrai apenas as strings e monta o texto
            $textos_finais = array();
            foreach ($partes_grupo as $pg) {
                $textos_finais[] = $pg['texto'];
            }
            
            return implode('. ', $textos_finais) . '.';
    }
    
    /**
     * Formata um pacote de horários lidando com minutos quebrados e múltiplos
     */
    private static function format_time_package($horarios)
    {
        $formatted = array();
        foreach ($horarios as $h) {
            $h = substr($h, 0, 5); // "20:00" ou "19:30"
            $minutos = substr($h, 3, 2);
            $hora    = substr($h, 0, 2);
            
            $f = ($minutos === '00') ? $hora . 'h' : $hora . 'h' . $minutos;
            $formatted[] = sprintf(__('às <span class="horas">%s</span>', 'cannal-espetaculos'), $f);
        }
        
        return self::format_list($formatted);
    }
    
    /**
     * Fatiador lógico: Descobre se os dias são picados ou contínuos (usando a régua do ciclo)
     */
    private static function format_days_chunked($dias, $week_order, $wp_locale)
    {
        $chunks = array();
        $current_chunk = array();
        $last_idx = -2;
        
        foreach ($dias as $day) {
            $idx = array_search($day, $week_order);
            // Se o índice pular em +1 exato, é consecutivo
            if ($idx === $last_idx + 1) {
                $current_chunk[] = $day;
            } else {
                if (!empty($current_chunk)) {
                    $chunks[] = $current_chunk;
                }
                $current_chunk = array($day);
            }
            $last_idx = $idx;
        }
        if (!empty($current_chunk)) {
            $chunks[] = $current_chunk;
        }
        
        // Constrói os textos a partir das fatias
        $items = array();
        foreach ($chunks as $chunk) {
            if (count($chunk) >= 3) {
                $start = $wp_locale->get_weekday_abbrev($wp_locale->get_weekday($chunk[0]));
                $end   = $wp_locale->get_weekday_abbrev($wp_locale->get_weekday(end($chunk)));
                $items[] = sprintf(__('<span class="dias">%1$s</span> a <span class="dias">%2$s</span>', 'cannal-espetaculos'), $start, $end);
            } else {
                foreach ($chunk as $d) {
                    $items[] = $wp_locale->get_weekday_abbrev($wp_locale->get_weekday($d));
                }
            }
        }
        
        return self::format_list($items);
    }
    
    /* =========================================================
     * SESSÕES AVULSAS E AJUDANTES GENÉRICOS
     * ========================================================= */
    
    private static function gerar_avulsas($sessoes)
    {
        $grupos = array();
        
        foreach ($sessoes as $sessao) {
            $ts      = strtotime($sessao['data']);
            $horario = substr($sessao['horario'], 0, 5);
            $chave   = wp_date('Y-m', $ts) . '-' . $horario;
            
            if (!isset($grupos[$chave])) {
                $grupos[$chave] = array('ts' => $ts, 'horario' => $horario, 'dias' => array());
            }
            $grupos[$chave]['dias'][] = (int) wp_date('j', $ts);
        }
        
        $chave_principal = '';
        $max_dias = 0;
        foreach ($grupos as $k => $g) {
            if (count($g['dias']) > $max_dias) {
                $max_dias = count($g['dias']);
                $chave_principal = $k;
            }
        }
        
        $principal = $grupos[$chave_principal];
        unset($grupos[$chave_principal]);
        
        $partes     = array(self::format_grupo_avulso($principal));
        $extras_str = array();
        
        foreach ($grupos as $grupo) {
            $extras_str[] = self::format_grupo_avulso($grupo);
        }
        
        $texto = implode('. ', $partes);
        
        if (!empty($extras_str)) {
            $label = _n('Sessão extra', 'Sessões extras', count($extras_str), 'cannal-espetaculos');
            $texto .= sprintf(__('. %1$s: %2$s', 'cannal-espetaculos'), $label, self::format_list($extras_str));
        }
        
        return $texto . '.';
    }
    
    private static function format_grupo_avulso($grupo)
    {
        $dias = $grupo['dias'];
        sort($dias);
        $mes_nome = mb_strtolower(wp_date('M', $grupo['ts']), 'UTF-8');
        
        if (count($dias) >= 3 && self::is_consecutive_math($dias)) {
            return sprintf(
                __('De <span class="dias">%1$s</span> a <span class="dias">%2$s</span> de <span class="meses">%3$s</span> às <span class="horarios">%4$sh</span>', 'cannal-espetaculos'),
                reset($dias), end($dias), $mes_nome, $grupo['horario']
                );
        }
        
        $dias_texto = self::format_list($dias);
        $formato    = (count($dias) === 1)
        ? __('<span class="dias">%1$s</span> de <span class="meses">%2$s</span> às <span class="horarios">%3$sh</span>', 'cannal-espetaculos')
        : __('Dias <span class="dias">%1$s</span> de <span class="meses">%2$s</span> às <span class="horarios">%3$sh</span>', 'cannal-espetaculos');
        
        return sprintf($formato, $dias_texto, $mes_nome, $grupo['horario']);
    }
    
    private static function format_list($items)
    {
        $count = count($items);
        if ($count === 0) return '';
        if ($count === 1) return $items[0];
        if ($count === 2) return sprintf(__('<span class="dias">%1$s</span> e <span class="dias">%2$s</span>', 'cannal-espetaculos'), $items[0], $items[1]);
        
        $last = array_pop($items);
        return sprintf(__('<span class="dias">%1$s</span> e <span class="dias">%2$s</span>', 'cannal-espetaculos'), implode(', ', $items), $last);
    }
    
    private static function is_consecutive_math($numeros)
    {
        if (empty($numeros)) return false;
        return (end($numeros) - reset($numeros)) === (count($numeros) - 1);
    }
}