<?php
/**
 * REST API controller.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder;

use RestaurantMenuBuilder\Frontend\Renderer;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All admin write operations run through these endpoints.
 *
 * Every route checks the capability, and WordPress verifies the X-WP-Nonce
 * header for cookie authenticated requests before the callback runs.
 */
class Rest_Api {

	public const NAMESPACE = 'rmb/v1';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Permission callback shared by every route.
	 *
	 * @return true|WP_Error
	 */
	public function can_manage() {
		if ( current_user_can_manage() ) {
			return true;
		}

		return new WP_Error(
			'rmb_forbidden',
			__( 'You do not have permission to manage restaurant menus.', 'restaurant-menu-builder' ),
			array( 'status' => rest_authorization_required_code() )
		);
	}

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		$auth = array( $this, 'can_manage' );

		register_rest_route(
			self::NAMESPACE,
			'/menus',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_menus' ),
					'permission_callback' => $auth,
					'args'                => array(
						'status'   => array( 'type' => 'string' ),
						'search'   => array( 'type' => 'string' ),
						'page'     => array( 'type' => 'integer' ),
						'per_page' => array( 'type' => 'integer' ),
					),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_menu' ),
					'permission_callback' => $auth,
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/menus/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_menu' ),
					'permission_callback' => $auth,
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_menu' ),
					'permission_callback' => $auth,
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_menu' ),
					'permission_callback' => $auth,
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/menus/(?P<id>\d+)/duplicate',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'duplicate_menu' ),
				'permission_callback' => $auth,
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/menus/(?P<id>\d+)/preview',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'preview_menu' ),
				'permission_callback' => $auth,
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/menus/(?P<id>\d+)/tree',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_tree' ),
				'permission_callback' => $auth,
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/categories',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_categories' ),
					'permission_callback' => $auth,
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_category' ),
					'permission_callback' => $auth,
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/categories/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_category' ),
					'permission_callback' => $auth,
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_category' ),
					'permission_callback' => $auth,
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/categories/(?P<id>\d+)/duplicate',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'duplicate_category' ),
				'permission_callback' => $auth,
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/categories/reorder',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'reorder_categories' ),
				'permission_callback' => $auth,
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/items',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'list_items' ),
					'permission_callback' => $auth,
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => $auth,
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/items/(?P<id>\d+)',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => $auth,
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => $auth,
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/items/(?P<id>\d+)/duplicate',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'duplicate_item' ),
				'permission_callback' => $auth,
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/items/reorder',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'reorder_items' ),
				'permission_callback' => $auth,
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/settings',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => $auth,
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_settings' ),
					'permission_callback' => $auth,
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/settings/reset',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'reset_settings' ),
				'permission_callback' => $auth,
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/cache/flush',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'flush_cache' ),
				'permission_callback' => $auth,
			)
		);
	}

	/* ------------------------------------------------------------------ Menus */

	/**
	 * List menus.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function list_menus( WP_REST_Request $request ): WP_REST_Response {
		$result = Menu::all(
			array(
				'status'   => (string) $request->get_param( 'status' ),
				'search'   => (string) $request->get_param( 'search' ),
				'page'     => (int) ( $request->get_param( 'page' ) ?? 1 ),
				'per_page' => (int) ( $request->get_param( 'per_page' ) ?? 50 ),
			)
		);

		$menu_ids        = wp_list_pluck( $result['items'], 'id' );
		$category_counts = Category::counts_for_menus( $menu_ids );
		$item_counts     = Item::counts_for_menus( $menu_ids );

		foreach ( $result['items'] as $index => $menu ) {
			$result['items'][ $index ]['counts'] = array(
				'categories' => $category_counts[ (int) $menu['id'] ] ?? 0,
				'items'      => $item_counts[ (int) $menu['id'] ] ?? 0,
			);
		}

		return $this->success( $result );
	}

	/**
	 * Get one menu.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_menu( WP_REST_Request $request ) {
		$menu = Menu::find( (int) $request['id'] );

		if ( null === $menu ) {
			return $this->not_found( __( 'That menu no longer exists.', 'restaurant-menu-builder' ) );
		}

		return $this->success( array( 'menu' => $menu ) );
	}

	/**
	 * Create a menu.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_menu( WP_REST_Request $request ) {
		$id = Menu::create( (array) $request->get_json_params() );

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		return $this->success(
			array(
				'menu'    => Menu::find( $id ),
				'message' => __( 'Menu created.', 'restaurant-menu-builder' ),
			),
			201
		);
	}

	/**
	 * Update a menu.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_menu( WP_REST_Request $request ) {
		$id     = (int) $request['id'];
		$result = Menu::update( $id, (array) $request->get_json_params() );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success(
			array(
				'menu'    => Menu::find( $id ),
				'message' => __( 'Menu saved.', 'restaurant-menu-builder' ),
			)
		);
	}

	/**
	 * Delete a menu.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_menu( WP_REST_Request $request ) {
		$result = Menu::delete( (int) $request['id'] );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success( array( 'message' => __( 'Menu deleted.', 'restaurant-menu-builder' ) ) );
	}

	/**
	 * Duplicate a menu.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function duplicate_menu( WP_REST_Request $request ) {
		$id = Menu::duplicate( (int) $request['id'] );

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		return $this->success(
			array(
				'menu'    => Menu::find( $id ),
				'message' => __( 'Menu duplicated.', 'restaurant-menu-builder' ),
			),
			201
		);
	}

	/**
	 * Return the full category and item tree for a menu in one request.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_tree( WP_REST_Request $request ) {
		$id   = (int) $request['id'];
		$menu = Menu::find( $id );

		if ( null === $menu ) {
			return $this->not_found( __( 'That menu no longer exists.', 'restaurant-menu-builder' ) );
		}

		$categories = Category::all( array( 'menu_id' => $id ) );
		$grouped    = Item::grouped_by_category( $id );

		foreach ( $categories as $index => $category ) {
			$categories[ $index ]['items'] = $grouped[ $category['id'] ] ?? array();
		}

		return $this->success(
			array(
				'menu'       => $menu,
				'categories' => $categories,
			)
		);
	}

	/**
	 * Render a preview of a menu, including draft categories and items.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function preview_menu( WP_REST_Request $request ) {
		$id   = (int) $request['id'];
		$menu = Menu::find( $id );

		if ( null === $menu ) {
			return $this->not_found( __( 'That menu no longer exists.', 'restaurant-menu-builder' ) );
		}

		$body = (array) $request->get_json_params();

		// Unsaved editor state can be previewed without writing to the database.
		if ( isset( $body['settings'] ) && is_array( $body['settings'] ) ) {
			$menu['settings'] = Menu::merge_settings( Menu::sanitize_settings( $body['settings'], $menu['settings'] ) );
		}

		Renderer::reset_instance();

		$html = Renderer::render( $id, array(), true, $menu );

		return $this->success(
			array(
				'html'          => $html,
				'stylesheet'    => RMB_URL . 'frontend/css/frontend.css?ver=' . rawurlencode( RMB_VERSION ),
				'script'        => RMB_URL . 'frontend/js/frontend.js?ver=' . rawurlencode( RMB_VERSION ),
				'global_css'    => Settings::sanitize_css( (string) Settings::get( 'custom_css', '' ) ),
			)
		);
	}

	/* ------------------------------------------------------------- Categories */

	/**
	 * List categories.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function list_categories( WP_REST_Request $request ): WP_REST_Response {
		return $this->success(
			array(
				'categories' => Category::all(
					array(
						'menu_id' => (int) ( $request->get_param( 'menu_id' ) ?? 0 ),
						'status'  => (string) $request->get_param( 'status' ),
					)
				),
			)
		);
	}

	/**
	 * Create a category.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_category( WP_REST_Request $request ) {
		$id = Category::create( (array) $request->get_json_params() );

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		return $this->success(
			array(
				'category' => Category::find( $id ),
				'message'  => __( 'Category added.', 'restaurant-menu-builder' ),
			),
			201
		);
	}

	/**
	 * Update a category.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_category( WP_REST_Request $request ) {
		$id     = (int) $request['id'];
		$result = Category::update( $id, (array) $request->get_json_params() );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success(
			array(
				'category' => Category::find( $id ),
				'message'  => __( 'Category saved.', 'restaurant-menu-builder' ),
			)
		);
	}

	/**
	 * Delete a category.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_category( WP_REST_Request $request ) {
		$result = Category::delete( (int) $request['id'] );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success( array( 'message' => __( 'Category deleted.', 'restaurant-menu-builder' ) ) );
	}

	/**
	 * Duplicate a category.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function duplicate_category( WP_REST_Request $request ) {
		$id = Category::duplicate( (int) $request['id'] );

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		return $this->success(
			array(
				'category' => Category::find( $id ),
				'message'  => __( 'Category duplicated.', 'restaurant-menu-builder' ),
			),
			201
		);
	}

	/**
	 * Reorder categories.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function reorder_categories( WP_REST_Request $request ) {
		$body    = (array) $request->get_json_params();
		$menu_id = absint( $body['menu_id'] ?? 0 );
		$order   = is_array( $body['order'] ?? null ) ? $body['order'] : array();

		$result = Category::reorder( $menu_id, $order );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success( array( 'message' => __( 'Order saved.', 'restaurant-menu-builder' ) ) );
	}

	/* ------------------------------------------------------------------ Items */

	/**
	 * List items.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function list_items( WP_REST_Request $request ): WP_REST_Response {
		return $this->success(
			array(
				'items' => Item::all(
					array(
						'menu_id'     => (int) ( $request->get_param( 'menu_id' ) ?? 0 ),
						'category_id' => (int) ( $request->get_param( 'category_id' ) ?? 0 ),
						'status'      => (string) $request->get_param( 'status' ),
						'search'      => (string) $request->get_param( 'search' ),
					)
				),
			)
		);
	}

	/**
	 * Create an item.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( WP_REST_Request $request ) {
		$id = Item::create( (array) $request->get_json_params() );

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		return $this->success(
			array(
				'item'    => Item::find( $id ),
				'message' => __( 'Item added.', 'restaurant-menu-builder' ),
			),
			201
		);
	}

	/**
	 * Update an item.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( WP_REST_Request $request ) {
		$id     = (int) $request['id'];
		$result = Item::update( $id, (array) $request->get_json_params() );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success(
			array(
				'item'    => Item::find( $id ),
				'message' => __( 'Item saved.', 'restaurant-menu-builder' ),
			)
		);
	}

	/**
	 * Delete an item.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( WP_REST_Request $request ) {
		$result = Item::delete( (int) $request['id'] );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success( array( 'message' => __( 'Item deleted.', 'restaurant-menu-builder' ) ) );
	}

	/**
	 * Duplicate an item.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function duplicate_item( WP_REST_Request $request ) {
		$id = Item::duplicate( (int) $request['id'] );

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		return $this->success(
			array(
				'item'    => Item::find( $id ),
				'message' => __( 'Item duplicated.', 'restaurant-menu-builder' ),
			),
			201
		);
	}

	/**
	 * Reorder items.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function reorder_items( WP_REST_Request $request ) {
		$body        = (array) $request->get_json_params();
		$category_id = absint( $body['category_id'] ?? 0 );
		$order       = is_array( $body['order'] ?? null ) ? $body['order'] : array();

		$result = Item::reorder( $category_id, $order );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $this->success( array( 'message' => __( 'Order saved.', 'restaurant-menu-builder' ) ) );
	}

	/* --------------------------------------------------------------- Settings */

	/**
	 * Read settings.
	 *
	 * @return WP_REST_Response
	 */
	public function get_settings(): WP_REST_Response {
		return $this->success(
			array(
				'general' => Settings::general(),
				'style'   => Settings::style(),
			)
		);
	}

	/**
	 * Save settings.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	public function save_settings( WP_REST_Request $request ): WP_REST_Response {
		$body = (array) $request->get_json_params();

		if ( isset( $body['general'] ) && is_array( $body['general'] ) ) {
			Settings::save_general( $body['general'] );
		}

		if ( isset( $body['style'] ) && is_array( $body['style'] ) ) {
			Settings::save_style( $body['style'] );
		}

		return $this->success(
			array(
				'general' => Settings::general(),
				'style'   => Settings::style(),
				'message' => __( 'Settings saved.', 'restaurant-menu-builder' ),
			)
		);
	}

	/**
	 * Restore default settings.
	 *
	 * @return WP_REST_Response
	 */
	public function reset_settings(): WP_REST_Response {
		Settings::reset();

		return $this->success(
			array(
				'general' => Settings::general(),
				'style'   => Settings::style(),
				'message' => __( 'Settings restored to defaults.', 'restaurant-menu-builder' ),
			)
		);
	}

	/**
	 * Clear the render cache.
	 *
	 * @return WP_REST_Response
	 */
	public function flush_cache(): WP_REST_Response {
		Cache::flush();

		return $this->success( array( 'message' => __( 'Menu cache cleared.', 'restaurant-menu-builder' ) ) );
	}

	/* ---------------------------------------------------------------- Helpers */

	/**
	 * Build a structured success response.
	 *
	 * @param array<string,mixed> $data   Payload.
	 * @param int                 $status HTTP status.
	 * @return WP_REST_Response
	 */
	private function success( array $data, int $status = 200 ): WP_REST_Response {
		return new WP_REST_Response( array_merge( array( 'success' => true ), $data ), $status );
	}

	/**
	 * Build a 404 error.
	 *
	 * @param string $message Message.
	 * @return WP_Error
	 */
	private function not_found( string $message ): WP_Error {
		return new WP_Error( 'rmb_not_found', $message, array( 'status' => 404 ) );
	}
}
