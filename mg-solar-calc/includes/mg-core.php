<?php
/**
 * Migi shared core – bundled (identical) in every mg-* plugin; whichever loads first defines it.
 * - One "Migi" admin menu + dashboard listing the active modules.
 * - Shared design system "mg-ui" (front-end) and "mg-admin" (wp-admin) in the Migi tone:
 *   Manrope, orange gradient actions, glass cards, soft pastel backgrounds, dark mode via [data-theme="dark"].
 * - Small helpers for migrating data from the old vt-* plugins.
 */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'mg_core_register_module' ) ) {

	define( 'MG_CORE_URL', plugin_dir_url( __DIR__ ) ); // Assets of the plugin that loaded the core first.
	define( 'MG_CORE_VERSION', '1.1.0' );

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

	function mg_core_ui_settings() {
		return wp_parse_args( (array) get_option( 'mg_ui', array() ), array(
			'accent' => '#f5a000',
			'accent2' => '#ff8a00',
			'font'   => 'yes',
			'glass'  => 'yes',
		) );
	}

	/** Registers + enqueues the shared front-end styles. Plugins add 'mg-ui' as a dependency. */
	function mg_core_register_ui() {
		if ( wp_style_is( 'mg-ui', 'registered' ) ) {
			return;
		}
		$ui   = mg_core_ui_settings();
		$deps = array();
		if ( 'yes' === $ui['font'] ) {
			wp_register_style( 'mg-font', 'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap', array(), null );
			$deps[] = 'mg-font';
		}
		wp_register_style( 'mg-ui', MG_CORE_URL . 'assets/css/mg-ui.css', $deps, MG_CORE_VERSION );
		$css = sprintf( '.mg-ui{--mg-accent:%1$s;--mg-accent-2:%2$s}', esc_attr( $ui['accent'] ), esc_attr( $ui['accent2'] ) );
		if ( 'yes' !== $ui['font'] ) {
			$css .= '.mg-ui{--mg-font:inherit}';
		}
		if ( 'yes' !== $ui['glass'] ) {
			$css .= '.mg-ui{--mg-card-bg:var(--mg-surface);--mg-backdrop:none}';
		}
		wp_add_inline_style( 'mg-ui', $css );
	}
	add_action( 'wp_enqueue_scripts', 'mg_core_register_ui', 1 );

	add_action( 'admin_enqueue_scripts', function () {
		$page   = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$screen = get_current_screen();
		$is_mg  = 0 === strpos( $page, 'mg' ) || 'migi' === $page || ( $screen && ( 0 === strpos( (string) $screen->post_type, 'mg' ) || 'product' === $screen->post_type ) );
		if ( ! $is_mg ) {
			return;
		}
		$ui = mg_core_ui_settings();
		if ( 'yes' === $ui['font'] ) {
			wp_enqueue_style( 'mg-font', 'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap', array(), null );
		}
		wp_enqueue_style( 'mg-admin', MG_CORE_URL . 'assets/css/mg-admin.css', array(), MG_CORE_VERSION );
		wp_add_inline_style( 'mg-admin', sprintf( '.mg-admin{--mg-accent:%1$s;--mg-accent-2:%2$s}', esc_attr( $ui['accent'] ), esc_attr( $ui['accent2'] ) ) );
	} );

	add_filter( 'admin_body_class', function ( $classes ) {
		$page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$pt   = isset( $_GET['post_type'] ) ? sanitize_key( $_GET['post_type'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( 0 === strpos( $page, 'mg' ) || 'migi' === $page || 0 === strpos( $pt, 'mg' ) ) {
			$classes .= ' mg-admin-screen';
		}
		return $classes;
	} );

	add_action( 'admin_menu', function () {
		add_menu_page( 'Migi', 'Migi', mg_core_capability(), 'migi', 'mg_core_dashboard', 'dashicons-screenoptions', '55.6' );
		add_submenu_page( 'migi', 'Migi – Tổng quan', 'Tổng quan', mg_core_capability(), 'migi', 'mg_core_dashboard' );
	}, 9 );

	/** Page header used by every Migi admin page. */
	function mg_core_admin_header( $title, $subtitle = '', $icon = 'dashicons-screenoptions' ) {
		printf(
			'<div class="mg-admin-hero"><span class="mg-admin-hero__icon dashicons %3$s"></span><div><p class="mg-admin-hero__eyebrow">MIGI</p><h1>%1$s</h1>%2$s</div></div>',
			esc_html( $title ),
			$subtitle ? '<p class="mg-admin-hero__sub">' . wp_kses_post( $subtitle ) . '</p>' : '',
			esc_attr( $icon )
		);
	}

	function mg_core_dashboard() {
		if ( isset( $_POST['mg_ui_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['mg_ui_nonce'] ), 'mg_ui' ) && current_user_can( mg_core_capability() ) ) {
			update_option( 'mg_ui', array(
				'accent'  => sanitize_hex_color( wp_unslash( $_POST['accent'] ?? '' ) ) ?: '#f5a000',
				'accent2' => sanitize_hex_color( wp_unslash( $_POST['accent2'] ?? '' ) ) ?: '#ff8a00',
				'font'    => empty( $_POST['font'] ) ? 'no' : 'yes',
				'glass'   => empty( $_POST['glass'] ) ? 'no' : 'yes',
			) );
			echo '<div class="notice notice-success is-dismissible"><p>Đã lưu giao diện.</p></div>';
		}
		$ui = mg_core_ui_settings();
		echo '<div class="wrap mg-admin">';
		mg_core_admin_header( 'Bộ plugin Migi', 'Quản lý các module Migi đang hoạt động và giao diện chung.' );
		echo '<div class="mg-admin-grid">';
		foreach ( $GLOBALS['mg_core_modules'] as $m ) {
			printf(
				'<div class="mg-admin-card mg-admin-module"><span class="mg-admin-module__icon dashicons %1$s"></span><h2>%2$s <span class="mg-admin-chip">v%3$s</span></h2><p>%4$s</p>%5$s</div>',
				esc_attr( isset( $m['icon'] ) ? $m['icon'] : 'dashicons-admin-plugins' ),
				esc_html( $m['name'] ),
				esc_html( $m['version'] ),
				esc_html( $m['description'] ),
				! empty( $m['settings'] ) ? '<p><a class="mg-admin-btn" href="' . esc_url( $m['settings'] ) . '">Mở cài đặt</a></p>' : ''
			);
		}
		echo '</div>';
		?>
		<form method="post" class="mg-admin-card mg-admin-ui-form">
			<?php wp_nonce_field( 'mg_ui', 'mg_ui_nonce' ); ?>
			<h2>Giao diện chung</h2>
			<p class="description">Áp dụng cho khối hiển thị của mọi plugin Migi trên website.</p>
			<div class="mg-admin-fields">
				<label>Màu nhấn <input type="color" name="accent" value="<?php echo esc_attr( $ui['accent'] ); ?>"></label>
				<label>Màu nhấn đậm (gradient) <input type="color" name="accent2" value="<?php echo esc_attr( $ui['accent2'] ); ?>"></label>
				<label class="mg-admin-switch"><input type="checkbox" name="font" value="1" <?php checked( 'yes', $ui['font'] ); ?>><span></span> Dùng font Manrope (tắt để dùng font của theme)</label>
				<label class="mg-admin-switch"><input type="checkbox" name="glass" value="1" <?php checked( 'yes', $ui['glass'] ); ?>><span></span> Hiệu ứng kính mờ &amp; nền pastel</label>
			</div>
			<p><button class="mg-admin-btn" type="submit">Lưu giao diện</button></p>
		</form>
		<?php
		echo '</div>';
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
