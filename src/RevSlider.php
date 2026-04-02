<?php
/**
 * Integração com RevSlider para banners de espetáculos.
 *
 * Utiliza o filtro revslider_get_slides_by_slider_id para clonar dinamicamente
 * um slide template (identificado pelo ID HTML "TEMPLATE_ESPETACULO") para cada
 * espetáculo elegível, substituindo placeholders nas camadas com dados reais.
 *
 * Regras de negócio:
 *   - Grupo 1 (Temporadas Ativas): _temporada_data_fim >= hoje, ordenado ASC por data_fim.
 *   - Grupo 2 (Próximas Temporadas): data_inicio_banner já passou mas temporada ainda não começou.
 *   - Apenas espetáculos com imagem destacada são incluídos.
 *
 * Performance: resultados em cache via WordPress Transients por 12 horas.
 * Cache invalidado ao salvar espetáculo ou temporada.
 *
 * @package    CANNALEspetaculos_Plugin
 * @subpackage CANNALEspetaculos_Plugin/includes
 */
class CANNALEspetaculos_RevSlider {

    /**
     * Chave do transient para os dados de espetáculos do banner.
     */
    const TRANSIENT_KEY = 'cannal_revslider_espetaculos';

    /**
     * Expiração do transient em segundos (12 horas).
     */
    const TRANSIENT_EXPIRY = 43200;

    // -------------------------------------------------------------------------
    // FILTRO PRINCIPAL: revslider_get_slides_by_slider_id
    // -------------------------------------------------------------------------

    /**
     * Filtra os slides do RevSlider para clonar o slide template TEMPLATE_ESPETACULO
     * para cada espetáculo elegível, substituindo placeholders com dados reais.
     *
     * @param array  $slides    Array de objetos de slide do RevSlider.
     * @param object $slider    Instância do slider.
     * @return array Array de slides modificado.
     */
    public static function filter_slides_by_slider_id( $slides, $slider ) {
        if ( ! is_array( $slides ) || empty( $slides ) ) {
            return $slides;
        }

        // 1. Identificar e remover o slide template da exibição original.
        $template_slide = null;
        $slides_sem_template = array();

        foreach ( $slides as $slide ) {
            // Verificar o ID HTML do slide via get_params().
            $params = method_exists( $slide, 'get_params' ) ? $slide->get_params() : array();
            $html_id = isset( $params['id'] ) ? $params['id'] : '';

            if ( 'TEMPLATE_ESPETACULO' === $html_id ) {
                // Guardar o template para clonagem — não o incluir na exibição.
                $template_slide = $slide;
            } else {
                $slides_sem_template[] = $slide;
            }
        }

        // Se não encontrou o template, retorna os slides sem modificação.
        if ( null === $template_slide ) {
            return $slides;
        }

        // 2. Buscar espetáculos elegíveis (com cache via transient).
        $espetaculos = self::get_espetaculos_para_banner();

        if ( empty( $espetaculos ) ) {
            // Sem espetáculos elegíveis: retorna slides sem o template.
            return $slides_sem_template;
        }

        // 3. Clonar o slide template para cada espetáculo e substituir placeholders.
        $slides_clonados = array();

        foreach ( $espetaculos as $item ) {
            $slide_clone = self::clonar_slide_com_dados( $template_slide, $item );

            if ( null !== $slide_clone ) {
                $slides_clonados[] = $slide_clone;
            }
        }

        // 4. Retornar: slides originais (sem template) + slides clonados.
        return array_merge( $slides_sem_template, $slides_clonados );
    }

    // -------------------------------------------------------------------------
    // CLONAGEM E SUBSTITUIÇÃO DE PLACEHOLDERS
    // -------------------------------------------------------------------------

