<?php
defined( 'ABSPATH' ) || exit;

class MGPC_Settings {

	const OPTION = 'mgpc_settings';

	/** Roles drive the compatibility rules. */
	public static function roles() {
		return array(
			'cpu'       => 'CPU',
			'mainboard' => 'Mainboard',
			'vga'       => 'Card màn hình',
			'ram'       => 'RAM',
			'storage'   => 'Ổ cứng',
			'case'      => 'Case',
			'psu'       => 'Nguồn',
			'cooler'    => 'Tản nhiệt CPU',
			'other'     => 'Khác (màn hình, chuột, phím...)',
		);
	}

	public static function icons() {
		return array( 'cpu', 'mainboard', 'vga', 'ram', 'ssd', 'hdd', 'case', 'psu', 'cooler', 'fan', 'monitor', 'mouse', 'keyboard', 'headset', 'software', 'other' );
	}

	public static function default_slots() {
		$s = function ( $key, $label, $role, $icon, $cats, $req = false, $multi = false ) {
			return array( 'key' => $key, 'label' => $label, 'role' => $role, 'icon' => $icon, 'cats' => $cats, 'required' => $req, 'multi' => $multi );
		};
		return array(
			$s( 'cpu', 'CPU', 'cpu', 'cpu', array( 'cpu' ), true ),
			$s( 'mainboard', 'Mainboard', 'mainboard', 'mainboard', array( 'mainboard' ), true ),
			$s( 'vga', 'Card màn hình', 'vga', 'vga', array( 'vga', 'card-man-hinh' ) ),
			$s( 'ram', 'RAM', 'ram', 'ram', array( 'ram' ), true, true ),
			$s( 'ssd', 'Ổ cứng SSD', 'storage', 'ssd', array( 'ssd', 'o-cung-ssd' ), true, true ),
			$s( 'hdd', 'Ổ cứng HDD', 'storage', 'hdd', array( 'hdd', 'o-cung-hdd' ), false, true ),
			$s( 'case', 'Case máy tính', 'case', 'case', array( 'case' ), true ),
			$s( 'psu', 'Nguồn máy tính', 'psu', 'psu', array( 'psu', 'nguon-may-tinh' ), true ),
			$s( 'cooler', 'Tản nhiệt CPU', 'cooler', 'cooler', array( 'tan-nhiet', 'tan-nhiet-cpu' ) ),
			$s( 'fan', 'Quạt tản nhiệt', 'other', 'fan', array( 'quat-tan-nhiet' ), false, true ),
			$s( 'monitor', 'Màn hình', 'other', 'monitor', array( 'man-hinh' ), false, true ),
			$s( 'mouse', 'Chuột', 'other', 'mouse', array( 'chuot' ) ),
			$s( 'keyboard', 'Bàn phím', 'other', 'keyboard', array( 'ban-phim' ) ),
			$s( 'headset', 'Tai nghe', 'other', 'headset', array( 'tai-nghe' ) ),
			$s( 'software', 'Phần mềm', 'other', 'software', array( 'phan-mem' ) ),
		);
	}

	public static function get() {
		return wp_parse_args( (array) get_option( self::OPTION, array() ), array(
			'title'            => 'Build PC – Tự chọn cấu hình máy tính',
			'intro'            => 'Chọn từng linh kiện, hệ thống tự kiểm tra tương thích và tính tổng chi phí cho bạn.',
			'slots'            => self::default_slots(),
			'compat_filter'    => 'yes',
			'psu_headroom'     => 30,
			'base_watt'        => 60,
			'attr_socket'      => 'pa_socket',
			'attr_ram_type'    => 'pa_loai-ram,pa_ho-tro-ram,pa_chuan-ram',
			'attr_form'        => 'pa_kich-thuoc-mainboard,pa_chuan-mainboard,pa_form-factor,pa_ho-tro-mainboard',
			'attr_watt'        => 'pa_tdp,pa_cong-suat-tieu-thu',
			'attr_psu_watt'    => 'pa_cong-suat',
			'discount_enabled' => 'no',
			'discount_percent' => 2,
			'discount_min'     => 4,
			'discount_label'   => 'Ưu đãi Build PC',
			'hotline'          => '',
			'zalo'             => '',
			'note'             => 'Giá có thể thay đổi theo thời điểm. Kỹ thuật viên sẽ kiểm tra lại cấu hình trước khi lắp ráp.',
			'consult_enabled'  => 'yes',
			'admin_email'      => '',
			'webhook'          => '',
			'per_page'         => 18,
		) );
	}

