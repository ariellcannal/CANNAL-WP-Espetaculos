(function($) {
    'use strict';

    $(document).ready(function() {

        // Gerenciamento de galeria de fotos
        if ($('.espetaculo-galeria-container').length) {
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
                });

                galeriaFrame.open();
            });

            $(document).on('click', '.espetaculo-galeria-image .remove-image', function(e) {
                e.preventDefault();
                var $image = $(this).closest('.espetaculo-galeria-image');
                var imageId = $image.data('id');
                var ids = $('#espetaculo_galeria').val().split(',').filter(Boolean);
                
                ids = ids.filter(function(id) {
                    return id != imageId;
                });

                $('#espetaculo_galeria').val(ids.join(','));
                $image.remove();
            });
        }

        // Gerenciamento de sessões
        if ($('.temporada-sessoes-container').length) {
            
            // Mostrar/ocultar containers baseado no tipo de sessão
            function toggleSessoesContainers() {
                var tipoSessao = $('input[name="temporada_tipo_sessao"]:checked').val();
                
                if (tipoSessao === 'avulsas') {
                    $('#sessoes-avulsas-container').show();
                    $('#sessoes-temporada-container').hide();
                } else if (tipoSessao === 'temporada') {
                    $('#sessoes-avulsas-container').hide();
                    $('#sessoes-temporada-container').show();
                } else {
                    $('#sessoes-avulsas-container').hide();
                    $('#sessoes-temporada-container').hide();
                }
            }

            $('input[name="temporada_tipo_sessao"]').on('change', toggleSessoesContainers);
            toggleSessoesContainers();

            // Carregar sessões existentes
            var sessoesData = $('#temporada_sessoes_data').val();
            if (sessoesData) {
                try {
                    var sessoes = JSON.parse(sessoesData);
                    
                    if (sessoes.avulsas) {
                        sessoes.avulsas.forEach(function(sessao) {
                            addSessaoAvulsa(sessao.data, sessao.horarios);
                        });
                    }

                    if (sessoes.temporada) {
                        Object.keys(sessoes.temporada).forEach(function(dia) {
                            var horarios = sessoes.temporada[dia];
                            $('input[name="temporada_sessoes_temporada[' + dia + ']"]').val(horarios.join(', '));
                        });
                    }
                } catch (e) {
                    console.error('Erro ao carregar sessões:', e);
                }
            }

            // Adicionar sessão avulsa
            $('.add-sessao-avulsa').on('click', function(e) {
                e.preventDefault();
                addSessaoAvulsa();
            });

            function addSessaoAvulsa(data, horarios) {
                data = data || '';
                horarios = horarios || [];
                
                var index = $('.sessao-avulsa-item').length;
                var horariosStr = horarios.join(', ');

                var html = '<div class="sessao-avulsa-item" style="margin-bottom: 15px; padding: 15px; border: 1px solid #ddd; background: #f9f9f9;">' +
                    '<p><label><strong>Data:</strong></label><br>' +
                    '<input type="date" class="sessao-avulsa-data" value="' + data + '" style="width: 200px;" /></p>' +
                    '<p><label><strong>Horários:</strong> (separar por vírgula)</label><br>' +
                    '<input type="text" class="sessao-avulsa-horarios" value="' + horariosStr + '" placeholder="Ex: 20h, 22h" style="width: 300px;" /></p>' +
                    '<button type="button" class="button remove-sessao-avulsa">Remover</button>' +
                    '</div>';

                $('#sessoes-avulsas-list').append(html);
            }

            $(document).on('click', '.remove-sessao-avulsa', function(e) {
                e.preventDefault();
                $(this).closest('.sessao-avulsa-item').remove();
                updateSessoesData();
            });

            // Atualizar dados de sessões ao salvar
            $(document).on('change', '.sessao-avulsa-data, .sessao-avulsa-horarios, input[name^="temporada_sessoes_temporada"]', function() {
                updateSessoesData();
            });

            function updateSessoesData() {
                var tipoSessao = $('input[name="temporada_tipo_sessao"]:checked').val();
                var sessoesData = {};

                if (tipoSessao === 'avulsas') {
                    sessoesData.avulsas = [];
                    $('.sessao-avulsa-item').each(function() {
                        var data = $(this).find('.sessao-avulsa-data').val();
                        var horariosStr = $(this).find('.sessao-avulsa-horarios').val();
                        var horarios = horariosStr.split(',').map(function(h) {
                            return h.trim();
                        }).filter(Boolean);

                        if (data && horarios.length > 0) {
                            sessoesData.avulsas.push({
                                data: data,
                                horarios: horarios
                            });
                        }
                    });
                } else if (tipoSessao === 'temporada') {
                    sessoesData.temporada = {};
                    $('input[name^="temporada_sessoes_temporada"]').each(function() {
                        var dia = $(this).attr('name').match(/\[(.*?)\]/)[1];
                        var horariosStr = $(this).val();
                        var horarios = horariosStr.split(',').map(function(h) {
                            return h.trim();
                        }).filter(Boolean);

                        if (horarios.length > 0) {
                            sessoesData.temporada[dia] = horarios;
                        }
                    });
                }

                $('#temporada_sessoes_data').val(JSON.stringify(sessoesData));
            }

            // Atualizar ao submeter o formulário
            $('form#post').on('submit', function() {
                updateSessoesData();
            });
        }

        // Copiar conteúdo do espetáculo
        $('#temporada_copiar_conteudo').on('change', function() {
            if ($(this).is(':checked')) {
                var espetaculoId = $('#temporada_espetaculo_id').val();
                if (espetaculoId) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'get_espetaculo_content',
                            espetaculo_id: espetaculoId
                        },
                        success: function(response) {
                            if (response.success && response.data.content) {
                                if (typeof tinymce !== 'undefined' && tinymce.get('content')) {
                                    tinymce.get('content').setContent(response.data.content);
                                } else {
                                    $('#content').val(response.data.content);
                                }
                            }
                        }
                    });
                }
            }
        });
    });

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
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_espetaculo_content',
                    espetaculo_id: espetaculoId
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
            
            // Abrir modal para nova temporada
            $('.open-temporada-modal').on('click', function(e) {
                e.preventDefault();
                $('#temporada-modal-title').text('Nova Temporada');
                $('#temporada-form')[0].reset();
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
                    data_inicio_banner: $('#modal_data_inicio_banner').val()
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

})(jQuery);
