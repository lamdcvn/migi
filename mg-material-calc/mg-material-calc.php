<?php
/**
 * Plugin Name:       Migi Material Calculator – Tính vật liệu ốp lát, dán tường
 * Description:       Khách nhập dài × rộng (× cao) của nhiều phòng, plugin tính diện tích, số viên/thùng gạch, cuộn giấy dán tường, tấm ốp, hộp sàn gỗ, thùng sơn, lượng dự phòng và tổng tiền; tự điền số lượng vào giỏ hàng WooCommerce và lưu chi tiết phòng vào đơn hàng. Shortcode: [mg_material_calc id="ID sản phẩm"]
 * Version:           1.1.0
 * Author:            Migi
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * WC requires at least: 7.0
 * Text Domain:       mg-material-calc
 */

defined( 'ABSPATH' ) || exit;

define( 'MGMC_VERSION', '1.1.0' );
define( 'MGMC_FILE', __FILE__ );
define( 'MGMC_DIR', plugin_dir_path( __FILE__ ) );
define( 'MGMC_URL', plugin_dir_url( __FILE__ ) );

require_once MGMC_DIR . 'includes/mg-core.php';

add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p><strong>Migi Material Calculator</strong> cần WooCommerce để hoạt động.</p></div>';
		} );
		return;
	}
	require_once MGMC_DIR . 'includes/class-mgmc-engine.php';
	require_once MGMC_DIR . 'includes/class-mgmc-config.php';
	require_once MGMC_DIR . 'includes/class-mgmc-admin.php';
	require_once MGMC_DIR . 'includes/class-mgmc-frontend.php';
	require_once MGMC_DIR . 'includes/class-mgmc-cart.php';
	require_once MGMC_DIR . 'includes/class-mgmc-quotes.php';

	mg_core_register_module( 'mg-material-calc', array(
		'name'        => 'Migi Material Calculator',
		'description' => 'Tính gạch, giấy dán tường, tấm ốp, sàn gỗ, sơn, vật tư đi kèm theo nhiều phòng + dự phòng; phối nhiều sản phẩm, báo giá, tự điền giỏ hàng.',
		'version'     => MGMC_VERSION,
		'settings'    => admin_url( 'admin.php?page=mg-material-calc' ),
		'icon'        => 'dashicons-grid-view',
	) );

	MGMC_Admin::init();
	MGMC_Frontend::init();
	MGMC_Cart::init();
	MGMC_Quotes::init();
} );

/** Sale unit of a product with the calculator enabled (used by Migi Quote). */
function mgmc_unit_for( $product ) {
	$c = class_exists( 'MGMC_Config' ) ? MGMC_Config::for_product( $product ) : null;
	return $c ? $c['unit'] : '';
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=mg-material-calc' ) ) . '">Cài đặt</a>' );
	return $links;
} );
