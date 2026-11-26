<?php
defined( 'ABSPATH' ) || exit;

class MGPC_Shortcode {

	public static function init() {
		add_shortcode( 'mg_pc_builder', array( __CLASS__, 'render' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register' ) );
	}

	public static function register() {
		wp_register_style( 'mgpc', MGPC_URL . 'assets/css/builder.css', array( 'mg-ui' ), MGPC_VERSION );
		wp_register_script( 'mgpc', MGPC_URL . 'assets/js/builder.js', array( 'jquery' ), MGPC_VERSION, true );
	}

	public static function config() {
		$s     = MGPC_Settings::get();
		$slots = array();
		foreach ( MGPC_Settings::slots() as $k => $slot ) {
			$slots[] = array( 'key' => $k, 'label' => $slot['label'], 'role' => $slot['role'], 'icon' => $slot['icon'], 'required' => (bool) $slot['required'], 'multi' => (bool) $slot['multi'] );
		}
		return array(
			'endpoint' => WC_AJAX::get_endpoint( '%%endpoint%%' ),
			'slots'    => $slots,
			'presets'  => MGPC_Presets::all(),
			'compat'   => 'yes' === $s['compat_filter'],
			'consult'  => 'yes' === $s['consult_enabled'],
			'hotline'  => $s['hotline'],
			'zalo'     => $s['zalo'] ? ( 0 === strpos( $s['zalo'], 'http' ) ? $s['zalo'] : 'https://zalo.me/' . preg_replace( '/\D/', '', $s['zalo'] ) ) : '',
			'note'     => $s['note'],
			'site'     => get_bloginfo( 'name' ),
			'currency' => array(
				'symbol'   => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' ),
				'format'   => get_option( 'woocommerce_currency_pos', 'left' ),
				'decimals' => wc_get_price_decimals(),
				'dec'      => wc_get_price_decimal_separator(),
				'thousand' => wc_get_price_thousand_separator(),
			),
		);
	}

	public static function render( $atts ) {
		$atts = shortcode_atts( array( 'title' => '', 'presets' => 'yes' ), $atts, 'mg_pc_builder' );
		$s    = MGPC_Settings::get();
		wp_enqueue_style( 'mgpc' );
		wp_enqueue_script( 'mgpc' );
		$cfg = self::config();
		if ( 'no' === $atts['presets'] ) {
			$cfg['presets'] = array();
		}
		ob_start();
		?>
		<div class="mgpc mg-ui" data-config="<?php echo esc_attr( wp_json_encode( $cfg ) ); ?>">
			<header class="mgpc-hero">
				<span class="mg-eyebrow">Build PC</span>
				<h2 class="mgpc-hero__title"><?php echo esc_html( $atts['title'] ? $atts['title'] : $s['title'] ); ?></h2>
				<?php if ( $s['intro'] ) : ?><p class="mgpc-hero__intro"><?php echo esc_html( $s['intro'] ); ?></p><?php endif; ?>
			</header>
			<section class="mgpc-presets" data-presets hidden></section>
			<div class="mgpc-layout">
				<section class="mgpc-main mg-card">
					<div class="mgpc-toolbar" data-toolbar></div>
					<ol class="mgpc-slots" data-slots></ol>
				</section>
				<aside class="mgpc-summary mg-card" data-summary></aside>
			</div>
			<div class="mgpc-mobilebar" data-mobilebar></div>
			<div class="mgpc-drawer" data-drawer hidden></div>
			<div class="mgpc-modal" data-modal hidden></div>
			<div class="mgpc-toast" data-toast role="status" aria-live="polite"></div>
			<noscript><p>Vui lòng bật JavaScript để sử dụng trình Build PC.</p></noscript>
		</div>
		<?php
		return ob_get_clean();
	}
}
