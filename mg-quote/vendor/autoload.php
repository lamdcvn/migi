<?php
// Minimal PSR-4 autoloader for the bundled dompdf (no Composer needed on the host).
defined( 'ABSPATH' ) || exit;
spl_autoload_register( function ( $class ) {
	static $map = null;
	if ( null === $map ) {
		$d   = __DIR__;
		$map = array(
			'Dompdf\\Cpdf'      => $d . '/dompdf/lib/Cpdf.php',
			'Dompdf\\'          => $d . '/dompdf/src/',
			'FontLib\\'         => $d . '/php-font-lib/src/FontLib/',
			'Svg\\'             => $d . '/php-svg-lib/src/Svg/',
			'Masterminds\\'     => $d . '/html5-php/src/',
			'Sabberworm\\CSS\\' => $d . '/php-css-parser/src/',
		);
	}
	if ( isset( $map[ $class ] ) && substr( $map[ $class ], -4 ) === '.php' ) {
		require $map[ $class ];
		return;
	}
	foreach ( $map as $prefix => $dir ) {
		if ( '\\' === substr( $prefix, -1 ) && 0 === strpos( $class, $prefix ) ) {
			$file = $dir . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
			if ( is_file( $file ) ) {
				require $file;
			}
			return;
		}
	}
} );
