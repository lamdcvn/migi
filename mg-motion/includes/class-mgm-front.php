<?php
defined( 'ABSPATH' ) || exit;

class MGM_Front {

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ), 20 );
		add_action( 'wp_head', array( __CLASS__, 'head' ), 1 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_action( 'wp_footer', array( __CLASS__, 'fab' ), 20 );
		add_shortcode( 'mg_marquee', array( __CLASS__, 'sc_marquee' ) );
		add_shortcode( 'mg_counter', array( __CLASS__, 'sc_counter' ) );
	}

	public static function active() {
		if ( is_admin() || wp_doing_ajax() || is_feed() ) {
			return false;
		}
		$where = MGM_Settings::get()['load_where'];
		if ( 'pages' === $where ) {
			return is_page() || is_front_page();
		}
		if ( 'front' === $where ) {
			return is_front_page() || is_page_template() || is_page( 'flatsome' );
		}
		return true;
	}

	/** Marks JS as available before paint; reveals content again if the script never runs (3s). */
	public static function head() {
		if ( ! self::active() ) {
			return;
		}
		echo "<script>document.documentElement.classList.add('mgm-js');setTimeout(function(){if(!window.mgmReady){document.documentElement.classList.remove('mgm-js')}},3000);</script>\n";
	}

	public static function assets() {
		if ( ! self::active() ) {
			return;
		}
		$s       = MGM_Settings::get();
		$presets = MGM_Settings::presets();
		wp_enqueue_style( 'mg-motion', MGM_URL . 'assets/css/mg-motion.css', array(), MGM_VERSION );
		wp_enqueue_script( 'mg-motion', MGM_URL . 'assets/js/mg-motion.js', array(), MGM_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_add_inline_script( 'mg-motion', 'window.mgmConfig=' . wp_json_encode( array(
			'auto'     => 'yes' === $s['auto'] ? $s['auto_sel'] : '',
			'variant'  => $presets[ $s['preset'] ]['variant'] ?? '',
			'progress' => 'yes' === $s['progress'],
			'cursor'   => 'yes' === $s['cursor'],
		) ) . ';', 'before' );
	}

	public static function body_class( $c ) {
		if ( self::active() ) {
			$s       = MGM_Settings::get();
			$presets = MGM_Settings::presets();
			$c[]     = 'mgm-preset-' . $s['preset'];
			if ( ! empty( $presets[ $s['preset'] ]['dark'] ) ) {
				$c[] = 'mgm-dark';
			}
			if ( 'yes' === $s['header_glass'] ) {
				$c[] = 'mgm-header-glass';
			}
		}
		return $c;
	}

	public static function fab() {
		$s = MGM_Settings::get();
		if ( ! self::active() || 'yes' !== $s['fab'] || ! ( $s['phone'] || $s['zalo'] || $s['messenger'] ) ) {
			return;
		}
		$zalo = $s['zalo'] ? ( 0 === strpos( $s['zalo'], 'http' ) ? $s['zalo'] : 'https://zalo.me/' . preg_replace( '/\D/', '', $s['zalo'] ) ) : '';
		$mess = $s['messenger'] ? ( 0 === strpos( $s['messenger'], 'http' ) ? $s['messenger'] : 'https://m.me/' . rawurlencode( $s['messenger'] ) ) : '';
		echo '<div class="mgm-fab mgm-fab--' . esc_attr( $s['fab_side'] ) . '">';
		if ( $mess ) {
			echo '<a class="mgm-fab-mess" href="' . esc_url( $mess ) . '" target="_blank" rel="noopener" aria-label="Messenger"><svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.4 2 2 6.1 2 11.4c0 2.9 1.4 5.4 3.7 7.1V22l3.4-1.9c.9.3 1.9.4 2.9.4 5.6 0 10-4.1 10-9.4S17.6 2 12 2zm1 12.6-2.5-2.7-5 2.7 5.5-5.8 2.6 2.7 4.9-2.7-5.5 5.8z"/></svg><span class="mgm-fab-label">Nhắn Messenger</span></a>';
		}
		if ( $zalo ) {
			echo '<a class="mgm-fab-zalo" href="' . esc_url( $zalo ) . '" target="_blank" rel="noopener" aria-label="Zalo">Zalo<span class="mgm-fab-label">Chat Zalo</span></a>';
		}
		if ( $s['phone'] ) {
			echo '<a class="mgm-fab-call" href="tel:' . esc_attr( $s['phone'] ) . '" aria-label="Gọi ' . esc_attr( $s['phone'] ) . '"><svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2z"/></svg><span class="mgm-fab-label">' . esc_html( $s['phone'] ) . '</span></a>';
		}
		echo '</div>';
	}

	public static function sc_marquee( $atts ) {
		$a = shortcode_atts( array( 'items' => '', 'speed' => 30, 'reverse' => 'no', 'class' => '' ), $atts, 'mg_marquee' );
		return sprintf( '<div class="mg-marquee%s %s" data-items="%s" data-speed="%d"></div>', 'yes' === $a['reverse'] ? ' mg-marquee-reverse' : '', esc_attr( $a['class'] ), esc_attr( $a['items'] ), max( 5, (int) $a['speed'] ) );
	}

	public static function sc_counter( $atts ) {
		$a = shortcode_atts( array( 'to' => 0, 'suffix' => '', 'prefix' => '', 'label' => '', 'class' => '' ), $atts, 'mg_counter' );
		$n = (float) str_replace( array( '.', ',' ), array( '', '.' ), (string) $a['to'] );
		return sprintf( '<div class="mgm-counter %s"><div class="mg-count mgm-counter__num" data-to="%s" data-suffix="%s">%s%s%s</div>%s</div>', esc_attr( $a['class'] ), esc_attr( $n ), esc_attr( $a['suffix'] ), esc_html( $a['prefix'] ), esc_html( number_format( $n, 0, ',', '.' ) ), esc_html( $a['suffix'] ), $a['label'] ? '<div class="mgm-counter__label">' . esc_html( $a['label'] ) . '</div>' : '' );
	}
}