    /**
     * Clona o slide template e injeta os dados do espetáculo nas camadas.
     *
     * Placeholders suportados nas camadas de texto (sem prefixo _espetaculo_ ou _temporada_):
     *   {{titulo}}              → Título do espetáculo
     *   {{espetaculo_url}}      → URL da página do espetáculo
     *   {{autor}}               → Autor do espetáculo
     *   {{ano_estreia}}         → Ano de estreia
     *   {{duracao}}             → Duração em minutos
     *   {{classificacao}}       → Classificação indicativa
     *   {{diretor}}             → Diretor (temporada ativa > próxima > espetáculo)
     *   {{elenco}}              → Elenco (temporada ativa > próxima > espetáculo)
     *   {{teatro_nome}}         → Nome do teatro da temporada
     *   {{teatro_endereco}}     → Endereço do teatro da temporada
     *   {{dias_horarios}}       → Dias e horários gerados dinamicamente
     *   {{data_inicio}}         → Data de início da temporada (d/m/Y)
     *   {{data_fim}}            → Data de fim da temporada (d/m/Y)
     *   {{data_inicio_cartaz}}  → Data de liberação do banner (d/m/Y)
     *   {{valores}}             → Valores dos ingressos
     *   {{link_vendas}}         → URL de venda de ingressos
     *   {{link_texto}}          → Texto do botão de ingressos
     *
     * Regras especiais:
     *   - &nbsp; no valor do postmeta → layer com visibility => off
     *   - Campo vazio após substituição → layer com visibility => off
     *
     * Layer de imagem (logotipo):
     *   A camada cujo src/url contenha o placeholder "{{logotipo}}" terá
     *   sua imagem substituída pela URL do _espetaculo_logotipo do espetáculo.
     *
     * Background do slide:
     *   Definido como a imagem destacada (banner) do espetáculo.
     *
     * @param object $template_slide Slide template original.
     * @param array  $item           Dados do espetáculo (ver get_espetaculos_para_banner).
     * @return object|null Slide clonado ou null em caso de erro.
     */
    private static function clonar_slide_com_dados( $template_slide, $item ) {
        // Clonar o objeto do slide em memória.
        $slide_clone = clone $template_slide;

        $espetaculo_id = $item['espetaculo_id'];
        $temporada_id  = $item['temporada_id'];

        // --- Background: imagem destacada do espetáculo ---
        $image_id  = get_post_thumbnail_id( $espetaculo_id );
        $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';

        if ( $image_url && method_exists( $slide_clone, 'get_params' ) && method_exists( $slide_clone, 'set_params' ) ) {
            $params = $slide_clone->get_params();

            // O RevSlider armazena o background em params['bg_image'] ou 'image'.
            if ( isset( $params['bg_image'] ) ) {
                $params['bg_image'] = $image_url;
            }
            if ( isset( $params['image'] ) ) {
                $params['image'] = $image_url;
            }

            $slide_clone->set_params( $params );
        }

        // --- Preparar mapa de substituição de placeholders ---
        // Tags sem prefixo _espetaculo_ ou _temporada_.
        // Prioridade: temporada ativa → próxima → meta do espetáculo (já resolvida em montar_item_espetaculo).

        $titulo         = get_the_title( $espetaculo_id );
        $espetaculo_url = CANNALEspetaculos_Rewrites::get_espetaculo_url( $espetaculo_id );

        // Formatar datas para exibição.
        $data_inicio_fmt = ! empty( $item['data_inicio'] ) ? date_i18n( 'd/m/Y', strtotime( $item['data_inicio'] ) ) : '';
        $data_fim_fmt    = ! empty( $item['data_fim'] )    ? date_i18n( 'd/m/Y', strtotime( $item['data_fim'] ) )    : '';
        $data_cartaz_fmt = ! empty( $item['data_inicio_cartaz'] ) ? date_i18n( 'd/m/Y', strtotime( $item['data_inicio_cartaz'] ) ) : '';

        // Texto do botão de ingressos: fallback para 'Ingressos Aqui'.
        $link_texto = ! empty( $item['link_texto'] ) ? $item['link_texto'] : 'Ingressos Aqui';

        // Mapa completo de placeholders → valores.
        // &nbsp; no valor é tratado como instrução para ocultar a layer (visibility => off).
        $placeholders = array(
            '{{titulo}}'           => $titulo,
            '{{espetaculo_url}}'   => $espetaculo_url,
            // Espetáculo
            '{{autor}}'            => $item['autor'],
            '{{ano_estreia}}'      => $item['ano_estreia'],
            '{{duracao}}'          => $item['duracao'],
            '{{classificacao}}'    => $item['classificacao'],
            // Temporada (com fallback já resolvido)
            '{{diretor}}'          => $item['diretor'],
            '{{elenco}}'           => $item['elenco'],
            '{{teatro_nome}}'      => $item['teatro_nome'],
            '{{teatro_endereco}}'  => $item['teatro_endereco'],
            '{{dias_horarios}}'    => $item['dias_horarios'],
            '{{data_inicio}}'      => $data_inicio_fmt,
            '{{data_fim}}'         => $data_fim_fmt,
            '{{data_inicio_cartaz}}' => $data_cartaz_fmt,
            '{{valores}}'          => $item['valores'],
            '{{link_vendas}}'      => $item['link_vendas'],
            '{{link_texto}}'       => $link_texto,
        );

        // --- URL do logotipo (postmeta _espetaculo_logotipo = attachment ID) ---
        $logotipo_id  = $item['logotipo_id'];
        $logotipo_url = '';

        if ( $logotipo_id && is_numeric( $logotipo_id ) ) {
            $logotipo_url = wp_get_attachment_image_url( intval( $logotipo_id ), 'full' );

            if ( ! $logotipo_url ) {
                // ID inválido ou anexo removido: não exibir layer de logotipo.
                $logotipo_url = '';
            }
        }

        // --- Manipular camadas (layers) ---
        if ( ! method_exists( $slide_clone, 'get_layers' ) || ! method_exists( $slide_clone, 'set_layers' ) ) {
            return $slide_clone;
        }

        $layers = $slide_clone->get_layers();

        if ( ! is_array( $layers ) ) {
            return $slide_clone;
        }

        foreach ( $layers as &$layer ) {
            if ( ! is_array( $layer ) ) {
                continue;
            }

            // Substituir placeholders em campos de texto das camadas.
            $text_fields = array( 'text', 'title', 'html', 'link', 'url' );

            foreach ( $text_fields as $field ) {
                if ( isset( $layer[ $field ] ) && is_string( $layer[ $field ] ) ) {
                    $layer[ $field ] = str_replace(
                        array_keys( $placeholders ),
                        array_values( $placeholders ),
                        $layer[ $field ]
                    );
                }
            }

            // Tratar layer de imagem do logotipo.
            // Identifica a layer pelo placeholder {{logotipo}} no src ou url.
            $is_logotipo_layer = false;

            foreach ( array( 'src', 'url', 'image_url' ) as $img_field ) {
                if ( isset( $layer[ $img_field ] ) && false !== strpos( $layer[ $img_field ], '{{logotipo}}' ) ) {
                    $is_logotipo_layer = true;

                    if ( ! empty( $logotipo_url ) ) {
                        // Substituir o placeholder pela URL real do logotipo.
                        $layer[ $img_field ] = $logotipo_url;
                    } else {
                        // Logotipo inválido ou ausente: ocultar a layer.
                        $layer['visibility'] = 'off';
                    }
                    break;
                }
            }

            // Ocultar layers de texto cujo conteúdo resultante esteja vazio
            // ou contenha &nbsp; (instrução explícita para ocultar a layer).
            if ( ! $is_logotipo_layer ) {
                foreach ( $text_fields as $field ) {
                    if ( isset( $layer[ $field ] ) && is_string( $layer[ $field ] ) ) {
                        $value = trim( $layer[ $field ] );
                        // Vazio ou &nbsp; → ocultar a layer.
                        if ( '' === $value || '&nbsp;' === $value || html_entity_decode( $value ) === '\u00a0' ) {
                            $layer['visibility'] = 'off';
                        }
                    }
                }
            }
        }
        unset( $layer ); // Limpar referência do foreach.

        $slide_clone->set_layers( $layers );

        return $slide_clone;
    }

