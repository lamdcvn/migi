<?php
defined( 'ABSPATH' ) || exit;

/** Optional "lean WordPress" switches. Each one is independent and off unless ticked in the settings. */
class MGM_Lite {

	private static function on( $k ) {
		return in_array( $k, (array) MGM_Settings::get()['lite'], true );
	}

	public static function init() {
		add_action( 'init', array( __CLASS__, 'setup' ), 1 );
	}

	public static function setup() {
		if ( self::on( 'emoji' ) ) {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
			add_filter( 'emoji_svg_url', '__return_false' );
			add_filter( 'wp_resource_hints', function ( $urls, $type ) {
				return 'dns-prefetch' === $type ? array_filter( $urls, function ( $u ) {
					return false === strpos( is_array( $u ) ? ( $u['href'] ?? '' ) : $u, 's.w.org' );
				} ) : $urls;
			}, 10, 2 );
		}
		if ( self::on( 'embeds' ) ) {
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
			add_action( 'wp_footer', function () {
				wp_dequeue_script( 'wp-embed' );
			} );
		}
		if ( self::on( 'meta' ) ) {
			remove_action( 'wp_head', 'rsd_link' );
			remove_action( 'wp_head', 'wlwmanifest_link' );
			remove_action( 'wp_head', 'wp_shortlink_wp_head' );
			remove_action( 'wp_head', 'wp_generator' );
			remove_action( 'wp_head', 'rest_output_link_wp_head' );
			remove_action( 'template_redirect', 'rest_output_link_header', 11 );
			remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
		}
		if ( self::on( 'xmlrpc' ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
			add_filter( 'wp_headers', function ( $h ) {
				unset( $h['X-Pingback'] );
				return $h;
			} );
		}
		if ( self::on( 'heartbeat' ) ) {
			add_action( 'wp_enqueue_scripts', function () {
				wp_deregister_script( 'heartbeat' );
			}, 100 );
		}
		if ( self::on( 'revisions' ) ) {
			add_filter( 'wp_revisions_to_keep', function ( $n ) {
				return -1 === (int) $n || $n > 5 ? 5 : $n;
			} );
		}
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'front' ), 100 );
		add_action( 'wp_default_scripts', array( __CLASS__, 'jq_migrate' ) );
	}

	public static function jq_migrate( $scripts ) {
		if ( is_admin() || ! self::on( 'jqmigrate' ) || empty( $scripts->registered['jquery'] ) ) {
			return;
		}
		$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
	}

	private static function is_shop_context() {
		return function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() );
	}

	public static function front() {
		if ( self::on( 'dashicons' ) && ! is_user_logged_in() ) {
			wp_dequeue_style( 'dashicons' );
		}
		if ( self::on( 'blocks' ) && is_singular() && ! has_blocks( get_queried_object_id() ) && ! self::is_shop_context() ) {
			foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles', 'wc-blocks-style' ) as $h ) {
				wp_dequeue_style( $h );
			}
		}
		if ( self::on( 'fragments' ) && ! self::is_shop_context() ) {
			wp_dequeue_script( 'wc-cart-fragments' );
		}
	}
}
