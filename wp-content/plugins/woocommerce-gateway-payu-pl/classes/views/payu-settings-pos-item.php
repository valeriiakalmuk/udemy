<?php

$defualt_profile = $is_default_profile? '<span class="dashicons dashicons-star-filled payu-tips" data-tip="'.__('Waluta domyślna', 'woocommerce_payu').'"></span>' : '';
$test_mode = $is_test_mode? '<span class="dashicons dashicons-admin-tools payu-tips" data-tip="'.__('Tryb testowy', 'woocommerce_payu').'"></span>' : '';
?>

<h3 data-currency="<?php echo $currency ?>">
	<span style="display: inline-block; width: 350px"><p><?php echo $defualt_profile.' '.$test_mode; ?> <strong><?php echo $currency; ?> </strong></p></span>
	<span style="display: inline-block; width: 80px; text-align: right; float: right"><a href="#" class="remove_payu_profile"><span class="dashicons dashicons-trash"></span></a></span>
</h3>
<div>
	<?php require 'payu-settings-pos-item-form.php'; ?>
</div>
