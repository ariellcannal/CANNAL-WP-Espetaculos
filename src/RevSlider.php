<?php

/**
 * Integração com RevSlider para banners de espetáculos.
 *
 * Utiliza a abordagem nativa Post-Based do RevSlider, filtrando os espetáculos
 * elegíveis via 'revslider_get_posts' e injetando dados extras via
 * 'sr_streamline_post_data_post' para substituição automática de placeholders.
 *
 * Regras de negócio:
 * - Grupo 1 (Em Cartaz): data_inicio <= hoje + 6 dias. Ordenado ASC por data_inicio.
 * - Grupo 2 (Em Breve): data_inicio > hoje + 6 dias E (banner_data vazia OU <= hoje).
 * - Apenas espetáculos com imagem destacada são incluídos.
 *
 * Performance: resultados em cache via WordPress Transients por 12 horas.
 * Cache invalidado ao salvar espetáculo ou temporada.
 *
 * @package    CANNALEspetaculos_Plugin
 * @subpackage CANNALEspetaculos_Plugin/includes
 */
class CANNALEspetaculos_RevSlider
{
    
    /**
     * Chave do transient para os dados de espetáculos do banner.
     */
    const TRANSIENT_KEY = 'cannal_revslider_espetaculos';
    
    /**
     * Expiração do transient em segundos (12 horas).
     */
    const TRANSIENT_EXPIRY = 43200;
    
    const replace_fields = [
        'espetaculo_id',
        'temporada_id',
        'grupo',
        'teatro_nome',
        'teatro_endereco',
        'dias_horarios',
        'data_inicio',
        'data_fim',
        'periodo',
        'valores',
        'link_vendas',
        'link_texto',
        'destaque1',
        'destaque2',
        'status',
        'autor',
        'ano_estreia',
        'duracao',
        'classificacao',
        'logotipo',
        'logotipo_branco',
        'logotipo_preto',
        'diretor',
        'elenco',
        'espetaculo_url',
        'sinopse'
    ];
    
    // -------------------------------------------------------------------------
    // FILTROS DE LAYERS (MODO CUSTOM)
    // -------------------------------------------------------------------------
    
    /**
     * Injeta os dados extras do espetáculo no array de dados do post do RevSlider.
     * Isso permite que o RevSlider substitua automaticamente os placeholders {{meta:chave}}
     * nas layers do slide template.
     *
     * @param array $post_data Array de dados dos posts já processados pelo RevSlider.
     * @param array $data      Array de dados originais dos posts.
     * @param array $metas     Array de metas usados no slider.
     * @param object $slider   Instância do slider.
     * @return array Array de dados modificado.
     */
    public static function filter_streamline_post_data($post_data, $data, $metas, $slider)
    {
        if (empty($post_data) || ! is_array($post_data)) {
            return $post_data;
        }
        
        // Buscar espetáculos elegíveis (com cache via transient)
        $espetaculos = self::get_espetaculos_para_banner();
        if (empty($espetaculos)) {
            return $post_data;
        }
        
        // Criar um mapa de espetáculos por ID para busca rápida
        $espetaculos_map = array();
        foreach ($espetaculos as $item) {
            $espetaculos_map[$item['espetaculo_id']] = $item;
        }
        
        foreach ($post_data as &$post) {
            $post_id = isset($post['id']) ? intval($post['id']) : 0;
            
            if (! $post_id || ! isset($espetaculos_map[$post_id])) {
                continue;
            }
            
            $item = $espetaculos_map[$post_id];
            
            if (! isset($post['meta']) || ! is_array($post['meta'])) {
                $post['meta'] = array();
            }
            
            // Faz as substituições dentro do destaque1 e destaque2
            foreach (['destaque1', 'destaque2'] as $tag) {
                if (! empty($item[$tag])) {
                    foreach (self::replace_fields as $field) {
                        $item[$tag] = str_replace('{{' . $field . '}}', ($item[$field] ?? ''), $item[$tag]);
                    }
                }
            }
            
            foreach (self::replace_fields as $field) {
                if (isset($item[$field])) {
                    $post['meta'][$field] = $item[$field];
                    $post[$field] = $item[$field];
                }
            }
        }
        unset($post);
        
        return $post_data;
    }
    
