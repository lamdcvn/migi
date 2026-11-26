<?php
defined( 'ABSPATH' ) || exit;

class MGBD_Admin {

	public static function init() {
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'product_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'product_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_product' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function assets( $hook ) {
		$screen = get_current_screen();
		$is_product  = $screen && 'product' === $screen->post_type;
		$is_settings = false !== strpos( (string) $hook, 'mg-bulk-deals' );
		if ( ! $is_product && ! $is_settings ) {
			return;
		}
		wp_enqueue_style( 'mgbd-admin', MGBD_URL . 'assets/css/admin.css', array(), MGBD_VERSION );
		wp_enqueue_script( 'mgbd-admin', MGBD_URL . 'assets/js/admin.js', array( 'jquery' ), MGBD_VERSION, true );
	}

	/** Renders the editable tier table. */
	public static function tier_table( $tiers ) {
		if ( empty( $tiers ) ) {
			$tiers = MGBD_Tiers::default_tiers();
		}
		?>
		<table class="mgbd-tier-table widefat">
			<thead>
				<tr>
					<th>Mua từ (SL)</th>
					<th>Giảm (%)</th>
					<th>Tiêu đề <span class="mgbd-hint">trống = "2 sản phẩm"</span></th>
					<th>Mô tả phụ <span class="mgbd-hint">trống = "Tiết kiệm x%"</span></th>
					<th>Badge</th>
					<th>Màu badge</th>
					<th>Chọn sẵn</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( array_values( $tiers ) as $i => $t ) { self::tier_row( $i, $t ); } ?>
			</tbody>
			<tfoot>
				<tr><td colspan="8">
					<button type="button" class="button mgbd-add-row">+ Thêm mức</button>
					<span class="mgbd-hint">Gợi ý badge: Phổ biến nhất · Giá tốt nhất · Thường mua · Bán chạy · Tiết kiệm nhất</span>
				</td></tr>
			</tfoot>
		</table>
		<script type="text/html" id="tmpl-mgbd-row"><?php self::tier_row( '{{KEY}}', array() ); ?></script>
		<?php
	}

	private static function tier_row( $key, $t ) {
		$t = wp_parse_args( $t, array( 'qty' => '', 'discount' => '', 'title' => '', 'subtitle' => '', 'badge' => '', 'badge_color' => '#7a2e2e', 'is_default' => '' ) );
		$n = 'mgbd_tiers[' . $key . ']';
		?>
		<tr>
			<td><input type="number" min="1" step="1" name="<?php echo esc_attr( $n ); ?>[qty]" value="<?php echo esc_attr( $t['qty'] ); ?>" class="small-text"></td>
			<td><input type="number" min="0" max="100" step="0.01" name="<?php echo esc_attr( $n ); ?>[discount]" value="<?php echo esc_attr( $t['discount'] ); ?>" class="small-text"></td>
			<td><input type="text" name="<?php echo esc_attr( $n ); ?>[title]" value="<?php echo esc_attr( $t['title'] ); ?>"></td>
			<td><input type="text" name="<?php echo esc_attr( $n ); ?>[subtitle]" value="<?php echo esc_attr( $t['subtitle'] ); ?>"></td>
			<td><input type="text" name="<?php echo esc_attr( $n ); ?>[badge]" value="<?php echo esc_attr( $t['badge'] ); ?>" placeholder="Phổ biến nhất"></td>
			<td><input type="color" name="<?php echo esc_attr( $n ); ?>[badge_color]" value="<?php echo esc_attr( $t['badge_color'] ); ?>"></td>
			<td class="mgbd-center"><input type="radio" name="mgbd_default" value="<?php echo esc_attr( $key ); ?>" <?php checked( 'yes', $t['is_default'] ); ?>></td>
			<td><button type="button" class="button-link mgbd-remove-row" aria-label="Xoá">&times;</button></td>
		</tr>
		<?php
	}

	/* ---------- Product edit screen ---------- */

	public static function product_tab( $tabs ) {
		$tabs['mgbd'] = array(
			'label'    => 'Deal số lượng',
			'target'   => 'mgbd_product_data',
			'class'    => array( 'hide_if_grouped', 'hide_if_external' ),
			'priority' => 65,
		);
		return $tabs;
	}

	public static function product_panel() {
		global $post;
		$mode  = get_post_meta( $post->ID, '_mgbd_mode', true );
		$mode  = $mode ? $mode : 'global';
		$tiers = get_post_meta( $post->ID, '_mgbd_tiers', true );
		if ( ! $tiers ) {
			$tiers = get_option( 'mgbd_global_tiers', array() );
		}
		?>
		<div id="mgbd_product_data" class="panel woocommerce_options_panel mg-admin-panel">
			<p class="mg-panel-intro"><strong>Migi · Deal số lượng</strong> – dùng cài đặt chung, tuỳ chỉnh riêng mức giảm/badge cho sản phẩm này, hoặc tắt.</p>
			<div class="options_group">
				<?php wp_nonce_field( 'mgbd_save_product', 'mgbd_nonce' ); ?>
				<p class="form-field">
					<label for="mgbd_mode">Deal mua nhiều</label>
					<select id="mgbd_mode" name="mgbd_mode">
						<option value="global" <?php selected( $mode, 'global' ); ?>>Dùng cài đặt chung</option>
						<option value="custom" <?php selected( $mode, 'custom' ); ?>>Tuỳ chỉnh riêng cho sản phẩm này</option>
						<option value="off" <?php selected( $mode, 'off' ); ?>>Tắt cho sản phẩm này</option>
					</select>
				</p>
				<div class="mgbd-custom-wrap" <?php echo 'custom' === $mode ? '' : 'style="display:none"'; ?>>
					<?php self::tier_table( $tiers ); ?>
				</div>
				<p class="mgbd-hint" style="padding:0 12px">
					Cài đặt chung: <a href="<?php echo esc_url( admin_url( 'admin.php?page=mg-bulk-deals' ) ); ?>">Migi → Deal số lượng</a>
				</p>
			</div>
		</div>
		<?php
	}

