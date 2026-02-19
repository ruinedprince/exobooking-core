<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/ruinedprince/exobooking-core
 * @since             0.1.0
 * @package           ExoBooking_Core
 *
 * @wordpress-plugin
 * Plugin Name:       ExoBooking Core
 * Plugin URI:        https://github.com/ruinedprince/exobooking-core
 * Description:       Plugin WordPress instalável que implementa um motor de reservas com proteção contra overbooking (concorrência de vagas).
 * Version:           0.3.0
 * Author:            Gabriel Maciel
 * Author URI:        https://github.com/ruinedprince
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       exobooking-core
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 0.1.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'EXOBOOKING_CORE_VERSION', '0.3.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-exobooking-core-activator.php
 */
function activate_exobooking_core() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';
	ExoBooking_Core_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-exobooking-core-deactivator.php
 */
function deactivate_exobooking_core() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-deactivator.php';
	ExoBooking_Core_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_exobooking_core' );
register_deactivation_hook( __FILE__, 'deactivate_exobooking_core' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-exobooking-core.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_exobooking_core() {

	$plugin = new ExoBooking_Core();
	$plugin->run();

}
run_exobooking_core();