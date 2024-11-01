<?php
/**
 * Print Speedy shipment label order action functionality.
 *
 * @link       https://sk-soft.net
 * @since      1.0.0
 *
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/admin
 */

/**
 * Print Speedy shipment label order action functionality.
 *
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/admin
 * @author     SK Software <office@sk-soft.net>
 */
class Sksoftware_Speedy_For_Woocommerce_Print_Shipment_Label_Order_Action {
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
	 * This action handles the Print Speedy shipment label action.
	 *
	 * @return void
	 */
	public function handle_action() {
		$order_id = filter_input( INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT );

		check_ajax_referer( 'sksoftware_speedy_for_woocommerce_print_shipment_label_' . $order_id, true );

		if ( ! $order_id ) {
			wp_die( esc_html__( 'Order ID is missing.', 'sksoftware-speedy-for-woocommerce' ) );
		}

		$order = wc_get_order( $order_id );

		if ( false === $this->order_utilities->get_is_valid_shipping_method( $order ) ) {
			wp_die( esc_html__( 'Order shipping method must be Speedy.', 'sksoftware-speedy-for-woocommerce' ) );
		}

		if ( false === $this->order_utilities->get_has_shipping_label( $order ) ) {
			wp_die( esc_html__( 'Order must have a shipping label.', 'sksoftware-speedy-for-woocommerce' ) );
		}

		$instance_id       = $this->order_utilities->get_speedy_shipping_method_instance( $order );
		$speedy_api_client = Sksoftware_Speedy_For_Woocommerce_Api_Client::create( $instance_id );

		$response = $speedy_api_client->print_shipment_label( $order );

		if ( 'application/pdf' !== $response['headers']['content-type'] || 200 !== $response['response']['code'] ) {
			wp_die( esc_html__( 'Failed to print shipment label.', 'sksoftware-speedy-for-woocommerce' ) );
		}

		header( 'Content-type:' . $response['headers']['content-type'] );
		header( 'Content-Disposition:inline' );

		echo $response['body']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		die;
	}
}
