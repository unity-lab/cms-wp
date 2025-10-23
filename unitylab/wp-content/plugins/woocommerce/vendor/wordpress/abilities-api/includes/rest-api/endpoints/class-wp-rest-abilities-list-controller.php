<?php
/**
 * REST API list controller for Abilities API.
 *
 * @package WordPress
 * @subpackage Abilities_API
 * @since 0.1.0
 */

declare( strict_types = 1 );

/**
 * Core controller used to access abilities via the REST API.
 *
 * @since 0.1.0
 *
 * @see WP_REST_Controller
 */
class WP_REST_Abilities_List_Controller extends WP_REST_Controller {

	/**
	 * Default number of items per page for pagination.
	 *
	 * @since 0.1.0
	 * @var int
	 */
	public const DEFAULT_PER_PAGE = 50;

	/**
	 * REST API namespace.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	protected $namespace = 'wp/v2';

	/**
	 * REST API base route.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	protected $rest_base = 'abilities';

	/**
	 * Registers the routes for abilities.
	 *
	 * @since 0.1.0
	 *
	 * @see register_rest_route()
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<name>[a-zA-Z0-9\-\/]+)',
			array(
				'args'   => array(
					'name' => array(
						'description' => __( 'Unique identifier for the ability.' ),
						'type'        => 'string',
						'pattern'     => '^[a-zA-Z0-9\-\/]+$',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Retrieves all abilities.
	 *
	 * @since 0.1.0
	 *
	 * @param \WP_REST_Request<array<string,mixed>> $request Full details about the request.
	 * @return \WP_REST_Response Response object on success.
	 */
	public function get_items( $request ) {
		// TODO: Add HEAD method support for performance optimization.
		// Should return early with empty body but include X-WP-Total and X-WP-TotalPages headers.
		// See: https://github.com/WordPress/wordpress-develop/blob/trunk/src/wp-includes/rest-api/endpoints/class-wp-rest-comments-controller.php#L316-L318

		$abilities = wp_get_abilities();

		// Handle pagination with explicit defaults.
		$params   = $request->get_params();
		$page     = $params['page'] ?? 1;
		$per_page = $params['per_page'] ?? self::DEFAULT_PER_PAGE;
		$offset   = ( $page - 1 ) * $per_page;

		$total_abilities = count( $abilities );
		$max_pages       = ceil( $total_abilities / $per_page );

		$abilities = array_slice( $abilities, $offset, $per_page );

		$data = array();
		foreach ( $abilities as $ability ) {
			$item   = $this->prepare_item_for_response( $ability, $request );
			$data[] = $this->prepare_response_for_collection( $item );
		}

		$response = rest_ensure_response( $data );

		$response->header( 'X-WP-Total', (string) $total_abilities );
		$response->header( 'X-WP-TotalPages', (string) $max_pages );

		$query_params = $request->get_query_params();
		$base         = add_query_arg( urlencode_deep( $query_params ), rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) );

		if ( $page > 1 ) {
			$prev_page = $page - 1;
			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->add_link( 'prev', $prev_link );
		}

		if ( $page < $max_pages ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );
			$response->add_link( 'next', $next_link );
		}

		return $response;
	}

	/**
	 * Retrieves a specific ability.
	 *
	 * @since 0.1.0
	 *
	 * @param \WP_REST_Request<array<string,mixed>> $request Full details about the request.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$ability = wp_get_ability( $request->get_param( 'name' ) );

		if ( ! $ability ) {
			return new \WP_Error(
				'rest_ability_not_found',
				__( 'Ability not found.' ),
				array( 'status' => 404 )
			);
		}

		$data = $this->prepare_item_for_response( $ability, $request );
		return rest_ensure_response( $data );
	}

	/**
	 * Checks if a given request has access to read abilities.
	 *
	 * @since 0.1.0
	 *
	 * @param \WP_REST_Request<array<string,mixed>> $request Full details about the request.
	 * @return bool True if the request has read access.
	 */
	public function get_permissions_check( $request ) {
		return current_user_can( 'read' );
	}

	/**
	 * Prepares an ability for response.
	 *
	 * @since 0.1.0
	 *
	 * @param \WP_Ability                           $ability The ability object.
	 * @param \WP_REST_Request<array<string,mixed>> $request Request object.
	 * @return \WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $ability, $request ) {
		$data = array(
			'name'          => $ability->get_name(),
			'label'         => $ability->get_label(),
			'description'   => $ability->get_description(),
			'input_schema'  => $ability->get_input_schema(),
			'output_schema' => $ability->get_output_schema(),
			'meta'          => $ability->get_meta(),
		);

		$context = $request->get_param( 'context' ) ?? 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );

		$fields = $this->get_fields_for_response( $request );
		if ( rest_is_field_included( '_links', $fields ) || rest_is_field_included( '_embedded', $fields ) ) {
			$links = array(
				'self'       => array(
					'href' => rest_url( sprintf( '%s/%s/%s', $this->namespace, $this->rest_base, $ability->get_name() ) ),
				),
				'collection' => array(
					'href' => rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ),
				),
			);

			$links['run'] = array(
				'href' => rest_url( sprintf( '%s/%s/%s/run', $this->namespace, $this->rest_base, $ability->get_name() ) ),
			);

			$response->add_links( $links );
		}

		return $response;
	}

	/**
	 * Retrieves the ability's schema, conforming to JSON Schema.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, mixed> Item schema data.
	 */
	public function get_item_schema(): array {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'ability',
			'type'       => 'object',
			'properties' => array(
				'name'          => array(
					'description' => __( 'Unique identifier for the ability.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'label'         => array(
					'description' => __( 'Display label for the ability.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'description'   => array(
					'description' => __( 'Description of the ability.' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'input_schema'  => array(
					'description' => __( 'JSON Schema for the ability input.' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'output_schema' => array(
					'description' => __( 'JSON Schema for the ability output.' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'meta'          => array(
					'description' => __( 'Meta information about the ability.' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			),
			'required'   => array( 'name', 'label', 'description' ),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Retrieves the query params for collections.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, mixed> Collection parameters.
	 */
	public function get_collection_params(): array {
		return array(
			'context'  => $this->get_context_param( array( 'default' => 'view' ) ),
			'page'     => array(
				'description'       => __( 'Current page of the collection.' ),
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
				'minimum'           => 1,
			),
			'per_page' => array(
				'description'       => __( 'Maximum number of items to be returned in result set.' ),
				'type'              => 'integer',
				'default'           => self::DEFAULT_PER_PAGE,
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);
	}
}
