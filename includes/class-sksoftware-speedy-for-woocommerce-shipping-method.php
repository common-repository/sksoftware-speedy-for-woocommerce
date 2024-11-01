<?php
/**
 * Base class for sksoftware Speedy for woocommerce shipping method.
 *
 * @link       https://sk-soft.net
 * @since      1.0.0
 *
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/includes
 */

use Automattic\WooCommerce\Utilities\I18nUtil;

/**
 * Base class for sksoftware speedy for woocommerce shipping method.
 *
 * @since      1.0.0
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/includes
 * @author     SK Software <office@sk-soft.net>
 */
class Sksoftware_Speedy_For_Woocommerce_Shipping_Method extends WC_Shipping_Method {
	/**
	 * Features this method supports. Possible features used by core:
	 * - shipping-zones Shipping zone functionality + instances
	 * - instance-settings Instance settings screens.
	 * - settings Non-instance settings screens. Enabled by default for BW compatibility with methods before instances existed.
	 * - instance-settings-modal Allows the instance settings to be loaded within a modal in the zones UI.
	 *
	 * @var array
	 */
	public $supports = array( 'shipping-zones', 'instance-settings', 'instance-settings-modal', 'settings' );

	/**
	 * @var Sksoftware_Speedy_For_Woocommerce_Api_Client
	 */
	private $api_client;

	/**
	 * @param int $instance_id
	 *
	 * @inheritDoc
	 */
	public function __construct( $instance_id = 0 ) {
		parent::__construct( $instance_id );

		$this->id                 = 'sksoftware_speedy_for_woocommerce';
		$this->title              = 'Speedy';
		$this->method_title       = 'Speedy';
		$this->method_description = __(
			'Allows customers to receive shipments using Speedy.',
			'sksoftware-speedy-for-woocommerce'
		);

		$this->init_form_fields();

		$this->init_settings();

		if ( $instance_id ) {
			$this->init_instance_settings();
		}

		if ( ! empty( $this->get_settings()['title'] ) ) {
			$this->method_title = $this->get_settings()['title'];
		}

		if ( ! empty( $this->get_settings()['description'] ) ) {
			$this->method_description = $this->get_settings()['description'];
		}

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

		$this->api_client = new Sksoftware_Speedy_For_Woocommerce_Api_Client( $this->get_settings() );
	}

