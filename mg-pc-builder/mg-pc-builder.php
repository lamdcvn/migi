<?php
/**
 * Plugin Name:       Migi PC Builder – Build cấu hình máy tính
 * Description:       Khách tự chọn CPU, mainboard, VGA, RAM, ổ cứng, case, nguồn... theo từng nhóm linh kiện; bộ lọc, tìm kiếm, kiểm tra tương thích (socket, RAM, kích thước, công suất nguồn), ước tính điện năng, cấu hình gợi ý, chia sẻ / in / tải file, thêm cả cấu hình vào giỏ, ưu đãi Build PC, nhận tư vấn. Shortcode: [mg_pc_builder]
 * Version:           1.0.0
 * Author:            Migi
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * WC requires at least: 7.0
 * Text Domain:       mg-pc-builder
 */

defined( 'ABSPATH' ) || exit;

define( 'MGPC_VERSION', '1.0.0' );
define( 'MGPC_FILE', __FILE__ );
define( 'MGPC_DIR', plugin_dir_path( __FILE__ ) );
define( 'MGPC_URL', plugin_dir_url( __FILE__ ) );

require_once MGPC_DIR . 'includes/mg-core.php';

add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p><strong>Migi PC Builder</strong> cần WooCommerce để hoạt động.</p></div>';
		} );
		return;
	}
	foreach ( array( 'settings', 'specs', 'compat', 'catalog', 'presets', 'requests', 'cart', 'shortcode' ) as $f ) {
		require_once MGPC_DIR . 'includes/class-mgpc-' . $f . '.php';
	}
	mg_core_register_module( 'mg-pc-builder', array(
		'name'        => 'Migi PC Builder',
		'description' => 'Build cấu hình PC: chọn linh kiện theo nhóm, kiểm tra tương thích, cấu hình gợi ý, chia sẻ, thêm cả bộ vào giỏ. Shortcode [mg_pc_builder].',
		'version'     => MGPC_VERSION,
		'settings'    => admin_url( 'admin.php?page=mg-pc-builder' ),
		'icon'        => 'dashicons-desktop',
	) );
	mg_core_ux_element( 'mg_pc_builder', array(
		'name'    => 'Build PC',
		'icon'    => 'PC',
		'info'    => '{{ title }}',
		'options' => array(
			'title'   => array( 'type' => 'textfield', 'heading' => 'Tiêu đề', 'default' => '', 'placeholder' => 'Theo cài đặt chung' ),
			'presets' => array( 'type' => 'radio-buttons', 'heading' => 'Cấu hình gợi ý', 'default' => 'yes', 'options' => array( 'yes' => array( 'title' => 'Hiện' ), 'no' => array( 'title' => 'Ẩn' ) ) ),
		),
	) );

	MGPC_Settings::init();
	MGPC_Specs::init();
	MGPC_Catalog::init();
	MGPC_Presets::init();
	MGPC_Requests::init();
	MGPC_Cart::init();
	MGPC_Shortcode::init();
} );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=mg-pc-builder' ) ) . '">Cài đặt</a>' );
	return $links;
} );
