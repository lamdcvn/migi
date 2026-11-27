<?php
defined( 'ABSPATH' ) || exit;

/** Renders a quote as HTML (web/print) or PDF (bundled dompdf, falls back to print view). */
class MGQ_PDF {

	/** Logo as data URI so dompdf never needs remote access. */
	public static function logo_src( $for_pdf ) {
		$id = (int) MGQ_Settings::get()['logo'];
		if ( ! $id ) {
			return '';
		}
		if ( ! $for_pdf ) {
			return (string) wp_get_attachment_image_url( $id, 'medium' );
		}
		$file = get_attached_file( $id );
		$meta = wp_get_attachment_metadata( $id );
		if ( $file && ! empty( $meta['sizes']['medium']['file'] ) ) {
			$file = path_join( dirname( $file ), $meta['sizes']['medium']['file'] );
		}
		if ( ! $file || ! is_readable( $file ) || filesize( $file ) > 2 * MB_IN_BYTES ) {
			return '';
		}
		$type = wp_check_filetype( $file );
		if ( ! in_array( $type['type'], array( 'image/png', 'image/jpeg', 'image/gif' ), true ) ) {
			return ''; // dompdf without remote/GD extras: keep to safe raster types.
		}
		return 'data:' . $type['type'] . ';base64,' . base64_encode( file_get_contents( $file ) ); // phpcs:ignore -- local file.
	}

	public static function html( array $q, $mode = 'web' ) {
		$s    = MGQ_Settings::get();
		$logo = self::logo_src( 'pdf' === $mode );
		ob_start();
		include MGQ_DIR . 'templates/quote.php';
		return ob_get_clean();
	}

	private static function storage_dir() {
		$up  = wp_upload_dir();
		$dir = trailingslashit( $up['basedir'] ) . 'mg-quote';
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir . '/fonts' );
			file_put_contents( $dir . '/index.php', "<?php // Silence.\n" ); // phpcs:ignore
			file_put_contents( $dir . '/.htaccess', "Deny from all\n" ); // phpcs:ignore
		}
		return $dir;
	}

	/** @return string|WP_Error PDF bytes */
	public static function pdf( array $q ) {
		try {
			require_once MGQ_DIR . 'vendor/autoload.php';
			$dir = self::storage_dir();
			$o   = new \Dompdf\Options();
			$o->set( 'defaultFont', 'DejaVu Sans' );
			$o->set( 'fontDir', MGQ_DIR . 'vendor/dompdf/lib/fonts' );
			$o->set( 'fontCache', $dir . '/fonts' );
			$o->set( 'tempDir', get_temp_dir() );
			$o->set( 'chroot', array( MGQ_DIR ) );
			$o->set( 'isRemoteEnabled', false );
			$o->set( 'isPhpEnabled', false );
			$o->set( 'defaultPaperSize', 'a4' );
			$pdf = new \Dompdf\Dompdf( $o );
			$pdf->loadHtml( self::html( $q, 'pdf' ), 'UTF-8' );
			$pdf->setPaper( 'A4', 'portrait' );
			$pdf->render();
			$canvas = $pdf->getCanvas();
			$canvas->page_text( 515, 815, '{PAGE_NUM}/{PAGE_COUNT}', $pdf->getFontMetrics()->getFont( 'DejaVu Sans' ), 7.5, array( .55, .55, .6 ) );
			return $pdf->output();
		} catch ( \Throwable $e ) {
			return new WP_Error( 'mgq_pdf', $e->getMessage() );
		}
	}

	/** Writes the PDF to a protected file (for email attachments). Caller deletes it. */
	public static function file( array $q ) {
		$bytes = self::pdf( $q );
		if ( is_wp_error( $bytes ) ) {
			return $bytes;
		}
		$path = trailingslashit( self::storage_dir() ) . sanitize_file_name( 'bao-gia-' . $q['number'] . '.pdf' );
		file_put_contents( $path, $bytes ); // phpcs:ignore
		return $path;
	}

	/** Sends the PDF to the browser, or the print view if PDF rendering failed. */
	public static function stream( array $q, $download = true ) {
		$bytes = self::pdf( $q );
		nocache_headers();
		if ( is_wp_error( $bytes ) ) {
			// Fallback: printable page that opens the browser's "Save as PDF" dialog.
			header( 'Content-Type: text/html; charset=utf-8' );
			echo str_replace( '</body>', '<script>window.addEventListener("load",function(){setTimeout(function(){window.print()},300)})</script></body>', self::html( $q, 'web' ) ); // phpcs:ignore -- template escapes.
			exit;
		}
		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: ' . ( $download ? 'attachment' : 'inline' ) . '; filename="' . sanitize_file_name( 'bao-gia-' . $q['number'] . '.pdf' ) . '"' );
		header( 'Content-Length: ' . strlen( $bytes ) );
		echo $bytes; // phpcs:ignore -- binary PDF.
		exit;
	}
}
