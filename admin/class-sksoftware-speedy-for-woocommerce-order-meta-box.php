<?php
/**
 * Order meta box functionality.
 *
 * @link       https://sk-soft.net
 * @since      1.0.0
 *
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/admin
 */

/**
 * Order meta box functionality.
 *
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/admin
 * @author     SK Software <office@sk-soft.net>
 */
class Sksoftware_Speedy_For_Woocommerce_Order_Meta_Box {
	/**
	 * The order utilities class.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Sksoftware_Speedy_For_Woocommerce_Order_Utilities $order_utilities The order utilities class.
	 */
	protected $order_utilities;

	/**
	 * The data loaded from the API.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array The data loaded from the API.
	 */
	private $data;

	/**
	 * @param Sksoftware_Speedy_For_Woocommerce_Order_Utilities $order_utilities The order utilities class.
	 */
	public function __construct( $order_utilities ) {
		$this->order_utilities = $order_utilities;
	}

	/**
	 * Add the meta box.
	 */
	public function add_meta_box() {
		$order = wc_get_order();

		if ( ! $order ) {
			return;
		}

		if ( false === $this->order_utilities->get_is_valid_shipping_method( $order ) ) {
			return;
		}

		$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
			? wc_get_page_screen_id( 'shop-order' )
			: 'shop_order';

		add_meta_box(
			'sksoftware-speedy-for-woocommerce',
			'Speedy' . wc_help_tip(
				__(
					'Settings for your Speedy shipment.',
					'sksoftware-speedy-for-woocommerce'
				)
			),
			array(
				$this,
				'meta_box_output',
			),
			$screen,
			'normal',
			'low'
		);
	}

	/**
	 * Handle meta box save.
	 *
	 * @param int $post_id
	 */
	public function handle_meta_box_save( $post_id ) {
		$order = wc_get_order( $post_id );

		if ( false === $this->order_utilities->get_is_valid_shipping_method( $order ) ) {
			return;
		}

		$is_shipment_created = 'yes' === $order->get_meta( '_sksoftware_speedy_for_woocommerce_shipment_created' );

		if ( $is_shipment_created ) {
			return;
		}

		$data = array();

		foreach ( $_POST as $key => $value ) {
			if ( 0 !== strpos( $key, '_sksoftware_speedy_for_woocommerce_form_field' ) ) {
				continue;
			}

			$meta_key          = sanitize_key(
				str_replace(
					'_sksoftware_speedy_for_woocommerce_form_field_',
					'',
					$key
				)
			);
			$data[ $meta_key ] = sanitize_text_field( $value );
		}

		$order->update_meta_data( '_sksoftware_speedy_for_woocommerce_shipment_parameters_override', $data );
		$order->save();
	}