	/** Slots keyed by slot key. */
	public static function slots() {
		$out = array();
		foreach ( (array) self::get()['slots'] as $s ) {
			if ( ! empty( $s['key'] ) ) {
				$out[ $s['key'] ] = $s;
			}
		}
		return $out;
	}

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function menu() {
		add_submenu_page( 'migi', 'Build PC', 'Build PC – Cài đặt', 'manage_woocommerce', 'mg-pc-builder', array( __CLASS__, 'page' ) );
	}

	public static function assets( $hook ) {
		$screen = get_current_screen();
		if ( false !== strpos( (string) $hook, 'mg-pc-builder' ) || ( $screen && in_array( $screen->post_type, array( 'product', 'mgpc_preset' ), true ) ) ) {
			wp_enqueue_script( 'mgpc-admin', MGPC_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable', 'selectWoo', 'wc-enhanced-select' ), MGPC_VERSION, true );
			wp_enqueue_style( 'woocommerce_admin_styles' );
			wp_add_inline_style( 'woocommerce_admin_styles', '.mgpc-drag{cursor:move;color:#98a2b3;width:18px;font-weight:700}.mgpc-center{text-align:center}.mgpc-del{color:#b42318;font-size:20px;text-decoration:none}.mgpc-slots input[type=text]{width:100%}.mgpc-slots td{vertical-align:middle}.mgpc-slots .ui-sortable-helper{background:#fff;box-shadow:0 8px 24px rgba(16,24,40,.15)}' );
		}
	}

	private static function save( $p ) {
		$s = self::get();
		foreach ( array( 'title', 'intro', 'attr_socket', 'attr_ram_type', 'attr_form', 'attr_watt', 'attr_psu_watt', 'discount_label', 'hotline', 'zalo' ) as $k ) {
			$s[ $k ] = sanitize_text_field( $p[ $k ] ?? '' );
		}
		$s['note']        = sanitize_textarea_field( $p['note'] ?? '' );
		$s['webhook']     = esc_url_raw( $p['webhook'] ?? '' );
		$s['admin_email'] = implode( ',', array_filter( array_map( 'sanitize_email', explode( ',', (string) ( $p['admin_email'] ?? '' ) ) ) ) );
		foreach ( array( 'compat_filter', 'discount_enabled', 'consult_enabled' ) as $k ) {
			$s[ $k ] = empty( $p[ $k ] ) ? 'no' : 'yes';
		}
		foreach ( array( 'psu_headroom' => 0, 'base_watt' => 0, 'discount_percent' => 0, 'discount_min' => 1, 'per_page' => 6 ) as $k => $min ) {
			$s[ $k ] = max( $min, (float) str_replace( ',', '.', (string) ( $p[ $k ] ?? 0 ) ) );
		}
		$slots = array();
		$keys  = array();
		foreach ( (array) ( $p['slots'] ?? array() ) as $row ) {
			$label = sanitize_text_field( $row['label'] ?? '' );
			if ( '' === $label ) {
				continue;
			}
			$key = sanitize_key( $row['key'] ?? '' );
			$key = $key ? $key : sanitize_key( remove_accents( $label ) );
			while ( isset( $keys[ $key ] ) ) {
				$key .= '2';
			}
			$keys[ $key ] = true;
			$role         = sanitize_key( $row['role'] ?? 'other' );
			$icon         = sanitize_key( $row['icon'] ?? 'other' );
			$slots[]      = array(
				'key'      => $key,
				'label'    => $label,
				'role'     => array_key_exists( $role, self::roles() ) ? $role : 'other',
				'icon'     => in_array( $icon, self::icons(), true ) ? $icon : 'other',
				'cats'     => array_values( array_filter( array_map( 'sanitize_title', (array) ( $row['cats'] ?? array() ) ) ) ),
				'required' => ! empty( $row['required'] ),
				'multi'    => ! empty( $row['multi'] ),
			);
		}
		$s['slots'] = $slots ? $slots : self::default_slots();
		update_option( self::OPTION, $s );
		MGPC_Catalog::flush_cache();
	}

	public static function page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		if ( isset( $_POST['mgpc_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['mgpc_nonce'] ), 'mgpc_save' ) ) {
			self::save( wp_unslash( $_POST ) ); // phpcs:ignore -- sanitized in save().
			echo '<div class="notice notice-success is-dismissible"><p>Đã lưu cài đặt.</p></div>';
		}
		$s     = self::get();
		$cats  = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
		$cats  = is_wp_error( $cats ) ? array() : $cats;
		$f     = function ( $key, $label, $suffix = '', $help = '' ) use ( $s ) {
			printf( '<tr><th><label for="mgpc_%1$s">%2$s</label></th><td><input type="text" class="regular-text" id="mgpc_%1$s" name="%1$s" value="%3$s"> %4$s%5$s</td></tr>', esc_attr( $key ), esc_html( $label ), esc_attr( $s[ $key ] ), esc_html( $suffix ), $help ? '<p class="description">' . esc_html( $help ) . '</p>' : '' );
		};
		$sw    = function ( $key, $label ) use ( $s ) {
			printf( '<label class="mg-admin-switch" style="display:flex;margin:6px 0"><input type="checkbox" name="%1$s" value="1" %2$s><span></span> %3$s</label>', esc_attr( $key ), checked( 'yes', $s[ $key ], false ), esc_html( $label ) );
		};
		$row   = function ( $i, $slot ) use ( $cats ) {
			$slot = wp_parse_args( $slot, array( 'key' => '', 'label' => '', 'role' => 'other', 'icon' => 'other', 'cats' => array(), 'required' => false, 'multi' => false ) );
			$n    = 'slots[' . $i . ']';
			echo '<tr class="mgpc-slot-row"><td class="mgpc-drag" title="Kéo để sắp xếp">⋮⋮</td>';
			echo '<td><input type="text" name="' . esc_attr( $n ) . '[label]" value="' . esc_attr( $slot['label'] ) . '" placeholder="Tên nhóm"><input type="hidden" name="' . esc_attr( $n ) . '[key]" value="' . esc_attr( $slot['key'] ) . '"></td>';
			echo '<td><select name="' . esc_attr( $n ) . '[role]">';
			foreach ( self::roles() as $k => $v ) {
				echo '<option value="' . esc_attr( $k ) . '" ' . selected( $slot['role'], $k, false ) . '>' . esc_html( $v ) . '</option>';
			}
			echo '</select></td><td><select name="' . esc_attr( $n ) . '[icon]">';
			foreach ( self::icons() as $ic ) {
				echo '<option value="' . esc_attr( $ic ) . '" ' . selected( $slot['icon'], $ic, false ) . '>' . esc_html( $ic ) . '</option>';
			}
			echo '</select></td><td><select class="mgpc-cats" multiple name="' . esc_attr( $n ) . '[cats][]" data-placeholder="Chọn danh mục…" style="width:100%">';
			foreach ( $cats as $c ) {
				echo '<option value="' . esc_attr( $c->slug ) . '" ' . selected( in_array( $c->slug, (array) $slot['cats'], true ), true, false ) . '>' . esc_html( $c->name ) . '</option>';
			}
			echo '</select></td>';
			echo '<td class="mgpc-center"><input type="checkbox" name="' . esc_attr( $n ) . '[required]" value="1" ' . checked( $slot['required'], true, false ) . '></td>';
			echo '<td class="mgpc-center"><input type="checkbox" name="' . esc_attr( $n ) . '[multi]" value="1" ' . checked( $slot['multi'], true, false ) . '></td>';
			echo '<td><button type="button" class="button-link mgpc-del" aria-label="Xoá">&times;</button></td></tr>';
		};
		?>
		<div class="wrap mg-admin mgpc-admin">
			<?php mg_core_admin_header( 'Build PC', 'Chèn trình build cấu hình bằng shortcode <code>[mg_pc_builder]</code>. Cấu hình gợi ý: <a href="' . esc_url( admin_url( 'edit.php?post_type=mgpc_preset' ) ) . '">Migi → Cấu hình gợi ý</a>.', 'dashicons-desktop' ); ?>
			<form method="post">
				<?php wp_nonce_field( 'mgpc_save', 'mgpc_nonce' ); ?>
				<nav class="nav-tab-wrapper mgpc-tabs">
					<a href="#mgpc-slots" class="nav-tab nav-tab-active">Nhóm linh kiện</a>
					<a href="#mgpc-compat" class="nav-tab">Tương thích</a>
					<a href="#mgpc-offer" class="nav-tab">Ưu đãi</a>
					<a href="#mgpc-display" class="nav-tab">Hiển thị &amp; tư vấn</a>
				</nav>
				<div class="mgpc-tab" id="mgpc-slots">
					<p class="description">Mỗi nhóm lấy sản phẩm từ các danh mục đã chọn. "Vai trò" dùng để kiểm tra tương thích. "Nhiều" cho phép chọn số lượng (RAM, ổ cứng, quạt...). Kéo ⋮⋮ để sắp xếp.</p>
					<table class="widefat mgpc-slots">
						<thead><tr><th></th><th>Tên nhóm</th><th>Vai trò</th><th>Biểu tượng</th><th style="width:34%">Danh mục sản phẩm</th><th>Bắt buộc</th><th>Nhiều</th><th></th></tr></thead>
						<tbody><?php foreach ( array_values( $s['slots'] ) as $i => $slot ) { $row( $i, $slot ); } ?></tbody>
						<tfoot><tr><td colspan="8"><button type="button" class="button mgpc-add">+ Thêm nhóm</button></td></tr></tfoot>
					</table>
					<script type="text/html" id="tmpl-mgpc-slot"><?php $row( '__i__', array() ); ?></script>
				</div>
				<div class="mgpc-tab" id="mgpc-compat" hidden>
					<table class="form-table">
						<tr><th>Lọc tương thích</th><td><?php $sw( 'compat_filter', 'Mặc định chỉ hiện linh kiện tương thích với cấu hình đang chọn (khách có thể tắt)' ); ?></td></tr>
						<?php
						$f( 'psu_headroom', 'Dự phòng công suất nguồn', '%', 'Nguồn nên lớn hơn tổng điện năng ước tính ít nhất mức này (khuyến nghị 30%).' );
						$f( 'base_watt', 'Điện năng nền (mainboard, ổ cứng, quạt)', 'W' );
						$f( 'attr_socket', 'Thuộc tính Socket', '', 'Slug thuộc tính WooCommerce, cách nhau dấu phẩy. Dùng khi sản phẩm chưa nhập thông số ở tab "Build PC".' );
						$f( 'attr_ram_type', 'Thuộc tính loại RAM' );
						$f( 'attr_form', 'Thuộc tính kích thước mainboard' );
						$f( 'attr_watt', 'Thuộc tính điện năng (TDP)' );
						$f( 'attr_psu_watt', 'Thuộc tính công suất nguồn' );
						?>
					</table>
				</div>
				<div class="mgpc-tab" id="mgpc-offer" hidden>
					<table class="form-table">
						<tr><th>Ưu đãi Build PC</th><td><?php $sw( 'discount_enabled', 'Giảm giá khi khách mua cả cấu hình từ trình Build PC' ); ?></td></tr>
						<?php
						$f( 'discount_percent', 'Mức giảm', '%', 'Tính trên tổng giá các linh kiện thuộc cấu hình.' );
						$f( 'discount_min', 'Số nhóm linh kiện tối thiểu', 'nhóm', 'VD 4: phải có ít nhất 4 nhóm khác nhau (CPU, Mainboard, RAM, SSD...).' );
						$f( 'discount_label', 'Tên hiển thị trong giỏ' );
						?>
					</table>
				</div>
				<div class="mgpc-tab" id="mgpc-display" hidden>
					<table class="form-table">
						<?php
						$f( 'title', 'Tiêu đề' );
						$f( 'intro', 'Mô tả ngắn' );
						$f( 'per_page', 'Số sản phẩm mỗi lần tải', 'sp' );
						$f( 'hotline', 'Hotline' );
						$f( 'zalo', 'Zalo (số hoặc link)' );
						?>
						<tr><th><label for="mgpc_note">Ghi chú dưới tạm tính</label></th><td><textarea id="mgpc_note" name="note" rows="3" class="large-text"><?php echo esc_textarea( $s['note'] ); ?></textarea></td></tr>
						<tr><th>Nhận tư vấn</th><td><?php $sw( 'consult_enabled', 'Hiện nút "Nhận tư vấn cấu hình" (khách để lại SĐT kèm cấu hình)' ); ?></td></tr>
						<?php
						$f( 'admin_email', 'Email nhận yêu cầu', '', 'Trống = email quản trị.' );
						$f( 'webhook', 'Webhook URL', '', 'Gửi yêu cầu tư vấn dạng JSON tới Google Sheets, Make, Zapier, CRM...' );
						?>
					</table>
				</div>
				<?php submit_button( 'Lưu cài đặt' ); ?>
			</form>
		</div>
		<?php
	}
}
