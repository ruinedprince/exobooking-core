<?php
/**
 * Fired during plugin deactivation.
 *
 * @link       https://github.com/ruinedprince/exobooking-core
 * @since      0.1.0
 *
 * @package    ExoBooking_Core
 * @subpackage ExoBooking_Core/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Fired during plugin deactivation.
 *
 * Executa as rotinas de limpeza necessárias ao desativar o plugin (ex.: cancelar
 * agendamentos de cron). As tabelas customizadas são preservadas intencionalmente
 * para não perder dados caso o plugin seja reativado.
 *
 * @since      0.1.0
 * @package    ExoBooking_Core
 * @subpackage ExoBooking_Core/includes
 * @author     Gabriel Maciel <jsttmaciel89@gmail.com>
 */
class ExoBooking_Core_Deactivator {

	/**
	 * Rotina de desativação do plugin.
	 *
	 * Cancela agendamentos de cron (se houver) e realiza outras limpezas necessárias.
	 * As tabelas do banco de dados são mantidas por decisão de projeto (EBC-4) para
	 * preservar dados ao reativar o plugin.
	 *
	 * @since    0.1.0
	 */
	public static function deactivate() {
		// Limpar agendamentos (cron jobs) se houver
		// Exemplo: wp_clear_scheduled_hook( 'exobooking_core_daily_task' );

		// Decisão EBC-4: não remover a tabela exobooking_estoque_vagas na desativação.
		// Padrão WordPress: dados permanecem para caso o plugin seja reativado.
		// Para remover a tabela, usar ExoBooking_Core_Estoque_Vagas_Schema::get_drop_table_sql() e $wpdb->query().
	}

}