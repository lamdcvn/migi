<?php
/**
 * Plugin Name:       Migi Quote – Báo giá & PDF
 * Description:       Hai hình thức báo giá: Chuẩn (giá web, khách tự tải từ Build PC / Tính vật liệu) và Cạnh tranh (giá tuỳ chỉnh, chỉ quản trị viên nhập). Mẫu PDF A4 có logo, tên công ty, thông tin liên hệ, tài khoản ngân hàng, chữ ký; gửi email kèm PDF, link xem cho khách, khách bấm đồng ý, tạo đơn hàng từ báo giá.
 * Version:           1.0.0
 * Author:            Migi
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * Text Domain:       mg-quote
 */

defined( 'ABSPATH' ) || exit;

define( 'MGQ_VERSION', '1.0.0' );
define( 'MGQ_FILE', __FILE__ );
define( 'MGQ_DIR', plugin_dir_path( __FILE__ ) );
define( 'MGQ_URL', plugin_dir_url( __FILE__ ) );

require_once MGQ_DIR . 'includes/mg-core.php';

add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	foreach ( array( 'settings', 'quote', 'pdf', 'admin', 'public', 'sources' ) as $f ) {
		require_once MGQ_DIR . 'includes/class-mgq-' . $f . '.php';
	}
	mg_core_register_module( 'mg-quote', array(
		'name'        => 'Migi Quote',
		'description' => 'Báo giá chuẩn (giá web) & cạnh tranh (giá do admin nhập), mẫu PDF có logo công ty, gửi email, link khách xem & đồng ý, tạo đơn hàng.',
		'version'     => MGQ_VERSION,
		'settings'    => admin_url( 'admin.php?page=mg-quote' ),
		'icon'        => 'dashicons-media-document',
	) );
	MGQ_Settings::init();
	MGQ_Quote::init();
	MGQ_Admin::init();
	MGQ_Public::init();
	MGQ_Sources::init();
} );

/** Other Migi plugins check this before showing "Tải báo giá PDF". */
function mgq_enabled() {
	return class_exists( 'MGQ_Quote' ) && 'yes' === MGQ_Settings::get()['standard_enabled'];
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=mg-quote' ) ) . '">Cài đặt</a>' );
	return $links;
} );
