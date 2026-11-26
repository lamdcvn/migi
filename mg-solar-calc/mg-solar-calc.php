<?php
/**
 * Plugin Name:       Migi Solar Calculator – Tính hệ thống điện mặt trời
 * Description:       Công cụ tính hệ thống điện mặt trời theo hoá đơn tiền điện EVN (bậc thang), diện tích mái, khu vực, 1/3 pha, tỷ lệ dùng ban ngày. Hỗ trợ hoà lưới / hybrid có pin / độc lập, tính chi phí – tiết kiệm – hoàn vốn, biểu đồ, thu thập khách hàng tiềm năng (lead), email, webhook, xuất CSV, gợi ý gói sản phẩm WooCommerce. Shortcode: [mg_solar_calc]
 * Version:           1.0.0
 * Author:            Migi
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Text Domain:       mg-solar-calc
 */

defined( 'ABSPATH' ) || exit;

define( 'MGSC_VERSION', '1.0.0' );
define( 'MGSC_FILE', __FILE__ );
define( 'MGSC_DIR', plugin_dir_path( __FILE__ ) );
define( 'MGSC_URL', plugin_dir_url( __FILE__ ) );

require_once MGSC_DIR . 'includes/mg-core.php';
require_once MGSC_DIR . 'includes/class-mgsc-settings.php';
require_once MGSC_DIR . 'includes/class-mgsc-calculator.php';
require_once MGSC_DIR . 'includes/class-mgsc-render.php';
require_once MGSC_DIR . 'includes/class-mgsc-leads.php';
require_once MGSC_DIR . 'includes/class-mgsc-shortcode.php';

mg_core_register_module( 'mg-solar-calc', array(
	'name'        => 'Migi Solar Calculator',
	'description' => 'Tính hệ thống điện mặt trời theo hoá đơn EVN, thu thập khách hàng tiềm năng. Shortcode [mg_solar_calc].',
	'version'     => MGSC_VERSION,
	'settings'    => admin_url( 'admin.php?page=mgsc-settings' ),
	'icon'        => 'dashicons-lightbulb',
) );

MGSC_Settings::init();
MGSC_Leads::init();
MGSC_Shortcode::init();

register_activation_hook( __FILE__, function () {
	// Upgrade from the old "vt-solar-calc" plugin: keep settings and leads.
	global $wpdb;
	mg_core_deactivate_old( 'vt-solar-calc/vt-solar-calc.php' );
	mg_core_migrate_option( 'vtsc_settings', 'mgsc_settings' );
	$wpdb->update( $wpdb->posts, array( 'post_type' => 'mgsc_lead' ), array( 'post_type' => 'vtsc_lead' ) );
	mg_core_migrate_meta_prefix( '_vtsc_', '_mgsc_' );

	if ( false === get_option( MGSC_Settings::OPTION ) ) {
		add_option( MGSC_Settings::OPTION, MGSC_Settings::defaults() );
	}
	MGSC_Leads::register_post_type();
	flush_rewrite_rules();
} );

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'admin.php?page=mgsc-settings' ) ) . '">Cài đặt</a>' );
	return $links;
} );
