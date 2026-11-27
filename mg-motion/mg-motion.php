<?php
/**
 * Plugin Name:       Migi Motion – Hiệu ứng landing theo lĩnh vực
 * Description:       Bộ hiệu ứng siêu nhẹ (không jQuery, ~8KB JS + 14KB CSS) cho Flatsome và mọi theme: xuất hiện khi cuộn, chữ bay từng ký tự, đếm số, chạy chữ, parallax, Ken Burns, chữ gradient, nút quét sáng, thẻ nghiêng theo chuột, lớp trang trí laser / aurora / bokeh… 7 bộ màu & chuyển động theo lĩnh vực, nút gọi/Zalo nổi, chế độ WordPress tối giản, tạo trang demo /flatsome.
 * Version:           1.0.0
 * Author:            Migi
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Text Domain:       mg-motion
 */

defined( 'ABSPATH' ) || exit;

define( 'MGM_VERSION', '1.0.0' );
define( 'MGM_FILE', __FILE__ );
define( 'MGM_DIR', plugin_dir_path( __FILE__ ) );
define( 'MGM_URL', plugin_dir_url( __FILE__ ) );

require_once MGM_DIR . 'includes/mg-core.php';
require_once MGM_DIR . 'includes/class-mgm-settings.php';
require_once MGM_DIR . 'includes/class-mgm-front.php';
require_once MGM_DIR . 'includes/class-mgm-lite.php';
require_once MGM_DIR . 'includes/class-mgm-demo.php';

mg_core_register_module( 'mg-motion', array(
	'name'        => 'Migi Motion',
	'description' => 'Hiệu ứng landing theo lĩnh vực cho Flatsome: thêm class trong UX Builder, bộ màu & chuyển động theo ngành, nút liên hệ nổi, chế độ tối giản.',
	'version'     => MGM_VERSION,
	'settings'    => admin_url( 'admin.php?page=mg-motion' ),
	'icon'        => 'dashicons-art',
) );

MGM_Settings::init();
MGM_Front::init();
MGM_Lite::init();
MGM_Demo::init();

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=mg-motion' ) ) . '">Cài đặt</a>' );
	return $links;
} );
