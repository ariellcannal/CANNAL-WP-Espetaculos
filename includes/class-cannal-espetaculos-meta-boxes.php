<?php
/**
 * Gerencia os meta boxes e campos personalizados.
 *
 * @package    Cannal_Espetaculos
 * @subpackage Cannal_Espetaculos/includes
 */

class Cannal_Espetaculos_Meta_Boxes {

    /**
     * Adiciona os meta boxes.
     */
    public function add_meta_boxes() {
        
        // Meta boxes para Espetáculos
        add_meta_box(
            'espetaculo_detalhes',
            'Detalhes do Espetáculo',
            array( $this, 'render_espetaculo_detalhes_meta_box' ),
            'espetaculo',
            'normal',
            'high'
        );

        add_meta_box(
            'espetaculo_galeria',
            'Galeria de Fotos',
            array( $this, 'render_espetaculo_galeria_meta_box' ),
            'espetaculo',
            'normal',
            'default'
        );

        add_meta_box(
            'espetaculo_temporadas',
            'Temporadas',
            array( $this, 'render_espetaculo_temporadas_meta_box' ),
            'espetaculo',
            'normal',
            'default'
        );

        // Meta boxes para Temporadas
        add_meta_box(
            'temporada_detalhes',
            'Detalhes da Temporada',
            array( $this, 'render_temporada_detalhes_meta_box' ),
            'temporada',
            'normal',
            'high'
        );

        add_meta_box(
            'temporada_sessoes',
            'Sessões',
            array( $this, 'render_temporada_sessoes_meta_box' ),
            'temporada',
            'normal',
            'default'
        );
    }

    /**
     * Renderiza o meta box de detalhes do espetáculo.
     */
    public function render_espetaculo_detalhes_meta_box( $post ) {
        wp_nonce_field( 'cannal_espetaculo_meta_box', 'cannal_espetaculo_meta_box_nonce' );

        $autor = get_post_meta( $post->ID, '_espetaculo_autor', true );
        $ano_estreia = get_post_meta( $post->ID, '_espetaculo_ano_estreia', true );
        $duracao = get_post_meta( $post->ID, '_espetaculo_duracao', true );
        $classificacao = get_post_meta( $post->ID, '_espetaculo_classificacao', true );

        ?>
        <table class="form-table">
            <tr>
                <th><label for="espetaculo_autor">Autor</label></th>
                <td><input type="text" id="espetaculo_autor" name="espetaculo_autor" value="<?php echo esc_attr( $autor ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="espetaculo_ano_estreia">Ano de Estreia</label></th>
                <td><input type="number" id="espetaculo_ano_estreia" name="espetaculo_ano_estreia" value="<?php echo esc_attr( $ano_estreia ); ?>" class="small-text" min="1900" max="2100" /></td>
            </tr>
            <tr>
                <th><label for="espetaculo_duracao">Duração</label></th>
                <td><input type="text" id="espetaculo_duracao" name="espetaculo_duracao" value="<?php echo esc_attr( $duracao ); ?>" class="regular-text" placeholder="Ex: 90 minutos" /></td>
            </tr>
            <tr>
                <th><label for="espetaculo_classificacao">Classificação Indicativa</label></th>
                <td>
                    <select id="espetaculo_classificacao" name="espetaculo_classificacao">
                        <option value="">Selecione...</option>
                        <option value="livre" <?php selected( $classificacao, 'livre' ); ?>>Livre</option>
                        <option value="10" <?php selected( $classificacao, '10' ); ?>>10 anos</option>
                        <option value="12" <?php selected( $classificacao, '12' ); ?>>12 anos</option>
                        <option value="14" <?php selected( $classificacao, '14' ); ?>>14 anos</option>
                        <option value="16" <?php selected( $classificacao, '16' ); ?>>16 anos</option>
                        <option value="18" <?php selected( $classificacao, '18' ); ?>>18 anos</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="espetaculo_exibir_galeria">Exibir Galeria</label></th>
                <td>
                    <?php $exibir_galeria = get_post_meta( $post->ID, '_espetaculo_exibir_galeria', true ); ?>
                    <label>
                        <input type="checkbox" id="espetaculo_exibir_galeria" name="espetaculo_exibir_galeria" value="1" <?php checked( $exibir_galeria === '' || $exibir_galeria === '1', true ); ?> />
                        Exibir galeria de fotos ao final do conteúdo
                    </label>
                    <p class="description">Desmarque para ocultar a galeria de fotos na página do espetáculo.</p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Renderiza o meta box de galeria do espetáculo.
     */
    public function render_espetaculo_galeria_meta_box( $post ) {
        wp_nonce_field( 'cannal_espetaculo_galeria_meta_box', 'cannal_espetaculo_galeria_meta_box_nonce' );
        
        $galeria = get_post_meta( $post->ID, '_espetaculo_galeria', true );
        $galeria_ids = ! empty( $galeria ) ? explode( ',', $galeria ) : array();
        ?>
        <div class="espetaculo-galeria-container">
            <div class="espetaculo-galeria-images">
                <?php
                if ( ! empty( $galeria_ids ) ) {
                    foreach ( $galeria_ids as $image_id ) {
                        $image_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
                        if ( $image_url ) {
                            echo '<div class="espetaculo-galeria-image" data-id="' . esc_attr( $image_id ) . '">';
                            echo '<img src="' . esc_url( $image_url ) . '" />';
                            echo '<button type="button" class="remove-image">×</button>';
                            echo '</div>';
                        }
                    }
                }
                ?>
            </div>
            <input type="text" id="espetaculo_galeria" name="espetaculo_galeria" value="<?php echo esc_attr( $galeria ); ?>" style="display:none;" readonly />
            <button type="button" class="button espetaculo-add-galeria">Adicionar Imagens</button>
        </div>
        <style>
            .espetaculo-galeria-images {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-bottom: 15px;
            }
            .espetaculo-galeria-image {
                position: relative;
                width: 100px;
                height: 100px;
            }
            .espetaculo-galeria-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                border: 1px solid #ddd;
            }
            .espetaculo-galeria-image .remove-image {
                position: absolute;
                top: -5px;
                right: -5px;
                background: #dc3232;
                color: white;
                border: none;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                cursor: pointer;
                font-size: 14px;
                line-height: 1;
            }
        </style>
        <?php
    }