	public static function save_product( $post_id ) {
		if ( ! isset( $_POST['mgbd_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mgbd_nonce'] ), 'mgbd_save_product' ) ) {
			return;
		}
		$mode = isset( $_POST['mgbd_mode'] ) ? sanitize_key( $_POST['mgbd_mode'] ) : 'global';
		$mode = in_array( $mode, array( 'global', 'custom', 'off' ), true ) ? $mode : 'global';
		update_post_meta( $post_id, '_mgbd_mode', $mode );

		if ( 'custom' === $mode ) {
			$default = isset( $_POST['mgbd_default'] ) ? sanitize_text_field( wp_unslash( $_POST['mgbd_default'] ) ) : null;
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized in MGBD_Tiers::sanitize().
			$tiers = MGBD_Tiers::sanitize( isset( $_POST['mgbd_tiers'] ) ? $_POST['mgbd_tiers'] : array(), $default );
			update_post_meta( $post_id, '_mgbd_tiers', $tiers );
		}
	}

	/* ---------- Global settings page ---------- */

	public static function menu() {
		add_submenu_page( 'migi', 'Deal số lượng', 'Deal số lượng', 'manage_woocommerce', 'mg-bulk-deals', array( __CLASS__, 'settings_page' ) );
	}

	public static function settings_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		if ( isset( $_POST['mgbd_settings_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['mgbd_settings_nonce'] ), 'mgbd_settings' ) ) {
			$s = array(
				'global_enabled'     => empty( $_POST['global_enabled'] ) ? 'no' : 'yes',
				'show_unit_note'     => empty( $_POST['show_unit_note'] ) ? 'no' : 'yes',
				'combine_variations' => empty( $_POST['combine_variations'] ) ? 'no' : 'yes',
				'heading'            => isset( $_POST['heading'] ) ? sanitize_text_field( wp_unslash( $_POST['heading'] ) ) : '',
				'cart_label'         => isset( $_POST['cart_label'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_label'] ) ) : '',
			);
			update_option( 'mgbd_settings', $s );
			$default = isset( $_POST['mgbd_default'] ) ? sanitize_text_field( wp_unslash( $_POST['mgbd_default'] ) ) : null;
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized in MGBD_Tiers::sanitize().
			update_option( 'mgbd_global_tiers', MGBD_Tiers::sanitize( isset( $_POST['mgbd_tiers'] ) ? $_POST['mgbd_tiers'] : array(), $default ) );
			echo '<div class="notice notice-success is-dismissible"><p>Đã lưu.</p></div>';
		}
		$s     = MGBD_Tiers::settings();
		$tiers = get_option( 'mgbd_global_tiers', MGBD_Tiers::default_tiers() );
		?>
		<div class="wrap mgbd-settings mg-admin">
			<?php mg_core_admin_header( 'Deal số lượng', 'Mua nhiều giảm giá: thẻ chọn số lượng kèm badge <strong>Phổ biến nhất</strong>, <strong>Giá tốt nhất</strong>… tự áp giá vào giỏ hàng.', 'dashicons-tag' ); ?>
			<form method="post">
				<?php wp_nonce_field( 'mgbd_settings', 'mgbd_settings_nonce' ); ?>
				<table class="form-table">
					<tr><th>Bật cho toàn shop</th><td><label><input type="checkbox" name="global_enabled" value="1" <?php checked( 'yes', $s['global_enabled'] ); ?>> Áp dụng bảng deal bên dưới cho mọi sản phẩm (có thể tắt/tuỳ chỉnh riêng ở từng sản phẩm, tab "Deal số lượng")</label></td></tr>
					<tr><th>Tiêu đề khối deal</th><td><input type="text" class="regular-text" name="heading" value="<?php echo esc_attr( $s['heading'] ); ?>"> <span class="mgbd-hint">để trống để ẩn</span></td></tr>
					<tr><th>Nhãn trong giỏ hàng</th><td><input type="text" class="regular-text" name="cart_label" value="<?php echo esc_attr( $s['cart_label'] ); ?>"></td></tr>
					<tr><th>Dòng giá mỗi sản phẩm</th><td><label><input type="checkbox" name="show_unit_note" value="1" <?php checked( 'yes', $s['show_unit_note'] ); ?>> Hiện "xx mỗi sản phẩm (giảm 20%)" dưới giá</label></td></tr>
					<tr><th>Gộp biến thể</th><td><label><input type="checkbox" name="combine_variations" value="1" <?php checked( 'yes', $s['combine_variations'] ); ?>> Cộng dồn số lượng các biến thể (màu/size khác nhau) của cùng 1 sản phẩm để tính deal</label></td></tr>
				</table>
				<h2 style="margin-top:8px">Các mức deal mặc định</h2>
				<p class="mgbd-hint">Mức áp dụng khi số lượng trong giỏ ≥ "Mua từ". Mức cuối hiển thị dạng "3+ sản phẩm".</p>
				<?php self::tier_table( $tiers ); ?>
				<?php submit_button( 'Lưu cài đặt' ); ?>
			</form>
		</div>
		<?php
	}
}
