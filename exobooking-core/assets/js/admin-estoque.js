/* global exobookingEstoque */
(function ($) {
	'use strict';

	$(document).ready(function () {
		var $btn   = $('#exobooking-estoque-add');
		var $msg   = $('#exobooking-estoque-msg');
		var $wrap  = $('#exobooking-estoque-table-wrap');
		var labels = exobookingEstoque.labels;

		$btn.on('click', function () {
			var data  = $('#exobooking-estoque-data').val();
			var vagas = $('#exobooking-estoque-vagas').val();

			if (!data || vagas === '') {
				$msg.css('color', '#cc0000').text(labels.fillAll);
				return;
			}

			$btn.prop('disabled', true);
			$msg.css('color', '#888').text(labels.saving);

			$.post(
				exobookingEstoque.ajaxUrl,
				{
					action:  'exobooking_save_estoque',
					nonce:   exobookingEstoque.nonce,
					post_id: exobookingEstoque.postId,
					data:    data,
					vagas:   vagas
				},
				function (response) {
					$btn.prop('disabled', false);
					if (response.success) {
						$msg.css('color', '#00a32a').text(response.data.message);
						// Actualiza tabela com os dados devolvidos pelo servidor
						if (response.data.rows) {
							var $tbody = $wrap.find('#exobooking-estoque-tbody');
							if ($tbody.length) {
								$tbody.html(response.data.rows);
							} else {
								// Primeira entrada: substitui mensagem "nenhuma data" pela tabela
								$wrap.html(
									'<table class="widefat striped" style="max-width:100%;">' +
									'<thead><tr>' +
									'<th>' + labels.colData        + '</th>' +
									'<th>' + labels.colVagasTotal  + '</th>' +
									'<th>' + labels.colReservadas  + '</th>' +
									'<th>' + labels.colDisponiveis + '</th>' +
									'</tr></thead>' +
									'<tbody id="exobooking-estoque-tbody">' + response.data.rows + '</tbody>' +
									'</table>'
								);
							}
						}
						setTimeout(function () { $msg.text(''); }, 3000);
					} else {
						var errMsg = (response.data && response.data.message)
							? response.data.message
							: labels.error;
						$msg.css('color', '#cc0000').text(errMsg);
					}
				}
			).fail(function () {
				$btn.prop('disabled', false);
				$msg.css('color', '#cc0000').text(labels.error);
			});
		});
	});
}(jQuery));
