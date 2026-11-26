<?php
/**
 * Plugin Name:       Migi Bulk Deals – Mua nhiều giảm giá
 * Description:       Bảng deal theo số lượng (mua 2 giảm 10%, mua 3+ giảm 20%...) với badge "Phổ biến nhất", "Giá tốt nhất", "Thường mua"... Tự áp giá vào giỏ hàng, mini-cart và đơn hàng. Tương thích Flatsome.
 * Version:           1.1.0
 * Author:            Migi
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * WC requires at least: 7.0
 * Text Domain:       mg-bulk-deals
 */

defined( 'ABSPATH' ) || exit;

define( 'MGBD_VERSION', '1.1.0' );
define( 'MGBD_FILE', __FILE__ );
define( 'MGBD_DIR', plugin_dir_path( __FILE__ ) );
define( 'MGBD_URL', plugin_dir_url( __FILE__ ) );

require_once MGBD_DIR . 'includes/mg-core.php';

add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p><strong>Migi Bulk Deals</strong> cần WooCommerce để hoạt động.</p></div>';
		} );
		return;
	}
	require_once MGBD_DIR . 'includes/class-mgbd-tiers.php';
	require_once MGBD_DIR . 'includes/class-mgbd-admin.php';
	require_once MGBD_DIR . 'includes/class-mgbd-frontend.php';
	require_once MGBD_DIR . 'includes/class-mgbd-cart.php';

	mg_core_register_module( 'mg-bulk-deals', array(
		'name'        => 'Migi Bulk Deals',
		'description' => 'Deal mua nhiều giảm giá theo số lượng, badge Phổ biến nhất / Giá tốt nhất.',
		'version'     => MGBD_VERSION,
		'settings'    => admin_url( 'admin.php?page=mg-bulk-deals' ),
		'icon'        => 'dashicons-tag',
	) );

	MGBD_Admin::init();
	MGBD_Frontend::init();
	MGBD_Cart::init();
} );

register_activation_hook( __FILE__, function () {
	// Upgrade from the old "vt-bulk-deals" plugin: keep its settings and per-product tiers.
	mg_core_deactivate_old( 'vt-bulk-deals/vt-bulk-deals.php' );
	mg_core_migrate_option( 'vtbd_settings', 'mgbd_settings' );
	mg_core_migrate_option( 'vtbd_global_tiers', 'mgbd_global_tiers' );
	mg_core_migrate_meta_prefix( '_vtbd_', '_mgbd_' );

	if ( false === get_option( 'mgbd_settings' ) ) {
		add_option( 'mgbd_settings', array(
			'global_enabled' => 'yes',
			'heading'        => 'Mua nhiều – tiết kiệm hơn',
			'cart_label'     => 'Deal: Bạn tiết kiệm',
			'show_unit_note' => 'yes',
			'combine_variations' => 'yes',
		) );
	}
	if ( false === get_option( 'mgbd_global_tiers' ) ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-mgbd-tiers.php';
		add_option( 'mgbd_global_tiers', MGBD_Tiers::default_tiers() );
	}
} );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=mg-bulk-deals' ) ) . '">Cài đặt</a>' );
	return $links;
} );
