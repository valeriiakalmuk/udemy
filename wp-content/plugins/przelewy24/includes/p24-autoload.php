<?php
/**
 * Autoload classes of Przelewy24 Plugin.
 *
 * The class has 3 requirements:
 * 1. The class should start (case insensitive) from ‘P24_’.
 * 2. The file has to reside in includes directory.
 * 3. The file should be prefixed with ‘class-’.
 * 4. The extension should be ‘php’.
 * 5. The underscores should be replaced by hyphens.
 */

defined( 'ABSPATH' ) || exit;

spl_autoload_register(
	function( $class ) {
		if ( preg_match( '/^p24_[a-z][a-z0-9\\_]+/i', $class ) ) {
			$normalised = strtolower( strtr( $class, array( '_' => '-' ) ) );
			$directories = array(
				__DIR__,
				__DIR__ . '/subscription',
				__DIR__ . '/report-data-stores',
				__DIR__ . '/rest_api',
				__DIR__ . '/multi_currency',
			);
			foreach ( $directories as $directory ) {
				$path_class = $directory . '/class-' . $normalised . '.php';
				if ( is_readable( $path_class ) ) {
					require_once $path_class;

					return;
				}
			}
		}
	}
);
