<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      0.1.0
 * @package    ExoBooking_Core
 * @subpackage ExoBooking_Core/includes
 * @author     Gabriel Maciel <jsttmaciel89@gmail.com>
 */
class ExoBooking_Core {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    0.1.0
	 */
	public function __construct() {
		if ( defined( 'EXOBOOKING_CORE_VERSION' ) ) {
			$this->version = EXOBOOKING_CORE_VERSION;
		} else {
			$this->version = '0.1.0';
		}
		$this->plugin_name = 'exobooking-core';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cpt-passeios.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-estoque-vagas-schema.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-estoque-vagas.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-reservas-schema.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-reservas.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rest-reservas-controller.php';
		if ( is_admin() ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-admin-estoque-metabox.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-admin-reservas.php';
		}
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function set_locale() {
		// Internacionalização será implementada quando necessário
		load_plugin_textdomain(
			'exobooking-core',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		if ( ! is_admin() ) {
			return;
		}
		ExoBooking_Core_Admin_Estoque_Metabox::init();
		ExoBooking_Core_Admin_Reservas::init();
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_public_hooks() {
		add_action( 'init', array( 'ExoBooking_Core_CPT_Passeios', 'register' ), 10 );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Registra as rotas da API REST (EBC-5).
	 *
	 * @since  0.4.0
	 */
	public function register_rest_routes() {
		$controller = new ExoBooking_Core_REST_Reservas_Controller();
		$controller->register_routes();
	}

	/**
	 * Run the plugin.
	 *
	 * Todos os hooks já foram registrados no construtor. Este método existe para
	 * manter compatibilidade com o ponto de entrada em exobooking-core.php e pode
	 * ser usado para inicializações adicionais no futuro.
	 *
	 * @since    0.1.0
	 */
	public function run() {
		// Hooks registrados no __construct(); nenhuma ação adicional necessária.
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.1.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.1.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}