<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://sk-soft.net
 * @since      1.0.0
 *
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/admin
 * @author     SK Software <office@sk-soft.net>
 */
class Sksoftware_Speedy_For_Woocommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/sksoftware-speedy-for-woocommerce-admin.min.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/sksoftware-speedy-for-woocommerce-admin.min.js',
			array( 'jquery' ),
			$this->version,
			false
		);

		wp_localize_script(
			$this->plugin_name,
			'sksoftware_speedy_for_woocommerce_admin',
			array(
				'api_host' => defined( 'SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_API_HOST' ) ? SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_API_HOST : '',
			)
		);

		// Use WC backbone modals in the plugin.
		wp_enqueue_script(
			'backbone-modal',
			get_site_url() . '/wp-content/plugins/woocommerce/assets/js/admin/backbone-modal.js',
			array( 'jquery', 'wp-util', 'backbone' ),
			$this->version,
			false
		);
	}

	/**
	 * Checks if woocommerce is installed and active.
	 *
	 * @since    1.0.0
	 */
	public function woocommerce_check() {
		if ( in_array(
			'woocommerce/woocommerce.php',
			apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
			true
		) ) {
			return;
		}

		echo '<div class="error">
				<p>' . esc_html__(
            'SKSoftware Speedy for WooCommerce requires WooCommerce to be installed and active.',
            'sksoftware-speedy-for-woocommerce'
        ) . '</p>
			</div>';
	}

	/**
	 * Checks if woocommerce currency is supported.
	 *
	 * @since    1.0.0
	 */
	public function environment_check() {
		$messages = array();

		if ( false === function_exists( 'get_woocommerce_currency' ) ) {
			return;
		}

		// Currency check.
		if ( ! in_array(
			get_woocommerce_currency(),
			array(
				'BGN',
				'EUR',
				'USD',
				'GBP',
				'CHF',
				'JPY',
				'RUB',
			),
			true
		) ) {
			$messages[] = __(
				'WooCommerce currency is set to one of BGN, EUR, USD, GBP, CHF, JPY, RUB',
				'sksoftware-speedy-for-woocommerce'
			);
		}

		if ( ! empty( $messages ) ) {
			/* translators: %s: Error message */
			$prefix    = __(
				'SKSoftware Speedy for WooCommerce requires that %s',
				'sksoftware-speedy-for-woocommerce'
			);
			$separator = ' ' . __( 'and', 'sksoftware-speedy-for-woocommerce' ) . ' ';

			echo '<div class="error">
				<p>' . esc_html( sprintf( $prefix, implode( $separator, $messages ) ) ) . '</p>
			</div>';
		}
	}

	/**
	 * Adds links to plugins page.
	 *
	 * @param array  $plugin_actions Plugin action links.
	 * @param string $plugin_file Path to the plugin file relative to the plugins' directory.
	 *
	 * @since   1.0.0
	 */
	public function add_plugin_settings_link( $plugin_actions, $plugin_file ) {
		if ( false === strpos( $plugin_file, $this->plugin_name ) ) {
			return $plugin_actions;
		}

		$new_actions = array();

		$new_actions['settings'] = sprintf(
		/* translators: %s: settings link */
			__( '<a href="%s">Settings</a>', 'sksoftware-speedy-for-woocommerce' ),
			esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping&section=sksoftware_speedy_for_woocommerce' ) )
		);

		$new_actions['shipping_methods'] = sprintf(
		/* translators: %s: shipping methods link */
			__(
				'<a href="%s">Shipping methods</a>',
				'sksoftware-speedy-for-woocommerce'
			),
			esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping' ) )
		);

		return array_merge( $new_actions, $plugin_actions );
	}

	/**
	 * This function triggers when the weight unit is updated
	 * and updates the weight unit in the shipping methods.
	 *
	 * @param string $option_name
	 * @param string $old_value
	 * @param string $value
	 *
	 * @return void
	 */
	public function woocommerce_weight_unit_updated( $option_name, $old_value, $value ) {
		if ( 'woocommerce_weight_unit' !== $option_name ) {
			return;
		}

		// If value has not changed, don't do anything.
		if ( $old_value === $value ) {
			return;
		}

		$data_store = WC_Data_Store::load( 'shipping-zone' );
		$raw_zones  = $data_store->get_zones();
		foreach ( $raw_zones as $raw_zone ) {
			$zones[] = new WC_Shipping_Zone( $raw_zone );
		}

		// add zone 0 manually to get shipping methods for locations not yet covered by zones.
		$zones[] = new WC_Shipping_Zone( 0 );

		foreach ( $zones as $zone ) {
			$zone_shipping_methods = $zone->get_shipping_methods();

			foreach ( $zone_shipping_methods as $method ) {
				if ( ! $method instanceof Sksoftware_Speedy_For_Woocommerce_Shipping_Method ) {
					continue;
				}

				$method->wc_weight_unit_updated( $value, $old_value );
			}
		}
	}

	/**
	 * This function triggers when the dimension unit is updated
	 * and updates the dimension unit in the shipping methods.
	 *
	 * @param string $option_name
	 * @param string $old_value
	 * @param string $value
	 *
	 * @return void
	 */
	public function woocommerce_dimension_unit_updated( $option_name, $old_value, $value ) {
		if ( 'woocommerce_dimension_unit' !== $option_name ) {
			return;
		}

		// If value has not changed, don't do anything.
		if ( $old_value === $value ) {
			return;
		}

		$data_store = WC_Data_Store::load( 'shipping-zone' );
		$raw_zones  = $data_store->get_zones();
		foreach ( $raw_zones as $raw_zone ) {
			$zones[] = new WC_Shipping_Zone( $raw_zone );
		}

		// add zone 0 manually to get shipping methods for locations not yet covered by zones.
		$zones[] = new WC_Shipping_Zone( 0 );

		foreach ( $zones as $zone ) {
			$zone_shipping_methods = $zone->get_shipping_methods();

			foreach ( $zone_shipping_methods as $method ) {
				if ( ! $method instanceof Sksoftware_Speedy_For_Woocommerce_Shipping_Method ) {
					continue;
				}

				$method->wc_dimension_unit_updated( $value, $old_value );
			}
		}
	}

	/**
	 * Remind user to clear cache after activating the plugin.
	 *
	 * @since    1.0.0
	 */
	public function start_free_trial_success() {
		$is_success = filter_input( INPUT_GET, 'sksoftware_speedy_for_woocommerce_start_free_trial_success', FILTER_SANITIZE_STRING );

		if ( null === $is_success ) {
			return;
		}

		$is_success = 'true' === $is_success;

		$message = __(
			'Your free trial has started successfully. You can now use the plugin for free for 14 days.',
			'sksoftware-speedy-for-woocommerce'
		);

		if ( ! $is_success ) {
			$message = __(
				'Your free trial has failed to start. Please try again later.',
				'sksoftware-speedy-for-woocommerce'
			);
		}

		$notice_type = $is_success ? 'success' : 'error';

		echo '<div class="notice notice-' . esc_attr( $notice_type ) . ' is-dismissible">
			  	<p>' . esc_html( $message ) . '</p>
			  </div>';
	}

	/**
	 * Add start free trial button to the plugin page.
	 *
	 * @return void
	 */
	public function start_free_trial_modal() {
		?>
        <script type="text/template" id="tmpl-sksoftware-speedy-for-woocommerce-start-free-trial">
            <div class="wc-backbone-modal">
                <div class="wc-backbone-modal-content">
                    <section class="wc-backbone-modal-main" role="main">
                        <header class="wc-backbone-modal-header">
                            <h1><?php esc_html_e( 'Start free trial', 'sksoftware-speedy-for-woocommerce' ); ?></h1>
                            <button class="modal-close modal-close-link dashicons dashicons-no-alt">
                                <span class="screen-reader-text">Close modal panel</span>
                            </button>
                        </header>
                        <article>
                            <div
                                style="display: flex; flex-direction: column; align-items: center; padding-left: 30px; padding-right: 30px;">
                                <img
                                    src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) . 'img/sk-soft-logo-white-background.svg' ); ?>"
                                    width="160"
                                    height="90"
                                    alt="sksoftware logo"
                                />
                                <h3>
									<?php
									esc_html_e(
										'You are one step away from getting your API key.',
										'sksoftware-speedy-for-woocommerce'
									);
									?>
                                </h3>
                                <p style="text-align: center;">
									<?php
									esc_html_e(
										'Please enter your email address and we will fill your API key automatically.',
										'sksoftware-speedy-for-woocommerce'
									);
									?>
                                </p>
                                <div
                                    id="sksoftware-speedy-for-woocommerce-errors"
                                    class="sksoftware-start-free-trial-errors"
                                ></div>
                                <p class="sksoftware-start-free-trial-form">
                                    <label for="admin_email" class="sksoftware-start-free-trial-label">
										<?php esc_html_e( 'Your email address', 'sksoftware-speedy-for-woocommerce' ); ?>
                                    </label>
                                    <br>
                                    <input
                                        type="text"
                                        id="admin_email"
                                        class="sksoftware-start-free-trial-input"
                                        value="<?php echo esc_attr( get_bloginfo( 'admin_email' ) ); ?>"
                                    >
                                    <br>
                                    <input name="accepted_terms" type="checkbox" id="accepted_terms">
                                    <label for="accepted_terms">
										<?php
										echo wp_kses(
											sprintf(
											/* translators: 1: Privacy policy link 2: Terms & Agreements link 3: License policy link */
												__(
													'I accept the <a target="_blank" href="%1$s">privacy policy</a>, <a target="_blank" href="%2$s">terms & agreements</a> and <a target="_blank" href="%3$s">license policy</a>',
													'sksoftware-speedy-for-woocommerce'
												),
												esc_url( 'https://sk-soft.net/privacy-policy/' ),
												esc_url( 'https://sk-soft.net/terms-and-conditions/' ),
												esc_url( 'https://sk-soft.net/license-agreement' )
											),
											array(
												'a' => array(
													'href' => array(),
													'target' => array(),
												),
											)
										);
										?>
                                    </label>
                                </p>
                            </div>
                        </article>
                        <footer>
                            <div class="inner">
                                <button
                                    id="sksoftware-speedy-for-woocommerce-get-license"
                                    class="button button-primary button-large"
                                    data-nonce="<?php echo esc_attr( wp_create_nonce( 'sksoftware_speedy_for_woocommerce_start_free_trial_action' ) ); ?>"
                                >
									<?php esc_html_e( 'Get license', 'sksoftware-speedy-for-woocommerce' ); ?>
                                </button>
                            </div>
                        </footer>
                    </section>
                </div>
            </div>
            <div class="wc-backbone-modal-backdrop modal-close"></div>
        </script>
		<?php
	}
}
