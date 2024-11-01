<?php
/**
 * The file that defines the api client class
 *
 * @link       https://sk-soft.net
 * @since      1.0.0
 *
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/includes
 */

/**
 * The api client class.
 *
 * This is used to define connection with sksoftware api.
 *
 * @since      1.0.0
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/includes
 * @author     SK Software <office@sk-soft.net>
 */
class Sksoftware_Speedy_For_Woocommerce_Api_Client {
	/**
	 * @var array
	 */
	private $settings;

	/**
	 * @param array $settings
	 */
	public function __construct( array $settings ) {
		$this->settings = $settings;
	}

	/**
	 * @param int $instance_id
	 *
	 * @return self
	 */
	public static function create( $instance_id ) {
		if ( ! class_exists( 'Sksoftware_Speedy_For_Woocommerce_Shipping_Method' ) ) {
			/**
			 * The class responsible for defining the shipping unit for woocommerce.
			 */
			require_once plugin_dir_path( __DIR__ ) . 'includes/class-sksoftware-speedy-for-woocommerce-shipping-method.php';
		}

		return new self(
			( new Sksoftware_Speedy_For_Woocommerce_Shipping_Method( $instance_id ) )->get_settings()
		);
	}

	/**
	 * @return void
	 */
	public function authenticate() {
		if ( $this->is_authenticated() ) {
			return;
		}

		$platform_parameters = array_merge(
			$this->get_platform_parameters(),
			array(
				'pluginSettings' => $this->settings,
				'storeSettings'  => $this->get_store_parameters(),
			)
		);

		$user_language = explode( '_', get_user_locale() )[0];

		$response = wp_remote_post(
			SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_API_HOST . '/api/authenticate',
			array(
				'timeout'   => SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_TIMEOUT,
				'sslverify' => SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_SSL_VERIFY,
				'headers'   => array(
					'x-api-key'       => isset( $this->settings['api_key'] ) ? $this->settings['api_key'] : '',
					'Accept'          => 'application/json',
					'Accept-Language' => $user_language,
				),
				'body'      => wp_json_encode(
					array(
						'providerParameters' => $this->get_provider_parameters(),
						'platformParameters' => $platform_parameters,
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			update_option( 'sksoftware_speedy_for_woocommerce_is_authenticated', 'no' );

			error_log(
				sprintf(
					'Invalid error when creating Speedy token. Error message: %s',
					$response->get_error_message()
				)
			);

			return;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $response_code ) {
			update_option( 'sksoftware_speedy_for_woocommerce_is_authenticated', 'yes' );

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			update_option( 'sksoftware_speedy_for_woocommerce_client_contracts', $data );

			return;
		}

		update_option( 'sksoftware_speedy_for_woocommerce_is_authenticated', 'no' );
		update_option( 'sksoftware_speedy_for_woocommerce_client_contracts', null );

		error_log( sprintf( 'Invalid response code %d when creating Speedy token.', $response_code ) );
		error_log( sprintf( 'Response body: %s.', wp_remote_retrieve_body( $response ) ) );
	}

	/**
	 * @return bool
	 */
	public function is_authenticated() {
		return 'yes' === get_option( 'sksoftware_speedy_for_woocommerce_is_authenticated' );
	}

	/**
	 * Calculates shipping price for cart.
	 *
	 * @param array $package
	 *
	 * @return string|null
	 */
	public function calculate_shipping_for_cart( $package ) {

		$payment_method = WC()->session->get( 'chosen_payment_method' );
		$office_id      = WC()->session->get( 'billing_sksoftware_speedy_office' );
		$apt_id         = WC()->session->get( 'billing_sksoftware_speedy_apt' );

		$platform_parameters = array_merge(
			$this->get_platform_parameters(),
			array(
				'paymentMethod'  => $payment_method,
				'package'        => $this->convert_package_to_array( $package ),
				'pluginSettings' => $this->settings,
				'storeSettings'  => $this->get_store_parameters(),
				'pickupOfficeId' => $office_id,
				'pickupAptId'    => $apt_id,
			)
		);

		$response = $this->request(
			'POST',
			SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_API_HOST . '/api/calculate-shipping-for-cart',
			array(
				'json' => array(
					'providerParameters' => $this->get_provider_parameters(),
					'platformParameters' => $platform_parameters,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log(
				sprintf(
					'Invalid error from calculate shipping for cart request. Error message: %s',
					$response->get_error_message()
				)
			);

			return null;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			error_log(
				sprintf(
					'Invalid response code %d from calculate shipping for cart request.',
					$response_code
				)
			);
			error_log( sprintf( 'Response body: %s.', wp_remote_retrieve_body( $response ) ) );

			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! isset( $data['price'] ) ) {
			error_log(
				sprintf(
					'Price is missing from calculate shipping for cart request. Response body: %s.',
					$body
				)
			);

			return null;
		}

		return $data['price'];
	}

	/**
	 * Calculates shipping price for order.
	 *
	 * @param WC_Order $order
	 *
	 * @return string|null
	 */
	public function calculate_shipping_for_order( $order ) {
		$payment_method = $order->get_payment_method();

		$shipment_parameters_override = $order->get_meta( '_sksoftware_speedy_for_woocommerce_shipment_parameters_override' );

		if ( ! is_array( $shipment_parameters_override ) ) {
			$shipment_parameters_override = array();
		}

		$office_id = $order->get_meta( 'billing_sksoftware_speedy_office' );
		$apt_id    = $order->get_meta( 'billing_sksoftware_speedy_apt' );

		$platform_parameters = array_merge(
			$this->get_platform_parameters(),
			array(
				'paymentMethod'              => $payment_method,
				'order'                      => $this->convert_order_to_array( $order ),
				'pluginSettings'             => $this->settings,
				'storeSettings'              => $this->get_store_parameters(),
				'shipmentParametersOverride' => $shipment_parameters_override,
				'pickupOfficeId'             => $office_id,
				'pickupAptId'                => $apt_id,
			)
		);

		$response = $this->request(
			'POST',
			SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_API_HOST . '/api/calculate-shipping-for-order',
			array(
				'json' => array(
					'providerParameters' => $this->get_provider_parameters(),
					'platformParameters' => $platform_parameters,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log(
				sprintf(
					'Invalid error from calculate shipping for order request. Error message: %s',
					$response->get_error_message()
				)
			);

			return null;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			error_log(
				sprintf(
					'Invalid response code %d from calculate shipping for order request.',
					$response_code
				)
			);
			error_log( sprintf( 'Response body: %s.', wp_remote_retrieve_body( $response ) ) );

			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! isset( $data['price'] ) ) {
			error_log(
				sprintf(
					'Price is missing from calculate shipping for order request. Response body: %s.',
					$body
				)
			);

			return null;
		}

		return $data['price'];
	}

	/**
	 * Create a shipment.
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function create_shipment( $order ) {
		$shipment_parameters_override = $order->get_meta( '_sksoftware_speedy_for_woocommerce_shipment_parameters_override' );

		if ( ! is_array( $shipment_parameters_override ) ) {
			$shipment_parameters_override = array();
		}

		$office_id = $order->get_meta( 'billing_sksoftware_speedy_office' );
		$apt_id    = $order->get_meta( 'billing_sksoftware_speedy_apt' );

		$platform_parameters = array_merge(
			$this->get_platform_parameters(),
			array(
				'order'                      => $this->convert_order_to_array( $order ),
				'pluginSettings'             => $this->settings,
				'storeSettings'              => $this->get_store_parameters(),
				'shipmentParametersOverride' => $shipment_parameters_override,
				'pickupOfficeId'             => $office_id,
				'pickupAptId'                => $apt_id,
			)
		);

		$response = $this->request(
			'POST',
			SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_API_HOST . '/api/create-shipment',
			array(
				'json' => array(
					'providerParameters' => $this->get_provider_parameters(),
					'platformParameters' => $platform_parameters,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_die( esc_html( $response->get_error_message() ) );
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			if ( 400 === $response_code ) {
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );

				wp_die(
					esc_html__(
						'Unable to create Speedy shipment.',
						'sksoftware-speedy-for-woocommerce'
					) . ' ' . esc_html( $data['message'] )
				);
			}

			error_log( sprintf( 'Unable to create Speedy shipment. Status code %d.', $response_code ) );

			wp_die( esc_html__( 'Unable to create Speedy shipment.', 'sksoftware-speedy-for-woocommerce' ) );
		}

		$body = wp_remote_retrieve_body( $response );

		return json_decode( $body, true );
	}

	/**
	 * Delete a shipment.
	 *
	 * @param WC_Order $order
	 *
	 * @return bool
	 */
	public function delete_shipment( $order ) {
		$platform_parameters = array_merge(
			$this->get_platform_parameters(),
			array(
				'shipmentNumber' => $order->get_meta( '_sksoftware_speedy_for_woocommerce_shipment_id' ),
				'comment'        => 'Shipment deleted via SKSoftware Speedy for WooCommerce plugin by admin request.',
				'pluginSettings' => $this->settings,
				'storeSettings'  => $this->get_store_parameters(),
			)
		);

		$response = $this->request(
			'POST',
			SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_API_HOST . '/api/delete-shipment',
			array(
				'json' => array(
					'providerParameters' => $this->get_provider_parameters(),
					'platformParameters' => $platform_parameters,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log(
				sprintf(
					'Invalid error from delete Speedy shipment. Error message: %s',
					$response->get_error_message()
				)
			);

			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			error_log( sprintf( 'Invalid response code %d from delete Speedy shipment.', $response_code ) );
		}

		return 200 === $response_code;
	}

	/**
	 * Print shipment label.
	 *
	 * @param WC_Order $order
	 *
	 * @return array|bool
	 */
	public function print_shipment_label( $order ) {
		$platform_parameters = array_merge(
			$this->get_platform_parameters(),
			array(
				'shipmentNumber' => $order->get_meta( '_sksoftware_speedy_for_woocommerce_shipment_id' ),
				'pluginSettings' => $this->settings,
				'storeSettings'  => $this->get_store_parameters(),
			)
		);

		$response = $this->request(
			'POST',
			SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_API_HOST . '/api/print-shipment-label',
			array(
				'json' => array(
					'providerParameters' => $this->get_provider_parameters(),
					'platformParameters' => $platform_parameters,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log(
				sprintf(
					'Invalid error from print Speedy shipment. Error message: %s',
					$response->get_error_message()
				)
			);

			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			error_log( sprintf( 'Invalid response code %d from print Speedy shipment.', $response_code ) );
		}

		return $response;
	}

	/**
	 * Get shipment parameters.
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public function get_shipment_parameters( $order ) {
		$shipment_parameters_override = $order->get_meta( '_sksoftware_speedy_for_woocommerce_shipment_parameters_override' );

		if ( ! is_array( $shipment_parameters_override ) ) {
			$shipment_parameters_override = array();

			$shipment_parameters_override = apply_filters(
				'sksoftware_speedy_for_woocommerce_default_shipment_parameters',
				$shipment_parameters_override,
				$order
			);
		}

		$platform_parameters = array_merge(
			$this->get_platform_parameters(),
			array(
				'paymentMethod'                => $order->get_payment_method(),
				'order'                        => $this->convert_order_to_array( $order ),
				'pluginSettings'               => $this->settings,
				'storeSettings'                => $this->get_store_parameters(),
				'shipment_parameters_override' => $shipment_parameters_override,
			)
		);

		$response = $this->request(
			'POST',
			SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_API_HOST . '/api/get-shipment-parameters',
			array(
				'json' => array(
					'providerParameters' => $this->get_provider_parameters(),
					'platformParameters' => $platform_parameters,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log(
				sprintf(
					'Invalid error from get shipment parameters request. Error message: %s',
					$response->get_error_message()
				)
			);

			return array();
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {
			error_log( sprintf( 'Invalid response code %d from get shipment parameters request.', $response_code ) );

			return array();
		}

		$body = wp_remote_retrieve_body( $response );

		return json_decode( $body, true );
	}

	/**
	 * Endpoint for starting free trial.
	 *
	 * @param string $email Administrator email.
	 * @param bool   $accepted_terms Whether the user has accepted the terms.
	 *
	 * @return array JSON content.
	 */
	public function start_free_trial( $email, $accepted_terms ) {
		$response = $this->request(
			'POST',
			SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_API_HOST . '/subscriptions/free-trial',
			array(
				'json' => array(
					'email'          => $email,
					'site_url'       => get_site_url(),
					'accepted_terms' => $accepted_terms,
					'type'           => 'speedy',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log(
				sprintf(
					'Invalid error from start free trial request. Error message: %s',
					$response->get_error_message()
				)
			);

			return array();
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 500 === $response_code ) {
			error_log( sprintf( 'Invalid response code %d from start free trial request.', $response_code ) );

			return array();
		}

		$body = wp_remote_retrieve_body( $response );

		return json_decode( $body, true );
	}

	/**
	 * Request wrapper method.
	 *
	 * @param string $method HTTP Method.
	 * @param string $url URL.
	 * @param array  $options Array of options.
	 *
	 * @return array|WP_Error
	 */
	private function request( $method, $url, $options = array() ) {
		if ( false === $this->is_authenticated() ) {
			$this->authenticate();
		}

		$user_language = explode( '_', get_user_locale() )[0];

		$options = array_merge(
			array(
				'sslverify' => SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_SSL_VERIFY,
				'timeout'   => SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_TIMEOUT,
				'headers'   => array_merge(
					array(
						'x-api-key'       => isset( $this->settings['api_key'] ) ? $this->settings['api_key'] : '',
						'Accept'          => 'application/json',
						'Accept-Language' => $user_language,
					),
					isset( $options['headers'] ) ? $options['headers'] : array()
				),
			),
			$options
		);

		if ( isset( $options['json'] ) ) {
			$options['headers']['Content-Type'] = 'application/json';
			$options['body']                    = wp_json_encode( $options['json'] );

			unset( $options['json'] );
		}

		return wp_remote_request(
			$url,
			array_merge(
				$options,
				array(
					'method' => $method,
				)
			)
		);
	}

	/**
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	private function convert_order_to_array( $order ) {
		$converted_order          = json_decode( wp_json_encode( $order->get_data() ), true );
		$converted_order['items'] = array();

		foreach ( $order->get_items() as $key => $item ) {
			$converted_order['items'][ $key ] = json_decode( wp_json_encode( $item->get_data() ), true );
			/** @var WC_Product $product */
			$product                                     = wc_get_product( $converted_order['items'][ $key ]['product_id'] );
			$converted_order['items'][ $key ]['product'] = json_decode( wp_json_encode( $product->get_data() ), true );
		}

		$converted_order['subtotal'] = $order->get_subtotal();

		return $converted_order;
	}

	/**
	 * Helper function to convert package to array.
	 *
	 * @param array $package
	 *
	 * @return array
	 */
	private function convert_package_to_array( $package ) {
		$converted_package = json_decode( wp_json_encode( $package ), true );

		foreach ( $converted_package['contents'] as $content_key => $content_value ) {
			/** @var WC_Product $product */
			$product = $package['contents'][ $content_key ]['data'];

			$converted_package['contents'][ $content_key ]['product'] = json_decode(
				wp_json_encode( $product->get_data() ),
				true
			);
		}

		return $converted_package;
	}

	/**
	 * Helper function to get provider parameters.
	 *
	 * @return array
	 */
	private function get_provider_parameters() {
		return array(
			'provider' => 'Speedy',
			'username' => isset( $this->settings['username'] ) ? $this->settings['username'] : '',
			'password' => isset( $this->settings['password'] ) ? $this->settings['password'] : '',
			'client'   => isset( $this->settings['client'] ) ? $this->settings['client'] : '',
		);
	}

	/**
	 * Helper function to get platform parameters.
	 *
	 * @return array
	 */
	private function get_platform_parameters() {
		global $wp_version;

		return array(
			'platform'           => 'WooCommerce',
			'wooCommerceVersion' => WC()->version,
			'wordpressVersion'   => $wp_version,
			'pluginVersion'      => SKSOFTWARE_SPEEDY_FOR_WOOCOMMERCE_VERSION,
			'phpVersionId'       => PHP_VERSION_ID,
		);
	}

	/**
	 * Helper function to get store parameters.
	 *
	 * @return array
	 */
	private function get_store_parameters() {
		return array(
			'storeCurrency'      => get_option( 'woocommerce_currency' ),
			'storeWeightUnit'    => get_option( 'woocommerce_weight_unit' ),
			'storeDimensionUnit' => get_option( 'woocommerce_dimension_unit' ),
			'storeCountry'       => get_option( 'woocommerce_default_country' ),
			'storeCalcTaxes'     => get_option( 'woocommerce_calc_taxes' ),
		);
	}
}
