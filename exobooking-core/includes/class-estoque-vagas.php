<?php
/**
 * Acesso aos dados de estoque de vagas por passeio e data (EBC-4).
 *
 * Fornece métodos para obter/inserir/atualizar estoque e vagas disponíveis.
 *
 * @since      0.2.0
 * @package    ExoBooking_Core
 * @subpackage ExoBooking_Core/includes
 * @author     Gabriel Maciel <jsttmaciel89@gmail.com>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Classe de acesso ao estoque de vagas.
 */
class ExoBooking_Core_Estoque_Vagas {

	/**
	 * Retorna a linha de estoque para um passeio e data, ou null se não existir.
	 *
	 * @since  0.2.0
	 * @param  int    $passeio_id ID do post do CPT passeio.
	 * @param  string $data       Data no formato Y-m-d.
	 * @return object|null Objeto com id, passeio_id, data, vagas_total, vagas_reservadas ou null.
	 */
	public static function get_estoque( $passeio_id, $data ) {
		global $wpdb;
		$table = ExoBooking_Core_Estoque_Vagas_Schema::get_table_name();
		$passeio_id = absint( $passeio_id );
		$data = self::normalize_date( $data );
		if ( ! $data ) {
			return null;
		}
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, passeio_id, data, vagas_total, vagas_reservadas FROM $table WHERE passeio_id = %d AND data = %s LIMIT 1",
				$passeio_id,
				$data
			)
		);
		return $row ? $row : null;
	}

	/**
	 * Retorna todas as linhas de estoque para um passeio (para uso no admin).
	 *
	 * @since  0.5.0
	 * @param  int $passeio_id ID do post do CPT passeio.
	 * @return array Lista de objetos com id, passeio_id, data, vagas_total, vagas_reservadas.
	 */
	public static function get_estoque_por_passeio( $passeio_id ) {
		global $wpdb;
		$table = ExoBooking_Core_Estoque_Vagas_Schema::get_table_name();
		$passeio_id = absint( $passeio_id );
		if ( ! $passeio_id ) {
			return array();
		}
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, passeio_id, data, vagas_total, vagas_reservadas FROM $table WHERE passeio_id = %d ORDER BY data ASC",
				$passeio_id
			)
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Retorna o número de vagas disponíveis (vagas_total - vagas_reservadas) para um passeio e data.
	 *
	 * @since  0.2.0
	 * @param  int    $passeio_id ID do post do CPT passeio.
	 * @param  string $data       Data no formato Y-m-d.
	 * @return int Vagas disponíveis, ou 0 se não houver registro.
	 */
	public static function get_vagas_disponiveis( $passeio_id, $data ) {
		$estoque = self::get_estoque( $passeio_id, $data );
		if ( ! $estoque ) {
			return 0;
		}
		$disponiveis = (int) $estoque->vagas_total - (int) $estoque->vagas_reservadas;
		return max( 0, $disponiveis );
	}

	/**
	 * Define o total de vagas para um passeio e data. Cria o registro se não existir.
	 *
	 * @since  0.2.0
	 * @param  int    $passeio_id   ID do post do CPT passeio.
	 * @param  string $data         Data no formato Y-m-d.
	 * @param  int    $vagas_total  Número total de vagas (>= 0).
	 * @return bool True em sucesso.
	 */
	public static function set_vagas_totais( $passeio_id, $data, $vagas_total ) {
		global $wpdb;
		$table = ExoBooking_Core_Estoque_Vagas_Schema::get_table_name();
		$passeio_id = absint( $passeio_id );
		$data = self::normalize_date( $data );
		$vagas_total = max( 0, (int) $vagas_total );
		if ( ! $data ) {
			return false;
		}

		$existe = self::get_estoque( $passeio_id, $data );
		if ( $existe ) {
			$wpdb->update(
				$table,
				array( 'vagas_total' => $vagas_total ),
				array( 'passeio_id' => $passeio_id, 'data' => $data ),
				array( '%d' ),
				array( '%d', '%s' )
			);
			return $wpdb->last_error === '';
		}

		$wpdb->insert(
			$table,
			array(
				'passeio_id'        => $passeio_id,
				'data'              => $data,
				'vagas_total'       => $vagas_total,
				'vagas_reservadas'  => 0,
			),
			array( '%d', '%s', '%d', '%d' )
		);
		return $wpdb->last_error === '';
	}

	/**
	 * Incrementa o número de vagas reservadas para um passeio e data.
	 *
	 * @since  0.2.0
	 * @param  int    $passeio_id ID do post do CPT passeio.
	 * @param  string $data       Data no formato Y-m-d.
	 * @param  int    $qtd        Quantidade a acrescentar (deve ser > 0).
	 * @return bool True se o incremento foi aplicado e não excede vagas_total.
	 */
	public static function incrementar_reservadas( $passeio_id, $data, $qtd = 1 ) {
		global $wpdb;
		$table = ExoBooking_Core_Estoque_Vagas_Schema::get_table_name();
		$passeio_id = absint( $passeio_id );
		$data = self::normalize_date( $data );
		$qtd = max( 0, (int) $qtd );
		if ( ! $data || $qtd === 0 ) {
			return false;
		}

		$estoque = self::get_estoque( $passeio_id, $data );
		if ( ! $estoque ) {
			return false;
		}
		$novo_reservadas = (int) $estoque->vagas_reservadas + $qtd;
		if ( $novo_reservadas > (int) $estoque->vagas_total ) {
			return false;
		}

		$wpdb->update(
			$table,
			array( 'vagas_reservadas' => $novo_reservadas ),
			array( 'passeio_id' => $passeio_id, 'data' => $data ),
			array( '%d' ),
			array( '%d', '%s' )
		);
		return $wpdb->last_error === '';
	}

	/**
	 * Reserva uma vaga de forma atômica (anti-overbooking). Executa um único UPDATE condicional
	 * para evitar race conditions em requisições simultâneas (EBC-5).
	 *
	 * @since  0.4.0
	 * @param  int    $passeio_id ID do post do CPT passeio.
	 * @param  string $data       Data no formato Y-m-d.
	 * @return bool True se uma vaga foi reservada; false se não há vaga disponível ou estoque inexistente.
	 */
	public static function reservar_vaga( $passeio_id, $data ) {
		global $wpdb;
		$table = ExoBooking_Core_Estoque_Vagas_Schema::get_table_name();
		$passeio_id = absint( $passeio_id );
		$data = self::normalize_date( $data );
		if ( ! $data ) {
			return false;
		}

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $table SET vagas_reservadas = vagas_reservadas + 1 WHERE passeio_id = %d AND data = %s AND (vagas_total - vagas_reservadas) >= 1",
				$passeio_id,
				$data
			)
		);
		return $wpdb->last_error === '' && $wpdb->rows_affected > 0;
	}

	/**
	 * Normaliza uma data para o formato Y-m-d.
	 *
	 * @since  0.2.0
	 * @param  string|int $data Data (string Y-m-d ou timestamp).
	 * @return string|null Data em Y-m-d ou null se inválida.
	 */
	public static function normalize_date( $data ) {
		if ( is_numeric( $data ) ) {
			$ts = (int) $data;
			return gmdate( 'Y-m-d', $ts ) ?: null;
		}
		$data = trim( (string) $data );
		if ( $data === '' ) {
			return null;
		}
		$ts = strtotime( $data );
		if ( $ts === false ) {
			return null;
		}
		return gmdate( 'Y-m-d', $ts );
	}
}
