<?php
/**
 * Recalculate Speedy shipping order action functionality.
 *
 * @link       https://sk-soft.net
 * @since      1.0.0
 *
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/admin
 */

/**
 * Recalculate Speedy shipping order action functionality.
 *
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/admin
 * @author     SK Software <office@sk-soft.net>
 */
class Sksoftware_Speedy_For_Woocommerce_Recalculate_Shipping_Order_Action {
	/**
	 * The order utilities class.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Sksoftware_Speedy_For_Woocommerce_Order_Utilities $order_utilities The order utilities class.
	 */
	protected $order_utilities;

	/**
	 * @param Sksoftware_Speedy_For_Woocommerce_Order_Utilities $order_utilities The order utilities class.
	 */
	public function __construct( $order_utilities ) {
		$this->order_utilities = $order_utilities;
	}

	/**
	 * This filter adds the Recalculate Speedy shipping action to order actions
	 * which have speedy as shipping method.
	 *
	 * @param array $actions
	 *
	 * @return array
	 */
	public function add_action( $actions ) {
		$order = wc_get_order();

		if ( false === $this->order_utilities->get_is_valid_shipping_method( $order ) ) {
			return $actions;
		}

		if ( 'yes' === $order->get_meta( '_sksoftware_speedy_for_woocommerce_shipment_created' ) ) {
			return $actions;
		}

		$actions['sksoftware_speedy_for_woocommerce_recalculate_shipping_order_action'] = __( 'Recalculate Speedy shipping', 'sksoftware-speedy-for-woocommerce' );

		return $actions;
	}

	/**
	 * This action handles the Recalculate Speedy shipping action.
	 *
	 * @param WC_Order $order
	 */
	public function handle_action( $order ) {
		if ( false === $this->order_utilities->get_is_valid_shipping_method( $order ) ) {
			return;
		}

		$instance_id       = $this->order_utilities->get_speedy_shipping_method_instance( $order );
		$speedy_api_client = Sksoftware_Speedy_For_Woocommerce_Api_Client::create( $instance_id );
		$price             = $speedy_api_client->calculate_shipping_for_order( $order );
		$shipping_methods  = $order->get_shipping_methods();

		/** @var WC_Order_Item_Shipping $shipping_method */
		foreach ( $shipping_methods as $shipping_method ) {
			if ( 'sksoftware_speedy_for_woocommerce' === $shipping_method->get_method_id() ) {
				$shipping_method->set_total( $price );
				$shipping_method->save();
			}
		}

		$order->calculate_shipping();

		$order->add_order_note( __( 'Speedy shipping recalculated.', 'sksoftware-speedy-for-woocommerce' ), 0, true );
	}
}
