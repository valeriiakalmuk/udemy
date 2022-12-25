<?php
/**
 * Template with input for files.
 *
 * @package Przelewy24
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $file ) ) {
	throw new LogicException( 'The variable $file is not set.' );
}

?>
		<tr>
			<td class="sort"></td>
			<td class="file_name">
				<input type="text" class="input_text" placeholder="<?php esc_attr_e( 'File name', 'woocommerce' ); ?>" name="_p24_sub_file_names[]" value="<?php echo esc_attr( $file['name'] ); ?>" />
			</td>
			<td class="file_url"><input type="text" class="input_text" placeholder="<?php esc_attr_e( 'http://', 'woocommerce' ); ?>" name="_p24_sub_file_urls[]" value="<?php echo esc_attr( $file['url'] ); ?>" /></td>
			<td class="file_url_choose" width="1%"><a href="#" class="button upload_file_button" data-choose="<?php esc_attr_e( 'Choose file', 'woocommerce' ); ?>" data-update="<?php esc_attr_e( 'Insert file URL', 'woocommerce' ); ?>"><?php echo esc_html__( 'Choose file', 'woocommerce' ); ?></a></td>
			<td width="1%"><a href="#" class="delete"><?php esc_html_e( 'Delete', 'woocommerce' ); ?></a></td>
		</tr>

<?php

