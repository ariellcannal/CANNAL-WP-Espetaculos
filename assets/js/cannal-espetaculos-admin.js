/**
 * JavaScript da área administrativa do plugin CANNAL Espetáculos
 */
(function($) {
	'use strict';

	/* =========================================================
	 * UTILITÁRIOS
	 * ========================================================= */

	/**
	 * Exibe um notice padrão do WordPress.
	 * Se o modal de temporadas estiver aberto, exibe dentro dele.
	 * Caso contrário, exibe no topo da página (.wrap).
	 *
	 * @param {string} message  Mensagem a exibir.
	 * @param {string} type     'success' | 'error' | 'warning' | 'info'
	 * @param {number} duration Milissegundos até sumir (0 = permanente).
	 */
	function cannalShowNotice(message, type, duration) {
		type = type || 'success';
		duration = (typeof duration !== 'undefined') ? duration : 4000;

		var $notice = $(
			'<div class="notice notice-' + type + ' is-dismissible cannal-ajax-notice">' +
			'<p>' + message + '</p>' +
			'<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dispensar este aviso.</span></button>' +
			'</div>'
		);

		$notice.on('click', '.notice-dismiss', function() {
			$notice.fadeOut(200, function() { $(this).remove(); });
		});

		// Se o modal está visível, inserir dentro do modal
		var $modal = $('#temporada-modal');
		if ($modal.is(':visible')) {
			var $modalContent = $modal.find('.temporada-modal-content');
			// Remover notices anteriores dentro do modal
			$modalContent.find('.cannal-ajax-notice').remove();
			$modalContent.prepend($notice);
		} else {
			// Inserir após o primeiro h1 ou h2 da página
			var $target = $('.wrap h1, .wrap h2').first();
			if ($target.length) {
				$target.after($notice);
			} else {
				$('.wrap').prepend($notice);
			}
			// Rolar até o notice
			$('html, body').animate({ scrollTop: $notice.offset().top - 60 }, 300);
		}

		if (duration > 0) {
			setTimeout(function() {
				$notice.fadeOut(500, function() { $(this).remove(); });
			}, duration);
		}
	}

	/**
	 * Exibe um diálogo de confirmação customizado (substitui confirm()).
	 * Retorna uma Promise que resolve true/false.
	 *
	 * @param {string} message
	 * @returns {Promise<boolean>}
	 */
	function cannalConfirm(message) {
		return new Promise(function(resolve) {
			var $dialog = $(
				'<div class="cannal-confirm-dialog">' +
				'<div class="cannal-confirm-box">' +
				'<p>' + message + '</p>' +
				'<p class="cannal-confirm-actions">' +
				'<button class="button button-primary cannal-confirm-yes">Confirmar</button> ' +
				'<button class="button cannal-confirm-no">Cancelar</button>' +
				'</p></div></div>'
			);
			$('body').append($dialog);
			$dialog.on('click', '.cannal-confirm-yes', function() {
				$dialog.remove();
				resolve(true);
			});
			$dialog.on('click', '.cannal-confirm-no', function() {
				$dialog.remove();
				resolve(false);
			});
		});
	}

	/* =========================================================
	 * GALERIA DE FOTOS
	 * ========================================================= */

	function initGaleria() {
		if (!$('.espetaculo-galeria-container').length) return;

		var galeriaFrame;

		$('.espetaculo-add-galeria').on('click', function(e) {
			e.preventDefault();

			if (galeriaFrame) { galeriaFrame.open(); return; }

			galeriaFrame = wp.media({
				title: 'Selecionar Imagens da Galeria',
				button: { text: 'Adicionar à Galeria' },
				multiple: true
			});

			galeriaFrame.on('select', function() {
				var attachments = galeriaFrame.state().get('selection').toJSON();
				var ids = $('#espetaculo_galeria').val().split(',').filter(Boolean);

				attachments.forEach(function(attachment) {
					if (ids.indexOf(attachment.id.toString()) === -1) {
						ids.push(attachment.id);
						var imageHtml =
							'<div class="espetaculo-galeria-image" data-id="' + attachment.id + '">' +
							'<img src="' + attachment.sizes.thumbnail.url + '" />' +
							'<button type="button" class="remove-image">&times;</button>' +
							'</div>';
						$('.espetaculo-galeria-images').append(imageHtml);
					}
				});

				var idsString = ids.join(',');
				$('#espetaculo_galeria').val(idsString);
				salvarGaleriaAjax(idsString);
			});

			galeriaFrame.open();
		});

		$(document).on('click', '.espetaculo-galeria-image .remove-image', function(e) {
			e.preventDefault();
			var $item = $(this).closest('.espetaculo-galeria-image');
			var imageId = $item.data('id');

			$item.remove();

			var ids = $('#espetaculo_galeria').val().split(',').filter(Boolean);
			ids = ids.filter(function(id) { return id != imageId; });
			var idsString = ids.join(',');
			$('#espetaculo_galeria').val(idsString);
			salvarGaleriaAjax(idsString);
		});

		function salvarGaleriaAjax(galeriaIds) {
			var postId = $('#post_ID').val();
			if (!postId) return;

			$.ajax({
				url: cannalAjax.ajaxurl,
				type: 'POST',
				data: {
					action: 'cannal_save_galeria',
					nonce: cannalAjax.espetaculo_nonce,
					post_id: postId,
					galeria_ids: galeriaIds
				},
				success: function(response) {
					if (!response.success) {
						cannalShowNotice('Erro ao salvar galeria.', 'error');
					}
				},
				error: function() {
					cannalShowNotice('Erro de comunicação ao salvar galeria.', 'error');
				}
			});
		}
	}

	/* =========================================================
	 * LOGOTIPO DO ESPETÁCULO
	 * ========================================================= */

	function initLogotipo() {
		if (!$('.espetaculo-logotipo-upload').length) return;

		var logotipoFrame;

		$('.espetaculo-logotipo-upload').on('click', function(e) {
			e.preventDefault();

			if (logotipoFrame) { logotipoFrame.open(); return; }

			logotipoFrame = wp.media({
				title: 'Selecionar Logotipo',
				button: { text: 'Usar esta imagem' },
				multiple: false
			});

			logotipoFrame.on('select', function() {
				var attachment = logotipoFrame.state().get('selection').first().toJSON();
				var previewUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

				$('#espetaculo_logotipo').val(attachment.id);
				$('#espetaculo-logotipo-preview').html('<img src="' + previewUrl + '" class="cannal-preview-img" />');
				$('.espetaculo-logotipo-remove').show();
			});

			logotipoFrame.open();
		});

		$('.espetaculo-logotipo-remove').on('click', function(e) {
			e.preventDefault();
			$('#espetaculo_logotipo').val('');
			$('#espetaculo-logotipo-preview').empty();
			$(this).hide();
		});
	}

	/* =========================================================
	 * ÍCONE DO ESPETÁCULO (FAVICON)
	 * ========================================================= */

	function initIcone() {
		if (!$('#btn-upload-icone').length) return;

		var iconeFrame;

		$('#btn-upload-icone').on('click', function(e) {
			e.preventDefault();

			if (iconeFrame) { iconeFrame.open(); return; }

			iconeFrame = wp.media({
				title: 'Selecionar Ícone (Favicon)',
				button: { text: 'Usar este ícone' },
				multiple: false,
				library: { type: 'image' }
			});

			iconeFrame.on('select', function() {
				var attachment = iconeFrame.state().get('selection').first().toJSON();
				var $error = $('#espetaculo-icone-error');

				$error.addClass('hidden').text('');

				// Validar proporção quadrada
				if (attachment.width !== attachment.height) {
					$error.removeClass('hidden').text(
						'A imagem deve ser quadrada (mesma largura e altura). ' +
						'Dimensões recebidas: ' + attachment.width + '×' + attachment.height + 'px.'
					);
					return;
				}

				// Validar tamanho máximo
				if (attachment.width > 512 || attachment.height > 512) {
					$error.removeClass('hidden').text(
						'A imagem não pode ser maior que 512×512px. ' +
						'Dimensões recebidas: ' + attachment.width + '×' + attachment.height + 'px.'
					);
					return;
				}

				var previewUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

				$('#espetaculo_icone_id').val(attachment.id);
				$('#espetaculo-icone-preview')
					.removeClass('hidden')
					.html('<img src="' + previewUrl + '" class="cannal-preview-img cannal-icone-preview" />');
				$('#btn-remove-icone').removeClass('hidden');
				$('#btn-upload-icone').text('Alterar Ícone');
			});

			iconeFrame.open();
		});

		$('#btn-remove-icone').on('click', function(e) {
			e.preventDefault();
			$('#espetaculo_icone_id').val('');
			$('#espetaculo-icone-preview').addClass('hidden').empty();
			$(this).addClass('hidden');
			$('#btn-upload-icone').text('Adicionar Ícone');
		});
	}

	/* =========================================================
	 * SESSÕES DA TEMPORADA (tela de edição direta)
	 * ========================================================= */

	function initSessoesDirectEdit() {
		if (!$('#temporada_tipo_sessao').length) return;

		function toggleSessoes() {
			var tipo = $('input[name="temporada_tipo_sessao"]:checked').val();
			if (tipo === 'avulsas') {
				$('#sessoes_avulsas_container').show();
				$('#sessoes_temporada_container').hide();
			} else {
				$('#sessoes_avulsas_container').hide();
				$('#sessoes_temporada_container').show();
			}
		}

		toggleSessoes();
		$('input[name="temporada_tipo_sessao"]').on('change', toggleSessoes);

		$(document).on('click', '.add-sessao-avulsa', function(e) {
			e.preventDefault();
			var row =
				'<div class="sessao-avulsa">' +
				'<input type="date" class="sessao-data" />' +
				'<input type="time" class="sessao-horario" />' +
				'<button type="button" class="button button btn-remove-dia">&times;</button>' +
				'</div>';
			$('#sessoes_avulsas_list').append(row);
			updateSessoesData();
		});

		$(document).on('click', '.btn-remove-sessao', function(e) {
			e.preventDefault();
			$(this).closest('.sessao-avulsa').remove();
			updateSessoesData();
		});

		function updateSessoesData() {
			var tipo = $('input[name="temporada_tipo_sessao"]:checked').val();
			var sessoes = { tipo: tipo, avulsas: [], temporada: {} };

			if (tipo === 'avulsas') {
				$('.sessao-avulsa').each(function() {
					var data = $(this).find('.sessao-data').val();
					var horario = $(this).find('.sessao-horario').val();
					if (data && horario) {
						sessoes.avulsas.push({ data: data, horario: horario });
					}
				});
			} else {
				['domingo', 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'].forEach(function(dia) {
					var horarios = [];
					for (var i = 1; i <= 3; i++) {
						var h = $('#sessoes_' + dia + '_' + i).val();
						if (h) horarios.push(h);
					}
					if (horarios.length) { sessoes.temporada[dia] = horarios.join(', '); }
				});
			}

			$('#sessoes_data').val(JSON.stringify(sessoes));
		}

		$('input[name="temporada_tipo_sessao"]').on('change', updateSessoesData);
		$(document).on('change', '.sessao-data, .sessao-horario', updateSessoesData);
		$(document).on('change', '[id^="sessoes_"]', updateSessoesData);
	}

	/* =========================================================
	 * COPIAR CONTEÚDO DO ESPETÁCULO (tela de edição direta)
	 * ========================================================= */

	function initCopiarConteudo() {
		if (!$('#copiar_conteudo_espetaculo').length) return;

		$('#copiar_conteudo_espetaculo').on('click', function(e) {
			e.preventDefault();
			var espetaculoId = $('#modal_espetaculo_id').val() || $('#espetaculo_id').val();

			if (!espetaculoId) {
				cannalShowNotice('Nenhum espetáculo selecionado.', 'warning');
				return;
			}

			cannalConfirm('Isso irá substituir o conteúdo atual. Continuar?').then(function(confirmed) {
				if (!confirmed) return;

				$.ajax({
					url: cannalAjax.ajaxurl,
					type: 'POST',
					data: {
						action: 'cannal_get_espetaculo_content',
						espetaculo_id: espetaculoId,
						nonce: cannalAjax.espetaculo_nonce
					},
					success: function(response) {
						if (response.success) {
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
							cannalShowNotice('Conteúdo copiado com sucesso!', 'success');
						} else {
							cannalShowNotice('Erro ao copiar conteúdo.', 'error');
						}
					},
					error: function() {
						cannalShowNotice('Erro de comunicação com o servidor.', 'error');
					}
				});
			});
		});
	}

	/* =========================================================
	 * MODAL DE TEMPORADAS
	 * ========================================================= */

	function initModalTemporadas() {
		if (!$('.espetaculo-temporadas-list').length) return;

		/* --- Toggle sessões no modal --- */
		function toggleModalSessoes() {
			var tipo = $('input[name="modal_tipo_sessao"]:checked').val();
			if (tipo === 'avulsas') {
				$('#modal_sessoes_avulsas_container').show();
				$('#modal_sessoes_temporada_container').hide();
			} else {
				$('#modal_sessoes_avulsas_container').hide();
				$('#modal_sessoes_temporada_container').show();
			}
		}

		/* --- Controles dinâmicos de Dias da Semana (Temporada) --- */
		$(document).on('change', '#select_add_dia_semana', function(e) {
			var dia = $(this).val(); // Pega o valor selecionado
			if (dia) {
				$('#linha_dia_' + dia).fadeIn(200); // Exibe a linha com um efeito suave
				$(this).val(''); // Reseta o select para o "Selecione..." para permitir nova escolha
			}
		});

		$(document).on('click', '.btn-remove-dia', function(e) {
			e.preventDefault();
			var dia = $(this).data('dia');
			var $tr = $('#linha_dia_' + dia);

			// Limpa os dados dos inputs para que não sejam salvos
			$tr.find('input[type="time"]').val('');
			// Oculta a linha
			$tr.hide();
		});

		$(document).on('change', 'input[name="modal_tipo_sessao"]', toggleModalSessoes);

		/* --- Adicionar / remover sessão avulsa no modal --- */
		$(document).on('click', '.modal-add-sessao-avulsa', function(e) {
			e.preventDefault();
			var row =
				'<div class="modal-sessao-avulsa sessao-linha-dinamica">' +
				'<input type="date" class="modal-sessao-data" />' +
				'<input type="time" class="modal-sessao-horario" />' +
				'<button type="button" class="button btn-remove-sessao">&times;</button>' +
				'</div>';
			$('#modal_sessoes_avulsas_list').append(row);
		});

		$(document).on('click', '.btn-remove-sessao', function(e) {
			e.preventDefault();
			$(this).closest('.modal-sessao-avulsa').remove();
		});

		/* --- Coletar dados de sessões do modal --- */
		function getModalSessoesData() {
			var tipo = $('input[name="modal_tipo_sessao"]:checked').val();
			var sessoes = { tipo: tipo, avulsas: [], temporada: {} };

			if (tipo === 'avulsas') {
				$('.modal-sessao-avulsa').each(function() {
					var data = $(this).find('.modal-sessao-data').val();
					var horario = $(this).find('.modal-sessao-horario').val();
					if (data && horario) {
						sessoes.avulsas.push({ data: data, horario: horario });
					}
				});
			} else {
				['domingo', 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'].forEach(function(dia) {
					var horarios = [];
					for (var i = 1; i <= 3; i++) {
						var h = $('#modal_sessoes_' + dia + '_' + i).val();
						if (h) horarios.push(h);
					}
					if (horarios.length) { sessoes.temporada[dia] = horarios.join(', '); }
				});
			}

			return JSON.stringify(sessoes);
		}

		/* --- Preencher sessões no modal ao editar --- */
		function setModalSessoesData(sessoesJson) {
			if (!sessoesJson) return;

			try {
				var sessoes = JSON.parse(sessoesJson);

				if (sessoes.tipo === 'avulsas') {
					$('#modal_tipo_sessao_avulsas').prop('checked', true);
				} else {
					$('#modal_tipo_sessao_temporada').prop('checked', true);
				}
				toggleModalSessoes();

				$('#modal_sessoes_avulsas_list').empty();

				if (sessoes.avulsas && sessoes.avulsas.length) {
					sessoes.avulsas.forEach(function(s) {
						var row =
							'<div class="modal-sessao-avulsa">' +
							'<input type="date" class="modal-sessao-data" value="' + s.data + '" />' +
							'<input type="time" class="modal-sessao-horario" value="' + s.horario + '" />' +
							'<button type="button" class="button button btn-remove-dia">&times;</button>' +
							'</div>';
						$('#modal_sessoes_avulsas_list').append(row);
					});
				}

				// Oculta todos os dias e limpa os inputs ANTES de carregar os novos dados
				$('div[id^="linha_dia_"]').hide().find('input[type="time"]').val('');

				if (sessoes.temporada) {
					for (var dia in sessoes.temporada) {
						var horarios = sessoes.temporada[dia].split(',').map(function(h) { return h.trim(); });

						if (horarios.length > 0) {
							// Se existem horários para este dia, exibe a TR
							$('#linha_dia_' + dia).show();

							horarios.forEach(function(horario, index) {
								if (index < 3) {
									$('#modal_sessoes_' + dia + '_' + (index + 1)).val(horario);
								}
							});
						}
					}
				}
			} catch (e) {
				console.error('CANNAL: Erro ao parsear sessões', e);
			}
		}

		/* --- Copiar conteúdo no modal --- */
		$(document).on('click', '#modal_copiar_conteudo', function(e) {
			e.preventDefault();
			var espetaculoId = $('#modal_espetaculo_id').val();

			if (!espetaculoId) {
				cannalShowNotice('Nenhum espetáculo selecionado.', 'warning');
				return;
			}

			cannalConfirm('Isso irá substituir o conteúdo atual. Continuar?').then(function(confirmed) {
				if (!confirmed) return;

				$.ajax({
					url: cannalAjax.ajaxurl,
					type: 'POST',
					data: {
						action: 'cannal_get_espetaculo_content',
						espetaculo_id: espetaculoId,
						nonce: cannalAjax.espetaculo_nonce
					},
					success: function(response) {
						if (response.success) {
							if (typeof tinymce !== 'undefined' && tinymce.get('modal_conteudo')) {
								tinymce.get('modal_conteudo').setContent(response.data.content);
							} else {
								$('#modal_conteudo').val(response.data.content);
							}
							cannalShowNotice('Conteúdo copiado com sucesso!', 'success');
						} else {
							cannalShowNotice('Erro ao copiar conteúdo.', 'error');
						}
					},
					error: function() {
						cannalShowNotice('Erro na requisição AJAX.', 'error');
					}
				});
			});
		});

		/* --- Abrir modal para nova temporada --- */
		$(document).on('click', '.open-temporada-modal', function(e) {
			e.preventDefault();
			var $form = $('#temporada-form');
			if (!$form.length) {
				cannalShowNotice('Erro: Modal não está disponível nesta página.', 'error');
				return;
			}

			$('#temporada-modal-title').text('Nova Temporada');
			$form[0].reset();
			$('#modal_temporada_id').val('');
			$('#modal_sessoes_avulsas_list').empty();
			$('#modal_tipo_sessao_temporada').prop('checked', true);
			toggleModalSessoes();
			// Limpar notices anteriores no modal
			$('#temporada-modal .cannal-ajax-notice').remove();

			// Oculta e limpa todos os dias da semana ao criar nova temporada
			$('div[id^="linha_dia_"]').hide().find('input[type="time"]').val('');
			$('#select_add_dia_semana').val('');

			$('#temporada-modal').fadeIn();
		});

		/* --- Duplicar temporada --- */
		$(document).on('click', '.duplicate-temporada-btn', function(e) {
			e.preventDefault();
			var temporadaId = $(this).data('temporada-id');

			$.ajax({
				url: cannalAjax.ajaxurl,
				type: 'POST',
				data: {
					action: 'cannal_get_temporada',
					nonce: cannalAjax.nonce,
					temporada_id: temporadaId
				},
				success: function(response) {
					if (response.success) {
						$('#temporada-modal-title').html(response.data.espetaculo_nome + ' <small>Duplicar Temporada</small>');
						$('#modal_temporada_id').val('');
						$('#modal_teatro_nome').val(response.data.teatro_nome);
						$('#modal_teatro_endereco').val(response.data.teatro_endereco);
						$('#modal_diretor').val(response.data.diretor);
						$('#modal_elenco').val(response.data.elenco);
						$('#modal_data_inicio').val('');
						$('#modal_data_fim').val('');
						$('#modal_valores').val(response.data.valores);
						$('#modal_link_vendas').val(response.data.link_vendas);
						$('#modal_link_texto').val(response.data.link_texto);
						$('#modal_data_banner').val('');

						if (response.data.sessoes_data) {
							setModalSessoesData(response.data.sessoes_data);
						}
						if (response.data.conteudo) {
							if (typeof tinymce !== 'undefined' && tinymce.get('modal_conteudo')) {
								tinymce.get('modal_conteudo').setContent(response.data.conteudo);
							} else {
								$('#modal_conteudo').val(response.data.conteudo);
							}
						}
						$('#temporada-modal .cannal-ajax-notice').remove();
						$('#temporada-modal').fadeIn();
					} else {
						cannalShowNotice('Erro ao carregar dados da temporada.', 'error');
					}
				},
				error: function() {
					cannalShowNotice('Erro na requisição AJAX.', 'error');
				}
			});
		});

		/* --- Editar temporada --- */
		$(document).on('click', '.edit-temporada-btn', function(e) {
			e.preventDefault();
			var temporadaId = $(this).data('temporada-id');

			$.ajax({
				url: cannalAjax.ajaxurl,
				type: 'POST',
				data: {
					action: 'cannal_get_temporada',
					nonce: cannalAjax.nonce,
					temporada_id: temporadaId
				},
				success: function(response) {
					if (response.success) {
						$('#temporada-modal-title').html(response.data.espetaculo_nome + ' <small>Editar Temporada</small>');
						$('#modal_temporada_id').val(temporadaId);
						$('#modal_teatro_nome').val(response.data.teatro_nome);
						$('#modal_teatro_endereco').val(response.data.teatro_endereco);
						$('#modal_diretor').val(response.data.diretor);
						$('#modal_elenco').val(response.data.elenco);
						$('#modal_data_inicio').val(response.data.data_inicio);
						$('#modal_data_fim').val(response.data.data_fim);
						$('#modal_valores').val(response.data.valores);
						$('#modal_link_vendas').val(response.data.link_vendas);
						$('#modal_link_texto').val(response.data.link_texto);
						$('#modal_data_banner').val(response.data.data_banner);

						if (typeof tinymce !== 'undefined' && tinymce.get('modal_conteudo')) {
							tinymce.get('modal_conteudo').setContent(response.data.conteudo || '');
						} else {
							$('#modal_conteudo').val(response.data.conteudo || '');
						}

						// Restaurar tipo_sessao ANTES de setModalSessoesData
						if (response.data.tipo_sessao === 'temporada') {
							$('#modal_tipo_sessao_temporada').prop('checked', true);
						} else {
							$('#modal_tipo_sessao_avulsas').prop('checked', true);
						}
						toggleModalSessoes();

						setModalSessoesData(response.data.sessoes_data);
						$('#temporada-modal .cannal-ajax-notice').remove();
						$('#temporada-modal').fadeIn();
					} else {
						cannalShowNotice('Erro ao carregar temporada: ' + (response.data ? response.data.message : 'Erro desconhecido.'), 'error');
					}
				},
				error: function() {
					cannalShowNotice('Erro na requisição AJAX.', 'error');
				}
			});
		});

		/* --- Excluir temporada --- */
		$(document).on('click', '.delete-temporada-btn', function(e) {
			e.preventDefault();
			// Capturar referências ANTES da Promise para não perder o contexto
			var temporadaId = $(this).data('temporada-id');
			var $row = $(this).closest('tr');

			cannalConfirm('Tem certeza que deseja excluir esta temporada? Esta ação não pode ser desfeita.').then(function(confirmed) {
				if (!confirmed) return;

				$.ajax({
					url: cannalAjax.ajaxurl,
					type: 'POST',
					data: {
						action: 'cannal_delete_temporada',
						nonce: cannalAjax.nonce,
						temporada_id: temporadaId
					},
					success: function(response) {
						if (response.success) {
							$row.fadeOut(300, function() {
								$(this).remove();
								// Se não sobrar linhas, exibir mensagem de vazio
								if ($('#temporadas-tbody tr').length === 0) {
									$('#no-temporadas-msg').show();
								}
							});
							cannalShowNotice('Temporada excluída com sucesso!', 'success');
						} else {
							cannalShowNotice('Erro ao excluir temporada: ' + (response.data ? response.data.message : 'Erro desconhecido.'), 'error');
						}
					},
					error: function(xhr) {
						console.error('CANNAL delete error', xhr.status, xhr.responseText);
						cannalShowNotice('Erro na requisição AJAX (status ' + xhr.status + ').', 'error');
					}
				});
			});
		});

		/* --- Fechar modal --- */
		$(document).on('click', '.temporada-modal-close', function() {
			$('#temporada-modal').fadeOut();
		});

		$(document).on('click', '#temporada-modal', function(e) {
			if ($(e.target).is('#temporada-modal')) {
				$('#temporada-modal').fadeOut();
			}
		});

		/* --- Salvar temporada via AJAX (sem reload) --- */
		$(document).on('submit', '#temporada-form', function(e) {
			e.preventDefault();
			e.stopPropagation();

			var $form = $(this);
			var $btn = $form.find('[type="submit"]');

			// Validação manual: teatro_nome obrigatório
			var teatroNome = $('#modal_teatro_nome').val().trim();
			if (!teatroNome) {
				cannalShowNotice('O campo "Nome do Teatro" é obrigatório.', 'error');
				$('#modal_teatro_nome').focus();
				return;
			}

			// Validação: se tipo temporada, data_inicio e data_fim obrigatórios
			var tipoSessao = $('input[name="modal_tipo_sessao"]:checked').val();
			if (tipoSessao === 'temporada') {
				if (!$('#modal_data_inicio').val()) {
					cannalShowNotice('Informe a Data de Início para temporadas por dias da semana.', 'error');
					$('#modal_data_inicio').focus();
					return;
				}
				if (!$('#modal_data_fim').val()) {
					cannalShowNotice('Informe a Data Final para temporadas por dias da semana.', 'error');
					$('#modal_data_fim').focus();
					return;
				}
			}

			$btn.prop('disabled', true).text('Salvando...');

			var conteudo = '';
			if (typeof tinymce !== 'undefined' && tinymce.get('modal_conteudo')) {
				conteudo = tinymce.get('modal_conteudo').getContent();
			} else {
				conteudo = $('#modal_conteudo').val();
			}

			var sessoesData = getModalSessoesData();

			var formData = {
				action: 'cannal_save_temporada',
				nonce: cannalAjax.nonce,
				temporada_id: $('#modal_temporada_id').val(),
				espetaculo_id: $('#modal_espetaculo_id').val(),
				teatro_nome: $('#modal_teatro_nome').val(),
				teatro_endereco: $('#modal_teatro_endereco').val(),
				diretor: $('#modal_diretor').val(),
				elenco: $('#modal_elenco').val(),
				data_inicio: $('#modal_data_inicio').val(),
				data_fim: $('#modal_data_fim').val(),
				valores: $('#modal_valores').val(),
				link_vendas: $('#modal_link_vendas').val(),
				link_texto: $('#modal_link_texto').val(),
				data_banner: $('#modal_data_banner').val(),
				tipo_sessao: tipoSessao,
				sessoes_data: sessoesData,
				conteudo: conteudo
			};

			$.ajax({
				url: cannalAjax.ajaxurl,
				type: 'POST',
				data: formData,
				success: function(response) {
					$btn.prop('disabled', false).text('Salvar Temporada');


					if (response.success) {
						$('#temporada-modal').fadeOut(300, function() {
							// Exibir notice na página após fechar o modal
							cannalShowNotice('Temporada salva com sucesso!', 'success');
						});
						atualizarLinhaTemporada(response.data);
					} else {
						cannalShowNotice('Erro ao salvar temporada: ' + (response.data ? response.data.message : 'Erro desconhecido.'), 'error');
					}
				},
				error: function(xhr) {
					$btn.prop('disabled', false).text('Salvar Temporada');
					console.error('CANNAL save error', xhr.status, xhr.responseText);
					cannalShowNotice('Erro de comunicação com o servidor (status ' + xhr.status + ').', 'error');
				}
			});
		});

		/**
		 * Atualiza ou insere a linha da temporada na tabela sem recarregar a página.
		 *
		 * @param {Object} data Dados retornados pelo AJAX
		 */
		function atualizarLinhaTemporada(data) {
			var $tbody = $('#temporadas-tbody');
			var $noMsg = $('#no-temporadas-msg');
			var rowId = 'temporada-row-' + data.temporada_id;
			var $row = $('#' + rowId);

			var rowHtml =
				'<td>' + (data.teatro || '') + '</td>' +
				'<td>' + (data.periodo || '') + '</td>' +
				'<td>' + (data.dias_horarios || '') + '</td>' +
				'<td>' + (data.status_label || '') + '</td>' +
				'<td>' +
				'<button type="button" class="button button-small edit-temporada-btn" data-temporada-id="' + data.temporada_id + '">Editar</button> ' +
				'<button type="button" class="button button-small duplicate-temporada-btn" data-temporada-id="' + data.temporada_id + '">Duplicar</button> ' +
				'<button type="button" class="button button-small button-link-delete delete-temporada-btn" data-temporada-id="' + data.temporada_id + '">Excluir</button>' +
				'</td>';

			if ($row.length) {
				// Atualizar linha existente
				$row.html(rowHtml).addClass('cannal-row-updated');
				setTimeout(function() { $row.removeClass('cannal-row-updated'); }, 2000);
			} else {
				// Inserir nova linha
				if (!$tbody.length) {
					// Tabela ainda não existe (primeira temporada) — criá-la dinamicamente
					var $table = $(
						'<table class="wp-list-table widefat fixed striped">' +
						'<thead><tr>' +
						'<th>Teatro</th>' +
						'<th>Per\u00edodo</th>' +
						'<th>Dias e Hor\u00e1rios</th>' +
						'<th>Status</th>' +
						'<th>A\u00e7\u00f5es</th>' +
						'</tr></thead>' +
						'<tbody id="temporadas-tbody"></tbody>' +
						'</table>'
					);
					// Inserir a tabela antes do botão "Adicionar Nova Temporada"
					$('.temporadas-actions').before($table);
					$tbody = $('#temporadas-tbody');
				}
				var $newRow = $('<tr id="' + rowId + '">' + rowHtml + '</tr>');
				$tbody.append($newRow);
				$newRow.addClass('cannal-row-updated');
				setTimeout(function() { $newRow.removeClass('cannal-row-updated'); }, 2000);

				// Esconder mensagem "nenhuma temporada"
				$noMsg.hide();
			}
		}
	}

	/* =========================================================
	 * INICIALIZAÇÃO
	 * ========================================================= */

	$(document).ready(function() {
		initGaleria();
		initLogotipo();
		initIcone();
		initSessoesDirectEdit();
		initCopiarConteudo();
		initModalTemporadas();
	});

})(jQuery);
