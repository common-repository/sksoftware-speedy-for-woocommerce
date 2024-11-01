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
 * The order utilities class.
 *
 * This is used to define common functions used to access
 * order data across the codebase.
 *
 * @since      1.0.0
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/includes
 * @author     SK Software <office@sk-soft.net>
 */
class Sksoftware_Speedy_For_Woocommerce_Order_Utilities {
	/**
	 * Checks if an order is chosen to be shipped with Speedy
	 *
	 * @param WC_Order $order
	 *
	 * @return bool
	 */
	public function get_is_valid_shipping_method( $order ) {
		$shipping_methods = $order->get_shipping_methods();

		/** @var WC_Order_Item_Shipping $shipping_method */
		foreach ( $shipping_methods as $shipping_method ) {
			if ( 'sksoftware_speedy_for_woocommerce' === $shipping_method->get_method_id() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get shipping method instance
	 *
	 * @param WC_Order $order
	 *
	 * @return string|null
	 */
	public function get_speedy_shipping_method_instance( $order ) {
		$shipping_methods = $order->get_shipping_methods();

		/** @var WC_Order_Item_Shipping $shipping_method */
		foreach ( $shipping_methods as $shipping_method ) {
			if ( 'sksoftware_speedy_for_woocommerce' === $shipping_method->get_method_id() ) {
				return $shipping_method->get_instance_id();
			}
		}

		return null;
	}

	/**
	 * Determine if the order can have a shipping label based on
	 * if the shipment is created and not deleted.
	 *
	 * @param WC_Order $order
	 *
	 * @return bool
	 */
	public function get_has_shipping_label( $order ) {
		$is_created = 'yes' === $order->get_meta( '_sksoftware_speedy_for_woocommerce_shipment_created' );
		$is_deleted = 'yes' === $order->get_meta( '_sksoftware_speedy_for_woocommerce_shipment_deleted' );

		return $is_created && false === $is_deleted;
	}

	/**
	 * Get the shipment label URL for an order id.
	 *
     * @param int $id Order ID.
	 *
     * @return string
	 */
	public function get_print_label_url( $id ) {
		return wp_nonce_url(
			admin_url( 'admin-ajax.php?action=sksoftware_speedy_for_woocommerce_print_shipment_label&order_id=' . $id ),
			'sksoftware_speedy_for_woocommerce_print_shipment_label_' . $id
		);
	}

	/**
	 * Create shipment for an order.
	 *
     * @param WC_Order $order
	 *
     * @return void
	 */
	public function create_shipment( $order ) {
		$instance_id = $this->get_speedy_shipping_method_instance( $order );

		$speedy_api_client = Sksoftware_Speedy_For_Woocommerce_Api_Client::create( $instance_id );
		$data              = $speedy_api_client->create_shipment( $order );
		$shipment_id       = sanitize_text_field( $data['id'] );

		/* translators: %s: Shipment number */
		$order->add_order_note( __( 'Speedy shipment created.', 'sksoftware-speedy-for-woocommerce' ) . ' ' . sprintf( __( 'Shipment ID %s.', 'sksoftware-speedy-for-woocommerce' ), $shipment_id ), 0, true );

		$order->update_meta_data( '_sksoftware_speedy_for_woocommerce_shipment_id', $shipment_id );
		$order->update_meta_data( '_sksoftware_speedy_for_woocommerce_shipment_created', 'yes' );
		$order->update_meta_data( '_sksoftware_speedy_for_woocommerce_shipment_deleted', 'no' );
		$order->save();
	}
}