	/**
	 * @inheritDoc
	 */
	public function init_form_fields() {
		parent::init_form_fields();

		$client           = array();
		$office_locations = array();

		if ( 'yes' === get_option( 'sksoftware_speedy_for_woocommerce_is_authenticated' ) ) {
			$contracts = get_option( 'sksoftware_speedy_for_woocommerce_client_contracts' );

			$client_address_locations_keys = array_map(
				function ( $contract ) {
					return $contract['clientId'];
				},
				$contracts['clients']
			);

			$client_address_locations_values = array_map(
				function ( $contract ) {
					return sprintf(
						'%s - %s - %s',
						$contract['clientId'],
						$contract['clientName'],
						$contract['address']['fullAddressString']
					);
				},
				$contracts['clients']
			);

			$client = array_combine( $client_address_locations_keys, $client_address_locations_values );

			$office_location      = $this->get_option( 'send_from_office_location' );
			$office_location_name = $this->get_option( 'send_from_office_location_name' );

			if ( $office_location ) {
				$office_locations = array(
					$office_location => $office_location_name,
				);
			}
		}

		$this->form_fields = array(
			'api_key'  => array(
				'title'       => __( 'SK Software API Key', 'sksoftware-speedy-for-woocommerce' ),
				'type'        => 'text',
				'description' => sprintf(
				/* translators: 1: SK Software Speedy for WooCommerce plugin link 2: Start free trial link */
					__(
						'API Key provided by SK Software Ltd. You can buy a license on %1$s or %2$s',
						'sksoftware-speedy-for-woocommerce'
					),
					'<a target="_blank" href="https://sk-soft.net/plugins/speedy-for-woocommerce">here</a>',
					'<a href="#" id="sksoftware-speedy-for-woocommerce-start-free-trial">start a 14 days free trial</a>.'
				),
				'default'     => '',
			),
			'username' => array(
				'title'       => __( 'Username', 'sksoftware-speedy-for-woocommerce' ),
				'type'        => 'text',
				'description' => __(
					'You can get your username and password by contacting Speedy.',
					'sksoftware-speedy-for-woocommerce'
				),
				'default'     => '',
			),
			'password' => array(
				'title'   => __( 'Password', 'sksoftware-speedy-for-woocommerce' ),
				'type'    => 'password',
				'default' => '',
			),
		);

		if ( 'yes' === get_option( 'sksoftware_speedy_for_woocommerce_is_authenticated' ) ) {
			$this->form_fields = array_merge(
				$this->form_fields,
				array(
					'client'                         => array(
						'title'       => __( 'Client to send from', 'sksoftware-speedy-for-woocommerce' ),
						'type'        => 'select',
						'description' => __(
							'Choose from which Speedy client you will send your orders.',
							'sksoftware-speedy-for-woocommerce'
						),
						'default'     => __( 'Please choose', 'sksoftware-speedy-for-woocommerce' ),
						'options'     => array_replace(
							array(
								'' => __( 'Please choose', 'sksoftware-speedy-for-woocommerce' ),
							),
							$client
						),
					),
					'sender_name'                    => array(
						'title'       => __( 'Sender name', 'sksoftware-speedy-for-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'Your first and last name.', 'sksoftware-speedy-for-woocommerce' ),
						'default'     => '',
					),
					'sender_phone'                   => array(
						'title'       => __( 'Sender phone number', 'sksoftware-speedy-for-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'Your phone number.', 'sksoftware-speedy-for-woocommerce' ),
						'default'     => '',
					),
					'sender_email'                   => array(
						'title'       => __( 'Sender e-mail address', 'sksoftware-speedy-for-woocommerce' ),
						'type'        => 'text',
						'description' => __( 'Your e-mail address.', 'sksoftware-speedy-for-woocommerce' ),
						'default'     => '',
					),
					'send_from'                      => array(
						'title'       => __( 'Send from', 'sksoftware-speedy-for-woocommerce' ),
						'type'        => 'select',
						'description' => __(
							'Choose whether you will ship your orders from Speedy Office or Address.',
							'sksoftware-speedy-for-woocommerce'
						),
						'default'     => __( 'office', 'sksoftware-speedy-for-woocommerce' ),
						'options'     => array(
							'office'  => __( 'Office', 'sksoftware-speedy-for-woocommerce' ),
							'address' => __( 'Address', 'sksoftware-speedy-for-woocommerce' ),
						),
					),
					'send_from_office_location_name' => array(
						'type' => 'hidden',
					),
					'send_from_office_location'      => array(
						'title'       => __( 'Office to send from', 'sksoftware-speedy-for-woocommerce' ),
						'type'        => 'select',
						'description' => __(
							'Choose from which Speedy office you will send your orders.',
							'sksoftware-speedy-for-woocommerce'
						),
						'default'     => __( 'Please choose', 'sksoftware-speedy-for-woocommerce' ),
						'options'     => array_replace(
							array(
								'' => __( 'Please choose', 'sksoftware-speedy-for-woocommerce' ),
							),
							$office_locations
						),
					),
					'should_send_tracking_link'      => array(
						'title'       => __( 'Send tracking link', 'sksoftware-speedy-for-woocommerce' ),
						'type'        => 'checkbox',
						'description' => __(
							'This option can send the tracking number of the shipment to the customer. If the shipment you generated has a tracking number, it will automatically send a note to the customer.',
							'sksoftware-speedy-for-woocommerce'
						),
						'default'     => false,
					),
				),
				$this->get_common_form_fields()
			);
		}

		$this->instance_form_fields = array_merge(
			array(
				'title'       => array(
					'title'       => __( 'Title', 'sksoftware-speedy-for-woocommerce' ),
					'type'        => 'text',
					'description' => __(
						'This controls the title of the shipping method, which the user sees during checkout.',
						'sksoftware-speedy-for-woocommerce'
					),
					'default'     => 'Speedy',
				),
				'description' => array(
					'title'       => __( 'Description', 'sksoftware-speedy-for-woocommerce' ),
					'type'        => 'text',
					'description' => __(
						'This controls the description of the shipping method, which the user sees during checkout.',
						'sksoftware-speedy-for-woocommerce'
					),
				),
			),
			$this->get_common_form_fields()
		);
	}

	/**
	 * Get form fields for both instance and global settings.
	 *
	 * @return array
	 */
	private function get_common_form_fields() {
		$default_product_weight  = null;
		$default_box_height      = null;
		$default_box_width       = null;
		$default_box_length      = null;
		$weight_unit_translation = I18nUtil::get_weight_unit_label( get_option( 'woocommerce_weight_unit', 'kg' ) );
		$length_unit_translation = I18nUtil::get_dimensions_unit_label( get_option( 'woocommerce_dimension_unit', 'cm' ) );

		switch ( get_option( 'woocommerce_weight_unit' ) ) {
			case 'kg':
				$default_product_weight = '0.250';
				break;
			case 'g':
				$default_product_weight = '250';
				break;
			case 'lbs':
				$default_product_weight = '0.5';
				break;
			case 'oz':
				$default_product_weight = '8';
				break;
		}

		switch ( get_option( 'woocommerce_dimension_unit' ) ) {
			case 'cm':
				$default_box_height = '15';
				$default_box_width  = '15';
				$default_box_length = '15';
				break;
			case 'm':
				$default_box_height = '0.15';
				$default_box_width  = '0.15';
				$default_box_length = '0.15';
				break;
			case 'mm':
				$default_box_height = '150';
				$default_box_width  = '150';
				$default_box_length = '150';
				break;
			case 'in':
				$default_box_height = '6';
				$default_box_width  = '6';
				$default_box_length = '6';
				break;
			case 'yd':
				$default_box_height = '0.16';
				$default_box_width  = '0.16';
				$default_box_length = '0.16';
				break;
		}

		return array(
			'delivery_type'            => array(
				'title'       => __( 'Delivery type', 'sksoftware-speedy-for-woocommerce' ),
				'type'        => 'select',
				'description' => __(
                    'This controls how the speedy shipping method will handle the delivery: will it deliver to office or address. You can setup multiple Speedy shipping methods to have both delivery types.',
                    'sksoftware-speedy-for-woocommerce'
                ),
				'default'     => 'office',
				'options'     => array(
					'office'  => 'Deliver to Office',
					'address' => 'Deliver to Address',
					'apt'     => 'Deliver to APT',
				),
			),
			'delivery_payee'           => array(
				'title'       => __( 'Delivery payee', 'sksoftware-speedy-for-woocommerce' ),
				'type'        => 'select',
				'description' => __(
					'This controls who will pay for the delivery of your shipment.',
					'sksoftware-speedy-for-woocommerce'
				),
				'default'     => $this->get_option( 'delivery_payee', 'RECIPIENT' ),
				'options'     => array(
					'RECIPIENT' => __( 'Your client will pay', 'sksoftware-speedy-for-woocommerce' ),
					'SENDER'    => __( 'I will pay', 'sksoftware-speedy-for-woocommerce' ),
				),
			),
			'return_shipping'          => array(
				'title'       => __( 'Return payee', 'sksoftware-speedy-for-woocommerce' ),
				'type'        => 'select',
				'description' => __(
					'This controls who will pay the return shipping of your shipment.',
					'sksoftware-speedy-for-woocommerce'
				),
				'default'     => $this->get_option( 'return_shipping', 'SENDER' ),
				'options'     => array(
					'SENDER'    => __( 'Your client will pay', 'sksoftware-speedy-for-woocommerce' ),
					'RECIPIENT' => __( 'I will pay', 'sksoftware-speedy-for-woocommerce' ),
				),
			),
			'package_type'             => array(
				'title'       => __( 'Default package type', 'sksoftware-speedy-for-woocommerce' ),
				'description' => __(
					'Package type to be used for shipments.',
					'sksoftware-speedy-for-woocommerce'
				),
				'type'        => 'select',
				'default'     => $this->get_option( 'package_type', 'BOX' ),
				'options'     => array(
					'CARTON BOX'      => __( 'Carton box', 'sksoftware-speedy-for-woocommerce' ), // кашон.
					'ENVELOPE'        => __( 'Envelope', 'sksoftware-speedy-for-woocommerce' ), // ПЛИК.
					'BAG'             => __( 'Bag', 'sksoftware-speedy-for-woocommerce' ), // ЧУВАЛ.
					'BOX'             => __( 'Box', 'sksoftware-speedy-for-woocommerce' ), // КУТИЯ.
					'BOX IN ENVELOPE' => __( 'Box in envelope', 'sksoftware-speedy-for-woocommerce' ), // КУТИЯ В ПЛИК.
					'STRETCH FOIL'    => __( 'Stretch foil', 'sksoftware-speedy-for-woocommerce' ), // СТРЕЧ.
					'BUBBLE FOI'      => __( 'Bubble foil', 'sksoftware-speedy-for-woocommerce' ), // ФОЛИО.
					'NYLON'           => __( 'Nylon', 'sksoftware-speedy-for-woocommerce' ), // НАЙЛОН.
				),
			),
			'default_product_weight'   => array(
				'title'       => sprintf(
				/* translators: %s: weight unit */
					__( 'Default product weight in %s', 'sksoftware-speedy-for-woocommerce' ),
					$weight_unit_translation
				),
				'type'        => 'decimal',
				'description' => __(
					'Weight to be used for products that are without weight.',
					'sksoftware-speedy-for-woocommerce'
				),
				'default'     => $this->get_option( 'default_product_weight', $default_product_weight ),
			),
			'default_box_height'       => array(
				'title'       => sprintf(
				/* translators: %s: dimension unit */
					__( 'Default box height in %s', 'sksoftware-speedy-for-woocommerce' ),
					$length_unit_translation
				),
				'type'        => 'decimal',
				'description' => __( 'Height to be used for shipment volume.', 'sksoftware-speedy-for-woocommerce' ),
				'default'     => $this->get_option( 'default_box_height', $default_box_height ),
			),
			'default_box_width'        => array(
				'title'       => sprintf(
				/* translators: %s: dimension unit */
					__( 'Default box width in %s', 'sksoftware-speedy-for-woocommerce' ),
					$length_unit_translation
				),
				'type'        => 'decimal',
				'description' => __( 'Width to be used for shipment volume.', 'sksoftware-speedy-for-woocommerce' ),
				'default'     => $this->get_option( 'default_box_width', $default_box_width ),
			),
			'default_box_length'       => array(
				'title'       => sprintf(
				/* translators: %s: dimension unit */
					__( 'Default box length in %s', 'sksoftware-speedy-for-woocommerce' ),
					$length_unit_translation
				),
				'type'        => 'decimal',
				'description' => __( 'Length to be used for shipment volume.', 'sksoftware-speedy-for-woocommerce' ),
				'default'     => $this->get_option( 'default_box_length', $default_box_length ),
			),
			'speedy_product'           => array(
				'title'       => __( 'Speedy service', 'sksoftware-speedy-for-woocommerce' ),
				'type'        => 'select',
				'description' => sprintf(
				/* translators: %s: Speedy link */
					__(
						'Choose which Speedy service to be used. More information about delivery time, pricing and maximum weight of each service can be found in %s.',
						'sksoftware-speedy-for-woocommerce'
					),
					'<a href="https://speedy.eu">https://speedy.eu</a>'
				),
				'default'     => $this->get_option( 'speedy_product', '505' ),
				'options'     => array(
					'505' => 'Standard 24 hours',
				),
			),
			'is_fragile'               => array(
				'title'       => __( 'Fragile label', 'sksoftware-speedy-for-woocommerce' ),
				'type'        => 'select',
				'description' => __(
					'Choose if your shipment should be marked as fragile.',
					'sksoftware-speedy-for-woocommerce'
				),
				'default'     => $this->get_option( 'is_fragile', 'false' ),
				'options'     => array(
					'false' => __( 'No', 'sksoftware-speedy-for-woocommerce' ),
					'true'  => __( 'Yes', 'sksoftware-speedy-for-woocommerce' ),
				),
			),
			'is_declared_amount'       => array(
				'title'       => __( 'Declared amount', 'sksoftware-speedy-for-woocommerce' ),
				'type'        => 'select',
				'description' => __(
					'Choose if your shipment should have a declared amount. The declared amount will be equal to the order price, exluding shipping.',
					'sksoftware-speedy-for-woocommerce'
				),
				'default'     => $this->get_option( 'is_declared_amount', 'false' ),
				'options'     => array(
					'false' => __( 'No', 'sksoftware-speedy-for-woocommerce' ),
					'true'  => __( 'Yes', 'sksoftware-speedy-for-woocommerce' ),
				),
			),
			'open_test_before_payment' => array(
				'title'       => __( 'Open and/or test before payment', 'sksoftware-speedy-for-woocommerce' ),
				'type'        => 'select',
				'description' => __(
					'Can the recipient open and/or test the shipment before paying?',
					'sksoftware-speedy-for-woocommerce'
				),
				'default'     => $this->get_option( 'open_test_before_payment', 'NO' ),
				'options'     => array(
					'NO'   => __( 'No', 'sksoftware-speedy-for-woocommerce' ),
					'OPEN' => __( 'Open', 'sksoftware-speedy-for-woocommerce' ),
					'TEST' => __( 'Open and test', 'sksoftware-speedy-for-woocommerce' ),
				),
			),
			'saturday_delivery'        => array(
				'title'       => __( 'Saturday delivery', 'sksoftware-speedy-for-woocommerce' ),
				'type'        => 'select',
				'description' => __(
					'Can the recipient receive his package on Saturday?',
					'sksoftware-speedy-for-woocommerce'
				),
				'default'     => $this->get_option( 'saturday_delivery', 'false' ),
				'options'     => array(
					'false' => __( 'No', 'sksoftware-speedy-for-woocommerce' ),
					'true'  => __( 'Yes', 'sksoftware-speedy-for-woocommerce' ),
				),
			),
			'cod_processing_type'      => array(
				'title'       => __( 'CoD processing type', 'sksoftware-speedy-for-woocommerce' ),
				'type'        => 'select',
				'description' => __(
					'How would you like to receive your money when sending shipments with CoD?',
					'sksoftware-speedy-for-woocommerce'
				),
				'default'     => $this->get_option( 'cod_processing_type', 'CASH' ),
				'options'     => array(
					'CASH'                  => __( 'Cash', 'sksoftware-speedy-for-woocommerce' ),
					'POSTAL_MONEY_TRANSFER' => __( 'Postal money transfer', 'sksoftware-speedy-for-woocommerce' ),
				),
			),
			'pricing_override'         => array(
				'title'       => __( 'Pricing override', 'sksoftware-speedy-for-woocommerce' ),
				'type'        => 'table',
				'description' => __(
					'This table allows you to change the price according to the conditions set in it. You can learn more about how it works and sample settings for different cases in our documentation.',
					'sksoftware-speedy-for-woocommerce'
				),
				'fields'      => array(
					'type'         => array(
						'title'   => __( 'Type', 'sksoftware-speedy-for-woocommerce' ),
						'type'    => 'select',
						'options' => array(
							'flat_rate'          => __( 'Flat rate', 'sksoftware-speedy-for-woocommerce' ),
							'merchant_all_below' => __(
								'Merchant pays all below',
								'sksoftware-speedy-for-woocommerce'
							),
							'merchant_all_above' => __(
								'Merchant pays all above',
								'sksoftware-speedy-for-woocommerce'
							),
						),
					),
					'order_amount' => array(
						'title' => __( 'Order amount equal or above', 'sksoftware-speedy-for-woocommerce' ),
						'type'  => 'decimal',
					),
					'weight'       => array(
						'title' => sprintf(
						/* translators: %s: weight unit */
							__( 'Weight equal or below in %s', 'sksoftware-speedy-for-woocommerce' ),
							$weight_unit_translation
						),
						'type'  => 'decimal',
					),
					'amount'       => array(
						'title' => __( 'Shipping cost equals', 'sksoftware-speedy-for-woocommerce' ),
						'type'  => 'decimal',
					),
				),
				'default'     => $this->get_option( 'pricing_override', array() ),
			),
		);
	}

	/**
	 * Calculate shipping function.
	 *
	 * @access public
	 *
	 * @param mixed $package
	 *
	 * @return void
	 */
	public function calculate_shipping( $package = array() ) {
		$price = $this->api_client->calculate_shipping_for_cart( $package );

		if ( null !== $price ) {
			$rate = array(
				'label' => $this->get_settings()['title'],
				'cost'  => $price,
			);

			$this->add_rate( $rate );
		}
	}

	/**
	 * Validates the data of a field of type table_field.
	 *
	 * @param string $key
	 * @param array  $data
	 *
	 * @return array
	 */
	public function validate_table_field( $key, $data ) {
		$post_data = empty( $data ) ? $this->recursive_sanitize_text_field( $_POST ) : $data; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$data = isset( $post_data['data'] ) ? $post_data['data'] : $post_data;

		$field_key = $this->get_field_key( $key );
		$data      = array_filter(
			$data,
			function ( $array_key ) use ( $field_key ) {
				return false !== strpos( $array_key, $field_key );
			},
			ARRAY_FILTER_USE_KEY
		);

		$table_data = array();

		foreach ( $data as $data_key => $data_value ) {
			$sanitized_key = str_replace( $field_key . '_', '', $data_key );

			list( $index, $name ) = explode( '_', $sanitized_key, 2 );

			if ( false === isset( $table_data[ $index ] ) ) {
				$table_data[ $index ] = array();
			}

			$table_data[ $index ][ $name ] = $data_value;
		}

		return $table_data;
	}

	/**
	 * @return bool
	 */
	public function process_admin_options() {
		global $current_section;

		$result = parent::process_admin_options();

		delete_option( 'sksoftware_speedy_for_woocommerce_is_authenticated' );

		$this->api_client = Sksoftware_Speedy_For_Woocommerce_Api_Client::create( 0 );
		$this->api_client->authenticate();

		if ( false === $this->api_client->is_authenticated() ) {
			$this->add_error(
				__(
					'Unable to authenticate Speedy. Please check your API keys and try again.',
					'sksoftware-speedy-for-woocommerce'
				)
			);

			if ( 'sksoftware_speedy_for_woocommerce' === $current_section ) {
				$this->display_errors();
			}
		}

		$this->init_form_fields();

		return $result;
	}

	/**
	 * Generate table HTML.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public function generate_table_html( $key, $data ) {
		$defaults     = array(
			'title'       => '',
			'description' => '',
			'fields'      => '',
		);
		$data         = wp_parse_args( $data, $defaults );
		$table_fields = $data['fields'];
		$table_data   = $this->get_option( $key, array() );
		$row_template = $this->generate_table_row_html( $key, $table_fields, $table_data );

		ob_start();

		include plugin_dir_path( __DIR__ ) . 'admin/partials/settings_table_html.php';

		return ob_get_clean();
	}

	/**
	 * @param string $table_key
	 * @param array  $table_fields
	 * @param array  $table_data
	 * @param null   $index
	 *
	 * @return false|string
	 */
	public function generate_table_row_html( $table_key, $table_fields, $table_data, $index = null ) {
		ob_start();

		include plugin_dir_path( __DIR__ ) . 'admin/partials/settings_table_row_html.php';

		return ob_get_clean();
	}

	/**
	 * @param string $key
	 * @param mixed  $data
	 * @param string $table_key
	 * @param mixed  $table_data
	 * @param int    $index
	 *
	 * @return string
	 */
	public function generate_table_select_html( $key, $data, $table_key, $table_data, $index ) {
		if ( null === $index ) {
			$field_key = $this->get_field_key( $table_key . '_%index%_' . $key );
		} else {
			$field_key = $this->get_field_key( $table_key . '_' . $index . '_' . $key );
		}

		$field_id = $field_key;

		$defaults = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => 'select',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
			'options'           => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		if ( null === $index ) {
			$value = null;
		} else {
			$value = $table_data[ $index ][ $key ];
		}

		ob_start();

		include plugin_dir_path( __DIR__ ) . 'admin/partials/settings_table_select_html.php';

		return ob_get_clean();
	}

	/**
	 * @param string $key
	 * @param mixed  $data
	 * @param string $table_key
	 * @param mixed  $table_data
	 * @param int    $index
	 *
	 * @return string
	 */
	public function generate_table_decimal_html( $key, $data, $table_key, $table_data, $index ) {
		if ( null === $index ) {
			$field_key = $this->get_field_key( $table_key . '_%index%_' . $key );
		} else {
			$field_key = $this->get_field_key( $table_key . '_' . $index . '_' . $key );
		}

		$field_id = $field_key;

		$defaults = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		if ( null === $index ) {
			$value = null;
		} else {
			$value = $table_data[ $index ][ $key ];
		}

		ob_start();

		include plugin_dir_path( __DIR__ ) . 'admin/partials/settings_table_decimal_html.php';

		return ob_get_clean();
	}

	/**
	 * @return array
	 */
	public function get_settings() {
		if ( ! $this->instance_id ) {
			return $this->settings;
		}

		return array_merge( $this->settings, $this->instance_settings );
	}

	/**
	 * Recursively validate array of texts.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public function recursive_sanitize_text_field( $array ) {
		foreach ( $array as $key => &$value ) {
			if ( is_array( $value ) ) {
				$value = $this->recursive_sanitize_text_field( $value );
			} else {
				$value = sanitize_text_field( $value );
			}
		}

		return $array;
	}

	/**
	 * This function is called when the weight unit is changed.
	 *
	 * @param string $value
	 * @param string $old_value
	 *
	 * @return boolean
	 * @throws \InvalidArgumentException Providing invalid weight throws an exception.
	 */
	public function wc_weight_unit_updated( $value, $old_value ) {
		$conversions = array(
			'g'   => array(
				'kg'  => 1 / 1000,
				'lbs' => 1 / 453.592,
				'oz'  => 1 / 28.3495,
			),
			'kg'  => array(
				'g'   => 1000,
				'lbs' => 2.20462,
				'oz'  => 35.274,
			),
			'lbs' => array(
				'g'  => 453.592,
				'kg' => 1 / 2.20462,
				'oz' => 16,
			),
			'oz'  => array(
				'g'   => 28.3495,
				'kg'  => 1 / 35.274,
				'lbs' => 1 / 16,
			),
		);

		if ( array_key_exists( $old_value, $conversions ) && array_key_exists( $value, $conversions[ $old_value ] ) ) {
			$factor = $conversions[ $old_value ][ $value ];
			$this->instance_settings['default_product_weight'] = (string) ( $this->get_instance_option( 'default_product_weight' ) * $factor );
		} else {
			throw new \InvalidArgumentException( 'Invalid weight unit.' );
		}

		return update_option(
			$this->get_instance_option_key(),
			apply_filters(
				'woocommerce_shipping_' . $this->id . '_instance_settings_values',
				$this->instance_settings,
				$this
			),
			'yes'
		);
	}

	/**
	 * This function is called when the dimension unit is changed.
	 *
	 * @param string $value
	 * @param string $old_value
	 *
	 * @return boolean
	 * @throws \InvalidArgumentException Providing invalid dimension throws an exception.
	 */
	public function wc_dimension_unit_updated( $value, $old_value ) {
		$conversions = array(
			'mm' => array(
				'cm' => 1 / 10,
				'm'  => 1 / 1000,
				'in' => 1 / 25.4,
				'yd' => 1 / 914.4,
			),
			'cm' => array(
				'mm' => 10,
				'm'  => 1 / 100,
				'in' => 1 / 2.54,
				'yd' => 1 / 91.44,
			),
			'm'  => array(
				'mm' => 1000,
				'cm' => 100,
				'in' => 1 / 0.0254,
				'yd' => 1 / 0.9144,
			),
			'in' => array(
				'mm' => 25.4,
				'cm' => 2.54,
				'm'  => 0.0254,
				'yd' => 1 / 36,
			),
			'yd' => array(
				'mm' => 914.4,
				'cm' => 91.44,
				'm'  => 0.9144,
				'in' => 36,
			),
		);

		if ( array_key_exists( $old_value, $conversions ) && array_key_exists( $value, $conversions[ $old_value ] ) ) {
			$factor                                        = $conversions[ $old_value ][ $value ];
			$this->instance_settings['default_box_height'] = (string) ( $this->get_instance_option( 'default_box_height' ) * $factor );
			$this->instance_settings['default_box_width']  = (string) ( $this->get_instance_option( 'default_box_width' ) * $factor );
			$this->instance_settings['default_box_length'] = (string) ( $this->get_instance_option( 'default_box_length' ) * $factor );
		} else {
			throw new \InvalidArgumentException( 'Invalid dimension unit.' );
		}

		return update_option( $this->get_instance_option_key(), apply_filters( 'woocommerce_shipping_' . $this->id . '_instance_settings_values', $this->instance_settings, $this ), 'yes' );
	}
}
