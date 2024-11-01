<?php // phpcs:ignore Squiz.Commenting.FileComment.Missing
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

if ( empty( $data ) ) : ?>
	<div style="margin-top: 12px;"></div>

	<p style="padding-left: 12px; padding-right: 12px; margin-bottom: 12px !important;">
		<?php echo esc_html__( 'Unable to connect to Speedy API.', 'sksoftware-speedy-for-woocommerce' ); ?>
	</p>
<?php else : ?>
	<div class="panel-wrap">
		<ul class="wc-tabs">
			<li class="general_options active">
				<a href="#sksoftware_speedy_for_woocommerce_general">
					<span><?php echo esc_html__( 'General', 'sksoftware-speedy-for-woocommerce' ); ?></span>
				</a>
			</li>
			<li class="recipient_options">
				<a href="#sksoftware_speedy_for_woocommerce_recipient">
					<span><?php echo esc_html__( 'Recipient', 'sksoftware-speedy-for-woocommerce' ); ?></span>
				</a>
			</li>
			<li class="shipping_options">
				<a href="#sksoftware_speedy_for_woocommerce_shipping">
					<span><?php echo esc_html__( 'Shipping', 'sksoftware-speedy-for-woocommerce' ); ?></span>
				</a>
			</li>
		</ul>

		<!-- General -->
		<div id="sksoftware_speedy_for_woocommerce_general" class="panel woocommerce_options_panel">
			<div class="options_group">
				<?php
				$this->render_form_field( 'weight', $data['weight'], $is_shipment_created );
				$this->render_form_field( 'height', $data['height'], $is_shipment_created );
				$this->render_form_field( 'width', $data['width'], $is_shipment_created );
				$this->render_form_field( 'length', $data['length'], $is_shipment_created );

				?>
			</div>
		</div>

		<!-- Recipient -->
		<div id="sksoftware_speedy_for_woocommerce_recipient" class="panel woocommerce_options_panel hidden">
			<div class="options_group">
				<?php
				$this->render_form_field( 'delivery_type', $data['delivery_type'], $is_shipment_created );

				$this->render_form_field(
					'billing_sksoftware_speedy_office',
					isset( $data['billing_sksoftware_speedy_office'] ) ? $data['billing_sksoftware_speedy_office'] : '',
					$is_shipment_created
				);
				$this->render_form_field(
					'billing_sksoftware_speedy_office_name',
					isset( $data['billing_sksoftware_speedy_office_name'] ) ? $data['billing_sksoftware_speedy_office_name'] : '',
					$is_shipment_created
				);

				$this->render_form_field(
					'billing_sksoftware_speedy_apt',
					isset( $data['billing_sksoftware_speedy_apt'] ) ? $data['billing_sksoftware_speedy_apt'] : '',
					$is_shipment_created
				);
				$this->render_form_field(
					'billing_sksoftware_speedy_apt_name',
					isset( $data['billing_sksoftware_speedy_apt_name'] ) ? $data['billing_sksoftware_speedy_apt_name'] : '',
					$is_shipment_created
				);

				$this->render_form_field( 'recipient_name', $data['recipient_name'], $is_shipment_created );
				$this->render_form_field( 'recipient_email', $data['recipient_email'], $is_shipment_created );
				$this->render_form_field(
					'recipient_postal_code',
					$data['recipient_postal_code'],
					$is_shipment_created
				);
				$this->render_form_field( 'recipient_city', $data['recipient_city'], $is_shipment_created );
				$this->render_form_field(
					'recipient_address_line_1',
					$data['recipient_address_line_1'],
					$is_shipment_created
				);
				$this->render_form_field(
					'recipient_address_line_2',
					$data['recipient_address_line_2'],
					$is_shipment_created
				);
				$this->render_form_field( 'recipient_phone', $data['recipient_phone'], $is_shipment_created );
				?>
			</div>
		</div>

		<!-- Shipping -->
		<div id="sksoftware_speedy_for_woocommerce_shipping" class="panel woocommerce_options_panel hidden">
			<div class="options_group">
				<?php
				$this->render_form_field( 'delivery_payee', $data['delivery_payee'], $is_shipment_created );
				$this->render_form_field( 'return_shipping', $data['return_shipping'], $is_shipment_created );
				$this->render_form_field( 'speedy_product', $data['speedy_product'], $is_shipment_created );
				$this->render_form_field(
					'open_test_before_payment',
					$data['open_test_before_payment'],
					$is_shipment_created
				);
				$this->render_form_field( 'is_fragile', $data['is_fragile'], $is_shipment_created );
				$this->render_form_field( 'is_declared_amount', $data['is_declared_amount'], $is_shipment_created );
				$this->render_form_field( 'declared_amount', $data['declared_amount'], $is_shipment_created );
				$this->render_form_field( 'is_cash_on_delivery', $data['is_cash_on_delivery'], $is_shipment_created );
				$this->render_form_field( 'cod_amount', $data['cod_amount'], $is_shipment_created );
				$this->render_form_field( 'saturday_delivery', $data['saturday_delivery'], $is_shipment_created );
				$this->render_form_field( 'package_type', $data['package_type'], $is_shipment_created );

				if ( isset( $data['cod_processing_type'] ) ) {
					$this->render_form_field(
						'cod_processing_type',
						$data['cod_processing_type'],
						$is_shipment_created
					);
				}
				$this->render_form_field( 'content', $data['content'], $is_shipment_created );
				?>
			</div>
		</div>

		<div class="clear"></div>
	</div>

	<div style="border-top: 1px solid #dfdfdf; padding: 1.5em 2em; text-align: right; background: #f8f8f8;">
		<?php if ( false === $is_shipment_created ) : ?>
			<span class="woocommerce-help-tip" data-tip="
			<?php
			echo esc_html__(
				'It is strongly advised to change order status back to "Pending payment" when recalculating shipping price. Your client will not be notified about this change.',
				'sksoftware-speedy-for-woocommerce'
			)
			?>
				"></span>

			<button type="button" class="button button-secondary" data-toggle="sksoftware-speedy-for-woocommerce-recalculate-shipping">
				<?php echo esc_html__( 'Recalculate shipping', 'sksoftware-speedy-for-woocommerce' ); ?>
			</button>

			<button type="button" class="button button-primary" data-toggle="sksoftware-speedy-for-woocommerce-shipment-create" style="margin-left: 1.5em;">
				<?php echo esc_html__( 'Create shipment', 'sksoftware-speedy-for-woocommerce' ); ?>
			</button>
		<?php endif; ?>

		<?php
		if ( $is_shipment_created && false === $is_shipment_deleted ) :
			$user_language = strpos( get_user_locale(), 'bg' ) !== false ? 'bg' : 'en';
			?>
			<a href="https://www.speedy.bg/<?php echo esc_attr( $user_language ); ?>/track-shipment?shipmentNumber=<?php echo esc_attr( $shipment_id ); ?>" target="_blank" class="button-link" style="line-height: 2.15384615; min-height: 30px;">
				<?php echo esc_html__( 'Track shipment', 'sksoftware-speedy-for-woocommerce' ); ?>
			</a>

			<a href="https://my.speedy.bg/consignments/view?bol=<?php echo esc_attr( $shipment_id ); ?>" target="_blank" class="button-link" style="line-height: 2.15384615; min-height: 30px;  margin-left: 1.5em;">
				<?php echo esc_html__( 'Review in courier', 'sksoftware-speedy-for-woocommerce' ); ?>
			</a>

			<button type="button" class="button-link button-link-delete" data-toggle="sksoftware-speedy-for-woocommerce-shipment-delete" style="line-height: 2.15384615; min-height: 30px; margin-left: 1.5em;">
				<?php echo esc_html__( 'Delete shipment', 'sksoftware-speedy-for-woocommerce' ); ?>
			</button>

			<a href="<?php echo esc_attr( $print_label_url ); ?>" class="button button-secondary" target="_blank" style="margin-left: 1.5em;">
				<?php echo esc_html__( 'Print label', 'sksoftware-speedy-for-woocommerce' ); ?>
			</a>
		<?php endif ?>
	</div>
<?php endif ?>
