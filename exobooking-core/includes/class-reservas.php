<?php
/**
 * Acesso aos dados de reservas (EBC-5).
 *
 * Fornece métodos para criar e consultar reservas.
 *
 * @since      0.4.0
 * @package    ExoBooking_Core
 * @subpackage ExoBooking_Core/includes
 * @author     Gabriel Maciel <jsttmaciel89@gmail.com>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Classe de acesso às reservas.
 */
class ExoBooking_Core_Reservas {

	/**
	 * Cria uma nova reserva na tabela exobooking_reservas.
	 *
	 * @since  0.4.0
	 * @param  int    $passeio_id   ID do post do CPT passeio.
	 * @param  string $data         Data no formato Y-m-d (será normalizada).
	 * @param  string $nome_cliente Nome do cliente.
	 * @param  string $email_cliente E-mail do cliente.
	 * @return int|null ID da reserva criada ou null em falha.
	 */
	public static function criar( $passeio_id, $data, $nome_cliente, $email_cliente ) {
		global $wpdb;
		$table = ExoBooking_Core_Reservas_Schema::get_table_name();
		$passeio_id = absint( $passeio_id );
		$data = ExoBooking_Core_Estoque_Vagas::normalize_date( $data );
		if ( ! $data ) {
			return null;
		}
		$nome_cliente = sanitize_text_field( $nome_cliente );
		$email_cliente = sanitize_email( $email_cliente );

		$result = $wpdb->insert(
			$table,
			array(
				'passeio_id'     => $passeio_id,
				'data'           => $data,
				'nome_cliente'   => $nome_cliente,
				'email_cliente'  => $email_cliente,
			),
			array( '%d', '%s', '%s', '%s' )
		);

		if ( $result === false || $wpdb->last_error !== '' ) {
			return null;
		}
		return (int) $wpdb->insert_id;
	}

	/**
	 * Retorna reservas para listagem no admin (com título do passeio).
	 *
	 * @since  0.5.0
	 * @param  array $args 'per_page' (int), 'offset' (int), 'orderby' (string), 'order' (ASC|DESC).
	 * @return array Lista de objetos com id, passeio_id, data, nome_cliente, email_cliente, criado_em, passeio_titulo.
	 */
	public static function get_todas( $args = array() ) {
		global $wpdb;
		$table   = ExoBooking_Core_Reservas_Schema::get_table_name();
		$per_page = isset( $args['per_page'] ) ? max( 1, (int) $args['per_page'] ) : 50;
		$offset   = isset( $args['offset'] ) ? max( 0, (int) $args['offset'] ) : 0;
		$orderby  = isset( $args['orderby'] ) && in_array( $args['orderby'], array( 'id', 'data', 'criado_em', 'nome_cliente' ), true ) ? $args['orderby'] : 'id';
		$order    = isset( $args['order'] ) && strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';
		$posts    = $wpdb->prefix . 'posts';

		$sql = $wpdb->prepare(
			"SELECT r.id, r.passeio_id, r.data, r.nome_cliente, r.email_cliente, r.criado_em, p.post_title AS passeio_titulo
			FROM $table r
			LEFT JOIN $posts p ON p.ID = r.passeio_id AND p.post_type = 'passeio'
			ORDER BY r.$orderby $order
			LIMIT %d OFFSET %d",
			$per_page,
			$offset
		);
		$rows = $wpdb->get_results( $sql );
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Retorna o total de reservas (para paginação).
	 *
	 * @since  0.5.0
	 * @return int
	 */
	public static function get_total() {
		global $wpdb;
		$table = ExoBooking_Core_Reservas_Schema::get_table_name();
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
	}
}
