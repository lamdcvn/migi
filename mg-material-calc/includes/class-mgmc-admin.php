<?php
defined( 'ABSPATH' ) || exit;

class MGMC_Admin {

	public static function init() {
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'panel' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function assets( $hook ) {
		$screen = get_current_screen();
		if ( ( $screen && 'product' === $screen->post_type ) || false !== strpos( (string) $hook, 'mg-material-calc' ) ) {
			wp_enqueue_style( 'mgmc-admin', MGMC_URL . 'assets/css/admin.css', array(), MGMC_VERSION );
			wp_enqueue_script( 'mgmc-admin', MGMC_URL . 'assets/js/admin.js', array( 'jquery' ), MGMC_VERSION, true );
		}
	}

	public static function tab( $tabs ) {
		$tabs['mgmc'] = array(
			'label'    => 'Tính vật liệu',
			'target'   => 'mgmc_product_data',
			'class'    => array( 'show_if_simple', 'show_if_variable' ),
			'priority' => 66,
		);
		return $tabs;
	}

	private static function input( $c, $key, $label, $suffix = '', $tip = '', $attrs = 'step="any" min="0"' ) {
		printf(
			'<p class="form-field"><label for="mgmc_%1$s">%2$s</label><input type="number" %6$s id="mgmc_%1$s" name="mgmc[%1$s]" value="%3$s" class="short"> <span class="mgmc-suffix">%4$s</span>%5$s</p>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( $c[ $key ] ),
			esc_html( $suffix ),
			$tip ? '<span class="description" style="display:block;clear:both;margin-left:150px">' . esc_html( $tip ) . '</span>' : '',
			$attrs // phpcs:ignore -- static attribute string.
		);
	}

	public static function panel() {
		global $post;
		$raw = get_post_meta( $post->ID, MGMC_Config::META, true );
		$c   = wp_parse_args( is_array( $raw ) ? $raw : array(), MGMC_Config::product_defaults() );
		$s   = MGMC_Config::settings();
		?>
		<div id="mgmc_product_data" class="panel woocommerce_options_panel mgmc-panel">
			<?php wp_nonce_field( 'mgmc_save', 'mgmc_nonce' ); ?>
			<div class="options_group">
				<p class="form-field">
					<label for="mgmc_enabled">Bật công cụ tính</label>
					<input type="checkbox" id="mgmc_enabled" name="mgmc[enabled]" value="yes" <?php checked( 'yes', $c['enabled'] ); ?>>
					<span class="description">Hiện bảng tính vật liệu theo kích thước phòng trên trang sản phẩm</span>
				</p>
				<p class="form-field">
					<label for="mgmc_type">Loại vật liệu</label>
					<select id="mgmc_type" name="mgmc[type]">
						<?php foreach ( MGMC_Config::types() as $k => $t ) : ?>
							<option value="<?php echo esc_attr( $k ); ?>" data-unit="<?php echo esc_attr( $t['unit'] ); ?>" <?php selected( $c['type'], $k ); ?>><?php echo esc_html( $t['label'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p class="form-field">
					<label for="mgmc_unit">Đơn vị bán</label>
					<input type="text" id="mgmc_unit" name="mgmc[unit]" value="<?php echo esc_attr( $c['unit'] ); ?>" class="short" placeholder="thùng / hộp / cuộn / tấm">
					<span class="description" style="display:block;clear:both;margin-left:150px">Giá sản phẩm WooCommerce là giá cho 1 đơn vị này.</span>
				</p>
				<p class="form-field">
					<label>Bề mặt áp dụng</label>
					<?php foreach ( MGMC_Config::surfaces() as $k => $v ) : ?>
						<label class="mgmc-inline"><input type="checkbox" name="mgmc[surfaces][]" value="<?php echo esc_attr( $k ); ?>" <?php checked( in_array( $k, (array) $c['surfaces'], true ) ); ?>> <?php echo esc_html( $v ); ?></label>
					<?php endforeach; ?>
					<span class="description" style="display:block;clear:both;margin-left:150px">Để trống = mặc định theo loại vật liệu.</span>
				</p>
			</div>

			<div class="options_group mgmc-type mgmc-type-tile">
				<?php
				self::input( $c, 'tile_w', 'Kích thước viên – rộng', 'cm' );
				self::input( $c, 'tile_h', 'Kích thước viên – dài', 'cm' );
				self::input( $c, 'joint', 'Ron (mạch) gạch', 'mm' );
				self::input( $c, 'per_unit', 'Số viên / thùng', 'viên', '', 'step="1" min="1"' );
				?>
				<p class="form-field">
					<label for="mgmc_count">Cách đếm khi lát thẳng</label>
					<select id="mgmc_count" name="mgmc[count]">
						<option value="area" <?php selected( $c['count'], 'area' ); ?>>Theo diện tích + % hao hụt (phổ biến)</option>
						<option value="grid" <?php selected( $c['count'], 'grid' ); ?>>Theo số viên thực tế, mỗi viên cắt tính 1 viên (dư nhiều, an toàn)</option>
					</select>
				</p>
			</div>
			<div class="options_group mgmc-type mgmc-type-wallpaper">
				<?php
				self::input( $c, 'roll_w', 'Khổ cuộn', 'm', 'VD: 0,53 (khổ nhỏ), 0,7, 1,06 (khổ lớn)' );
				self::input( $c, 'roll_l', 'Chiều dài cuộn', 'm' );
				self::input( $c, 'repeat', 'Bước lặp hoa văn', 'cm', '0 nếu giấy trơn / không cần ghép hoa văn' );
				?>
			</div>
			<div class="options_group mgmc-type mgmc-type-panel">
				<?php
				self::input( $c, 'panel_w', 'Bề rộng tấm', 'cm' );
				self::input( $c, 'panel_l', 'Chiều dài tấm', 'cm' );
				?>
				<p class="form-field">
					<label for="mgmc_per_unit_panel">Số tấm / đơn vị bán</label>
					<input type="number" step="1" min="1" id="mgmc_per_unit_panel" name="mgmc_per_unit_panel" value="<?php echo esc_attr( max( 1, (int) $c['per_unit'] ) ); ?>" class="short"> <span class="mgmc-suffix">tấm</span>
					<span class="description" style="display:block;clear:both;margin-left:150px">1 nếu bán lẻ từng tấm</span>
				</p>
				<?php
				?>
			</div>
			<div class="options_group mgmc-type mgmc-type-area">
				<?php self::input( $c, 'm2_per_unit', 'Số m² / đơn vị bán', 'm²', 'VD: sàn gỗ 2,2–2,6 m²/hộp' ); ?>
			</div>
			<div class="options_group mgmc-type mgmc-type-paint">
				<?php
				self::input( $c, 'coverage', 'Độ phủ', 'm²/lít/lớp' );
				self::input( $c, 'coats', 'Số lớp', 'lớp', '', 'step="1" min="1"' );
				self::input( $c, 'unit_volume', 'Dung tích 1 đơn vị', 'lít' );
				?>
			</div>

			<div class="options_group">
				<?php
				foreach ( array( 'reserve' => array( 'Dự phòng mặc định', '%', 'Khách có thể đổi. Để trống = theo cài đặt chung (' . $s['reserve'] . '%).' ), 'waste_straight' => array( 'Hao hụt lát thẳng', '%', 'Dùng khi đếm theo diện tích. Trống = ' . $s['waste_straight'] . '%' ), 'waste_diagonal' => array( 'Hao hụt lát chéo', '%', 'Trống = ' . $s['waste_diagonal'] . '%' ), 'waste_herringbone' => array( 'Hao hụt xương cá', '%', 'Trống = ' . $s['waste_herringbone'] . '%' ) ) as $k => $l ) {
					self::input( $c, $k, $l[0], $l[1], $l[2] );
				}
				?>
				<p class="form-field">
					<label for="mgmc_min_reserve_unit">Luôn dư ít nhất 1 <?php echo esc_html( $c['unit'] ? $c['unit'] : 'đơn vị' ); ?></label>
					<input type="checkbox" id="mgmc_min_reserve_unit" name="mgmc[min_reserve_unit]" value="yes" <?php checked( 'yes', $c['min_reserve_unit'] ); ?>>
					<span class="description">Khi dự phòng chưa làm tăng số đơn vị mua, cộng thêm 1</span>
				</p>
			</div>
		</div>
		<?php
	}

	public static function save( $post_id ) {
		if ( ! isset( $_POST['mgmc_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mgmc_nonce'] ), 'mgmc_save' ) || ! isset( $_POST['mgmc'] ) ) {
			return;
		}
		$in    = wp_unslash( $_POST['mgmc'] ); // phpcs:ignore -- sanitized field by field below.
		$d     = MGMC_Config::product_defaults();
		$types = MGMC_Config::types();
		$c     = array(
			'enabled'          => empty( $in['enabled'] ) ? 'no' : 'yes',
			'type'             => isset( $in['type'], $types[ $in['type'] ] ) ? $in['type'] : 'tile',
			'unit'             => isset( $in['unit'] ) ? sanitize_text_field( $in['unit'] ) : '',
			'count'            => isset( $in['count'] ) && 'area' === $in['count'] ? 'area' : 'grid',
			'surfaces'         => isset( $in['surfaces'] ) ? array_values( array_intersect( (array) $in['surfaces'], array_keys( MGMC_Config::surfaces() ) ) ) : array(),
			'min_reserve_unit' => empty( $in['min_reserve_unit'] ) ? 'no' : 'yes',
		);
		foreach ( array( 'tile_w', 'tile_h', 'joint', 'per_unit', 'roll_w', 'roll_l', 'repeat', 'panel_w', 'panel_l', 'm2_per_unit', 'coverage', 'coats', 'unit_volume' ) as $k ) {
			$v       = isset( $in[ $k ] ) ? str_replace( ',', '.', (string) $in[ $k ] ) : '';
			$c[ $k ] = is_numeric( $v ) ? max( 0, (float) $v ) : $d[ $k ];
		}
		// "per_unit" appears in both the tile and panel groups; use the one from the visible group.
		if ( 'panel' === $c['type'] && isset( $_POST['mgmc_per_unit_panel'] ) ) {
			$c['per_unit'] = max( 1, (int) $_POST['mgmc_per_unit_panel'] );
		}
		foreach ( array( 'reserve', 'waste_straight', 'waste_diagonal', 'waste_herringbone' ) as $k ) {
			$v       = isset( $in[ $k ] ) ? str_replace( ',', '.', trim( (string) $in[ $k ] ) ) : '';
			$c[ $k ] = is_numeric( $v ) ? min( 100, max( 0, (float) $v ) ) : '';
		}
		update_post_meta( $post_id, MGMC_Config::META, $c );
	}

	/* ---------- Global settings ---------- */

	public static function menu() {
		add_submenu_page( 'migi', 'Tính vật liệu', 'Tính vật liệu', 'manage_woocommerce', 'mg-material-calc', array( __CLASS__, 'settings_page' ) );
	}

	public static function settings_page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		if ( isset( $_POST['mgmc_settings_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['mgmc_settings_nonce'] ), 'mgmc_settings' ) ) {
			$p = wp_unslash( $_POST ); // phpcs:ignore -- sanitized below.
			$s = array(
				'title'           => sanitize_text_field( $p['title'] ),
				'intro'           => sanitize_text_field( $p['intro'] ),
				'note'            => sanitize_textarea_field( $p['note'] ),
				'reserve_options' => implode( ',', array_filter( array_map( 'floatval', explode( ',', (string) $p['reserve_options'] ) ), 'is_finite' ) ),
				'auto_qty'        => empty( $p['auto_qty'] ) ? 'no' : 'yes',
				'show_price_m2'   => empty( $p['show_price_m2'] ) ? 'no' : 'yes',
				'open_default'    => empty( $p['open_default'] ) ? 'no' : 'yes',
			);
			foreach ( array( 'reserve', 'waste_straight', 'waste_diagonal', 'waste_herringbone', 'wall_height' ) as $k ) {
				$s[ $k ] = max( 0, (float) str_replace( ',', '.', (string) $p[ $k ] ) );
			}
			if ( '' === $s['reserve_options'] || false === strpos( ',' . $s['reserve_options'] . ',', ',0,' ) ) {
				$s['reserve_options'] = '0' . ( '' === $s['reserve_options'] ? '' : ',' . $s['reserve_options'] );
			}
			update_option( MGMC_Config::OPTION, $s );
			echo '<div class="notice notice-success is-dismissible"><p>Đã lưu.</p></div>';
		}
		$s = MGMC_Config::settings();
		$f = function ( $key, $label, $suffix = '', $help = '' ) use ( $s ) {
			printf( '<tr><th><label for="mgmc_%1$s">%2$s</label></th><td><input type="text" class="regular-text" id="mgmc_%1$s" name="%1$s" value="%3$s"> %4$s%5$s</td></tr>', esc_attr( $key ), esc_html( $label ), esc_attr( $s[ $key ] ), esc_html( $suffix ), $help ? '<p class="description">' . esc_html( $help ) . '</p>' : '' );
		};
		$cb = function ( $key, $label ) use ( $s ) {
			printf( '<label style="display:block;margin:4px 0"><input type="checkbox" name="%1$s" value="1" %2$s> %3$s</label>', esc_attr( $key ), checked( 'yes', $s[ $key ], false ), esc_html( $label ) );
		};
		?>
		<div class="wrap">
			<h1>Tính vật liệu – Cài đặt chung</h1>
			<p>Bật cho từng sản phẩm tại <strong>Sửa sản phẩm → Dữ liệu sản phẩm → tab "Tính vật liệu"</strong>. Chèn ở trang khác bằng shortcode <code>[mg_material_calc id="ID sản phẩm"]</code>.</p>
			<form method="post">
				<?php wp_nonce_field( 'mgmc_settings', 'mgmc_settings_nonce' ); ?>
				<table class="form-table">
					<?php
					$f( 'title', 'Tiêu đề khối tính' );
					$f( 'intro', 'Mô tả ngắn' );
					$f( 'reserve', 'Dự phòng mặc định', '%', 'Phần mua dư để bù vỡ, sai số thi công, sửa chữa sau này (thường 5–10%).' );
					$f( 'reserve_options', 'Các mức dự phòng cho khách chọn', '%', 'Cách nhau bởi dấu phẩy.' );
					$f( 'waste_straight', 'Hao hụt lát thẳng (theo diện tích)', '%' );
					$f( 'waste_diagonal', 'Hao hụt lát chéo 45°', '%' );
					$f( 'waste_herringbone', 'Hao hụt lát xương cá', '%' );
					$f( 'wall_height', 'Chiều cao tường mặc định', 'm' );
					?>
					<tr><th>Tuỳ chọn</th><td>
						<?php
						$cb( 'auto_qty', 'Tự động điền số lượng vào ô "Số lượng" khi tính xong' );
						$cb( 'show_price_m2', 'Hiện giá quy đổi theo m² dưới giá sản phẩm' );
						$cb( 'open_default', 'Mở sẵn khối tính khi vào trang sản phẩm' );
						?>
					</td></tr>
					<tr><th><label for="mgmc_note">Ghi chú dưới kết quả</label></th><td><textarea id="mgmc_note" name="note" rows="3" class="large-text"><?php echo esc_textarea( $s['note'] ); ?></textarea></td></tr>
				</table>
				<?php submit_button( 'Lưu cài đặt' ); ?>
			</form>
		</div>
		<?php
	}
}