    // -------------------------------------------------------------------------
    // CONSULTA DE ESPETÁCULOS ELEGÍVEIS (COM CACHE)
    // -------------------------------------------------------------------------

    /**
     * Retorna os espetáculos elegíveis para o banner, com cache via transient.
     *
     * Grupo 1 — Temporadas Ativas:
     *   _temporada_data_fim >= hoje, ordenado por _temporada_data_fim ASC.
     *   Apenas espetáculos com imagem destacada.
     *
     * Grupo 2 — Próximas Temporadas:
     *   _temporada_data_inicio_banner <= hoje (banner já liberado)
     *   mas _temporada_data_inicio > hoje (temporada ainda não começou).
     *   Apenas espetáculos com imagem destacada.
     *
     * @return array Lista de arrays com chaves:
     *               espetaculo_id, temporada_id, teatro, dias_horarios,
     *               link_vendas, link_texto, data_inicio, data_fim, grupo.
     */
    public static function get_espetaculos_para_banner() {
        // Transients desativados quando WP_DEBUG está ativo (facilita desenvolvimento).
        $use_cache = ! ( defined( 'WP_DEBUG' ) && WP_DEBUG );

        if ( $use_cache ) {
            $cached = get_transient( self::TRANSIENT_KEY );

            if ( false !== $cached ) {
                return $cached;
            }
        }

        $hoje = current_time( 'Y-m-d' );
        $resultado = array();
        $espetaculo_ids_vistos = array(); // Evitar duplicatas.

        // --- Grupo 1: Temporadas Ativas (_temporada_data_fim >= hoje) ---
        $temporadas_ativas = get_posts( array(
            'post_type'      => 'temporada',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'     => '_temporada_data_fim',
                    'value'   => $hoje,
                    'compare' => '>=',
                    'type'    => 'DATE',
                ),
            ),
            'meta_key'  => '_temporada_data_fim',
            'orderby'   => 'meta_value',
            'order'     => 'ASC',
        ) );

        foreach ( $temporadas_ativas as $temporada ) {
            $espetaculo_id = intval( get_post_meta( $temporada->ID, '_temporada_espetaculo_id', true ) );

            if ( ! $espetaculo_id ) {
                continue;
            }

            // Apenas espetáculos com imagem destacada.
            if ( ! has_post_thumbnail( $espetaculo_id ) ) {
                continue;
            }

            // Evitar duplicatas (um espetáculo pode ter várias temporadas ativas).
            if ( in_array( $espetaculo_id, $espetaculo_ids_vistos, true ) ) {
                continue;
            }

            $espetaculo_ids_vistos[] = $espetaculo_id;

            $resultado[] = self::montar_item_espetaculo( $espetaculo_id, $temporada->ID, 'ativo' );
        }

        // --- Grupo 2: Próximas Temporadas ---
        // Banner já liberado (data_inicio_banner <= hoje) mas temporada ainda não começou.
        $temporadas_proximas = get_posts( array(
            'post_type'      => 'temporada',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => '_temporada_data_inicio_banner',
                    'value'   => $hoje,
                    'compare' => '<=',
                    'type'    => 'DATE',
                ),
                array(
                    'key'     => '_temporada_data_inicio',
                    'value'   => $hoje,
                    'compare' => '>',
                    'type'    => 'DATE',
                ),
            ),
            'meta_key'  => '_temporada_data_inicio',
            'orderby'   => 'meta_value',
            'order'     => 'ASC',
        ) );

        foreach ( $temporadas_proximas as $temporada ) {
            $espetaculo_id = intval( get_post_meta( $temporada->ID, '_temporada_espetaculo_id', true ) );

            if ( ! $espetaculo_id ) {
                continue;
            }

            // Apenas espetáculos com imagem destacada.
            if ( ! has_post_thumbnail( $espetaculo_id ) ) {
                continue;
            }

            // Não duplicar espetáculos que já estão no Grupo 1.
            if ( in_array( $espetaculo_id, $espetaculo_ids_vistos, true ) ) {
                continue;
            }

            $espetaculo_ids_vistos[] = $espetaculo_id;

            $resultado[] = self::montar_item_espetaculo( $espetaculo_id, $temporada->ID, 'proximo' );
        }

        // Salvar no transient por 12 horas (somente se WP_DEBUG estiver desativado).
        if ( $use_cache ) {
            set_transient( self::TRANSIENT_KEY, $resultado, self::TRANSIENT_EXPIRY );
        }

        return $resultado;
    }

    /**
     * Monta o array de dados de um espetáculo para uso na clonagem de slides.
     *
     * Prioridade de dados para campos compartilhados entre temporada e espetáculo:
     *   1. Temporada ativa (grupo 'ativo')
     *   2. Próxima temporada (grupo 'proximo')
     *   3. Post meta do espetáculo (fallback)
     *
     * @param int    $espetaculo_id ID do post espetáculo.
     * @param int    $temporada_id  ID do post temporada (ativa ou próxima).
     * @param string $grupo         'ativo' ou 'proximo'.
     * @return array
     */
    private static function montar_item_espetaculo( $espetaculo_id, $temporada_id, $grupo ) {

        // --- Metas da temporada ---
        $t_teatro_nome     = get_post_meta( $temporada_id, '_temporada_teatro_nome', true );
        $t_teatro_endereco = get_post_meta( $temporada_id, '_temporada_teatro_endereco', true );
        $t_diretor         = get_post_meta( $temporada_id, '_temporada_diretor', true );
        $t_elenco          = get_post_meta( $temporada_id, '_temporada_elenco', true );
        $t_data_inicio     = get_post_meta( $temporada_id, '_temporada_data_inicio', true );
        $t_data_fim        = get_post_meta( $temporada_id, '_temporada_data_fim', true );
        $t_valores         = get_post_meta( $temporada_id, '_temporada_valores', true );
        $t_link_vendas     = get_post_meta( $temporada_id, '_temporada_link_vendas', true );
        $t_link_texto      = get_post_meta( $temporada_id, '_temporada_link_texto', true );
        $t_data_inicio_cartaz = get_post_meta( $temporada_id, '_temporada_data_inicio_cartaz', true );
        $t_sessoes_raw     = get_post_meta( $temporada_id, '_temporada_sessoes_data', true );

        // Gerar dias e horários dinamicamente a partir das sessões.
        $t_dias_horarios = '';
        if ( class_exists( 'CANNALEspetaculos_DiasHorarios' ) && ! empty( $t_sessoes_raw ) ) {
            $t_dias_horarios = CANNALEspetaculos_DiasHorarios::gerar( $t_sessoes_raw );
        }

        // --- Metas do espetáculo (fallback) ---
        $e_autor          = get_post_meta( $espetaculo_id, '_espetaculo_autor', true );
        $e_diretor        = get_post_meta( $espetaculo_id, '_espetaculo_diretor', true );
        $e_elenco         = get_post_meta( $espetaculo_id, '_espetaculo_elenco', true );
        $e_ano_estreia    = get_post_meta( $espetaculo_id, '_espetaculo_ano_estreia', true );
        $e_duracao        = get_post_meta( $espetaculo_id, '_espetaculo_duracao', true );
        $e_classificacao  = get_post_meta( $espetaculo_id, '_espetaculo_classificacao', true );
        $e_logotipo       = get_post_meta( $espetaculo_id, '_espetaculo_logotipo', true );

        // --- Aplicar prioridade: temporada > espetáculo para campos compartilhados ---
        $diretor = ! empty( $t_diretor ) ? $t_diretor : $e_diretor;
        $elenco  = ! empty( $t_elenco )  ? $t_elenco  : $e_elenco;

        return array(
            'espetaculo_id'        => $espetaculo_id,
            'temporada_id'         => $temporada_id,
            'grupo'                => $grupo,
            // Temporada
            'teatro_nome'          => (string) $t_teatro_nome,
            'teatro_endereco'      => (string) $t_teatro_endereco,
            'dias_horarios'        => (string) $t_dias_horarios,
            'data_inicio'          => (string) $t_data_inicio,
            'data_fim'             => (string) $t_data_fim,
            'valores'              => (string) $t_valores,
            'link_vendas'          => (string) $t_link_vendas,
            'link_texto'           => (string) $t_link_texto,
            'data_inicio_cartaz'   => (string) $t_data_inicio_cartaz,
            // Espetáculo
            'autor'                => (string) $e_autor,
            'ano_estreia'          => (string) $e_ano_estreia,
            'duracao'              => (string) $e_duracao,
            'classificacao'        => (string) $e_classificacao,
            'logotipo_id'          => (string) $e_logotipo,
            // Campos com prioridade temporada > espetáculo
            'diretor'              => (string) $diretor,
            'elenco'               => (string) $elenco,
        );
    }

    // -------------------------------------------------------------------------
    // INVALIDAÇÃO DE CACHE
    // -------------------------------------------------------------------------

    /**
     * Invalida o transient ao salvar um espetáculo ou temporada.
     *
     * @param int $post_id ID do post salvo.
     */
    public static function invalidar_cache( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        $post_type = get_post_type( $post_id );

        if ( in_array( $post_type, array( 'espetaculo', 'temporada' ), true ) ) {
            delete_transient( self::TRANSIENT_KEY );
        }
    }

    // -------------------------------------------------------------------------
    // CÓDIGO LEGADO (mantido para compatibilidade com shortcode e outros usos)
    // -------------------------------------------------------------------------

    /**
     * Obtém os espetáculos para exibição no banner (método legado).
     *
     * @deprecated Usar get_espetaculos_para_banner() para o filtro do RevSlider.
     */
    public static function get_banner_espetaculos() {
        $hoje = current_time( 'Y-m-d' );

        $em_cartaz = get_posts( array(
            'post_type'      => 'espetaculo',
            'posts_per_page' => -1,
            'meta_query'     => array(
                'relation' => 'EXISTS',
                array(
                    'key'     => '_espetaculo_banner_temporada_id',
                    'compare' => 'EXISTS',
                ),
            ),
        ) );

        $espetaculos_ordenados = array();

        foreach ( $em_cartaz as $espetaculo ) {
            $temporada_id = get_post_meta( $espetaculo->ID, '_espetaculo_banner_temporada_id', true );

            if ( ! $temporada_id ) {
                continue;
            }

            $data_inicio        = get_post_meta( $temporada_id, '_temporada_data_inicio', true );
            $data_fim           = get_post_meta( $temporada_id, '_temporada_data_fim', true );
            $data_inicio_cartaz = get_post_meta( $temporada_id, '_temporada_data_inicio_cartaz', true );

            if ( $data_inicio_cartaz && $hoje < $data_inicio_cartaz ) {
                continue;
            }

            if ( $data_inicio && $data_fim ) {
                if ( $hoje >= $data_inicio && $hoje <= $data_fim ) {
                    $status = 'em_cartaz';
                } elseif ( $hoje < $data_inicio ) {
                    $status = 'futuro';
                } else {
                    continue;
                }

                $espetaculos_ordenados[] = array(
                    'post'        => $espetaculo,
                    'temporada_id' => $temporada_id,
                    'status'      => $status,
                    'data_inicio' => $data_inicio,
                );
            }
        }

        usort( $espetaculos_ordenados, function ( $a, $b ) {
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

        $temporadas = get_posts( array(
            'post_type'      => 'temporada',
            'posts_per_page' => 1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => '_temporada_espetaculo_id',
                    'value'   => $espetaculo_id,
                    'compare' => '=',
                ),
                array(
                    'key'     => '_temporada_data_fim',
                    'value'   => $hoje,
                    'compare' => '>=',
                    'type'    => 'DATE',
                ),
            ),
            'orderby'  => 'meta_value',
            'meta_key' => '_temporada_data_inicio',
            'order'    => 'ASC',
        ) );

        if ( ! empty( $temporadas ) ) {
            update_post_meta( $espetaculo_id, '_espetaculo_banner_temporada_id', $temporadas[0]->ID );
        } else {
            delete_post_meta( $espetaculo_id, '_espetaculo_banner_temporada_id' );
        }
    }

    /**
     * Gera dados para uso no RevSlider (método legado).
     */
    public static function get_slide_data( $espetaculo_id, $temporada_id ) {
        $espetaculo = get_post( $espetaculo_id );
        $temporada  = get_post( $temporada_id );

        if ( ! $espetaculo || ! $temporada ) {
            return null;
        }

        $teatro_nome   = get_post_meta( $temporada_id, '_temporada_teatro_nome', true );
        $dias_horarios = get_post_meta( $temporada_id, '_temporada_dias_horarios', true );
        $link_vendas   = get_post_meta( $temporada_id, '_temporada_link_vendas', true );
        $link_texto    = get_post_meta( $temporada_id, '_temporada_link_texto', true );
        $espetaculo_url = CANNALEspetaculos_Rewrites::get_espetaculo_url( $espetaculo_id );

        $image_id  = get_post_thumbnail_id( $espetaculo_id );
        $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';

        return array(
            'titulo'        => $espetaculo->post_title,
            'teatro'        => $teatro_nome,
            'dias_horarios' => $dias_horarios,
            'link_vendas'   => $link_vendas,
            'link_texto'    => ! empty( $link_texto ) ? $link_texto : 'Ingressos Aqui',
            'espetaculo_url' => $espetaculo_url,
            'image_url'     => $image_url,
        );
    }

    /**
     * Shortcode para exibir dados de espetáculos no RevSlider.
     * Uso: [cannal_banner_espetaculos]
     */
    public static function shortcode_banner_espetaculos( $atts ) {
        $atts = shortcode_atts( array( 'limit' => 10 ), $atts );

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
     * Hook legado: atualiza a temporada do banner ao salvar uma temporada.
     *
     * @param int $post_id ID do post salvo.
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

    /**
     * Ajusta o comportamento do slider "cannal_cartaz" para usar posts específicos.
     *
     * @param array      $query_args Argumentos de consulta originais.
     * @param object|int $slider     Instância ou ID do slider.
     * @return array Argumentos de consulta ajustados.
     */
    public static function filter_cartaz_slider_posts( $query_args, $slider = null ) {
        $slider_alias = '';

        if ( is_object( $slider ) && method_exists( $slider, 'getAlias' ) ) {
            $slider_alias = $slider->getAlias();
        } elseif ( is_object( $slider ) && property_exists( $slider, 'alias' ) ) {
            $slider_alias = $slider->alias;
        } elseif ( is_array( $query_args ) && isset( $query_args['slider_alias'] ) ) {
            $slider_alias = $query_args['slider_alias'];
        }

        if ( 'cannal_cartaz' !== $slider_alias ) {
            return $query_args;
        }

        $espetaculo_ids = self::get_cartaz_espetaculo_ids();

        if ( empty( $espetaculo_ids ) ) {
            $query_args['post__in']      = array( 0 );
            $query_args['posts_per_page'] = 0;
            return $query_args;
        }

        $query_args['post_type']         = 'espetaculo';
        $query_args['post__in']          = $espetaculo_ids;
        $query_args['orderby']           = 'post__in';
        $query_args['posts_per_page']    = count( $espetaculo_ids );
        $query_args['ignore_sticky_posts'] = true;

        return $query_args;
    }

    /**
     * Retorna os IDs de espetáculos elegíveis para o cartaz (método legado).
     *
     * @return int[] Lista de IDs em ordem ascendente pela data de início da temporada.
     */
    private static function get_cartaz_espetaculo_ids() {
        $hoje = current_time( 'Y-m-d' );

        $temporadas = get_posts( array(
            'post_type'      => 'temporada',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_key'       => '_temporada_data_inicio',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
        ) );

        $espetaculo_ids = array();

        foreach ( $temporadas as $temporada ) {
            $espetaculo_id = intval( get_post_meta( $temporada->ID, '_temporada_espetaculo_id', true ) );

            if ( ! $espetaculo_id || in_array( $espetaculo_id, $espetaculo_ids, true ) ) {
                continue;
            }

            $data_inicio        = get_post_meta( $temporada->ID, '_temporada_data_inicio', true );
            $data_fim           = get_post_meta( $temporada->ID, '_temporada_data_fim', true );
            $data_inicio_cartaz = get_post_meta( $temporada->ID, '_temporada_data_inicio_cartaz', true );

            $temporada_ativa  = $data_inicio && $data_inicio <= $hoje && ( empty( $data_fim ) || $data_fim <= $hoje );
            $cartaz_liberado  = ( '' === $data_inicio_cartaz || empty( $data_inicio_cartaz ) || $data_inicio_cartaz <= $hoje );

            if ( ! $temporada_ativa && ! $cartaz_liberado ) {
                continue;
            }

            if ( ! has_post_thumbnail( $espetaculo_id ) ) {
                continue;
            }

            $espetaculo_ids[] = $espetaculo_id;
        }

        return $espetaculo_ids;
    }
}

// -------------------------------------------------------------------------
// REGISTRO DE HOOKS E SHORTCODES
// -------------------------------------------------------------------------

// Shortcode legado.
add_action( 'init', array( 'CANNALEspetaculos_RevSlider', 'register_shortcode' ) );

// Hook legado: atualizar temporada do banner ao salvar.
add_action( 'save_post', array( 'CANNALEspetaculos_RevSlider', 'on_temporada_save' ) );

// Filtro legado: ajustar posts do slider cannal_cartaz.
add_filter( 'revslider_get_posts', array( 'CANNALEspetaculos_RevSlider', 'filter_cartaz_slider_posts' ), 10, 2 );

// Novo filtro: clonar slide template para cada espetáculo elegível.
add_filter( 'revslider_get_slides_by_slider_id', array( 'CANNALEspetaculos_RevSlider', 'filter_slides_by_slider_id' ), 10, 2 );

// Invalidar cache ao salvar espetáculo ou temporada.
add_action( 'save_post', array( 'CANNALEspetaculos_RevSlider', 'invalidar_cache' ) );
