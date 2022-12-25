<?php

namespace WGPayuVendor;

if (!\defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
?>
<form method="POST">
    <table class="wpdesk_helper_key_table">
        <tr>
            <td><?php 
\esc_html(\__('Key:', 'wp-wpdesk-license'));
?></td>
            <td><input class="wpdesk_helper_input" name="api_key" type="text"
                       value="<?php 
echo \esc_attr($api_key);
?>" <?php 
echo \esc_attr($disabled);
?> /></td>
        </tr>
        <tr>
            <td><?php 
\esc_html(\__('Email:', 'wp-wpdesk-license'));
?></td>
            <td><input class="wpdesk_helper_input" name="activation_email" type="email"
                       value="<?php 
echo \esc_attr($activation_email);
?>" <?php 
echo \esc_attr($disabled);
?> /></td>
        </tr>
        <tr>
            <td></td>
            <td>
				<?php 
if ($activation_status == 'Deactivated') {
    ?>
                    <button class="wpdesk_helper_button button button-primary"><?php 
    echo \esc_html(\__('Activate', 'wp-wpdesk-license'));
    ?></button>
				<?php 
} else {
    ?>
                    <button class="wpdesk_helper_button button"><?php 
    echo \esc_html(\__('Deactivate', 'wp-wpdesk-license'));
    ?></button>
				<?php 
}
?>
            </td>
        </tr>
    </table>
    <input type="hidden" name="plugin" value="<?php 
echo \esc_attr($plugin);
?>"/>
	<?php 
if ($activation_status == 'Deactivated') {
    ?>
        <input type="hidden" name="action" value="activate"/>
	<?php 
} else {
    ?>
        <input type="hidden" name="action" value="deactivate"/>
	<?php 
}
?>
</form>
<?php 
