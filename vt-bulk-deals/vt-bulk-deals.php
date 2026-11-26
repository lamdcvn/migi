<?php
/**
 * Plugin Name:       VT Bulk Deals – Mua nhiều giảm giá
 * Description:       Bảng deal theo số lượng (mua 2 giảm 10%, mua 3+ giảm 20%...) với badge "Phổ biến nhất", "Giá tốt nhất", "Thường mua"... Tự áp giá vào giỏ hàng, mini-cart và đơn hàng. Tương thích Flatsome.
 * Version:           1.0.0
 * Author:            Dịch Vụ Việt Trung
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * WC requires at least: 7.0
 * Text Domain:       vt-bulk-deals
 */

defined( 'ABSPATH' ) || exit;

define( 'VTBD_VERSION', '1.0.0' );
define( 'VTBD_FILE', __FILE__ );
define( 'VTBD_DIR', plugin_dir_path( __FILE__ ) );
define( 'VTBD_URL', plugin_dir_url( __FILE__ ) );

add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p><strong>VT Bulk Deals</strong> cần WooCommerce để hoạt động.</p></div>';
		} );
		return;
	}
	require_once VTBD_DIR . 'includes/class-vtbd-tiers.php';
	require_once VTBD_DIR . 'includes/class-vtbd-admin.php';
	require_once VTBD_DIR . 'includes/class-vtbd-frontend.php';
	require_once VTBD_DIR . 'includes/class-vtbd-cart.php';

	VTBD_Admin::init();
	VTBD_Frontend::init();
	VTBD_Cart::init();
} );

register_activation_hook( __FILE__, function () {
	if ( false === get_option( 'vtbd_settings' ) ) {
		add_option( 'vtbd_settings', array(
			'global_enabled' => 'yes',
			'heading'        => 'Mua nhiều – tiết kiệm hơn',
			'cart_label'     => 'Deal: Bạn tiết kiệm',
			'show_unit_note' => 'yes',
			'combine_variations' => 'yes',
		) );
	}
	if ( false === get_option( 'vtbd_global_tiers' ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-vtbd-tiers.php';
		add_option( 'vtbd_global_tiers', VTBD_Tiers::default_tiers() );
	}
} );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=vt-bulk-deals' ) ) . '">Cài đặt</a>' );
	return $links;
} );
