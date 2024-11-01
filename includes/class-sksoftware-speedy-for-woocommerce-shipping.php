<?php
/**
 * Fired to define woocommerce shipping unit.
 *
 * @link       https://sk-soft.net
 * @since      1.0.0
 *
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/includes
 */

/**
 * Fired to define woocommerce shipping unit.
 *
 * This class defines all code necessary to register woocommerce shipping unit.
 *
 * @since      1.0.0
 * @package    Sksoftware_Speedy_For_Woocommerce
 * @subpackage Sksoftware_Speedy_For_Woocommerce/includes
 * @author     SK Software <office@sk-soft.net>
 */
class Sksoftware_Speedy_For_Woocommerce_Shipping {
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function init() {
		if ( ! class_exists( 'Sksoftware_Speedy_For_Woocommerce_Shipping_Method' ) ) {
			/**
			 * The class responsible for defining the shipping unit for woocommerce.
			 */
			require_once plugin_dir_path( __DIR__ ) . 'includes/class-sksoftware-speedy-for-woocommerce-shipping-method.php';
		}
	}

	/**
	 * This filter is used to add the Speedy shipping method.
	 *
	 * @param array $methods
	 *
	 * @return array
	 */
	public function add_shipping_method( $methods ) {
		$methods['sksoftware_speedy_for_woocommerce'] = 'Sksoftware_Speedy_For_Woocommerce_Shipping_Method';

		return $methods;
	}
}
