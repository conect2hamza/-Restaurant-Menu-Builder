<?php
/**
 * Admin controller.
 *
 * @package RestaurantMenuBuilder
 */

declare( strict_types=1 );

namespace RestaurantMenuBuilder\Admin;

use RestaurantMenuBuilder\Category;
use RestaurantMenuBuilder\Currency;
use RestaurantMenuBuilder\Icons;
use RestaurantMenuBuilder\Installer;
use RestaurantMenuBuilder\Item;
use RestaurantMenuBuilder\Menu;
use RestaurantMenuBuilder\Rest_Api;
use RestaurantMenuBuilder\Settings;

use function RestaurantMenuBuilder\current_user_can_manage;
use function RestaurantMenuBuilder\manage_capability;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the admin screens and their assets.
 */
class Admin {

	public const SLUG = 'rmb-menus';

	public const MENUS_SLUG = 'rmb-menus-list';

	/**
	 * Screen IDs registered by the plugin.
	 *
	 * @var string[]
	 */
	private array $screens = array();

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_notices', array( $this, 'schema_notice' ) );
		add_filter( 'plugin_action_links_' . RMB_BASENAME, array( $this, 'action_links' ) );
	}

	/**
	 * Register the admin menu tree.
	 *
	 * @return void
	 */
	public function register_menu(): void {
		$capability = manage_capability();

		$this->screens[] = add_menu_page(
			__( 'Restaurant Menu', 'restaurant-menu-builder' ),
			__( 'Restaurant Menu', 'restaurant-menu-builder' ),
			$capability,
			self::SLUG,
			array( $this, 'render_dashboard' ),
			'dashicons-food',
			56
		);

		$this->screens[] = add_submenu_page(
			self::SLUG,
			__( 'Dashboard', 'restaurant-menu-builder' ),
			__( 'Dashboard', 'restaurant-menu-builder' ),
			$capability,
			self::SLUG,
			array( $this, 'render_dashboard' )
		);

		$this->screens[] = add_submenu_page(
			self::SLUG,
			__( 'All Menus', 'restaurant-menu-builder' ),
			__( 'All Menus', 'restaurant-menu-builder' ),
			$capability,
			'rmb-menus-list',
			array( $this, 'render_menus' )
		);

		$this->screens[] = add_submenu_page(
			self::SLUG,
			__( 'Add New Menu', 'restaurant-menu-builder' ),
			__( 'Add New', 'restaurant-menu-builder' ),
			$capability,
			'rmb-new',
			array( $this, 'render_new' )
		);

		$this->screens[] = add_submenu_page(
			self::SLUG,
			__( 'Categories', 'restaurant-menu-builder' ),
			__( 'Categories', 'restaurant-menu-builder' ),
			$capability,
			'rmb-categories',
			array( $this, 'render_categories' )
		);

		$this->screens[] = add_submenu_page(
			self::SLUG,
			__( 'Items', 'restaurant-menu-builder' ),
			__( 'Items', 'restaurant-menu-builder' ),
			$capability,
			'rmb-items',
			array( $this, 'render_items' )
		);

		$this->screens[] = add_submenu_page(
			self::SLUG,
			__( 'Style', 'restaurant-menu-builder' ),
			__( 'Style', 'restaurant-menu-builder' ),
			$capability,
			'rmb-style',
			array( $this, 'render_style' )
		);

		$this->screens[] = add_submenu_page(
			self::SLUG,
			__( 'Settings', 'restaurant-menu-builder' ),
			__( 'Settings', 'restaurant-menu-builder' ),
			$capability,
			'rmb-settings',
			array( $this, 'render_settings' )
		);

		$this->screens[] = add_submenu_page(
			self::SLUG,
			__( 'Help', 'restaurant-menu-builder' ),
			__( 'Help', 'restaurant-menu-builder' ),
			$capability,
			'rmb-help',
			array( $this, 'render_help' )
		);
	}

	/**
	 * Load admin assets only on the plugin screens.
	 *
	 * @param string $hook Current screen hook.
	 * @return void
	 */
	public function enqueue( string $hook ): void {
		if ( ! in_array( $hook, $this->screens, true ) ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_style(
			'rmb-admin',
			RMB_URL . 'admin/css/admin.css',
			array(),
			RMB_VERSION
		);

		wp_enqueue_script(
			'rmb-admin',
			RMB_URL . 'admin/js/admin.js',
			array( 'wp-i18n' ),
			RMB_VERSION,
			true
		);

		wp_enqueue_script(
			'rmb-admin-screens',
			RMB_URL . 'admin/js/screens.js',
			array( 'rmb-admin' ),
			RMB_VERSION,
			true
		);

		wp_set_script_translations( 'rmb-admin', 'restaurant-menu-builder', RMB_PATH . 'languages' );
		wp_set_script_translations( 'rmb-admin-screens', 'restaurant-menu-builder', RMB_PATH . 'languages' );

		// wp_localize_script() casts top level scalars to strings; a JSON literal
		// keeps booleans and integers intact for the JavaScript side.
		wp_add_inline_script(
			'rmb-admin',
			'window.rmbData = ' . wp_json_encode( $this->script_data() ) . ';',
			'before'
		);
	}

	/**
	 * Data handed to the admin script.
	 *
	 * @return array<string,mixed>
	 */
	private function script_data(): array {
		$menus = Menu::all( array( 'per_page' => 200 ) );

		$currencies = array();

		foreach ( Currency::all() as $code => $definition ) {
			$currencies[] = array(
				'code'   => $code,
				'label'  => (string) $definition['label'],
				'symbol' => (string) $definition['symbol'],
			);
		}

		$image_sizes = get_intermediate_image_sizes();
		$image_sizes[] = 'full';

		return array(
			'restUrl'     => esc_url_raw( rest_url( Rest_Api::NAMESPACE ) ),
			'nonce'       => wp_create_nonce( 'wp_rest' ),
			'adminUrl'    => esc_url_raw( admin_url( 'admin.php' ) ),
			'pages'       => array(
				'dashboard'  => self::SLUG,
				'menus'      => self::MENUS_SLUG,
				'new'        => 'rmb-new',
				'categories' => 'rmb-categories',
				'items'      => 'rmb-items',
				'style'      => 'rmb-style',
				'settings'   => 'rmb-settings',
				'help'       => 'rmb-help',
			),
			'canManage'   => current_user_can_manage(),
			'menus'       => $menus['items'],
			'icons'       => Icons::for_admin(),
			'iconGroups'  => Icons::groups(),
			'ui'          => Icons::ui_for_admin(),
			'layouts'     => Settings::layouts(),
			'fonts'       => Settings::font_labels(),
			'currencies'  => $currencies,
			'imageSizes'  => array_values( array_unique( $image_sizes ) ),
			'settings'    => Settings::general(),
			'style'       => Settings::style(),
			'styleFields' => $this->style_fields(),
			'defaults'    => array(
				'style'   => Settings::default_style(),
				'general' => Settings::default_general(),
			),
			'maxVariations' => Item::MAX_VARIATIONS,
		);
	}

	/**
	 * Field definitions for the style editor.
	 *
	 * @return array<int,array<string,string>>
	 */
	private function style_fields(): array {
		return array(
			array(
				'key'   => 'color_primary',
				'label' => __( 'Primary', 'restaurant-menu-builder' ),
				'type'  => 'color',
				'help'  => __( 'Section titles and item names.', 'restaurant-menu-builder' ),
			),
			array(
				'key'   => 'color_accent',
				'label' => __( 'Accent', 'restaurant-menu-builder' ),
				'type'  => 'color',
				'help'  => __( 'Rules, focus outlines and highlights.', 'restaurant-menu-builder' ),
			),
			array(
				'key'   => 'color_text',
				'label' => __( 'Text', 'restaurant-menu-builder' ),
				'type'  => 'color',
				'help'  => '',
			),
			array(
				'key'   => 'color_text_secondary',
				'label' => __( 'Secondary text', 'restaurant-menu-builder' ),
				'type'  => 'color',
				'help'  => __( 'Descriptions and price labels.', 'restaurant-menu-builder' ),
			),
			array(
				'key'   => 'color_background',
				'label' => __( 'Background', 'restaurant-menu-builder' ),
				'type'  => 'color',
				'help'  => '',
			),
			array(
				'key'   => 'color_border',
				'label' => __( 'Border', 'restaurant-menu-builder' ),
				'type'  => 'color',
				'help'  => __( 'Category pills, cards and the dotted price leader.', 'restaurant-menu-builder' ),
			),
			array(
				'key'   => 'color_category_active',
				'label' => __( 'Active category', 'restaurant-menu-builder' ),
				'type'  => 'color',
				'help'  => __( 'Fill of the selected category pill.', 'restaurant-menu-builder' ),
			),
			array(
				'key'   => 'size_heading',
				'label' => __( 'Category heading size', 'restaurant-menu-builder' ),
				'type'  => 'size',
				'help'  => __( 'Use a CSS length, for example 30px or 2rem.', 'restaurant-menu-builder' ),
			),
			array(
				'key'   => 'size_body',
				'label' => __( 'Body size', 'restaurant-menu-builder' ),
				'type'  => 'size',
				'help'  => '',
			),
			array(
				'key'   => 'size_category',
				'label' => __( 'Category navigation size', 'restaurant-menu-builder' ),
				'type'  => 'size',
				'help'  => '',
			),
			array(
				'key'   => 'size_price',
				'label' => __( 'Price size', 'restaurant-menu-builder' ),
				'type'  => 'size',
				'help'  => '',
			),
		);
	}

	/* ---------------------------------------------------------------- Screens */

	/**
	 * Dashboard overview.
	 *
	 * @return void
	 */
	public function render_dashboard(): void {
		$this->guard();

		// The editor is reachable from the top level slug too, so a bookmarked
		// editor URL keeps working after the dashboard was added in front of it.
		$menu_id = isset( $_GET['menu'] ) ? absint( wp_unslash( $_GET['menu'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen routing.
		$view    = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen routing.

		if ( 'edit' === $view && $menu_id > 0 && null !== Menu::find( $menu_id ) ) {
			$this->view( 'menu-editor', array( 'menu_id' => $menu_id ) );

			return;
		}

		$this->view( 'dashboard' );
	}

	/**
	 * All menus, or the editor when a menu is selected.
	 *
	 * @return void
	 */
	public function render_menus(): void {
		$this->guard();

		$menu_id = isset( $_GET['menu'] ) ? absint( wp_unslash( $_GET['menu'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen routing.
		$view    = isset( $_GET['view'] ) ? sanitize_key( wp_unslash( $_GET['view'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen routing.

		if ( 'edit' === $view && $menu_id > 0 && null !== Menu::find( $menu_id ) ) {
			$this->view( 'menu-editor', array( 'menu_id' => $menu_id ) );

			return;
		}

		$this->view( 'menus' );
	}

	/**
	 * Add New screen.
	 *
	 * @return void
	 */
	public function render_new(): void {
		$this->guard();
		$this->view( 'menus', array( 'open_create' => true ) );
	}

	/**
	 * Admin URL of the menu editor for one menu.
	 *
	 * @param int $menu_id Menu ID.
	 * @return string
	 */
	public static function editor_url( int $menu_id ): string {
		return admin_url( 'admin.php?page=' . self::MENUS_SLUG . '&view=edit&menu=' . $menu_id );
	}

	/**
	 * Categories screen.
	 *
	 * @return void
	 */
	public function render_categories(): void {
		$this->guard();
		$this->view( 'categories', array( 'menu_id' => $this->requested_menu_id() ) );
	}

	/**
	 * Items screen.
	 *
	 * @return void
	 */
	public function render_items(): void {
		$this->guard();
		$this->view( 'items', array( 'menu_id' => $this->requested_menu_id() ) );
	}

	/**
	 * Style screen.
	 *
	 * @return void
	 */
	public function render_style(): void {
		$this->guard();
		$this->view( 'style', array( 'menu_id' => $this->requested_menu_id() ) );
	}

	/**
	 * Settings screen.
	 *
	 * @return void
	 */
	public function render_settings(): void {
		$this->guard();
		$this->view( 'settings' );
	}

	/**
	 * Help screen.
	 *
	 * @return void
	 */
	public function render_help(): void {
		$this->guard();
		$this->view( 'help' );
	}

	/* ---------------------------------------------------------------- Helpers */

	/**
	 * Stop rendering when the user lacks the capability.
	 *
	 * @return void
	 */
	private function guard(): void {
		if ( ! current_user_can_manage() ) {
			wp_die( esc_html__( 'You do not have permission to manage restaurant menus.', 'restaurant-menu-builder' ), 403 );
		}
	}

	/**
	 * Menu ID from the query string, falling back to the first available menu.
	 *
	 * @return int
	 */
	private function requested_menu_id(): int {
		$menu_id = isset( $_GET['menu'] ) ? absint( wp_unslash( $_GET['menu'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen routing.

		if ( $menu_id > 0 && null !== Menu::find( $menu_id ) ) {
			return $menu_id;
		}

		$default = absint( Settings::get( 'default_menu', 0 ) );

		if ( $default > 0 && null !== Menu::find( $default ) ) {
			return $default;
		}

		$menus = Menu::all( array( 'per_page' => 1 ) );

		return isset( $menus['items'][0]['id'] ) ? (int) $menus['items'][0]['id'] : 0;
	}

	/**
	 * Include a view file.
	 *
	 * @param string              $name View name.
	 * @param array<string,mixed> $data Variables exposed to the view as $rmb.
	 * @return void
	 */
	private function view( string $name, array $data = array() ): void {
		$file = RMB_PATH . 'admin/views/' . sanitize_file_name( $name ) . '.php';

		if ( ! is_readable( $file ) ) {
			return;
		}

		$rmb = $data;

		include $file;
	}

	/**
	 * Warn when the tables are missing.
	 *
	 * @return void
	 */
	public function schema_notice(): void {
		if ( ! current_user_can_manage() || Installer::tables_exist() ) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html__( 'Restaurant Menu Builder could not find its database tables. Deactivate and reactivate the plugin to create them.', 'restaurant-menu-builder' )
		);
	}

	/**
	 * Add a settings link on the plugins screen.
	 *
	 * @param string[] $links Existing links.
	 * @return string[]
	 */
	public function action_links( array $links ): array {
		$url = admin_url( 'admin.php?page=' . self::MENUS_SLUG );

		array_unshift(
			$links,
			sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html__( 'Menus', 'restaurant-menu-builder' ) )
		);

		return $links;
	}

	/**
	 * Shared page header markup.
	 *
	 * @param string $title    Page title.
	 * @param string $subtitle Supporting line.
	 * @return void
	 */
	public static function header( string $title, string $subtitle = '', string $icon = '' ): void {
		echo '<div class="rmb-page-header">';
		echo '<div class="rmb-page-heading">';

		if ( '' !== $icon ) {
			// Built from the fixed interface icon registry, never from user input.
			echo '<span class="rmb-page-icon">' . Icons::render_ui( $icon, 'rmb-ui-icon', 22 ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo '<h1 class="rmb-page-title">' . esc_html( $title ) . '</h1>';
		echo '</div>';

		if ( '' !== $subtitle ) {
			echo '<p class="rmb-page-subtitle">' . esc_html( $subtitle ) . '</p>';
		}

		echo '</div>';
	}

	/**
	 * Render a menu picker used by the standalone screens.
	 *
	 * @param int    $selected Selected menu ID.
	 * @param string $page     Admin page slug to link to.
	 * @return void
	 */
	public static function menu_picker( int $selected, string $page ): void {
		$menus = Menu::all( array( 'per_page' => 200 ) );

		if ( empty( $menus['items'] ) ) {
			return;
		}

		echo '<form class="rmb-menu-picker" method="get" action="' . esc_url( admin_url( 'admin.php' ) ) . '">';
		echo '<input type="hidden" name="page" value="' . esc_attr( $page ) . '" />';
		echo '<label class="rmb-field-label" for="rmb-menu-picker">' . esc_html__( 'Menu', 'restaurant-menu-builder' ) . '</label>';
		echo '<select id="rmb-menu-picker" name="menu" class="rmb-select">';

		foreach ( $menus['items'] as $menu ) {
			printf(
				'<option value="%1$d" %2$s>%3$s</option>',
				(int) $menu['id'],
				selected( $selected, (int) $menu['id'], false ),
				esc_html( $menu['name'] )
			);
		}

		echo '</select>';
		echo '<button type="submit" class="rmb-button rmb-button-secondary">' . esc_html__( 'Switch menu', 'restaurant-menu-builder' ) . '</button>';
		echo '</form>';
	}

	/**
	 * Render an empty state that links to menu creation.
	 *
	 * @return void
	 */
	public static function no_menus_state(): void {
		echo '<div class="rmb-empty">';
		echo '<h2>' . esc_html__( 'No menus yet', 'restaurant-menu-builder' ) . '</h2>';
		echo '<p>' . esc_html__( 'Create your first menu, then add categories and dishes to it.', 'restaurant-menu-builder' ) . '</p>';
		printf(
			'<a class="rmb-button rmb-button-primary" href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=rmb-new' ) ),
			esc_html__( 'Create a menu', 'restaurant-menu-builder' )
		);
		echo '</div>';
	}
}
