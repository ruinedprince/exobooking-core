<?php
/**
 * Página admin "Reservas" para listar reservas realizadas.
 *
 * Submenu sob Passeios.
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
 * Admin: listagem de reservas.
 */
class ExoBooking_Core_Admin_Reservas {

	/**
	 * Slug da página no menu.
	 *
	 * @since  0.5.0
	 */
	const PAGE_SLUG = 'exobooking-reservas';
	const PER_PAGE = 20;

	/**
	 * Registra o submenu e a página.
	 *
	 * @since  0.5.0
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ) );
	}

	/**
	 * Adiciona o submenu "Reservas" em Passeios.
	 *
	 * @since  0.5.0
	 */
	public static function add_menu_page() {
		add_submenu_page(
			'edit.php?post_type=passeio',
			__( 'Reservas', 'exobooking-core' ),
			__( 'Reservas', 'exobooking-core' ),
			'edit_posts',
			self::PAGE_SLUG,
			array( __CLASS__, 'render_page' )
		);
	}

	/**
	 * Renderiza a página de listagem de reservas.
	 *
	 * @since  0.5.0
	 */
	public static function render_page() {
		$paged = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
		$offset = ( $paged - 1 ) * self::PER_PAGE;
		$total  = ExoBooking_Core_Reservas::get_total();
		$itens  = ExoBooking_Core_Reservas::get_todas( array(
			'per_page' => self::PER_PAGE,
			'offset'   => $offset,
			'orderby'  => 'criado_em',
			'order'    => 'DESC',
		) );
		$total_pages = $total > 0 ? (int) ceil( $total / self::PER_PAGE ) : 1;
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Reservas', 'exobooking-core' ); ?></h1>
			<p><?php esc_html_e( 'Listagem das reservas realizadas via API ou sistema.', 'exobooking-core' ); ?></p>

			<?php if ( empty( $itens ) ) : ?>
				<p><?php esc_html_e( 'Nenhuma reserva encontrada.', 'exobooking-core' ); ?></p>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th scope="col" class="column-id" style="width: 60px;"><?php esc_html_e( 'ID', 'exobooking-core' ); ?></th>
							<th scope="col" class="column-cliente"><?php esc_html_e( 'Cliente', 'exobooking-core' ); ?></th>
							<th scope="col" class="column-email"><?php esc_html_e( 'E-mail', 'exobooking-core' ); ?></th>
							<th scope="col" class="column-passeio"><?php esc_html_e( 'Passeio', 'exobooking-core' ); ?></th>
							<th scope="col" class="column-data"><?php esc_html_e( 'Data', 'exobooking-core' ); ?></th>
							<th scope="col" class="column-criado"><?php esc_html_e( 'Criado em', 'exobooking-core' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $itens as $r ) : ?>
							<tr>
								<td><?php echo (int) $r->id; ?></td>
								<td><?php echo esc_html( $r->nome_cliente ); ?></td>
								<td><a href="mailto:<?php echo esc_attr( $r->email_cliente ); ?>"><?php echo esc_html( $r->email_cliente ); ?></a></td>
								<td>
									<?php
									if ( ! empty( $r->passeio_titulo ) ) {
										$edit_url = get_edit_post_link( $r->passeio_id, 'raw' );
										if ( $edit_url ) {
											echo '<a href="' . esc_url( $edit_url ) . '">' . esc_html( $r->passeio_titulo ) . '</a>';
										} else {
											echo esc_html( $r->passeio_titulo );
										}
									} else {
										echo '—';
									}
									?>
								</td>
								<td><?php echo esc_html( $r->data ); ?></td>
								<td><?php echo esc_html( $r->criado_em ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<?php if ( $total_pages > 1 ) : ?>
					<div class="tablenav bottom">
						<div class="tablenav-pages">
							<span class="pagination-links">
								<?php
								$base = add_query_arg( 'paged', '%#%' );
								echo wp_kses_post(
									paginate_links( array(
										'base'      => $base,
										'format'    => '',
										'prev_text' => '&laquo;',
										'next_text' => '&raquo;',
										'total'    => $total_pages,
										'current'  => $paged,
									) )
								);
								?>
							</span>
							<span class="displaying-num"><?php echo esc_html( sprintf( _n( '%s item', '%s itens', $total, 'exobooking-core' ), number_format_i18n( $total ) ) ); ?></span>
						</div>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
	}
}
