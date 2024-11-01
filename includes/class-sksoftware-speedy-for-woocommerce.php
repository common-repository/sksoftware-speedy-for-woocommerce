<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://sk-soft.net
 * @since      1.0.0
 *
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/includes
 * @author     SK Software <office@sk-soft.net>
 */
class Sksoftware_Speedy_For_Woocommerce {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Sksoftware_Speedy_For_Woocommerce_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The order utilities class.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Sksoftware_Speedy_For_Woocommerce_Order_Utilities $order_utilities The order utilities class.
	 */
	protected $order_utilities;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_VERSION' ) ) {
			$this->version = SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_VERSION;
		} else {
			$this->version = '1.1.0';
		}

		$this->plugin_name = 'sksoftware-speedy-for-woocommerce';

		$this->load_dependencies();
		$this->declare_hpos_support();
		$this->set_locale();
		$this->define_woocommerce_shipping_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Sksoftware_Speedy_For_Woocommerce_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Sksoftware_Speedy_For_Woocommerce_Loader. Orchestrates the hooks of the plugin.
	 * - Sksoftware_Speedy_For_Woocommerce_I18n. Defines internationalization functionality.
	 * - Sksoftware_Speedy_For_Woocommerce_Admin. Defines all hooks for the admin area.
	 * - Sksoftware_Speedy_For_Woocommerce_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-sksoftware-speedy-for-woocommerce-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-sksoftware-speedy-for-woocommerce-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-sksoftware-speedy-for-woocommerce-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'public/class-sksoftware-speedy-for-woocommerce-public.php';

		/**
		 * The class responsible for defining all actions that occur when initializing
		 * shipping unit for woocommerce.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-sksoftware-speedy-for-woocommerce-shipping.php';

		/**
		 * The class responsible for all api calls actions that occur when using the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-sksoftware-speedy-for-woocommerce-api-client.php';

		/**
		 * The class responsible for common utilities that occur when using the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-sksoftware-speedy-for-woocommerce-order-utilities.php';

		/**
		 * The class responsible for declaring hpos support.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-sksoftware-speedy-for-woocommerce-declare-hpos-support.php';

		$this->loader          = new Sksoftware_Speedy_For_Woocommerce_Loader();
		$this->order_utilities = new Sksoftware_Speedy_For_Woocommerce_Order_Utilities();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Sksoftware_Speedy_For_Woocommerce_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Sksoftware_Speedy_For_Woocommerce_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Sksoftware_Speedy_For_Woocommerce_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'plugin_action_links', $plugin_admin, 'add_plugin_settings_link', 10, 2 );

		$this->loader->add_action( 'admin_footer', $plugin_admin, 'start_free_trial_modal' );

		$this->loader->add_action( 'admin_notices', $plugin_admin, 'woocommerce_check' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'environment_check' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'start_free_trial_success' );

		$this->loader->add_action( 'updated_option', $plugin_admin, 'woocommerce_weight_unit_updated', 10, 3 );
		$this->loader->add_action( 'updated_option', $plugin_admin, 'woocommerce_dimension_unit_updated', 10, 3 );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->define_create_speedy_shipment_order_action_hooks();
		$this->define_delete_speedy_shipment_order_action_hooks();
		$this->define_recalculate_shipping_order_action_hooks();
		$this->define_print_speedy_shipment_label_order_action_hooks();
		$this->define_speedy_order_meta_box_hooks();

		$this->loader->add_action( 'woocommerce_loaded', $this, 'define_register_bulk_action' );

		$this->define_start_free_trial_ajax();
	}

	/**
	 * Register all of the hooks related to the create shipment order
	 * functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function define_register_bulk_action() {
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-sksoftware-speedy-for-woocommerce-create-bulk-shipment-action.php';

		$plugin_create_bulk_shipment_action = new Sksoftware_Speedy_For_Woocommerce_Create_Bulk_Shipment_Action(
			$this->order_utilities
		);

		if ( class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ) {
			$hook = 'woocommerce_page_wc-orders';
		} else {
			$hook = 'edit-shop_order';
		}

		add_filter( 'bulk_actions-' . $hook, array( $plugin_create_bulk_shipment_action, 'add_action' ) );
		add_action(
			'handle_bulk_actions-' . $hook,
			array( $plugin_create_bulk_shipment_action, 'handle_action' ),
			10,
			3
		);
		add_action( 'admin_notices', array( $plugin_create_bulk_shipment_action, 'handle_completion' ) );
	}

	/**
	 * Register all of the hooks related to the create shipment order
	 * functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_create_speedy_shipment_order_action_hooks() {
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-sksoftware-speedy-for-woocommerce-create-shipment-order-action.php';

		$plugin_create_speedy_shipment_order_action = new Sksoftware_Speedy_For_Woocommerce_Create_Shipment_Order_Action(
			$this->order_utilities
		);

		$this->loader->add_filter(
			'woocommerce_order_actions',
			$plugin_create_speedy_shipment_order_action,
			'add_action'
		);
		$this->loader->add_action(
			'woocommerce_order_action_sksoftware_speedy_for_woocommerce_shipment_create_order_action',
			$plugin_create_speedy_shipment_order_action,
			'handle_action'
		);
	}

	/**
	 * Register all of the hooks related to the delete shipment order
	 * functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_delete_speedy_shipment_order_action_hooks() {
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-sksoftware-speedy-for-woocommerce-delete-shipment-order-action.php';

		$plugin_delete_speedy_shipment_order_action = new Sksoftware_Speedy_For_Woocommerce_Delete_Shipment_Order_Action(
			$this->order_utilities
		);

		$this->loader->add_filter(
			'woocommerce_order_actions',
			$plugin_delete_speedy_shipment_order_action,
			'add_action'
		);
		$this->loader->add_action(
			'woocommerce_order_action_sksoftware_speedy_for_woocommerce_shipment_delete_order_action',
			$plugin_delete_speedy_shipment_order_action,
			'handle_action'
		);
	}

	/**
	 * Register all of the hooks related to the recalculation of shipping order
	 * functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_recalculate_shipping_order_action_hooks() {
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-sksoftware-speedy-for-woocommerce-recalculate-shipping-order-action.php';

		$plugin_recalculate_shipping_order_action = new Sksoftware_Speedy_For_Woocommerce_Recalculate_Shipping_Order_Action(
			$this->order_utilities
		);

		$this->loader->add_filter(
			'woocommerce_order_actions',
			$plugin_recalculate_shipping_order_action,
			'add_action'
		);
		$this->loader->add_action(
			'woocommerce_order_action_sksoftware_speedy_for_woocommerce_recalculate_shipping_order_action',
			$plugin_recalculate_shipping_order_action,
			'handle_action'
		);
	}

	/**
	 * Register all of the hooks related to the print shipment label
	 * functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_print_speedy_shipment_label_order_action_hooks() {
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-sksoftware-speedy-for-woocommerce-print-shipment-label-order-action.php';

		$plugin_print_speedy_shipment_label_order_action = new Sksoftware_Speedy_For_Woocommerce_Print_Shipment_Label_Order_Action(
			$this->order_utilities
		);

		$this->loader->add_action(
			'wp_ajax_sksoftware_speedy_for_woocommerce_print_shipment_label',
			$plugin_print_speedy_shipment_label_order_action,
			'handle_action'
		);
	}

	/**
	 * Register all of the hooks related to the speedy meta box
	 * functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_speedy_order_meta_box_hooks() {
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-sksoftware-speedy-for-woocommerce-order-meta-box.php';

		$plugin_order_meta_box = new Sksoftware_Speedy_For_Woocommerce_Order_Meta_Box(
			$this->order_utilities
		);

		$this->loader->add_action( 'add_meta_boxes', $plugin_order_meta_box, 'add_meta_box' );
		$this->loader->add_action(
			'woocommerce_process_shop_order_meta',
			$plugin_order_meta_box,
			'handle_meta_box_save'
		);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Sksoftware_Speedy_For_Woocommerce_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action(
			'woocommerce_after_shipping_rate',
			$plugin_public,
			'sksoftware_woocommerce_speedy_office_select2_field',
			10,
			2
		);

		$this->loader->add_action(
			'woocommerce_after_shipping_rate',
			$plugin_public,
			'sksoftware_woocommerce_speedy_apt_select2_field',
			10,
			2
		);

		$this->loader->add_filter( 'woocommerce_checkout_fields', $plugin_public, 'add_office_checkout_fields' );
		$this->loader->add_filter( 'woocommerce_checkout_fields', $plugin_public, 'add_apt_checkout_fields' );

		$this->loader->add_action(
			'woocommerce_checkout_update_order_review',
			$plugin_public,
			'woocommerce_checkout_update_order_review'
		);

		$this->loader->add_action(
			'woocommerce_after_checkout_validation',
			$plugin_public,
			'office_field_validation',
			10,
			2
		);
		$this->loader->add_action(
			'woocommerce_after_checkout_validation',
			$plugin_public,
			'apt_field_validation',
			10,
			2
		);

		$this->loader->add_filter(
			'woocommerce_default_address_fields',
			$plugin_public,
			'woocommerce_default_address_fields'
		);

		$this->loader->add_filter(
			'woocommerce_checkout_get_value',
			$plugin_public,
			'woocommerce_checkout_get_office_value',
			10,
			2
		);
		$this->loader->add_filter(
			'woocommerce_checkout_get_value',
			$plugin_public,
			'woocommerce_checkout_get_apt_value',
			10,
			2
		);

		$this->loader->add_filter(
			'woocommerce_checkout_update_order_review',
			$plugin_public,
			'woocommerce_checkout_update_order_review_2'
		);

		$this->loader->add_filter(
			'woocommerce_cart_shipping_packages',
			$plugin_public,
			'add_office_to_shipping_packages'
		);
	}

	/** Register woocommerce shipping init action */
	private function define_woocommerce_shipping_hooks() {
		$plugin_shipping = new Sksoftware_Speedy_For_Woocommerce_Shipping();

		$this->loader->add_action( 'woocommerce_shipping_init', $plugin_shipping, 'init' );
		$this->loader->add_filter( 'woocommerce_shipping_methods', $plugin_shipping, 'add_shipping_method' );
	}

	/**
	 * Register start free trial ajax action
	 *
	 * @return void
	 */
	private function define_start_free_trial_ajax() {
		$this->loader->add_action(
			'wp_ajax_sksoftware_speedy_for_woocommerce_start_free_trial_action',
			$this,
			'start_free_trial_action'
		);
		$this->loader->add_action(
			'wp_ajax_sksoftware_speedy_for_woocommerce_start_free_trial_save_api_key_action',
			$this,
			'start_free_trial_save_api_key_action'
		);
	}

	/**
	 * Start free trial action callback
	 *
	 * @return void
	 */
	public function start_free_trial_action() {
		check_ajax_referer( 'sksoftware_speedy_for_woocommerce_start_free_trial_action' );

		$api_client     = Sksoftware_Speedy_For_Woocommerce_Api_Client::create( 0 );
		$admin_email    = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : null;
		$accepted_terms = isset( $_POST['accepted_terms'] ) ? 'true' === sanitize_text_field( $_POST['accepted_terms'] ) : null;

		$response = $api_client->start_free_trial( $admin_email, $accepted_terms );

		if ( 200 === $response['status'] ) {
			$this->update_setting( 'api_key', $response['api_key'] );

			wp_send_json_success();
		}

		wp_send_json_error( $response['violations'] );
	}

	/**
	 * This function is used to update single setting in the plugin's settings array.
	 *
	 * @param string $key The key.
	 * @param string $value The value.
	 *
	 * @return bool|mixed
	 */
	private function update_setting( $key, $value ) {
		$option_key = 'woocommerce_sksoftware_speedy_for_woocommerce_settings';

		$options = get_option( $option_key );

		if ( ! array_key_exists( $key, $options ) ) {
			return false;
		}

		$options[ $key ] = $value;

		return update_option( $option_key, $options );
	}

	/**
	 * This function is used to get single setting from the plugin's settings array.
	 *
	 * @param string $key The key.
	 *
	 * @return bool|mixed
	 */
	private function get_setting( $key ) {
		$option_key = 'woocommerce_sksoftware_speedy_for_woocommerce_settings';

		$options = get_option( $option_key );

		if ( ! array_key_exists( $key, $options ) ) {
			return false;
		}

		return $options[ $key ];
	}

	/**
	 * Declare HPOS support.
	 *
	 * @return void
	 */
	private function declare_hpos_support() {
		$plugin_declare_hpos_support = new Sksoftware_Speedy_For_Woocommerce_Declare_Hpos_Support();

		$this->loader->add_action( 'before_woocommerce_init', $plugin_declare_hpos_support, 'declare_hpos_support' );
	}
}
