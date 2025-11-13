(function($) {
    'use strict';

    $(document).ready(function() {
        console.log('CANNAL Espetáculos: JavaScript carregado');
        console.log('cannalAjax:', typeof cannalAjax !== 'undefined' ? cannalAjax : 'NÃO DEFINIDO');

        // Gerenciamento de galeria de fotos
        if ($('.espetaculo-galeria-container').length) {
            console.log('CANNAL: Galeria encontrada, inicializando...');
            var galeriaFrame;

            $('.espetaculo-add-galeria').on('click', function(e) {
                e.preventDefault();

                if (galeriaFrame) {
                    galeriaFrame.open();
                    return;
                }

                galeriaFrame = wp.media({
                    title: 'Selecionar Imagens da Galeria',
                    button: {
                        text: 'Adicionar à Galeria'
                    },
                    multiple: true
                });

                galeriaFrame.on('select', function() {
                    var attachments = galeriaFrame.state().get('selection').toJSON();
                    var ids = $('#espetaculo_galeria').val().split(',').filter(Boolean);

                    attachments.forEach(function(attachment) {
                        if (ids.indexOf(attachment.id.toString()) === -1) {
                            ids.push(attachment.id);
                            
                            var imageHtml = '<div class="espetaculo-galeria-image" data-id="' + attachment.id + '">' +
                                '<img src="' + attachment.sizes.thumbnail.url + '" />' +
                                '<button type="button" class="remove-image">×</button>' +
                                '</div>';
                            
                            $('.espetaculo-galeria-images').append(imageHtml);
                        }
                    });

                    $('#espetaculo_galeria').val(ids.join(','));
                    console.log('CANNAL: Galeria atualizada. IDs:', ids.join(','));
                });

                galeriaFrame.open();
            });

            $(document).on('click', '.espetaculo-galeria-image .remove-image', function(e) {
                e.preventDefault();
                var $item = $(this).closest('.espetaculo-galeria-image');
                var imageId = $item.data('id');
                
                $item.remove();
                
                var ids = $('#espetaculo_galeria').val().split(',').filter(Boolean);
                ids = ids.filter(function(id) {
                    return id != imageId;
                });
                $('#espetaculo_galeria').val(ids.join(','));
                console.log('CANNAL: Imagem removida. IDs restantes:', ids.join(','));
            });
        }

        // Gerenciamento de sessões
        if ($('#temporada_tipo_sessao').length) {
            
            function toggleSessoes() {
                var tipo = $('input[name="temporada_tipo_sessao"]:checked').val();
                if (tipo === 'avulsas') {
                    $('#sessoes-avulsas-container').show();
                    $('#sessoes-temporada-container').hide();
                } else {
                    $('#sessoes-avulsas-container').hide();
                    $('#sessoes-temporada-container').show();
                }
            }

            $('input[name="temporada_tipo_sessao"]').on('change', toggleSessoes);
            toggleSessoes();

            // Adicionar sessão avulsa
            $('#add-sessao-avulsa').on('click', function(e) {
                e.preventDefault();
                var data = $('#nova_sessao_data').val();
                var horario = $('#nova_sessao_horario').val();
                
                if (!data || !horario) {
                    alert('Por favor, preencha data e horário.');
                    return;
                }

                var row = '<tr>' +
                    '<td>' + data + '</td>' +
                    '<td>' + horario + '</td>' +
                    '<td><button type="button" class="button button-small remove-sessao">Remover</button></td>' +
                    '<input type="hidden" name="temporada_sessoes_avulsas[]" value="' + data + '|' + horario + '" />' +
                    '</tr>';
                
                $('#sessoes-avulsas-list tbody').append(row);
                $('#nova_sessao_data').val('');
                $('#nova_sessao_horario').val('');
                updateSessoesData();
            });

            $(document).on('click', '.remove-sessao', function(e) {
                e.preventDefault();
                $(this).closest('tr').remove();
                updateSessoesData();
            });

            function updateSessoesData() {
                var sessoes = {
                    tipo: $('input[name="temporada_tipo_sessao"]:checked').val(),
                    avulsas: [],
                    temporada: {}
                };

                if (sessoes.tipo === 'avulsas') {
                    $('input[name="temporada_sessoes_avulsas[]"]').each(function() {
                        var parts = $(this).val().split('|');
                        sessoes.avulsas.push({
                            data: parts[0],
                            horario: parts[1]
                        });
                    });
                } else {
                    $('input[name^="temporada_sessoes_temporada"]').each(function() {
                        var dia = $(this).attr('name').match(/\[([^\]]+)\]/)[1];
                        var horario = $(this).val();
                        if (horario) {
                            sessoes.temporada[dia] = horario;
                        }
                    });
                }

                $('#temporada_sessoes_data').val(JSON.stringify(sessoes));
            }

            $('input[name^="temporada_sessoes_temporada"]').on('change', updateSessoesData);
            $('input[name="temporada_tipo_sessao"]').on('change', updateSessoesData);
        }

        // Botão copiar conteúdo do espetáculo (na tela de temporada)
        $('#temporada_espetaculo_id').on('change', function() {
            var espetaculoId = $(this).val();
            if (espetaculoId) {
                $('#btn-copiar-conteudo').prop('disabled', false);
            } else {
                $('#btn-copiar-conteudo').prop('disabled', true);
            }
        });

        $('#btn-copiar-conteudo').on('click', function(e) {
            e.preventDefault();
            var espetaculoId = $('#temporada_espetaculo_id').val();
            
            if (!espetaculoId) {
                alert('Por favor, selecione um espetáculo primeiro.');
                return;
            }
            
            if (!confirm('Isso irá substituir o conteúdo atual da temporada. Continuar?')) {
                return;
            }
            
            $.ajax({
                url: cannalAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_espetaculo_content',
                    espetaculo_id: espetaculoId,
                    nonce: cannalAjax.espetaculo_nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Usar o editor do WordPress
                        if (typeof tinymce !== 'undefined') {
                            var editor = tinymce.get('content');
                            if (editor) {
                                editor.setContent(response.data.content);
                            } else {
                                $('#content').val(response.data.content);
                            }
                        } else {
                            $('#content').val(response.data.content);
                        }
                        alert('Conteúdo copiado com sucesso!');
                    } else {
                        alert('Erro ao copiar conteúdo.');
                    }
                },
                error: function() {
                    alert('Erro de comunicação com o servidor.');
                }
            });
        });

        // Gerenciamento de Modal de Temporadas
        if ($('.espetaculo-temporadas-list').length) {
            console.log('CANNAL: Lista de temporadas encontrada');
            console.log('CANNAL: Modal existe?', $('#temporada-modal').length > 0);
            
            // Botão copiar conteúdo no modal
            $(document).on('click', '#modal_copiar_conteudo', function(e) {
                e.preventDefault();
                
                var espetaculoId = $('#modal_espetaculo_id').val();
                if (!espetaculoId) {
                    alert('Nenhum espetáculo selecionado.');
                    return;
                }
                
                if (!confirm('Isso irá substituir o conteúdo atual. Continuar?')) {
                    return;
                }
                
                $.ajax({
                    url: cannalAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'get_espetaculo_content',
                        espetaculo_id: espetaculoId,
                        nonce: cannalAjax.espetaculo_nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Copiar para o editor do modal
                            if (typeof tinymce !== 'undefined' && tinymce.get('modal_conteudo')) {
                                tinymce.get('modal_conteudo').setContent(response.data.content);
                            } else {
                                $('#modal_conteudo').val(response.data.content);
                            }
                            alert('Conteúdo copiado com sucesso!');
                        } else {
                            alert('Erro ao copiar conteúdo.');
                        }
                    },
                    error: function() {
                        alert('Erro na requisição AJAX.');
                    }
                });
            });
            
            // Abrir modal para nova temporada
            $('.open-temporada-modal').on('click', function(e) {
                e.preventDefault();
                console.log('CANNAL: Abrindo modal para nova temporada');
                
                var $form = $('#temporada-form');
                if ($form.length === 0) {
                    console.error('CANNAL: Formulário #temporada-form não encontrado!');
                    alert('Erro: Modal não está disponível nesta página.');
                    return;
                }
                
                $('#temporada-modal-title').text('Nova Temporada');
                $form[0].reset();
                $('#modal_temporada_id').val('');
                $('#temporada-modal').fadeIn();
            });

            // Editar temporada
            $(document).on('click', '.edit-temporada-btn', function(e) {
                e.preventDefault();
                var temporadaId = $(this).data('temporada-id');
                
                $.ajax({
                    url: cannalAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'get_temporada',
                        nonce: cannalAjax.nonce,
                        temporada_id: temporadaId
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#temporada-modal-title').text('Editar Temporada');
                            $('#modal_temporada_id').val(temporadaId);
                            $('#modal_teatro_nome').val(response.data.teatro_nome);
                            $('#modal_teatro_endereco').val(response.data.teatro_endereco);
                            $('#modal_data_inicio').val(response.data.data_inicio);
                            $('#modal_data_fim').val(response.data.data_fim);
                            $('#modal_valores').val(response.data.valores);
                            $('#modal_link_vendas').val(response.data.link_vendas);
                            $('#modal_link_texto').val(response.data.link_texto);
                            $('#modal_data_inicio_banner').val(response.data.data_inicio_banner);
                            
                            // Preencher conteúdo no editor
                            if (typeof tinymce !== 'undefined' && tinymce.get('modal_conteudo')) {
                                tinymce.get('modal_conteudo').setContent(response.data.conteudo || '');
                            } else {
                                $('#modal_conteudo').val(response.data.conteudo || '');
                            }
                            
                            $('#temporada-modal').fadeIn();
                        } else {
                            alert('Erro ao carregar temporada: ' + response.data.message);
                        }
                    }
                });
            });

            // Excluir temporada
            $(document).on('click', '.delete-temporada-btn', function(e) {
                e.preventDefault();
                if (!confirm('Tem certeza que deseja excluir esta temporada?')) {
                    return;
                }
                
                var temporadaId = $(this).data('temporada-id');
                var $row = $(this).closest('tr');
                
                $.ajax({
                    url: cannalAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'delete_temporada',
                        nonce: cannalAjax.nonce,
                        temporada_id: temporadaId
                    },
                    success: function(response) {
                        if (response.success) {
                            $row.fadeOut(function() {
                                $(this).remove();
                            });
                            alert('Temporada excluída com sucesso!');
                        } else {
                            alert('Erro ao excluir temporada: ' + response.data.message);
                        }
                    }
                });
            });

            // Fechar modal
            $('.temporada-modal-close').on('click', function() {
                $('#temporada-modal').fadeOut();
            });

            // Fechar modal ao clicar fora
            $(window).on('click', function(e) {
                if ($(e.target).is('#temporada-modal')) {
                    $('#temporada-modal').fadeOut();
                }
            });

            // Salvar temporada via AJAX
            $('#temporada-form').on('submit', function(e) {
                e.preventDefault();
                
                // Obter conteúdo do editor
                var conteudo = '';
                if (typeof tinymce !== 'undefined' && tinymce.get('modal_conteudo')) {
                    conteudo = tinymce.get('modal_conteudo').getContent();
                } else {
                    conteudo = $('#modal_conteudo').val();
                }
                
                var formData = {
                    action: 'save_temporada',
                    nonce: cannalAjax.nonce,
                    temporada_id: $('#modal_temporada_id').val(),
                    espetaculo_id: $('#modal_espetaculo_id').val(),
                    teatro_nome: $('#modal_teatro_nome').val(),
                    teatro_endereco: $('#modal_teatro_endereco').val(),
                    data_inicio: $('#modal_data_inicio').val(),
                    data_fim: $('#modal_data_fim').val(),
                    valores: $('#modal_valores').val(),
                    link_vendas: $('#modal_link_vendas').val(),
                    link_texto: $('#modal_link_texto').val(),
                    data_inicio_banner: $('#modal_data_inicio_banner').val(),
                    conteudo: conteudo
                };
                
                $.ajax({
                    url: cannalAjax.ajaxurl,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            alert('Temporada salva com sucesso!');
                            $('#temporada-modal').fadeOut();
                            location.reload(); // Recarregar para atualizar a lista
                        } else {
                            alert('Erro ao salvar temporada: ' + response.data.message);
                        }
                    },
                    error: function() {
                        alert('Erro de comunicação com o servidor.');
                    }
                });
            });
        }
    });

})(jQuery);