    /**
     * Renderiza o meta box de temporadas do espetáculo.
     */
    public function render_espetaculo_temporadas_meta_box( $post ) {
        $temporadas = get_posts( array(
            'post_type' => 'temporada',
            'posts_per_page' => -1,
            'meta_key' => '_temporada_espetaculo_id',
            'meta_value' => $post->ID,
            'meta_query' => array(
                array(
                    'key' => '_temporada_data_fim',
                    'compare' => 'EXISTS'
                )
            ),
            'orderby' => 'meta_value',
            'order' => 'DESC'
        ) );

        ?>
        <div class="espetaculo-temporadas-list">
            <?php if ( ! empty( $temporadas ) ) : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Nome do Teatro</th>
                            <th>Período</th>
                            <th>Dias e Horários</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $temporadas as $temporada ) : 
                            $teatro = get_post_meta( $temporada->ID, '_temporada_teatro_nome', true );
                            $data_inicio = get_post_meta( $temporada->ID, '_temporada_data_inicio', true );
                            $data_fim = get_post_meta( $temporada->ID, '_temporada_data_fim', true );
                            $sessoes_data = get_post_meta( $temporada->ID, '_temporada_sessoes_data', true );
                            
                            // Gerar Dias e Horários
                            $dias_horarios_texto = '';
                            if ( ! empty( $sessoes_data ) ) {
                                $sessoes = json_decode( $sessoes_data, true );
                                if ( $sessoes && $sessoes['tipo'] === 'avulsas' && ! empty( $sessoes['avulsas'] ) ) {
                                    $datas = array();
                                    foreach ( $sessoes['avulsas'] as $sessao ) {
                                        $datas[] = date_i18n( 'd/m', strtotime( $sessao['data'] ) ) . ' às ' . $sessao['horario'];
                                    }
                                    $dias_horarios_texto = implode( ', ', array_slice( $datas, 0, 2 ) );
                                    if ( count( $datas ) > 2 ) $dias_horarios_texto .= '...';
                                } elseif ( $sessoes && $sessoes['tipo'] === 'temporada' && ! empty( $sessoes['temporada'] ) ) {
                                    $dias_semana_labels = array(
                                        'domingo' => 'Dom', 'segunda' => 'Seg', 'terca' => 'Ter',
                                        'quarta' => 'Qua', 'quinta' => 'Qui', 'sexta' => 'Sex', 'sabado' => 'Sáb'
                                    );
                                    $dias = array();
                                    foreach ( $sessoes['temporada'] as $dia => $horarios ) {
                                        if ( ! empty( $horarios ) ) {
                                            $label = isset( $dias_semana_labels[$dia] ) ? $dias_semana_labels[$dia] : ucfirst($dia);
                                            $dias[] = $label . ' ' . $horarios;
                                        }
                                    }
                                    $dias_horarios_texto = implode( ', ', $dias );
                                }
                            }
                            
