<?php
/**
 * Acesso aos dados de reservas (EBC-5, EBC-6).
 *
 * Fornece métodos para criar e consultar reservas (inclui status desde EBC-6).
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

		$status = 'pendente';
		$result = $wpdb->insert(
			$table,
			array(
				'passeio_id'     => $passeio_id,
				'data'           => $data,
				'nome_cliente'   => $nome_cliente,
				'email_cliente'  => $email_cliente,
				'status'         => $status,
			),
			array( '%d', '%s', '%s', '%s', '%s' )
		);

		if ( $result === false || $wpdb->last_error !== '' ) {
			return null;
		}
		return (int) $wpdb->insert_id;
	}

	/**
	 * Retorna reservas para listagem no admin (com título do passeio). EBC-6: inclui status e filtro por status.
	 *
	 * @since  0.5.0
	 * @param  array $args 'per_page' (int), 'offset' (int), 'orderby' (string), 'order' (ASC|DESC), 'status' (string, opcional).
	 * @return array Lista de objetos com id, passeio_id, data, nome_cliente, email_cliente, status, criado_em, passeio_titulo.
	 */
	public static function get_todas( $args = array() ) {
		global $wpdb;
		$table    = ExoBooking_Core_Reservas_Schema::get_table_name();
		$per_page = isset( $args['per_page'] ) ? max( 1, (int) $args['per_page'] ) : 50;
		$offset   = isset( $args['offset'] ) ? max( 0, (int) $args['offset'] ) : 0;
		$orderby  = isset( $args['orderby'] ) && in_array( $args['orderby'], array( 'id', 'data', 'criado_em', 'nome_cliente', 'status' ), true ) ? $args['orderby'] : 'id';
		$order    = isset( $args['order'] ) && strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';
		$status   = isset( $args['status'] ) && in_array( $args['status'], ExoBooking_Core_Reservas_Schema::STATUS_VALIDOS, true ) ? $args['status'] : null;
		$posts    = $wpdb->prefix . 'posts';

		$where = '1=1';
		$prepare_args = array();
		if ( $status !== null ) {
			$where .= ' AND r.status = %s';
			$prepare_args[] = $status;
		}
		$prepare_args[] = $per_page;
		$prepare_args[] = $offset;

		$sql = "SELECT r.id, r.passeio_id, r.data, r.nome_cliente, r.email_cliente, r.status, r.criado_em, p.post_title AS passeio_titulo
			FROM $table r
			LEFT JOIN $posts p ON p.ID = r.passeio_id AND p.post_type = 'passeio'
			WHERE $where
			ORDER BY r.$orderby $order
			LIMIT %d OFFSET %d";
		$sql = $wpdb->prepare( $sql, $prepare_args );
		$rows = $wpdb->get_results( $sql );
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Retorna o total de reservas (para paginação). EBC-6: aceita filtro por status.
	 *
	 * @since  0.5.0
	 * @param  array $args 'status' (string, opcional).
	 * @return int
	 */
	public static function get_total( $args = array() ) {
		global $wpdb;
		$table  = ExoBooking_Core_Reservas_Schema::get_table_name();
		$status = isset( $args['status'] ) && in_array( $args['status'], ExoBooking_Core_Reservas_Schema::STATUS_VALIDOS, true ) ? $args['status'] : null;
		if ( $status !== null ) {
			return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE status = %s", $status ) );
		}
		// $table deriva de $wpdb->prefix (fonte confiável); interpolação direta é segura aqui.
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
	}

	/**
	 * Atualiza o status de uma reserva (EBC-6).
	 *
	 * @since  0.6.0
	 * @param  int    $reserva_id ID da reserva.
	 * @param  string $status     Um de: pendente, confirmada, cancelada.
	 * @return bool True se atualizou, false em caso de erro ou status inválido.
	 */
	public static function atualizar_status( $reserva_id, $status ) {
		if ( ! in_array( $status, ExoBooking_Core_Reservas_Schema::STATUS_VALIDOS, true ) ) {
			return false;
		}
		global $wpdb;
		$table = ExoBooking_Core_Reservas_Schema::get_table_name();
		$res = $wpdb->update(
			$table,
			array( 'status' => $status ),
			array( 'id' => absint( $reserva_id ) ),
			array( '%s' ),
			array( '%d' )
		);
		return $res !== false;
	}
}
