<?php
/**
 * Fired during plugin activation.
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
 * Fired during plugin activation.
 *
 * Verifica requisitos mínimos de versão (WordPress e PHP) e cria as tabelas
 * customizadas do plugin via dbDelta.
 *
 * @since      0.1.0
 * @package    ExoBooking_Core
 * @subpackage ExoBooking_Core/includes
 * @author     Gabriel Maciel <jsttmaciel89@gmail.com>
 */
class ExoBooking_Core_Activator {

	/**
	 * Rotina de ativação do plugin.
	 *
	 * Valida versões mínimas de WordPress e PHP, cria as tabelas de estoque
	 * de vagas e de reservas caso ainda não existam, e registra a versão
	 * instalada na tabela de opções do WordPress.
	 *
	 * @since    0.1.0
	 */
	public static function activate() {
		// Verificar requisitos mínimos
		global $wp_version;
		
		// Requisitos mínimos
		$min_wp_version = '5.0';
		$min_php_version = '7.4';
		
		// Verificar versão do WordPress
		if ( version_compare( $wp_version, $min_wp_version, '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die(
				sprintf(
					/* translators: 1: Versão mínima do WordPress requerida */
					__( 'Este plugin requer WordPress %s ou superior. Por favor, atualize o WordPress.', 'exobooking-core' ),
					$min_wp_version
				),
				__( 'Versão do WordPress incompatível', 'exobooking-core' ),
				array( 'back_link' => true )
			);
		}
		
		// Verificar versão do PHP
		if ( version_compare( PHP_VERSION, $min_php_version, '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die(
				sprintf(
					/* translators: 1: Versão mínima do PHP requerida */
					__( 'Este plugin requer PHP %s ou superior. Versão atual: %s', 'exobooking-core' ),
					$min_php_version,
					PHP_VERSION
				),
				__( 'Versão do PHP incompatível', 'exobooking-core' ),
				array( 'back_link' => true )
			);
		}
		
		// Criar tabela de estoque de vagas por passeio e data (EBC-4)
		require_once plugin_dir_path( __FILE__ ) . 'class-estoque-vagas-schema.php';
		ExoBooking_Core_Estoque_Vagas_Schema::create_table();

		// Criar tabela de reservas (EBC-5, EBC-6)
		require_once plugin_dir_path( __FILE__ ) . 'class-reservas-schema.php';
		ExoBooking_Core_Reservas_Schema::create_table();
		ExoBooking_Core_Reservas_Schema::maybe_upgrade();

		// Registrar opção de versão do plugin
		update_option( 'exobooking_core_version', EXOBOOKING_CORE_VERSION );
	}

}