                            $hoje = current_time( 'Y-m-d' );
                            if ( $data_inicio && $data_fim ) {
                                if ( $hoje < $data_inicio ) {
                                    $status = 'Futura';
                                } elseif ( $hoje >= $data_inicio && $hoje <= $data_fim ) {
                                    $status = 'Em Cartaz';
                                } else {
                                    $status = 'Encerrada';
                                }
                            } else {
                                $status = 'Sem datas';
                            }
                        ?>
                        <tr>
                            <td><?php echo esc_html( $teatro ); ?></td>
                            <td>
                                <?php 
                                if ( $data_inicio && $data_fim ) {
                                    echo esc_html( date_i18n( 'd/m/Y', strtotime( $data_inicio ) ) ) . ' - ' . esc_html( date_i18n( 'd/m/Y', strtotime( $data_fim ) ) );
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html( $dias_horarios_texto ); ?></td>
                            <td><?php echo esc_html( $status ); ?></td>
                            <td>
                                <button type="button" class="button button-small edit-temporada-btn" data-temporada-id="<?php echo $temporada->ID; ?>">Editar</button>
                                <button type="button" class="button button-small duplicate-temporada-btn" data-temporada-id="<?php echo $temporada->ID; ?>">Duplicar</button>
                                <button type="button" class="button button-small button-link-delete delete-temporada-btn" data-temporada-id="<?php echo $temporada->ID; ?>">Excluir</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>Nenhuma temporada cadastrada ainda.</p>
            <?php endif; ?>
            <p style="margin-top: 15px;">
                <button type="button" class="button button-primary open-temporada-modal" data-espetaculo-id="<?php echo $post->ID; ?>">Adicionar Nova Temporada</button>
            </p>
        </div>
        <?php
        
        // Armazenar o ID do espetáculo para usar no modal
        add_action( 'admin_footer', function() use ( $post ) {
            $this->render_temporada_modal( $post->ID );
        } );
    }

