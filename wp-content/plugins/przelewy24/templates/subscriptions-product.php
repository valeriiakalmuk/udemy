<?php
/**
 * Template to edit subscription.
 *
 * @package Przelewy24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $files ) ) {
	throw new LogicException( 'The variable $files is not set.' );
}

?>
<div id='p24_subscription_options' class='panel woocommerce_options_panel'>
	<div class='options_group'>
		<?php

		woocommerce_wp_text_input(
			array(
				'id'          => '_subscription_price',
				'label'       => __( 'Price' ),
				'placeholder' => '',
				'desc_tip'    => 'true',
				'description' => __( 'Enter Subscription Price.' ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_days',
				'label'       => __( 'Days' ),
				'placeholder' => '',
				'desc_tip'    => 'true',
				'description' => __( 'Enter Subcription Length in Days.' ),
			)
		);
		?>
	</div>


	<div class="downloadable downloadable_files">
		<table class="widefat">

		<thead>
		<tr>
			<th class="sort">&nbsp;</th>
			<th><?php esc_html_e( 'Name', 'woocommerce' ); ?></th>
			<th colspan="2"><?php esc_html_e( 'File URL', 'woocommerce' ); ?></th>
			<th>&nbsp;</th>
		</tr>
		</thead>
		<tbody>

		<?php if ( $files ) : ?>
			<?php foreach ( $files as $file ) : ?>
				<?php require __DIR__ . '/subscriptions-product-file.php'; ?>
		<?php endforeach; ?>
		<?php else : ?>
			<?php
			$file = array(
				'name' => '',
				'url'  => '',
			);
			?>
			<?php require __DIR__ . '/subscriptions-product-file.php'; ?>
		<?php endif; ?>

		</tbody>
		<tfoot>
		<tr>
			<th colspan="5">
				<?php
					$file = array(
						'name' => '',
						'url'  => '',
					);
					ob_start();
					require __DIR__ . '/subscriptions-product-file.php';
					$content = ob_get_clean();
					?>
				<a href="#" class="button insert" data-row="<?php echo esc_attr( $content ); ?>">
					<?php echo esc_html( __( 'Add File', 'woocommerce' ) ); ?>
				</a>
			</th>
		</tr>
		</tfoot>
		</table>


	</div>




</div>
<?php