    /**
     * Retorna os espetáculos elegíveis para o banner, com cache via transient.
     *
     * @return array Lista de arrays com chaves dos espetaculos
     */
    public static function get_espetaculos_para_banner()
    {
        // Transients desativados quando WP_DEBUG está ativo.
        $use_cache = ! (defined('WP_DEBUG') && WP_DEBUG);
        
        if ($use_cache) {
            $cached = get_transient(self::TRANSIENT_KEY);
            if (false !== $cached) {
                return $cached;
            }
        }
        
        $hoje = current_time('Y-m-d');
        // Limite de 6 dias (se a estreia é segunda, a partir da terça anterior já entra)
        $limite_cartaz = date('Y-m-d', strtotime('+6 days', strtotime($hoje)));
        
        // Buscar todas as temporadas ordenadas pela data de início
        $args = array(
            'post_type'      => 'temporada',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_key'       => '_temporada_data_inicio',
            'orderby'        => 'meta_value',
            'order'          => 'ASC'
        );
        $temporadas = get_posts($args);
        
        $grupo_cartaz = array();
        $grupo_breve  = array();
        $espetaculo_ids_vistos = array(); // Evitar duplicatas de espetáculos
        
        foreach ($temporadas as $temporada) {
            $espetaculo_id = intval(get_post_meta($temporada->ID, '_temporada_espetaculo_id', true));
            
            // Validações básicas e impedimento de duplicação do mesmo espetáculo
            if (! $espetaculo_id || ! has_post_thumbnail($espetaculo_id) || in_array($espetaculo_id, $espetaculo_ids_vistos, true)) {
                continue;
            }
            
            $data_inicio = get_post_meta($temporada->ID, '_temporada_data_inicio', true);
            $data_fim    = get_post_meta($temporada->ID, '_temporada_data_fim', true);
            $banner_data = get_post_meta($temporada->ID, '_temporada_data_banner', true);
            
            // Ignorar se a temporada já foi encerrada
            if (! empty($data_fim) && $data_fim < $hoje) {
                continue;
            }
            
            // Ignorar se não possui data de inicio cadastrada
            if (empty($data_inicio)) {
                continue;
            }
            
            $status = CANNALEspetaculos_DiasHorarios::get_status_temporada($temporada);
            if ($status == __('Em Cartaz', 'cannal-espetaculos')) {
                // Grupo 1: Em cartaz (Estreia em menos de 6 dias ou já estreou)
                $grupo_cartaz[] = array(
                    'espetaculo_id' => $espetaculo_id,
                    'temporada_id' => $temporada->ID,
                    'grupo' => 'em_cartaz'
                );
                $espetaculo_ids_vistos[] = $espetaculo_id;
            } else if ($status == __('Em Breve', 'cannal-espetaculos') && (empty($banner_data) || $banner_data <= $hoje)) {
                // Grupo 2: Em breve (Ainda não estreou e faltam mais de 6 dias)
                // Regra do banner: Data do banner precisa ser vazia ou já ter passado
                $grupo_breve[] = array(
                    'espetaculo_id' => $espetaculo_id,
                    'temporada_id' => $temporada->ID,
                    'grupo' => 'em_breve'
                );
                $espetaculo_ids_vistos[] = $espetaculo_id;
            } else if ($status == __('Última Semana', 'cannal-espetaculos')) {
                $grupo_ultima_semana[] = array(
                    'espetaculo_id' => $espetaculo_id,
                    'temporada_id' => $temporada->ID,
                    'grupo' => 'ultima_semana'
                );
                $espetaculo_ids_vistos[] = $espetaculo_id;
            }
        }
        
        // Os arrays já estão naturalmente ordenados por data_inicio ASC porque o get_posts os trouxe assim
        $temporadas_processadas = array_merge($grupo_ultima_semana, $grupo_cartaz, $grupo_breve);
        
        $resultado = array();
        foreach ($temporadas_processadas as $item) {
            $resultado[] = self::montar_item_espetaculo($item['espetaculo_id'], $item['temporada_id'], $item['grupo']);
        }
        
        if ($use_cache) {
            set_transient(self::TRANSIENT_KEY, $resultado, self::TRANSIENT_EXPIRY);
        }
        if(WP_DEBUG){
            //return [$resultado[1]]; 
        }
        return $resultado;
    }
    
