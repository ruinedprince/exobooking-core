<?php
/**
 * Registro do Custom Post Type "Passeios" (EBC-3).
 *
 * Nome no singular conforme pedido no JIRA. Slug interno: passeio.
 *
 * @since      0.1.0
 * @package    ExoBooking_Core
 * @subpackage ExoBooking_Core/includes
 * @author     Gabriel Maciel <jsttmaciel89@gmail.com>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Classe que registra o CPT Passeios.
 */
class ExoBooking_Core_CPT_Passeios {

	/**
	 * Slug do post type (singular).
	 *
	 * @since  0.1.0
	 * @var string
	 */
	const POST_TYPE = 'passeio';

	/**
	 * Registra o Custom Post Type "Passeios" no hook init.
	 *
	 * @since 0.1.0
	 */
	public static function register() {
		$labels = array(
			'name'                  => _x( 'Passeios', 'post type general name', 'exobooking-core' ),
			'singular_name'         => _x( 'Passeio', 'post type singular name', 'exobooking-core' ),
			'menu_name'             => _x( 'Passeios', 'admin menu', 'exobooking-core' ),
			'name_admin_bar'        => _x( 'Passeio', 'add new on admin bar', 'exobooking-core' ),
			'add_new'               => _x( 'Adicionar novo', 'passeio', 'exobooking-core' ),
			'add_new_item'          => __( 'Adicionar passeio', 'exobooking-core' ),
			'new_item'              => __( 'Novo passeio', 'exobooking-core' ),
			'edit_item'             => __( 'Editar passeio', 'exobooking-core' ),
			'view_item'             => __( 'Ver passeio', 'exobooking-core' ),
			'all_items'             => __( 'Todos', 'exobooking-core' ),
			'search_items'          => __( 'Buscar', 'exobooking-core' ),
			'not_found'             => __( 'Nenhum passeio encontrado.', 'exobooking-core' ),
			'not_found_in_trash'    => __( 'Nenhum passeio na lixeira.', 'exobooking-core' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'passeio' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon'          => 'dashicons-palmtree',
			'supports'           => array( 'title', 'editor', 'thumbnail' ),
		);

		register_post_type( self::POST_TYPE, $args );
	}
}
