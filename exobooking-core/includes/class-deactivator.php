<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://github.com/ruinedprince/exobooking-core
 * @since      0.1.0
 *
 * @package    ExoBooking_Core
 * @subpackage ExoBooking_Core/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      0.1.0
 * @package    ExoBooking_Core
 * @subpackage ExoBooking_Core/includes
 * @author     Gabriel Maciel <jsttmaciel89@gmail.com>
 */
class ExoBooking_Core_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
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