    /**
     * Renderiza o meta box de detalhes da temporada.
     */
    public function render_temporada_detalhes_meta_box( $post ) {
        wp_nonce_field( 'cannal_temporada_meta_box', 'cannal_temporada_meta_box_nonce' );
        
        $espetaculo_id = get_post_meta( $post->ID, '_temporada_espetaculo_id', true );
        
        // Se vier da URL
        if ( empty( $espetaculo_id ) && isset( $_GET['espetaculo_id'] ) ) {
            $espetaculo_id = intval( $_GET['espetaculo_id'] );
        }

        $espetaculos = get_posts( array(
            'post_type' => 'espetaculo',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ) );
        
        $teatro_nome = get_post_meta( $post->ID, '_temporada_teatro_nome', true );
        $teatro_endereco = get_post_meta( $post->ID, '_temporada_teatro_endereco', true );
        $data_inicio = get_post_meta( $post->ID, '_temporada_data_inicio', true );
        $data_fim = get_post_meta( $post->ID, '_temporada_data_fim', true );
        $valores = get_post_meta( $post->ID, '_temporada_valores', true );
        $link_vendas = get_post_meta( $post->ID, '_temporada_link_vendas', true );
        $link_texto = get_post_meta( $post->ID, '_temporada_link_texto', true );
        $data_inicio_banner = get_post_meta( $post->ID, '_temporada_data_inicio_banner', true );

        ?>
        <table class="form-table">
            <tr>
                <th><label for="temporada_espetaculo_id">Espetáculo *</label></th>
                <td>
                    <select id="temporada_espetaculo_id" name="temporada_espetaculo_id" style="width: 100%; max-width: 400px;" required>
                        <option value="">Selecione um espetáculo</option>
                        <?php foreach ( $espetaculos as $espetaculo ) : ?>
                            <option value="<?php echo esc_attr( $espetaculo->ID ); ?>" <?php selected( $espetaculo_id, $espetaculo->ID ); ?>>
                                <?php echo esc_html( $espetaculo->post_title ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label>Copiar Conteúdo</label></th>
                <td>
                    <button type="button" id="btn-copiar-conteudo" class="button" <?php echo empty( $espetaculo_id ) ? 'disabled' : ''; ?>>
                        Copiar conteúdo do espetáculo
                    </button>
                    <p class="description">Copia o conteúdo (release) do espetáculo selecionado para esta temporada.</p>
                </td>
            </tr>
            <tr>
                <th><label for="temporada_teatro_nome">Nome do Teatro *</label></th>
                <td><input type="text" id="temporada_teatro_nome" name="temporada_teatro_nome" value="<?php echo esc_attr( $teatro_nome ); ?>" class="regular-text" required /></td>
            </tr>
            <tr>
                <th><label for="temporada_teatro_endereco">Endereço do Teatro</label></th>
                <td><input type="text" id="temporada_teatro_endereco" name="temporada_teatro_endereco" value="<?php echo esc_attr( $teatro_endereco ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="temporada_data_inicio">Data de Início *</label></th>
                <td><input type="date" id="temporada_data_inicio" name="temporada_data_inicio" value="<?php echo esc_attr( $data_inicio ); ?>" required /></td>
            </tr>
            <tr>
                <th><label for="temporada_data_fim">Data Final *</label></th>
                <td><input type="date" id="temporada_data_fim" name="temporada_data_fim" value="<?php echo esc_attr( $data_fim ); ?>" required /></td>
            </tr>
            <tr>
                <th><label for="temporada_valores">Valores</label></th>
                <td><textarea id="temporada_valores" name="temporada_valores" rows="3" class="large-text"><?php echo esc_textarea( $valores ); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="temporada_link_vendas">Link de Vendas</label></th>
                <td><input type="url" id="temporada_link_vendas" name="temporada_link_vendas" value="<?php echo esc_attr( $link_vendas ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="temporada_link_texto">Texto de Exibição do Link</label></th>
                <td><input type="text" id="temporada_link_texto" name="temporada_link_texto" value="<?php echo esc_attr( $link_texto ); ?>" class="regular-text" placeholder="Ex: Ingressos Aqui" /></td>
            </tr>
            <tr>
                <th><label for="temporada_data_inicio_banner">Data de Início do Banner</label></th>
                <td><input type="date" id="temporada_data_inicio_banner" name="temporada_data_inicio_banner" value="<?php echo esc_attr( $data_inicio_banner ); ?>" /></td>
            </tr>
        </table>
        <?php
    }

    /**
     * Renderiza o meta box de sessões da temporada.
     */
    public function render_temporada_sessoes_meta_box( $post ) {
        $tipo_sessao = get_post_meta( $post->ID, '_temporada_tipo_sessao', true );
        $sessoes_data = get_post_meta( $post->ID, '_temporada_sessoes_data', true );

        ?>
        <div class="temporada-sessoes-container">
            <p>
                <label><strong>Tipo de Sessões:</strong></label><br>
                <label>
                    <input type="radio" name="temporada_tipo_sessao" value="avulsas" <?php checked( $tipo_sessao, 'avulsas' ); ?> />
                    Sessões Avulsas
                </label>
                &nbsp;&nbsp;
                <label>
                    <input type="radio" name="temporada_tipo_sessao" value="temporada" <?php checked( $tipo_sessao, 'temporada' ); ?> />
                    Temporada (dias da semana)
                </label>
            </p>

            <div id="sessoes-avulsas-container" style="display: none;">
                <p><strong>Sessões Avulsas:</strong></p>
                <div id="sessoes-avulsas-list"></div>
                <button type="button" class="button add-sessao-avulsa">Adicionar Sessão</button>
            </div>

            <div id="sessoes-temporada-container" style="display: none;">
                <p><strong>Dias da Semana e Horários:</strong></p>
                <table class="form-table">
                    <?php
                    $dias_semana = array(
                        'domingo' => 'Domingo',
                        'segunda' => 'Segunda-feira',
                        'terca' => 'Terça-feira',
                        'quarta' => 'Quarta-feira',
                        'quinta' => 'Quinta-feira',
                        'sexta' => 'Sexta-feira',
                        'sabado' => 'Sábado'
                    );
                    foreach ( $dias_semana as $key => $label ) :
                    ?>
                    <tr>
                        <th><label><?php echo esc_html( $label ); ?></label></th>
                        <td>
                            <input type="text" name="temporada_sessoes_temporada[<?php echo esc_attr( $key ); ?>]" 
                                   value="" class="regular-text" 
                                   placeholder="Ex: 20h, 22h (separar por vírgula)" />
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <input type="hidden" id="temporada_sessoes_data" name="temporada_sessoes_data" value="<?php echo esc_attr( $sessoes_data ); ?>" />
        </div>
        <?php
    }

    /**
     * Salva os meta dados do espetáculo.
     */
    public function save_espetaculo_meta( $post_id ) {
        
        // Verificar se pelo menos um dos nonces está presente
        $has_detalhes_nonce = isset( $_POST['cannal_espetaculo_meta_box_nonce'] );
        $has_galeria_nonce = isset( $_POST['cannal_espetaculo_galeria_meta_box_nonce'] );
        
        if ( ! $has_detalhes_nonce && ! $has_galeria_nonce ) {
            return;
        }

        // Verificar nonces
        $detalhes_valid = $has_detalhes_nonce && wp_verify_nonce( $_POST['cannal_espetaculo_meta_box_nonce'], 'cannal_espetaculo_meta_box' );
        $galeria_valid = $has_galeria_nonce && wp_verify_nonce( $_POST['cannal_espetaculo_galeria_meta_box_nonce'], 'cannal_espetaculo_galeria_meta_box' );
        
        if ( ! $detalhes_valid && ! $galeria_valid ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( get_post_type( $post_id ) !== 'espetaculo' ) {
            return;
        }

        // Salvar campos
        if ( isset( $_POST['espetaculo_autor'] ) ) {
            update_post_meta( $post_id, '_espetaculo_autor', sanitize_text_field( $_POST['espetaculo_autor'] ) );
        }
        
        if ( isset( $_POST['espetaculo_ano_estreia'] ) ) {
            update_post_meta( $post_id, '_espetaculo_ano_estreia', sanitize_text_field( $_POST['espetaculo_ano_estreia'] ) );
        }
        
        if ( isset( $_POST['espetaculo_duracao'] ) ) {
            update_post_meta( $post_id, '_espetaculo_duracao', sanitize_text_field( $_POST['espetaculo_duracao'] ) );
        }
        
        if ( isset( $_POST['espetaculo_classificacao'] ) ) {
            update_post_meta( $post_id, '_espetaculo_classificacao', sanitize_text_field( $_POST['espetaculo_classificacao'] ) );
        }
        
        // Exibir galeria (checkbox)
        $exibir_galeria = isset( $_POST['espetaculo_exibir_galeria'] ) ? '1' : '0';
        update_post_meta( $post_id, '_espetaculo_exibir_galeria', $exibir_galeria );
        
        // DEBUG: Log detalhado da galeria
        error_log( '=== CANNAL DEBUG GALERIA ===' );
        error_log( 'Post ID: ' . $post_id );
        error_log( 'Galeria isset: ' . ( isset( $_POST['espetaculo_galeria'] ) ? 'SIM' : 'NÃO' ) );
        if ( isset( $_POST['espetaculo_galeria'] ) ) {
            error_log( 'Galeria valor: ' . $_POST['espetaculo_galeria'] );
            $result = update_post_meta( $post_id, '_espetaculo_galeria', sanitize_text_field( $_POST['espetaculo_galeria'] ) );
            error_log( 'Update result: ' . ( $result ? 'SUCESSO' : 'FALHOU' ) );
            error_log( 'Valor salvo: ' . get_post_meta( $post_id, '_espetaculo_galeria', true ) );
        }
        error_log( '=== FIM DEBUG GALERIA ===' );
    }

    /**
     * Salva os meta dados da temporada.
     */
    public function save_temporada_meta( $post_id ) {
        
        if ( ! isset( $_POST['cannal_temporada_meta_box_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['cannal_temporada_meta_box_nonce'], 'cannal_temporada_meta_box' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( get_post_type( $post_id ) !== 'temporada' ) {
            return;
        }

        // Salvar espetáculo vinculado
        if ( isset( $_POST['temporada_espetaculo_id'] ) ) {
            $espetaculo_id = intval( $_POST['temporada_espetaculo_id'] );
            update_post_meta( $post_id, '_temporada_espetaculo_id', $espetaculo_id );

            // Atualizar o título da temporada
            $espetaculo = get_post( $espetaculo_id );
            $teatro_nome = isset( $_POST['temporada_teatro_nome'] ) ? sanitize_text_field( $_POST['temporada_teatro_nome'] ) : '';
            if ( $espetaculo && $teatro_nome ) {
                $novo_titulo = $teatro_nome . ' - ' . $espetaculo->post_title;
                wp_update_post( array(
                    'ID' => $post_id,
                    'post_title' => $novo_titulo
                ) );
            }

            // Copiar conteúdo se solicitado
            if ( isset( $_POST['temporada_copiar_conteudo'] ) && $_POST['temporada_copiar_conteudo'] == '1' ) {
                wp_update_post( array(
                    'ID' => $post_id,
                    'post_content' => $espetaculo->post_content
                ) );
            }
        }

        // Salvar campos da temporada
        if ( isset( $_POST['temporada_teatro_nome'] ) ) {
            update_post_meta( $post_id, '_temporada_teatro_nome', sanitize_text_field( $_POST['temporada_teatro_nome'] ) );
        }
        
        if ( isset( $_POST['temporada_teatro_endereco'] ) ) {
            update_post_meta( $post_id, '_temporada_teatro_endereco', sanitize_text_field( $_POST['temporada_teatro_endereco'] ) );
        }
        
        if ( isset( $_POST['temporada_data_inicio'] ) ) {
            update_post_meta( $post_id, '_temporada_data_inicio', sanitize_text_field( $_POST['temporada_data_inicio'] ) );
        }
        
        if ( isset( $_POST['temporada_data_fim'] ) ) {
            update_post_meta( $post_id, '_temporada_data_fim', sanitize_text_field( $_POST['temporada_data_fim'] ) );
        }
        
        if ( isset( $_POST['temporada_valores'] ) ) {
            update_post_meta( $post_id, '_temporada_valores', sanitize_textarea_field( $_POST['temporada_valores'] ) );
        }
        
        if ( isset( $_POST['temporada_link_vendas'] ) ) {
            update_post_meta( $post_id, '_temporada_link_vendas', esc_url_raw( $_POST['temporada_link_vendas'] ) );
        }
        
        if ( isset( $_POST['temporada_link_texto'] ) ) {
            update_post_meta( $post_id, '_temporada_link_texto', sanitize_text_field( $_POST['temporada_link_texto'] ) );
        }
        
        if ( isset( $_POST['temporada_data_inicio_banner'] ) ) {
            update_post_meta( $post_id, '_temporada_data_inicio_banner', sanitize_text_field( $_POST['temporada_data_inicio_banner'] ) );
        }
        
        if ( isset( $_POST['temporada_tipo_sessao'] ) ) {
            update_post_meta( $post_id, '_temporada_tipo_sessao', sanitize_text_field( $_POST['temporada_tipo_sessao'] ) );
        }
        
        if ( isset( $_POST['temporada_sessoes_data'] ) ) {
            update_post_meta( $post_id, '_temporada_sessoes_data', wp_kses_post( $_POST['temporada_sessoes_data'] ) );
        }

        // Processar e salvar sessões
        $this->process_sessoes( $post_id );
    }

    /**
     * Processa as sessões e gera o campo "Dias e Horários".
     */
    private function process_sessoes( $post_id ) {
        $tipo_sessao = get_post_meta( $post_id, '_temporada_tipo_sessao', true );
        $sessoes_data = get_post_meta( $post_id, '_temporada_sessoes_data', true );
        
        if ( empty( $sessoes_data ) ) {
            return;
        }

        $sessoes = json_decode( $sessoes_data, true );
        $dias_horarios = '';

        if ( $tipo_sessao === 'avulsas' && ! empty( $sessoes['avulsas'] ) ) {
            $dias_horarios = $this->format_sessoes_avulsas( $sessoes['avulsas'] );
        } elseif ( $tipo_sessao === 'temporada' && ! empty( $sessoes['temporada'] ) ) {
            $dias_horarios = $this->format_sessoes_temporada( $sessoes['temporada'] );
        }

        update_post_meta( $post_id, '_temporada_dias_horarios', $dias_horarios );
    }

    /**
     * Formata sessões avulsas.
     */
    private function format_sessoes_avulsas( $sessoes ) {
        // Implementação da formatação de sessões avulsas
        // Agrupar por mês e horário
        return 'Formatação de sessões avulsas (a implementar)';
    }

    /**
     * Formata sessões de temporada.
     */
    private function format_sessoes_temporada( $sessoes ) {
        // Implementação da formatação de sessões de temporada
        // Agrupar por dia da semana e horário
        return 'Formatação de sessões de temporada (a implementar)';
    }
    
    /**
     * Renderiza o modal de temporadas no admin footer.
     */
    public function render_temporada_modal( $espetaculo_id ) {
        // Verificar se estamos na tela de edição de espetáculo
        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== 'espetaculo' || $screen->base !== 'post' ) {
            return;
        }
        ?>
        <!-- Modal para adicionar/editar temporada -->
        <div id="temporada-modal" class="temporada-modal" style="display: none;">
            <div class="temporada-modal-content">
                <span class="temporada-modal-close">&times;</span>
                <h2 id="temporada-modal-title">Nova Temporada</h2>
                <form id="temporada-form">
                    <input type="hidden" id="modal_temporada_id" name="temporada_id" value="" />
                    <input type="hidden" id="modal_espetaculo_id" name="espetaculo_id" value="<?php echo esc_attr( $espetaculo_id ); ?>" />
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="modal_teatro_nome">Nome do Teatro *</label></th>
                            <td><input type="text" id="modal_teatro_nome" name="teatro_nome" class="regular-text" required /></td>
                        </tr>
                        <tr>
                            <th><label for="modal_teatro_endereco">Endereço do Teatro</label></th>
                            <td><input type="text" id="modal_teatro_endereco" name="teatro_endereco" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th><label for="modal_data_inicio">Data de Início *</label></th>
                            <td><input type="date" id="modal_data_inicio" name="data_inicio" required /></td>
                        </tr>
                        <tr>
                            <th><label for="modal_data_fim">Data Final *</label></th>
                            <td><input type="date" id="modal_data_fim" name="data_fim" required /></td>
                        </tr>
                        <tr>
                            <th><label for="modal_valores">Valores</label></th>
                            <td><textarea id="modal_valores" name="valores" rows="3" class="large-text"></textarea></td>
                        </tr>
                        <tr>
                            <th><label for="modal_link_vendas">Link de Vendas</label></th>
                            <td><input type="url" id="modal_link_vendas" name="link_vendas" class="regular-text" /></td>
                        </tr>
                        <tr>
                            <th><label for="modal_link_texto">Texto do Link</label></th>
                            <td><input type="text" id="modal_link_texto" name="link_texto" class="regular-text" placeholder="Ingressos Aqui" /></td>
                        </tr>
                        <tr>
                            <th><label for="modal_data_inicio_banner">Data de Início do Banner</label></th>
                            <td><input type="date" id="modal_data_inicio_banner" name="data_inicio_banner" /></td>
                        </tr>
                        <tr>
                            <th><label>Tipo de Sessão</label></th>
                            <td>
                                <label>
                                    <input type="radio" name="modal_tipo_sessao" value="avulsas" id="modal_tipo_sessao_avulsas" checked />
                                    Sessões Avulsas
                                </label>
                                &nbsp;&nbsp;
                                <label>
                                    <input type="radio" name="modal_tipo_sessao" value="temporada" id="modal_tipo_sessao_temporada" />
                                    Temporada (dias da semana)
                                </label>
                            </td>
                        </tr>
                    </table>
                    
                    <div id="modal_sessoes_avulsas_container" style="margin: 15px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">
                        <p><strong>Sessões Avulsas:</strong></p>
                        <div id="modal_sessoes_avulsas_list"></div>
                        <button type="button" class="button modal-add-sessao-avulsa">Adicionar Sessão</button>
                    </div>
                    
                    <div id="modal_sessoes_temporada_container" style="display: none; margin: 15px 0; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">
                        <p><strong>Dias da Semana e Horários:</strong></p>
                        <table class="form-table">
                            <?php
                            $dias_semana = array(
                                'domingo' => 'Domingo',
                                'segunda' => 'Segunda-feira',
                                'terca' => 'Terça-feira',
                                'quarta' => 'Quarta-feira',
                                'quinta' => 'Quinta-feira',
                                'sexta' => 'Sexta-feira',
                                'sabado' => 'Sábado'
                            );
                            foreach ( $dias_semana as $key => $label ) :
                            ?>
                            <tr>
                                <th><label><?php echo esc_html( $label ); ?></label></th>
                                <td style="display: flex; gap: 10px;">
                                    <input type="time" name="modal_sessoes_<?php echo esc_attr( $key ); ?>_1" id="modal_sessoes_<?php echo esc_attr( $key ); ?>_1" />
                                    <input type="time" name="modal_sessoes_<?php echo esc_attr( $key ); ?>_2" id="modal_sessoes_<?php echo esc_attr( $key ); ?>_2" />
                                    <input type="time" name="modal_sessoes_<?php echo esc_attr( $key ); ?>_3" id="modal_sessoes_<?php echo esc_attr( $key ); ?>_3" />
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                    
                    <input type="hidden" id="modal_sessoes_data" name="sessoes_data" value="" />
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="modal_conteudo">Conteúdo</label></th>
                            <td>
                                <button type="button" id="modal_copiar_conteudo" class="button" style="margin-bottom: 10px;">
                                    Copiar conteúdo do espetáculo
                                </button>
                                <?php 
                                wp_editor( '', 'modal_conteudo', array(
                                    'textarea_name' => 'conteudo',
                                    'textarea_rows' => 8,
                                    'media_buttons' => false,
                                    'teeny' => true,
                                    'quicktags' => true
                                ) );
                                ?>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary">Salvar Temporada</button>
                        <button type="button" class="button temporada-modal-close">Cancelar</button>
                    </p>
                </form>
            </div>
        </div>
        <?php
    }
}