	/**
	 * Output of the meta box.
	 *
	 * @param WP_Post|WC_Order $post_or_order_object
	 */
	public function meta_box_output( $post_or_order_object ) {
		$order = $post_or_order_object instanceof WP_Post ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

		if ( false === $this->order_utilities->get_is_valid_shipping_method( $order ) ) {
			wp_die( esc_html__( 'Order shipping method is not Speedy.', 'sksoftware-speedy-for-woocommerce' ) );
		}

		$is_shipment_created = 'yes' === $order->get_meta( '_sksoftware_speedy_for_woocommerce_shipment_created' );
		$is_shipment_deleted = 'yes' === $order->get_meta( '_sksoftware_speedy_for_woocommerce_shipment_deleted' );
		$shipment_id         = $order->get_meta( '_sksoftware_speedy_for_woocommerce_shipment_id' );

		if ( $this->order_utilities->get_has_shipping_label( $order ) ) {
			$print_label_url = $this->order_utilities->get_print_label_url( $order->get_id() );
		}

		$instance_id       = $this->order_utilities->get_speedy_shipping_method_instance( $order );
		$speedy_api_client = Sksoftware_Speedy_For_Woocommerce_Api_Client::create( $instance_id );

		$this->data = $speedy_api_client->get_shipment_parameters( $order );

		$data = $this->data;

		ob_start();

		include plugin_dir_path( __DIR__ ) . 'admin/partials/meta_box_output.php';

		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @param bool   $disabled
	 */
	private function render_form_field( // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		$key,
		// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		$value,
		$disabled
	) {
		$field_type = $this->get_field_type( $key );

		if ( 'choice' === $field_type ) {
			$field_choices = $this->get_field_choices( $key );
		}

		include plugin_dir_path( __DIR__ ) . 'admin/partials/meta_box_form_field.php';
	}

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	private function get_field_type( $key ) {
		if ( in_array(
			$key,
			array(
				'weight',
				'height',
				'width',
				'length',
				'quantity',
				'declared_amount',
				'cod_amount',
			),
			true
		) ) {
			return 'number';
		}

		if ( in_array(
			$key,
			array(
				'speedy_product',
				'delivery_type',
				'package_type',
				'billing_sksoftware_speedy_office',
				'billing_sksoftware_speedy_apt',
				'delivery_payee',
				'return_shipping',
				'is_fragile',
				'is_declared_amount',
				'is_cash_on_delivery',
				'open_test_before_payment',
				'saturday_delivery',
				'cod_processing_type',
			),
			true
		) ) {
			return 'choice';
		}

		if ( in_array(
			$key,
			array(
				'billing_sksoftware_speedy_office_name',
				'billing_sksoftware_speedy_apt_name',
			),
			true
		) ) {
			return 'hidden';
		}

		return 'text';
	}

	/**
	 * @param string $key
	 *
	 * @return array|string[]
	 */
	private function get_field_choices( $key ) {
		if ( 'speedy_product' === $key ) {
			return array(
				'505' => __( 'Standard 24 hours', 'sksoftware-speedy-for-woocommerce' ),
			);
		}

		if ( 'is_fragile' === $key ) {
			return array(
				'false' => __( 'No', 'sksoftware-speedy-for-woocommerce' ),
				'true'  => __( 'Yes', 'sksoftware-speedy-for-woocommerce' ),
			);
		}

		if ( 'package_type' === $key ) {
			return array(
				'CARTON BOX'      => __( 'Carton box', 'sksoftware-speedy-for-woocommerce' ), // кашон.
				'ENVELOPE'        => __( 'Envelope', 'sksoftware-speedy-for-woocommerce' ), // ПЛИК.
				'BAG'             => __( 'Bag', 'sksoftware-speedy-for-woocommerce' ), // ЧУВАЛ.
				'BOX'             => __( 'Box', 'sksoftware-speedy-for-woocommerce' ), // КУТИЯ.
				'BOX IN ENVELOPE' => __( 'Box in envelope', 'sksoftware-speedy-for-woocommerce' ), // КУТИЯ В ПЛИК.
				'STRETCH FOIL'    => __( 'Stretch foil', 'sksoftware-speedy-for-woocommerce' ), // СТРЕЧ.
				'BUBBLE FOI'      => __( 'Bubble foil', 'sksoftware-speedy-for-woocommerce' ), // ФОЛИО.
				'NYLON'           => __( 'Nylon', 'sksoftware-speedy-for-woocommerce' ), // НАЙЛОН.
			);
		}

		if ( 'is_declared_amount' === $key ) {
			return array(
				'false' => __( 'No', 'sksoftware-speedy-for-woocommerce' ),
				'true'  => __( 'Yes', 'sksoftware-speedy-for-woocommerce' ),
			);
		}

		if ( 'is_cash_on_delivery' === $key ) {
			return array(
				'false' => __( 'No', 'sksoftware-speedy-for-woocommerce' ),
				'true'  => __( 'Yes', 'sksoftware-speedy-for-woocommerce' ),
			);
		}

		if ( 'delivery_type' === $key ) {
			return array(
				'office'  => __( 'Deliver to Office', 'sksoftware-speedy-for-woocommerce' ),
				'address' => __( 'Deliver to Address', 'sksoftware-speedy-for-woocommerce' ),
				'apt'     => __( 'Deliver to APT', 'sksoftware-speedy-for-woocommerce' ),
			);
		}

		if ( 'billing_sksoftware_speedy_office' === $key ) {
			$choices = array();

			if ( ! empty( $this->data['billing_sksoftware_speedy_office'] ) && ! empty( $this->data['billing_sksoftware_speedy_office_name'] ) ) {
				$choices[] = array(
					$this->data['billing_sksoftware_speedy_office'] => $this->data['billing_sksoftware_speedy_office_name'],
				);
			}

			if ( ! isset( $choices[0] ) ) {
				return array(
					'' => __( 'Please choose', 'sksoftware-speedy-for-woocommerce' ),
				);
			}

			return wc_array_merge_recursive_numeric(
				array(
					'' => __( 'Please choose', 'sksoftware-speedy-for-woocommerce' ),
				),
				$choices[0]
			);
		}

		if ( 'billing_sksoftware_speedy_apt' === $key ) {
			$choices = array();

			if ( ! empty( $this->data['billing_sksoftware_speedy_apt'] ) && ! empty( $this->data['billing_sksoftware_speedy_apt_name'] ) ) {
				$choices[] = array(
					$this->data['billing_sksoftware_speedy_apt'] => $this->data['billing_sksoftware_speedy_apt_name'],
				);
			}

			if ( ! isset( $choices[0] ) ) {
				return array(
					'' => __( 'Please choose', 'sksoftware-speedy-for-woocommerce' ),
				);
			}

			return wc_array_merge_recursive_numeric(
				array(
					'' => __( 'Please choose', 'sksoftware-speedy-for-woocommerce' ),
				),
				$choices[0]
			);
		}

		if ( 'delivery_payee' === $key ) {
			return array(
				'RECIPIENT' => __( 'Your client will pay', 'sksoftware-speedy-for-woocommerce' ),
				'SENDER'    => __( 'I will pay', 'sksoftware-speedy-for-woocommerce' ),
			);
		}

		if ( 'return_shipping' === $key ) {
			return array(
				'SENDER'    => __( 'Your client will pay', 'sksoftware-speedy-for-woocommerce' ),
				'RECIPIENT' => __( 'I will pay', 'sksoftware-speedy-for-woocommerce' ),
			);
		}

		if ( 'open_test_before_payment' === $key ) {
			return array(
				'NO'   => __( 'No', 'sksoftware-speedy-for-woocommerce' ),
				'OPEN' => __( 'Open', 'sksoftware-speedy-for-woocommerce' ),
				'TEST' => __( 'Open and test', 'sksoftware-speedy-for-woocommerce' ),
			);
		}

		if ( 'saturday_delivery' === $key ) {
			return array(
				'false' => __( 'No', 'sksoftware-speedy-for-woocommerce' ),
				'true'  => __( 'Yes', 'sksoftware-speedy-for-woocommerce' ),
			);
		}

		if ( 'cod_processing_type' === $key ) {
			return array(
				'CASH'                  => __( 'Cash', 'sksoftware-speedy-for-woocommerce' ),
				'POSTAL_MONEY_TRANSFER' => __( 'Postal money transfer', 'sksoftware-speedy-for-woocommerce' ),
			);
		}

		return array();
	}
}
