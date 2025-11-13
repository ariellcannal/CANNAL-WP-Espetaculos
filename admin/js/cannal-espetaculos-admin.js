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

})(jQuery);
