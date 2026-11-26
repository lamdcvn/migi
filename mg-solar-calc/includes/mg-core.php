<?php
/**
 * Migi shared core: one "Migi" admin menu for every mg-* plugin.
 * This file is bundled (identical) in each Migi plugin; whichever loads first defines it.
 */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'mg_core_register_module' ) ) {

	$GLOBALS['mg_core_modules'] = array();

	/**
	 * @param string $slug Module slug.
	 * @param array  $args name, description, version, settings (admin URL), icon (dashicon).
	 */
	function mg_core_register_module( $slug, array $args ) {
		$GLOBALS['mg_core_modules'][ $slug ] = $args;
	}

	function mg_core_capability() {
		return class_exists( 'WooCommerce' ) ? 'manage_woocommerce' : 'manage_options';
	}

	add_action( 'admin_menu', function () {
		add_menu_page( 'Migi', 'Migi', mg_core_capability(), 'migi', 'mg_core_dashboard', 'dashicons-screenoptions', '55.6' );
		add_submenu_page( 'migi', 'Migi – Tổng quan', 'Tổng quan', mg_core_capability(), 'migi', 'mg_core_dashboard' );
	}, 9 );

	function mg_core_dashboard() {
		echo '<div class="wrap"><h1>Migi</h1><p>Các module Migi đang hoạt động:</p><div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;max-width:1100px">';
		foreach ( $GLOBALS['mg_core_modules'] as $m ) {
			printf(
				'<div class="card" style="margin:0;max-width:none"><h2 style="display:flex;gap:8px;align-items:center"><span class="dashicons %1$s"></span>%2$s <small style="color:#888;font-weight:normal">v%3$s</small></h2><p>%4$s</p>%5$s</div>',
				esc_attr( isset( $m['icon'] ) ? $m['icon'] : 'dashicons-admin-plugins' ),
				esc_html( $m['name'] ),
				esc_html( $m['version'] ),
				esc_html( $m['description'] ),
				! empty( $m['settings'] ) ? '<p><a class="button button-primary" href="' . esc_url( $m['settings'] ) . '">Cài đặt</a></p>' : ''
			);
		}
		echo '</div></div>';
	}

	/** Copies an option from an old name if the new one does not exist yet. */
	function mg_core_migrate_option( $old, $new ) {
		$value = get_option( $old, null );
		if ( null !== $value && false === get_option( $new ) ) {
			add_option( $new, $value );
		}
	}

	/** Renames post meta keys from an old prefix to a new one. */
	function mg_core_migrate_meta_prefix( $old_prefix, $new_prefix ) {
		global $wpdb;
		$wpdb->query( $wpdb->prepare(
			"UPDATE {$wpdb->postmeta} SET meta_key = CONCAT(%s, SUBSTRING(meta_key, %d)) WHERE meta_key LIKE %s",
			$new_prefix,
			strlen( $old_prefix ) + 1,
			$wpdb->esc_like( $old_prefix ) . '%'
		) );
	}

	/** Deactivates the old (pre-rename) plugin so both versions never run together. */
	function mg_core_deactivate_old( $basename ) {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( is_plugin_active( $basename ) ) {
			deactivate_plugins( $basename, true );
		}
	}
}