    /**
     * Monta o array de dados de um espetáculo.
     */
    private static function montar_item_espetaculo($espetaculo_id, $temporada_id, $grupo)
    {
        // --- Metas da temporada ---
        $teatro_nome     = get_post_meta($temporada_id, '_temporada_teatro_nome', true);
        $teatro_endereco = get_post_meta($temporada_id, '_temporada_teatro_endereco', true);
        $elenco          = get_post_meta($temporada_id, '_temporada_elenco', true);
        $valores         = get_post_meta($temporada_id, '_temporada_valores', true);
        $link_vendas     = get_post_meta($temporada_id, '_temporada_link_vendas', true);
        $link_texto      = get_post_meta($temporada_id, '_temporada_link_texto', true);
        $destaque1       = get_post_meta($temporada_id, '_temporada_banner_destaque1', true);
        $destaque2       = get_post_meta($temporada_id, '_temporada_banner_destaque2', true);
        $tipo_sessao     = get_post_meta($temporada_id, '_temporada_tipo_sessao', true);
        $data_inicio_raw = get_post_meta($temporada_id, '_temporada_data_inicio', true);
        $data_fim_raw    = get_post_meta($temporada_id, '_temporada_data_fim', true);
        $sessoes_raw     = get_post_meta($temporada_id, '_temporada_sessoes_data', true);
        
        $dias_horarios   = CANNALEspetaculos_DiasHorarios::gerar((string) $tipo_sessao, (string) $sessoes_raw);
        $periodo         = CANNALEspetaculos_DiasHorarios::get_periodo_temporada($data_inicio_raw, $data_fim_raw);
        $data_inicio     = CANNALEspetaculos_DiasHorarios::get_data_formatada($data_inicio_raw);
        $data_fim        = CANNALEspetaculos_DiasHorarios::get_data_formatada($data_fim_raw);
        
        // --- Metas do espetáculo ---
        $diretor         = get_post_meta($espetaculo_id, '_espetaculo_diretor', true);
        $elenco          = ! empty($elenco) ? $elenco : get_post_meta($espetaculo_id, '_espetaculo_elenco', true);
        $sinopse         = get_post_meta($espetaculo_id, '_espetaculo_sinopse', true);
        $autor           = get_post_meta($espetaculo_id, '_espetaculo_autor', true);
        $ano_estreia     = get_post_meta($espetaculo_id, '_espetaculo_ano_estreia', true);
        $duracao         = get_post_meta($espetaculo_id, '_espetaculo_duracao', true);
        $classificacao   = get_post_meta($espetaculo_id, '_espetaculo_classificacao', true);
        
        // Pega os IDs através da URL salva
        $id_logo        = attachment_url_to_postid(get_post_meta($espetaculo_id, '_espetaculo_logotipo', true));
        $id_logo_branco = attachment_url_to_postid(get_post_meta($espetaculo_id, '_espetaculo_logotipo_branco', true));
        $id_logo_preto  = attachment_url_to_postid(get_post_meta($espetaculo_id, '_espetaculo_logotipo_preto', true));
        
        // Retorna apenas as URLs no tamanho 'medium' (com fallback para o meta original)
        $logotipo        = $id_logo ? wp_get_attachment_image_url($id_logo, 'medium') : get_post_meta($espetaculo_id, '_espetaculo_logotipo', true);
        $logotipo_branco = $id_logo_branco ? wp_get_attachment_image_url($id_logo_branco, 'medium') : get_post_meta($espetaculo_id, '_espetaculo_logotipo_branco', true);
        $logotipo_preto  = $id_logo_preto ? wp_get_attachment_image_url($id_logo_preto, 'medium') : get_post_meta($espetaculo_id, '_espetaculo_logotipo_preto', true);
        
        // --- Preparar dados extras ---
        $espetaculo_url = get_permalink($espetaculo_id);
        $link_texto = ! empty($link_texto) ? $link_texto : __('Ingressos Aqui', 'cannal-espetaculos');
        
        if ($grupo == 'em_cartaz') {
            $status = __('Em Cartaz', 'cannal-espetaculos');
        } else if ($grupo == 'ultima_semana') {
            $status = __('Última Semana', 'cannal-espetaculos');
        } else {
            $status = __('Em Breve', 'cannal-espetaculos') . ': <span class="data">' . $data_inicio . '</span>';
        }
        
        $return = [];
        foreach (self::replace_fields as $field) {
            if (! isset(${$field})) {
                continue;
            } else if (! in_array($field, ['dias_horarios', 'periodo', 'data_inicio', 'data_fim', 'logotipo', 'logotipo_preto', 'logotipo_branco'])) {
                ${$field} = esc_html(${$field});
            }
            
            if ($field == 'status') {
                $return[$field] = '<span class="' . esc_attr($field . ' ' . $field . '-' . $grupo) . '">' . $status . '</span>';
            } else if ($field == 'classificacao') {
                $return[$field] = '<span class="' . esc_attr($field . ' ' . $field . '-' . $classificacao) . '">' . $classificacao . '</span>';
            } else if ($field == 'duracao') {
                $return[$field] = '<span class="' . esc_attr($field) . '">' . sprintf(__('%s minutos', 'cannal-espetaculos'), $duracao) . "</span>";
            } else if (! in_array($field, ['espetaculo_id', 'temporada_id', 'logotipo', 'logotipo_preto', 'logotipo_branco'])) {
                $return[$field] = '<span class="' . esc_attr($field) . '">' . ${$field} . '</span>';
            } else {
                $return[$field] = ${$field};
            }
        }
        return $return;
    }
    
