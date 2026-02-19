<?php
/**
 * Controller REST para o endpoint de reservas (EBC-5).
 *
 * POST /wp-json/exobooking/v1/reservas — cria reserva com proteção anti-overbooking.
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
 * Controller REST para reservas.
 */
class ExoBooking_Core_REST_Reservas_Controller extends WP_REST_Controller {

	/**
	 * Namespace da API.
	 *
	 * @since  0.4.0
	 * @var string
	 */
	protected $namespace = 'exobooking/v1';

	/**
	 * Rest base (recurso).
	 *
	 * @since  0.4.0
	 * @var string
	 */
	protected $rest_base = 'reservas';

	/**
	 * Registra as rotas do recurso.
	 *
	 * O endpoint de criação de reserva é público (permission_callback retorna true
	 * intencionalmente) para permitir que visitantes não autenticados realizem
	 * reservas via formulário ou integração externa.
	 *
	 * @since  0.4.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					// Endpoint público: qualquer visitante pode criar uma reserva.
					'permission_callback' => '__return_true',
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
			)
		);
	}

	/**
	 * Cria uma reserva (POST). Valida dados, reserva vaga de forma atômica e insere registro.
	 *
	 * @since  0.4.0
	 * @param  WP_REST_Request $request Requisição com body JSON.
	 * @return WP_REST_Response|WP_Error Resposta 201 com dados da reserva ou erro 4xx/5xx.
	 */
	public function create_item( $request ) {
		$passeio_id = (int) $request->get_param( 'passeio_id' );
		$data       = $request->get_param( 'data' );
		$nome       = $request->get_param( 'nome' );
		$email      = $request->get_param( 'email' );

		// Validação: passeio existe e é do tipo passeio
		$post = $passeio_id ? get_post( $passeio_id ) : null;
		if ( ! $post || $post->post_type !== 'passeio' || $post->post_status === 'trash' ) {
			return new WP_Error(
				'exobooking_passeio_invalido',
				__( 'Passeio não encontrado ou inválido.', 'exobooking-core' ),
				array( 'status' => 400 )
			);
		}

		$data_normalized = ExoBooking_Core_Estoque_Vagas::normalize_date( $data );
		if ( ! $data_normalized ) {
			return new WP_Error(
				'exobooking_data_invalida',
				__( 'Data inválida. Use o formato YYYY-MM-DD.', 'exobooking-core' ),
				array( 'status' => 400 )
			);
		}

		if ( ! is_email( $email ) ) {
			return new WP_Error(
				'exobooking_email_invalido',
				__( 'E-mail inválido.', 'exobooking-core' ),
				array( 'status' => 400 )
			);
		}

		$nome = is_string( $nome ) ? trim( $nome ) : '';
		if ( $nome === '' ) {
			return new WP_Error(
				'exobooking_nome_obrigatorio',
				__( 'Nome do cliente é obrigatório.', 'exobooking-core' ),
				array( 'status' => 400 )
			);
		}

		// Anti-overbooking: reserva atômica de uma vaga
		$vaga_reservada = ExoBooking_Core_Estoque_Vagas::reservar_vaga( $passeio_id, $data_normalized );
		if ( ! $vaga_reservada ) {
			return new WP_Error(
				'exobooking_sem_vagas',
				__( 'Não há vagas disponíveis para este passeio nesta data.', 'exobooking-core' ),
				array( 'status' => 409 )
			);
		}

		$reserva_id = ExoBooking_Core_Reservas::criar( $passeio_id, $data_normalized, $nome, $email );
		if ( ! $reserva_id ) {
			return new WP_Error(
				'exobooking_erro_criar_reserva',
				__( 'Não foi possível registrar a reserva. Tente novamente.', 'exobooking-core' ),
				array( 'status' => 500 )
			);
		}

		$response = new WP_REST_Response(
			array(
				'id'         => $reserva_id,
				'passeio_id' => $passeio_id,
				'data'       => $data_normalized,
				'nome'       => $nome,
				'email'      => sanitize_email( $email ),
				'status'     => 'pendente',
			),
			201
		);
		return $response;
	}

	/**
	 * Retorna o schema dos argumentos para criação (POST).
	 *
	 * @since  0.4.0
	 * @param  string $method WP_REST_Server::CREATABLE.
	 * @return array Argumentos com validação e sanitização.
	 */
	public function get_endpoint_args_for_item_schema( $method = WP_REST_Server::CREATABLE ) {
		if ( $method !== WP_REST_Server::CREATABLE ) {
			return array();
		}
		return array(
			'passeio_id' => array(
				'description'       => __( 'ID do passeio (post type passeio).', 'exobooking-core' ),
				'type'              => 'integer',
				'required'          => true,
				'minimum'           => 1,
				'sanitize_callback' => 'absint',
			),
			'data'       => array(
				'description'       => __( 'Data da reserva (YYYY-MM-DD).', 'exobooking-core' ),
				'type'              => 'string',
				'required'          => true,
				'format'            => 'date',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'nome'       => array(
				'description'       => __( 'Nome do cliente.', 'exobooking-core' ),
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			),
			'email'      => array(
				'description'       => __( 'E-mail do cliente.', 'exobooking-core' ),
				'type'              => 'string',
				'required'          => true,
				'format'            => 'email',
				'sanitize_callback' => 'sanitize_email',
			),
		);
	}
}
