<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://sk-soft.net
 * @since      1.0.0
 *
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/public
 * @author     SK Software <office@sk-soft.net>
 */
class Sksoftware_Speedy_For_Woocommerce_Public {

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
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if ( ! is_checkout() ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/sksoftware-speedy-for-woocommerce-public.min.js',
			array(
				'jquery',
			),
			$this->version,
			false
		);

		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/sksoftware-speedy-for-woocommerce-public.min.css',
			array(),
			$this->version
		);

		wp_localize_script(
			$this->plugin_name,
			'sksoftware_speedy_for_woocommerce_public',
			array(
				'api_key' => defined( 'SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_API_HOST' ) ? SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_API_HOST : '',
			)
		);

		wp_localize_script(
			$this->plugin_name,
			'offices_object',
			array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) )
		);

		wp_localize_script(
			$this->plugin_name,
			'sites_object',
			array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) )
		);
	}

	/**
	 * This action is triggered on checkout update.
	 * It triggers recalculation of the shipping price when the shipping method changes.
	 *
	 * @param WC_Shipping_Rate $method
	 * @param int              $index
	 *
	 * @return void
	 */
	public function sksoftware_woocommerce_speedy_office_select2_field( $method, $index ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( ! is_checkout() ) {
			return;
		}

		$chosen_methods  = WC()->session->get( 'chosen_shipping_methods' );
		$chosen_shipping = $chosen_methods[0];

		// If the method is not selected do not show the office field.
		if ( $method->id !== $chosen_shipping ) {
			return;
		}

		$method_id   = $method->get_method_id();
		$instance_id = $method->get_instance_id();

		if ( 'sksoftware_speedy_for_woocommerce' !== $method_id ) {
			return;
		}

		$settings = ( new Sksoftware_Speedy_For_Woocommerce_Shipping_Method( $instance_id ) )->get_settings();

		if ( 'office' !== $settings['delivery_type'] ) {
			return;
		}

		?>

        <div class="sksoftware-speedy-for-woocommerce-select2">
            <select
                id="sksoftware_speedy_office"
                class="select"
                data-placeholder="<?php esc_attr_e( 'Select an office', 'sksoftware-speedy-for-woocommerce' ); ?>"
                data-label="<?php esc_attr_e( 'Office', 'sksoftware-speedy-for-woocommerce' ); ?>"
            >
                <option value="">
					<?php esc_html_e( 'Select an office', 'sksoftware-speedy-for-woocommerce' ); ?>
                </option>

				<?php if ( ! empty( WC()->session->get( 'billing_sksoftware_speedy_office' ) ) ) { ?>
                    <option
                        value="<?php echo esc_attr( WC()->session->get( 'billing_sksoftware_speedy_office' ) ); ?>"
                        selected
                    >
						<?php echo esc_html( WC()->session->get( 'billing_sksoftware_speedy_office_name' ) ); ?>
                    </option>
				<?php } ?>
            </select>
        </div>

		<?php
	}

	/**
	 * This action is triggered on checkout update.
	 * It triggers recalculation of the shipping price when the shipping method changes.
	 *
	 * @param WC_Shipping_Rate $method
	 * @param int              $index
	 *
	 * @return void
	 */
	public function sksoftware_woocommerce_speedy_apt_select2_field( $method, $index ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		if ( ! is_checkout() ) {
			return;
		}

		$chosen_methods  = WC()->session->get( 'chosen_shipping_methods' );
		$chosen_shipping = $chosen_methods[0];

		// If the method is not selected do not show the APT field.
		if ( $method->id !== $chosen_shipping ) {
			return;
		}

		$method_id   = $method->get_method_id();
		$instance_id = $method->get_instance_id();

		if ( 'sksoftware_speedy_for_woocommerce' !== $method_id ) {
			return;
		}

		$settings = ( new Sksoftware_Speedy_For_Woocommerce_Shipping_Method( $instance_id ) )->get_settings();

		if ( 'apt' !== $settings['delivery_type'] ) {
			return;
		}

		?>

        <div class="sksoftware-speedy-for-woocommerce-select2">
            <select
                id="sksoftware_speedy_apt"
                class="select"
                data-placeholder="<?php esc_attr_e( 'Select APT', 'sksoftware-speedy-for-woocommerce' ); ?>"
                data-label="<?php esc_attr_e( 'APT', 'sksoftware-speedy-for-woocommerce' ); ?>"
            >
                <option
                    value="">
					<?php
					esc_html_e(
						'Select APT',
						'sksoftware-speedy-for-woocommerce'
					);
					?>
                </option>
				<?php
				if ( ! empty( WC()->session->get( 'billing_sksoftware_speedy_apt' ) ) ) {
					?>
                    <option
                        value="<?php echo esc_attr( WC()->session->get( 'billing_sksoftware_speedy_apt' ) ); ?>"
                        selected
                    >
						<?php echo esc_html( WC()->session->get( 'billing_sksoftware_speedy_apt_name' ) ); ?>
                    </option>
					<?php
				}
				?>
            </select>
        </div>
		<?php
	}

	/**
	 * This action is triggered on checkout update. It triggers recalculation of the shipping price when the payment method changes.
	 *
	 * @param string $query
	 */
	public function woocommerce_checkout_update_order_review( $query ) {
		$packages = WC()->cart->get_shipping_packages();

		foreach ( $packages as $package_key => $package ) {
			$session_key = 'shipping_for_package_' . $package_key;

			WC()->session->__unset( $session_key );
		}

		parse_str( $query, $result );

		if ( isset( $result['payment_method'] ) ) {
			WC()->session->set( 'chosen_payment_method', $result['payment_method'] );
		}

		WC()->cart->calculate_shipping();
	}

	/**
	 * Validate the office field.
	 *
	 * @param array    $fields
	 * @param WP_Error $errors
	 *
	 * @return void
	 */
	public function office_field_validation( $fields, $errors ) {
		if ( ! $this->has_speedy_office_shipping_method() ) {
			return;
		}

		if ( empty( $fields['billing_sksoftware_speedy_office'] ) ) {
			$errors->add(
				'shipping',
				__( '<strong>Office</strong> is required field.', 'sksoftware-speedy-for-woocommerce' )
			);

			return;
		}

		$this->set_sksoftware_speedy_office_data( $fields );
	}

	/**
	 * Validate the apt field.
	 *
	 * @param array    $fields
	 * @param WP_Error $errors
	 *
	 * @return void
	 */
	public function apt_field_validation( $fields, $errors ) {
		if ( ! $this->has_speedy_apt_shipping_method() ) {
			return;
		}

		if ( empty( $fields['billing_sksoftware_speedy_apt'] ) ) {
			$errors->add(
				'shipping',
				__( '<strong>APT</strong> is required field.', 'sksoftware-speedy-for-woocommerce' )
			);

			return;
		}

		$this->set_sksoftware_speedy_apt_data( $fields );
	}

	/**
	 * This action is triggered on checkout update.
	 * It sets the speedy data in the WooCommerce session.
	 *
	 * @param string $post_data
	 *
	 * @return void
	 */
	public function woocommerce_checkout_update_order_review_2( $post_data ) {
		parse_str( $post_data, $post_data );

		$this->set_sksoftware_speedy_office_data( $post_data );
		$this->set_sksoftware_speedy_apt_data( $post_data );
	}

	/**
	 * This method sets the speedy office data in the WooCommerce session.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	private function set_sksoftware_speedy_office_data( $data ) {
		$indexes = array(
			'billing_sksoftware_speedy_office',
			'billing_sksoftware_speedy_office_name',
			'shipping_sksoftware_speedy_office',
			'shipping_sksoftware_speedy_office_name',
		);

		foreach ( $indexes as $index ) {
			if ( isset( $data[ $index ] ) ) {
				WC()->session->set( $index, $data[ $index ] );
			}
		}
	}

	/**
	 * This method sets the speedy apt data in the WooCommerce session.
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	private function set_sksoftware_speedy_apt_data( $data ) {
		$indexes = array(
			'billing_sksoftware_speedy_apt',
			'billing_sksoftware_speedy_apt_name',
			'shipping_sksoftware_speedy_apt',
			'shipping_sksoftware_speedy_apt_name',
		);

		foreach ( $indexes as $index ) {
			if ( isset( $data[ $index ] ) ) {
				WC()->session->set( $index, $data[ $index ] );
			}
		}
	}

	/**
	 * Get office value from the WooCommerce session.
	 *
	 * @param string $value Default value if the index is not found.
	 * @param int    $index The index of the value.
	 *
	 * @return array|string
	 */
	public function woocommerce_checkout_get_office_value( $value, $index ) {
		$indexes = array(
			'billing_sksoftware_speedy_office',
			'billing_sksoftware_speedy_office_name',
			'shipping_sksoftware_speedy_office',
			'shipping_sksoftware_speedy_office_name',
		);

		if ( ! in_array( $index, $indexes, true ) ) {
			return $value;
		}

		return WC()->session->get( $index );
	}

	/**
	 * Get apt value from the WooCommerce session.
	 *
	 * @param string $value Default value if the index is not found.
	 * @param int    $index The index of the value.
	 *
	 * @return string
	 */
	public function woocommerce_checkout_get_apt_value( $value, $index ) {
		$indexes = array(
			'billing_sksoftware_speedy_apt',
			'billing_sksoftware_speedy_apt_name',
			'shipping_sksoftware_speedy_apt',
			'shipping_sksoftware_speedy_apt_name',
		);

		if ( ! in_array( $index, $indexes, true ) ) {
			return $value;
		}

		return WC()->session->get( $index );
	}

	/**
	 * Add hidden fields to the checkout.
	 *
	 * @param array $fields Fields array.
	 *
	 * @return array
	 */
	public function woocommerce_default_address_fields( $fields ) {
		$fields['sksoftware_speedy_office']      = array( 'type' => 'hidden' );
		$fields['sksoftware_speedy_office_name'] = array( 'type' => 'hidden' );
		$fields['sksoftware_speedy_apt']         = array( 'type' => 'hidden' );
		$fields['sksoftware_speedy_apt_name']    = array( 'type' => 'hidden' );

		return $fields;
	}

	/**
	 * Check if the order has speedy office shipping method.
	 *
	 * @return bool
	 */
	private function has_speedy_office_shipping_method() {
		$packages = WC()->shipping()->get_packages();

		foreach ( $packages as $i => $package ) {
			$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';

			$exploded    = explode( ':', $chosen_method, 2 );
			$method_id   = $exploded[0];
			$instance_id = $exploded[1];

			if ( 'sksoftware_speedy_for_woocommerce' !== $method_id ) {
				continue;
			}

			$settings = ( new Sksoftware_Speedy_For_Woocommerce_Shipping_Method( $instance_id ) )->get_settings();

			if ( 'office' !== $settings['delivery_type'] ) {
				continue;
			}

			return true;
		}

		return false;
	}

	/**
	 * Check if the order has speedy apt shipping method.
	 *
	 * @return bool
	 */
	private function has_speedy_apt_shipping_method() {
		$packages = WC()->shipping()->get_packages();

		foreach ( $packages as $i => $package ) {
			$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';

			$exploded    = explode( ':', $chosen_method, 2 );
			$method_id   = $exploded[0];
			$instance_id = $exploded[1];

			if ( 'sksoftware_speedy_for_woocommerce' !== $method_id ) {
				continue;
			}

			$settings = ( new Sksoftware_Speedy_For_Woocommerce_Shipping_Method( $instance_id ) )->get_settings();

			if ( 'apt' !== $settings['delivery_type'] ) {
				continue;
			}

			return true;
		}

		return false;
	}

	/**
	 * Add hidden office fields to the checkout.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function add_office_checkout_fields( $fields ) {
		$fields['shipping']['shipping_sksoftware_speedy_office'] = array(
			'type' => 'hidden',
		);

		$fields['shipping']['shipping_sksoftware_speedy_office_name'] = array(
			'type' => 'hidden',
		);

		$fields['billing']['billing_sksoftware_speedy_office'] = array(
			'type' => 'hidden',
		);

		$fields['billing']['billing_sksoftware_speedy_office_name'] = array(
			'type' => 'hidden',
		);

		return $fields;
	}

	/**
	 * Add hidden apt fields to the checkout.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public function add_apt_checkout_fields( $fields ) {
		$fields['shipping']['shipping_sksoftware_speedy_apt'] = array(
			'type' => 'hidden',
		);

		$fields['shipping']['shipping_sksoftware_speedy_apt_name'] = array(
			'type' => 'hidden',
		);

		$fields['billing']['billing_sksoftware_speedy_apt'] = array(
			'type' => 'hidden',
		);

		$fields['billing']['billing_sksoftware_speedy_apt_name'] = array(
			'type' => 'hidden',
		);

		return $fields;
	}

	/**
	 * Add the field to the checkout
	 *
	 * @param array $packages Packages array.
	 *
	 * @return array
	 */
	public function add_office_to_shipping_packages( $packages ) {
		foreach ( $packages as $key => $package ) {
			if ( isset( $packages[ $key ]['destination'] ) ) {
				$packages[ $key ]['destination']['speedy_office'] = WC()->checkout()->get_value( 'shipping_sksoftware_speedy_office' );
				$packages[ $key ]['destination']['speedy_apt']    = WC()->checkout()->get_value( 'shipping_sksoftware_speedy_apt' );
			}
		}

		return $packages;
	}
}