    // -------------------------------------------------------------------------
    // INVALIDAÇÃO DE CACHE
    // -------------------------------------------------------------------------
    
    public static function invalidar_cache($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (wp_is_post_revision($post_id)) {
            return;
        }
        
        $post_type = get_post_type($post_id);
        
        if (in_array($post_type, array('espetaculo', 'temporada'), true)) {
            delete_transient(self::TRANSIENT_KEY);
        }
    }
    
    /**
     * filter revslider_get_posts
     * Injeta a query customizada usando EXATAMENTE os mesmos dados da lógica geral.
     */
    public static function filter_cartaz_slider_posts($query_args, $slider = null)
    {
        $is_espetaculos_template = false;
        $slides = array();
        
        // 1. Obter os slides
        if (is_object($slider)) {
            if (method_exists($slider, 'get_slides')) {
                $slides = $slider->get_slides();
            } elseif (method_exists($slider, 'getSlides')) {
                $slides = $slider->getSlides();
            }
        } elseif (is_numeric($slider) && $slider > 0 && class_exists('RevSliderSlider')) {
            try {
                $slider_obj = new RevSliderSlider();
                if (method_exists($slider_obj, 'initByID')) {
                    $slider_obj->initByID(intval($slider));
                } elseif (method_exists($slider_obj, 'init_by_id')) {
                    $slider_obj->init_by_id(intval($slider));
                }
                
                if (method_exists($slider_obj, 'get_slides')) {
                    $slides = $slider_obj->get_slides();
                } elseif (method_exists($slider_obj, 'getSlides')) {
                    $slides = $slider_obj->getSlides();
                }
            } catch (Exception $e) {}
        }
        
        // 2. Vasculhar os slides encontrados buscando a flag de template do espetaculo
        if (! empty($slides) && is_array($slides)) {
            foreach ($slides as $slide) {
                $params = null;
                if (method_exists($slide, 'get_params')) {
                    $params = $slide->get_params();
                } elseif (method_exists($slide, 'getParams')) {
                    $params = $slide->getParams();
                }
                
                if (! empty($params) && isset($params['attr']['class']) && strpos($params['attr']['class'], 'espetaculos_banner') !== false) {
                    $is_espetaculos_template = true;
                    break;
                }
            }
        }
        
        if (! $is_espetaculos_template) {
            return $query_args;
        }
        
        // --- A partir daqui, injetamos a nossa query customizada ---
        
        // Aproveitamos o mesmo método central, garantindo que o array post__in
        // obedeça exatamente a mesma ordem das camadas substituidas (e utilize o cache)
        $espetaculos_banner = self::get_espetaculos_para_banner();
        $espetaculo_ids = array();
        
        if (! empty($espetaculos_banner)) {
            foreach ($espetaculos_banner as $item) {
                if (!empty($item['espetaculo_id'])) {
                    $espetaculo_ids[] = intval($item['espetaculo_id']);
                }
            }
        }
        
        if (empty($espetaculo_ids)) {
            $query_args['post__in'] = array(0);
            $query_args['posts_per_page'] = 0;
            return $query_args;
        }
        
        $query_args['post_type'] = 'espetaculo';
        $query_args['post__in'] = $espetaculo_ids;
        $query_args['orderby'] = 'post__in'; // OBRIGATÓRIO para forçar o WP a respeitar a ordem do array
        $query_args['posts_per_page'] = count($espetaculo_ids);
        $query_args['ignore_sticky_posts'] = true;
        
        return $query_args;
    }
}

// -------------------------------------------------------------------------
// REGISTRO DE HOOKS E SHORTCODES
// -------------------------------------------------------------------------

// Injetar dados formatados nas Layers
add_filter('sr_streamline_post_data_post', array('CANNALEspetaculos_RevSlider', 'filter_streamline_post_data'), 10, 4);

// Selecionar os posts e a ordem que compõem os slides
add_filter('revslider_get_posts', array('CANNALEspetaculos_RevSlider', 'filter_cartaz_slider_posts'), 10, 2);

// Limpeza de cache ao salvar
add_action('save_post', array('CANNALEspetaculos_RevSlider', 'invalidar_cache'));