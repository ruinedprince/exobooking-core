<?php
/**
 * Metabox "Estoque de vagas" na tela de edição do passeio (admin).
 *
 * Usa AJAX para salvar entradas de estoque sem interferir no formulário
 * principal do WordPress (Publicar / Atualizar), evitando o problema de
 * formulários aninhados.
 *
 * @since      0.5.0
 * @package    ExoBooking_Core
 * @subpackage ExoBooking_Core/includes
 * @author     Gabriel Maciel <jsttmaciel89@gmail.com>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Metabox de estoque de vagas para o CPT passeio.
 */
class ExoBooking_Core_Admin_Estoque_Metabox {

	/**
	 * Nonce para as chamadas AJAX.
	 *
	 * @since  0.5.0
	 */
	const NONCE_ACTION = 'exobooking_estoque_ajax';
	const AJAX_ACTION  = 'exobooking_save_estoque';

	/**
	 * Registra metabox, AJAX handler e enqueue de scripts.
	 *
	 * @since  0.5.0
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_metabox' ) );
		add_action( 'wp_ajax_' . self::AJAX_ACTION, array( __CLASS__, 'ajax_save_estoque' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	/**
	 * Registra a metabox na tela de edição do passeio.
	 *
	 * @since  0.5.0
	 */
	public static function register_metabox() {
		add_meta_box(
			'exobooking_estoque_vagas',
			__( 'Estoque de vagas', 'exobooking-core' ),
			array( __CLASS__, 'render_metabox' ),
			'passeio',
			'normal',
			'default'
		);
	}

	/**
	 * Carrega o script de estoque somente na tela de edição de passeios.
	 *
	 * @since  0.5.0
	 * @param  string $hook Sufixo do hook da página atual.
	 */
	public static function enqueue_scripts( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== 'passeio' ) {
			return;
		}
		global $post;
		$post_id = $post ? (int) $post->ID : 0;

