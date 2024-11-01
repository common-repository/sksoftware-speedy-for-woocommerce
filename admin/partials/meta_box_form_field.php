<?php // phpcs:ignore Squiz.Commenting.FileComment.Missing
use Automattic\WooCommerce\Utilities\I18nUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

$field_id   = '_sksoftware-speedy-for-woocommerce-form-field-' . str_replace( '_', '-', $key );
$field_name = '_sksoftware_speedy_for_woocommerce_form_field_' . $key;
?>

<?php if ( 'hidden' !== $field_type ) : ?>
<p class="form-field">
    <label for="<?php echo esc_attr( $field_id ); ?>">
		<?php
        $weight_unit_label    = I18nUtil::get_weight_unit_label( get_option( 'woocommerce_weight_unit', 'kg' ) );
        $dimension_unit_label = I18nUtil::get_dimensions_unit_label( get_option( 'woocommerce_dimension_unit', 'cm' ) );

		$labels = array(
			'weight'                           => sprintf(
			/* translators: %s: weight unit */
				__( 'Weight (in %s)', 'sksoftware-speedy-for-woocommerce' ),
				$weight_unit_label
			),
			'height'                           => sprintf(
			/* translators: %s: dimension unit */
				__( 'Height (in %s)', 'sksoftware-speedy-for-woocommerce' ),
				$dimension_unit_label,
			),
			'width'                            => sprintf(
			/* translators: %s: dimension unit */
				__( 'Width (in %s)', 'sksoftware-speedy-for-woocommerce' ),
				$dimension_unit_label
			),
			'length'                           => sprintf(
			    /* translators: %s: dimension unit */
				__( 'Length (in %s)', 'sksoftware-speedy-for-woocommerce' ),
				$dimension_unit_label
			),
			'delivery_type'                    => __( 'Delivery type', 'sksoftware-speedy-for-woocommerce' ),
			'billing_sksoftware_speedy_office' => __( 'Office', 'sksoftware-speedy-for-woocommerce' ),
			'billing_sksoftware_speedy_apt'    => __( 'APT', 'sksoftware-speedy-for-woocommerce' ),
			'recipient_name'                   => __( 'Recipient Name', 'sksoftware-speedy-for-woocommerce' ),
			'recipient_email'                  => __( 'Recipient Email', 'sksoftware-speedy-for-woocommerce' ),
			'recipient_postal_code'            => __( 'Recipient Postal Code', 'sksoftware-speedy-for-woocommerce' ),
			'recipient_city'                   => __( 'Recipient City', 'sksoftware-speedy-for-woocommerce' ),
			'recipient_address_line_1'         => __( 'Recipient Address Line 1', 'sksoftware-speedy-for-woocommerce' ),
			'recipient_address_line_2'         => __( 'Recipient Address Line 2', 'sksoftware-speedy-for-woocommerce' ),
			'recipient_address_number'         => __( 'Recipient Address Number', 'sksoftware-speedy-for-woocommerce' ),
			'recipient_phone'                  => __( 'Recipient Phone', 'sksoftware-speedy-for-woocommerce' ),
			'recipient_vat_number'             => __( 'Recipient Vat Number', 'sksoftware-speedy-for-woocommerce' ),
			'speedy_product'                   => __( 'Delivery Product', 'sksoftware-speedy-for-woocommerce' ),
			'content'                          => __( 'Content', 'sksoftware-speedy-for-woocommerce' ),
			'is_fragile'                       => __( 'Is fragile?', 'sksoftware-speedy-for-woocommerce' ),
			'is_declared_amount'               => __( 'Declare amount?', 'sksoftware-speedy-for-woocommerce' ),
			'declared_amount'                  => __( 'Declared amount', 'sksoftware-speedy-for-woocommerce' ),
			'is_cash_on_delivery'              => __( 'Cash on delivery?', 'sksoftware-speedy-for-woocommerce' ),
			'cod_amount'                       => __( 'Cash on delivery amount', 'sksoftware-speedy-for-woocommerce' ),
			'delivery_payee'                   => __( 'Delivery payee', 'sksoftware-speedy-for-woocommerce' ),
			'return_shipping'                  => __( 'Return payee', 'sksoftware-speedy-for-woocommerce' ),
			'open_test_before_payment'         => __(
				'Open or test before paying?',
				'sksoftware-speedy-for-woocommerce'
			),
			'saturday_delivery'                => __( 'Saturday delivery?', 'sksoftware-speedy-for-woocommerce' ),
			'package_type'                     => __( 'Package type', 'sksoftware-speedy-for-woocommerce' ),
			'cod_processing_type'              => __( 'CoD processing type', 'sksoftware-speedy-for-woocommerce' ),
		);

		if ( isset( $labels[ $key ] ) ) {
			echo esc_html( $labels[ $key ] );
		} else {
			echo esc_html__( 'N/A', 'sksoftware-speedy-for-woocommerce' );
		}
		?>
    </label>
	<?php endif; ?>

	<?php

	$tips = array(
		'content'  => __( 'Shipment content (items).', 'sksoftware-speedy-for-woocommerce' ),
		'quantity' => __( 'Shipment item quantity.', 'sksoftware-speedy-for-woocommerce' ),
	);

	if ( isset( $tips[ $key ] ) ) {
		?>
        <span class="woocommerce-help-tip" data-tip="<?php echo esc_attr( $tips[ $key ] ); ?>"></span>
	<?php } ?>
	<?php if ( 'choice' === $field_type ) : ?>
        <select
            class="short <?php echo ( 'recipient_country' === $key || 'country_origin' === $key ) ? 'wc-enhanced-select' : 'select'; ?>"
            name="<?php echo esc_attr( $field_name ); ?>"
            id="<?php echo esc_attr( $field_id ); ?>"
			<?php if ( $disabled ) : ?>
                disabled
			<?php endif ?>

        >
			<?php foreach ( $field_choices as $choice_key => $choice_value ) : ?>
				<?php $is_selected = (string) $value === (string) $choice_key; ?>
                <option value="<?php echo esc_attr( $choice_key ); ?>" <?php echo $is_selected ? 'selected' : ''; ?>>
					<?php echo esc_html( $choice_value ); ?>
                </option>
			<?php endforeach; ?>
        </select>
	<?php elseif ( 'hidden' === $field_type ) : ?>
        <input
            type="hidden"
            name="<?php echo esc_attr( $field_name ); ?>"
            id="<?php echo esc_attr( $field_id ); ?>"
			<?php if ( $disabled ) : ?>
                disabled
			<?php endif ?>
        >
	<?php else : ?>
        <input
            type="<?php echo esc_attr( $field_type ); ?>"
            class="short"
            name="<?php echo esc_attr( $field_name ); ?>"
            id="<?php echo esc_attr( $field_id ); ?>"
            value="<?php echo esc_attr( (string) $value ); ?>"
			<?php if ( 'number' === $field_type ) : ?>
                step="any"
			<?php endif; ?>
			<?php if ( $disabled ) : ?>
                disabled
			<?php endif; ?>
        >
	<?php endif; ?>

	<?php if ( 'hidden' !== $field_type ) : ?>
</p>
<?php endif; ?>
