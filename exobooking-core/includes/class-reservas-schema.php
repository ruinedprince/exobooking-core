<?php
/**
 * Schema da tabela de reservas (EBC-5).
 *
 * Define o nome da tabela e o SQL para criação via dbDelta.
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
 * Classe do schema da tabela exobooking_reservas.
 */
class ExoBooking_Core_Reservas_Schema {

	/**
	 * Nome da tabela (sem prefixo do WordPress).
	 *
	 * @since  0.4.0
	 * @var string
	 */
	const TABLE_NAME = 'exobooking_reservas';

	/**
	 * Retorna o nome completo da tabela com prefixo do site.
	 *
	 * @since  0.4.0
	 * @global wpdb $wpdb
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE_NAME;
	}

	/**
	 * Retorna o SQL para criação da tabela (compatível com dbDelta).
	 *
	 * @since  0.4.0
	 * @global wpdb $wpdb
	 * @return string
	 */
	public static function get_create_table_sql() {
		global $wpdb;
		$table_name = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			passeio_id bigint(20) unsigned NOT NULL,
			data date NOT NULL,
			nome_cliente varchar(255) NOT NULL DEFAULT '',
			email_cliente varchar(255) NOT NULL DEFAULT '',
			criado_em datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY passeio_data (passeio_id, data)
		) $charset_collate;";

		return $sql;
	}

	/**
	 * Cria a tabela no banco usando dbDelta.
	 *
	 * @since  0.4.0
	 */
	public static function create_table() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$sql = self::get_create_table_sql();
		dbDelta( $sql );
	}

	/**
	 * Retorna o SQL para remoção da tabela (DROP TABLE).
	 * Usado apenas se a desativação optar por remover a tabela.
	 *
	 * @since  0.4.0
	 * @return string
	 */
	public static function get_drop_table_sql() {
		global $wpdb;
		$table_name = self::get_table_name();
		return "DROP TABLE IF EXISTS $table_name";
	}
}