		wp_enqueue_script(
			'exobooking-admin-estoque',
			plugins_url( '../assets/js/admin-estoque.js', __FILE__ ),
			array( 'jquery' ),
			EXOBOOKING_CORE_VERSION,
			true
		);
		wp_localize_script(
			'exobooking-admin-estoque',
			'exobookingEstoque',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
				'postId'  => $post_id,
				'labels'  => array(
					'saving'       => __( 'Salvando...', 'exobooking-core' ),
					'error'        => __( 'Erro ao salvar. Tente novamente.', 'exobooking-core' ),
					'fillAll'      => __( 'Informe a data e o número de vagas.', 'exobooking-core' ),
					'colData'      => __( 'Data', 'exobooking-core' ),
					'colVagasTotal'  => __( 'Vagas totais', 'exobooking-core' ),
					'colReservadas'  => __( 'Reservadas', 'exobooking-core' ),
					'colDisponiveis' => __( 'Disponíveis', 'exobooking-core' ),
				),
			)
		);
	}

	/**
	 * Handler AJAX: salva uma entrada de estoque para um passeio e data.
	 *
	 * @since  0.5.0
	 */
	public static function ajax_save_estoque() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), self::NONCE_ACTION ) ) {
			wp_send_json_error( array( 'message' => __( 'Nonce inválido.', 'exobooking-core' ) ) );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		if ( ! $post_id || get_post_type( $post_id ) !== 'passeio' ) {
			wp_send_json_error( array( 'message' => __( 'Passeio inválido.', 'exobooking-core' ) ) );
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Sem permissão.', 'exobooking-core' ) ) );
		}

		$data  = sanitize_text_field( wp_unslash( isset( $_POST['data'] ) ? $_POST['data'] : '' ) );
		$vagas = isset( $_POST['vagas'] ) ? max( 0, (int) $_POST['vagas'] ) : 0;

		$data_norm = ExoBooking_Core_Estoque_Vagas::normalize_date( $data );
		if ( ! $data_norm ) {
			wp_send_json_error( array( 'message' => __( 'Data inválida.', 'exobooking-core' ) ) );
		}

		$ok = ExoBooking_Core_Estoque_Vagas::set_vagas_totais( $post_id, $data_norm, $vagas );
		if ( ! $ok ) {
			wp_send_json_error( array( 'message' => __( 'Erro ao atualizar o estoque.', 'exobooking-core' ) ) );
		}

		$itens = ExoBooking_Core_Estoque_Vagas::get_estoque_por_passeio( $post_id );
		$rows  = '';
		foreach ( $itens as $item ) {
			$disponiveis = max( 0, (int) $item->vagas_total - (int) $item->vagas_reservadas );
			$rows .= '<tr>';
			$rows .= '<td>' . esc_html( $item->data ) . '</td>';
			$rows .= '<td>' . (int) $item->vagas_total . '</td>';
			$rows .= '<td>' . (int) $item->vagas_reservadas . '</td>';
			$rows .= '<td>' . (int) $disponiveis . '</td>';
			$rows .= '</tr>';
		}

		wp_send_json_success(
			array(
				'message' => __( 'Estoque atualizado.', 'exobooking-core' ),
				'rows'    => $rows,
			)
		);
	}

	/**
	 * Renderiza o conteúdo da metabox.
	 *
	 * Não contém uma tag <form> própria para evitar aninhamento com o formulário
	 * do WordPress. O botão usa AJAX via admin-estoque.js.
	 *
	 * @since  0.5.0
	 * @param  WP_Post $post Objeto do post (passeio).
	 */
	public static function render_metabox( $post ) {
		if ( $post->post_type !== 'passeio' ) {
			return;
		}

		$itens = ExoBooking_Core_Estoque_Vagas::get_estoque_por_passeio( $post->ID );
		?>
		<div class="exobooking-estoque-metabox">
			<p>
				<label for="exobooking-estoque-data">
					<strong><?php esc_html_e( 'Data', 'exobooking-core' ); ?></strong>
				</label>
				<input type="date" id="exobooking-estoque-data" value="" style="margin-left: 6px;" />
				&nbsp;&nbsp;
				<label for="exobooking-estoque-vagas">
					<strong><?php esc_html_e( 'Vagas totais', 'exobooking-core' ); ?></strong>
				</label>
				<input type="number" id="exobooking-estoque-vagas" min="0" value="0" style="width: 70px; margin-left: 6px;" />
				&nbsp;
				<button type="button" id="exobooking-estoque-add" class="button button-secondary">
					<?php esc_html_e( 'Adicionar / Atualizar', 'exobooking-core' ); ?>
				</button>
				<span id="exobooking-estoque-msg" style="margin-left: 10px; font-style: italic; vertical-align: middle;"></span>
			</p>

			<div id="exobooking-estoque-table-wrap">
				<?php if ( ! empty( $itens ) ) : ?>
					<table class="widefat striped" style="max-width: 100%;">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Data', 'exobooking-core' ); ?></th>
								<th><?php esc_html_e( 'Vagas totais', 'exobooking-core' ); ?></th>
								<th><?php esc_html_e( 'Reservadas', 'exobooking-core' ); ?></th>
								<th><?php esc_html_e( 'Disponíveis', 'exobooking-core' ); ?></th>
							</tr>
						</thead>
						<tbody id="exobooking-estoque-tbody">
							<?php foreach ( $itens as $item ) : ?>
								<?php $disponiveis = max( 0, (int) $item->vagas_total - (int) $item->vagas_reservadas ); ?>
								<tr>
									<td><?php echo esc_html( $item->data ); ?></td>
									<td><?php echo (int) $item->vagas_total; ?></td>
									<td><?php echo (int) $item->vagas_reservadas; ?></td>
									<td><?php echo (int) $disponiveis; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p style="color: #666;"><?php esc_html_e( 'Nenhuma data com estoque definido. Use os campos acima para adicionar.', 'exobooking-core' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
