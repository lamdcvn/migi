<?php
/**
 * Plugin Name:       VT Solar Calculator – Tính hệ thống điện mặt trời
 * Description:       Công cụ tính hệ thống điện mặt trời theo hoá đơn tiền điện EVN (bậc thang), diện tích mái, khu vực, 1/3 pha, tỷ lệ dùng ban ngày. Hỗ trợ hoà lưới / hybrid có pin / độc lập, tính chi phí – tiết kiệm – hoàn vốn, biểu đồ, thu thập khách hàng tiềm năng (lead), email, webhook, xuất CSV, gợi ý gói sản phẩm WooCommerce. Shortcode: [vt_solar_calc]
 * Version:           1.0.0
 * Author:            Dịch Vụ Việt Trung
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Text Domain:       vt-solar-calc
 */

defined( 'ABSPATH' ) || exit;

define( 'VTSC_VERSION', '1.0.0' );
define( 'VTSC_FILE', __FILE__ );
define( 'VTSC_DIR', plugin_dir_path( __FILE__ ) );
define( 'VTSC_URL', plugin_dir_url( __FILE__ ) );

require_once VTSC_DIR . 'includes/class-vtsc-settings.php';
require_once VTSC_DIR . 'includes/class-vtsc-calculator.php';
require_once VTSC_DIR . 'includes/class-vtsc-render.php';
require_once VTSC_DIR . 'includes/class-vtsc-leads.php';
require_once VTSC_DIR . 'includes/class-vtsc-shortcode.php';

VTSC_Settings::init();
VTSC_Leads::init();
VTSC_Shortcode::init();

register_activation_hook( __FILE__, function () {
	if ( false === get_option( VTSC_Settings::OPTION ) ) {
		add_option( VTSC_Settings::OPTION, VTSC_Settings::defaults() );
	}
	VTSC_Leads::register_post_type();
	flush_rewrite_rules();
} );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'edit.php?post_type=vtsc_lead&page=vtsc-settings' ) ) . '">Cài đặt</a>' );
	return $links;
} );
