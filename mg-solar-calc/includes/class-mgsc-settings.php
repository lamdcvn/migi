<?php
defined( 'ABSPATH' ) || exit;

class MGSC_Settings {

	const OPTION = 'mgsc_settings';

	/**
	 * Default parameters. Prices are editable in the admin; check them against current EVN tariffs and your own quotes.
	 */
	public static function defaults() {
		return array(
			// EVN residential tiers: [kWh in tier (0 = unlimited), price VNĐ/kWh before VAT].
			'tiers'            => array( array( 50, 1984 ), array( 50, 2050 ), array( 100, 2380 ), array( 100, 2998 ), array( 100, 3350 ), array( 0, 3460 ) ),
			'vat'              => 8,
			'business_price'   => 3152,
			'sun_nam'          => 4.6,
			'sun_trung'        => 4.4,
			'sun_bac'          => 3.6,
			'performance'      => 80,
			'panel_w'          => 550,
			'panel_m2'         => 2.6,
			'price_ongrid'     => 11000000,
			'price_hybrid'     => 14000000,
			'price_offgrid'    => 16000000,
			'battery_price'    => 5500000,
			'battery_module'   => 5,
			'battery_dod'      => 90,
			'surcharge_3pha'   => 5,
			'max_1pha_kw'      => 10,
			'inverter_sizes'   => '3,5,6,8,10,12,15,20,25,30,40,50,60,80,100',
			'sell_price'       => 0,
			'sell_cap'         => 20,
			'degradation'      => 0.5,
			'price_increase'   => 3,
			'years'            => 25,
			'co2_factor'       => 0.6766,
			// Form.
			'title'            => 'Công cụ tính toán hệ thống điện mặt trời phù hợp',
			'default_bill'     => 1500000,
			'default_area'     => 30,
			'default_day'      => 50,
			'enable_types'     => 'yes',
			'enable_customer'  => 'yes',
			'require_lead'     => 'yes',
			'lead_email_req'   => 'no',
			'lead_address'     => 'yes',
			'lead_intro'       => 'Vui lòng nhập thông tin liên hệ để nhận kết quả và được tư vấn miễn phí',
			'hotline'          => '',
			'zalo'             => '',
			'cta_text'         => 'Nhận báo giá chi tiết',
			'cta_url'          => '',
			'disclaimer'       => 'Kết quả mang tính tham khảo. Chi phí và sản lượng thực tế phụ thuộc vào khảo sát mái, thiết bị và thời tiết.',
			// Notifications.
			'admin_email'      => '',
			'email_customer'   => 'yes',
			'webhook'          => '',
			'packages'         => array(),
			'accent'           => '#28a745',
		);
	}

	public static function get() {
		$s = wp_parse_args( (array) get_option( self::OPTION, array() ), self::defaults() );
		if ( ! $s['admin_email'] ) {
			$s['admin_email'] = get_option( 'admin_email' );
		}
		return $s;
	}

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function menu() {
		add_submenu_page( 'migi', 'Cài đặt tính điện mặt trời', 'Điện mặt trời – Cài đặt', 'manage_options', 'mgsc-settings', array( __CLASS__, 'page' ) );
	}

	public static function assets( $hook ) {
		if ( false === strpos( (string) $hook, 'mgsc-settings' ) ) {
			return;
		}
		wp_enqueue_style( 'mgsc-admin', MGSC_URL . 'assets/css/admin.css', array(), MGSC_VERSION );
		wp_enqueue_script( 'mgsc-admin', MGSC_URL . 'assets/js/admin.js', array( 'jquery' ), MGSC_VERSION, true );
	}

	private static function numeric_keys() {
		return array( 'vat', 'business_price', 'sun_nam', 'sun_trung', 'sun_bac', 'performance', 'panel_w', 'panel_m2', 'price_ongrid', 'price_hybrid', 'price_offgrid', 'battery_price', 'battery_module', 'battery_dod', 'surcharge_3pha', 'max_1pha_kw', 'sell_price', 'sell_cap', 'degradation', 'price_increase', 'years', 'co2_factor', 'default_bill', 'default_area', 'default_day' );
	}

	private static function save( $post ) {
		$d = self::defaults();
		$s = array();
		foreach ( self::numeric_keys() as $k ) {
			$v       = isset( $post[ $k ] ) ? str_replace( array( ' ', '.' ), '', (string) $post[ $k ] ) : '';
			$v       = str_replace( ',', '.', $v );
			$s[ $k ] = is_numeric( $v ) ? max( 0, (float) $v ) : $d[ $k ];
		}
		// Decimal fields typed with a dot must keep it: re-read them without stripping dots.
		foreach ( array( 'sun_nam', 'sun_trung', 'sun_bac', 'panel_m2', 'degradation', 'price_increase', 'co2_factor', 'vat', 'surcharge_3pha' ) as $k ) {
			$v       = isset( $post[ $k ] ) ? str_replace( ',', '.', trim( (string) $post[ $k ] ) ) : '';
			$s[ $k ] = is_numeric( $v ) ? max( 0, (float) $v ) : $d[ $k ];
		}
		$s['years'] = max( 1, min( 40, (int) $s['years'] ) );
		foreach ( array( 'title', 'lead_intro', 'hotline', 'zalo', 'cta_text', 'inverter_sizes' ) as $k ) {
			$s[ $k ] = isset( $post[ $k ] ) ? sanitize_text_field( wp_unslash( $post[ $k ] ) ) : '';
		}
		$s['disclaimer']  = isset( $post['disclaimer'] ) ? sanitize_textarea_field( wp_unslash( $post['disclaimer'] ) ) : '';
		$s['cta_url']     = isset( $post['cta_url'] ) ? esc_url_raw( wp_unslash( $post['cta_url'] ) ) : '';
		$s['webhook']     = isset( $post['webhook'] ) ? esc_url_raw( wp_unslash( $post['webhook'] ) ) : '';
		$s['admin_email'] = isset( $post['admin_email'] ) ? implode( ',', array_filter( array_map( 'sanitize_email', explode( ',', wp_unslash( $post['admin_email'] ) ) ) ) ) : '';
		$s['accent']      = isset( $post['accent'] ) && sanitize_hex_color( $post['accent'] ) ? sanitize_hex_color( $post['accent'] ) : $d['accent'];
		foreach ( array( 'enable_types', 'enable_customer', 'require_lead', 'lead_email_req', 'lead_address', 'email_customer' ) as $k ) {
			$s[ $k ] = empty( $post[ $k ] ) ? 'no' : 'yes';
		}

		$tiers = array();
		if ( isset( $post['tiers'] ) && is_array( $post['tiers'] ) ) {
			foreach ( $post['tiers'] as $t ) {
				$size  = isset( $t[0] ) ? absint( $t[0] ) : 0;
				$price = isset( $t[1] ) ? (float) str_replace( array( '.', ',' ), array( '', '.' ), $t[1] ) : 0;
				if ( $price > 0 ) {
					$tiers[] = array( $size, $price );
				}
			}
		}
		$s['tiers'] = $tiers ? $tiers : $d['tiers'];

		$packages = array();
		if ( isset( $post['packages'] ) && is_array( $post['packages'] ) ) {
			foreach ( $post['packages'] as $p ) {
				$title = isset( $p['title'] ) ? sanitize_text_field( wp_unslash( $p['title'] ) ) : '';
				$pid   = isset( $p['product_id'] ) ? absint( $p['product_id'] ) : 0;
				$url   = isset( $p['url'] ) ? esc_url_raw( wp_unslash( $p['url'] ) ) : '';
				if ( ! $title && ! $pid && ! $url ) {
					continue;
				}
				$packages[] = array(
					'min'        => isset( $p['min'] ) ? (float) str_replace( ',', '.', $p['min'] ) : 0,
					'max'        => isset( $p['max'] ) ? (float) str_replace( ',', '.', $p['max'] ) : 0,
					'title'      => $title,
					'product_id' => $pid,
					'url'        => $url,
				);
			}
		}
		$s['packages'] = $packages;
		update_option( self::OPTION, $s );
	}

	private static function field( $s, $key, $label, $suffix = '', $help = '', $type = 'text' ) {
		printf(
			'<tr><th><label for="mgsc_%1$s">%2$s</label></th><td><input type="%6$s" id="mgsc_%1$s" name="%1$s" value="%3$s" class="regular-text"> %4$s%5$s</td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( is_array( $s[ $key ] ) ? '' : $s[ $key ] ),
			esc_html( $suffix ),
			$help ? '<p class="description">' . esc_html( $help ) . '</p>' : '',
			esc_attr( $type )
		);
	}

	private static function check( $s, $key, $label ) {
		printf( '<label class="mgsc-check"><input type="checkbox" name="%1$s" value="1" %2$s> %3$s</label>', esc_attr( $key ), checked( 'yes', $s[ $key ], false ), esc_html( $label ) );
	}

	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( isset( $_POST['mgsc_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['mgsc_nonce'] ), 'mgsc_save' ) ) {
			self::save( $_POST ); // phpcs:ignore -- each field is sanitized in save().
			echo '<div class="notice notice-success is-dismissible"><p>Đã lưu cài đặt.</p></div>';
		}
		$s = self::get();
		?>
		<div class="wrap mgsc-admin">
			<h1>Tính hệ thống điện mặt trời – Cài đặt</h1>
			<p>Chèn công cụ vào trang bằng shortcode <code>[mg_solar_calc]</code> (dùng được trong Flatsome UX Builder qua phần tử "Shortcode" hoặc "Text").</p>
			<form method="post">
				<?php wp_nonce_field( 'mgsc_save', 'mgsc_nonce' ); ?>
				<nav class="nav-tab-wrapper mgsc-tabs">
					<a href="#tab-price" class="nav-tab nav-tab-active">Giá điện EVN</a>
					<a href="#tab-tech" class="nav-tab">Thông số kỹ thuật</a>
					<a href="#tab-cost" class="nav-tab">Chi phí &amp; tài chính</a>
					<a href="#tab-form" class="nav-tab">Biểu mẫu</a>
					<a href="#tab-lead" class="nav-tab">Thông báo lead</a>
					<a href="#tab-pack" class="nav-tab">Gói sản phẩm</a>
				</nav>

				<div class="mgsc-tab" id="tab-price">
					<h2>Giá bán lẻ điện sinh hoạt bậc thang</h2>
					<p class="description">Số kWh mỗi bậc (0 = không giới hạn, dùng cho bậc cuối) và đơn giá chưa VAT. Hãy cập nhật khi EVN điều chỉnh giá.</p>
					<table class="widefat mgsc-repeat" data-name="tiers" style="max-width:520px">
						<thead><tr><th>Bậc</th><th>Số kWh trong bậc</th><th>Đơn giá (VNĐ/kWh)</th><th></th></tr></thead>
						<tbody>
						<?php foreach ( $s['tiers'] as $i => $t ) : ?>
							<tr><td class="mgsc-idx"><?php echo (int) $i + 1; ?></td>
								<td><input type="number" min="0" name="tiers[<?php echo (int) $i; ?>][0]" value="<?php echo esc_attr( $t[0] ); ?>"></td>
								<td><input type="number" min="0" step="any" name="tiers[<?php echo (int) $i; ?>][1]" value="<?php echo esc_attr( $t[1] ); ?>"></td>
								<td><button type="button" class="button-link mgsc-del">&times;</button></td></tr>
						<?php endforeach; ?>
						</tbody>
						<tfoot><tr><td colspan="4"><button type="button" class="button mgsc-add">+ Thêm bậc</button></td></tr></tfoot>
					</table>
					<table class="form-table">
						<?php
						self::field( $s, 'vat', 'Thuế VAT', '%' );
						self::field( $s, 'business_price', 'Giá điện kinh doanh (bình quân, chưa VAT)', 'VNĐ/kWh', 'Dùng khi khách chọn "Kinh doanh / sản xuất".' );
						?>
					</table>
				</div>

				<div class="mgsc-tab" id="tab-tech" hidden>
					<table class="form-table">
						<?php
						self::field( $s, 'sun_nam', 'Số giờ nắng/ngày – Miền Nam', 'giờ' );
						self::field( $s, 'sun_trung', 'Số giờ nắng/ngày – Miền Trung', 'giờ' );
						self::field( $s, 'sun_bac', 'Số giờ nắng/ngày – Miền Bắc', 'giờ' );
						self::field( $s, 'performance', 'Hiệu suất hệ thống (PR)', '%', 'Tổn hao nhiệt, dây dẫn, inverter, bụi bẩn... thường 75–85%.' );
						self::field( $s, 'panel_w', 'Công suất 1 tấm pin', 'Wp' );
						self::field( $s, 'panel_m2', 'Diện tích mái cho 1 tấm', 'm²', 'Bao gồm khoảng hở lắp đặt.' );
						self::field( $s, 'inverter_sizes', 'Các cỡ inverter (kW)', '', 'Cách nhau bởi dấu phẩy, hệ thống chọn cỡ phù hợp gần nhất.' );
						self::field( $s, 'max_1pha_kw', 'Công suất tối đa cho điện 1 pha', 'kW', 'Vượt mức này sẽ khuyến nghị dùng điện 3 pha.' );
						self::field( $s, 'battery_module', 'Dung lượng 1 module pin lưu trữ', 'kWh' );
						self::field( $s, 'battery_dod', 'Độ xả sâu pin (DoD)', '%' );
						self::field( $s, 'co2_factor', 'Hệ số phát thải lưới điện', 'kg CO₂/kWh' );
						?>
					</table>
				</div>

				<div class="mgsc-tab" id="tab-cost" hidden>
					<table class="form-table">
						<?php
						self::field( $s, 'price_ongrid', 'Đơn giá hệ hoà lưới', 'VNĐ/kWp' );
						self::field( $s, 'price_hybrid', 'Đơn giá hệ hybrid (chưa gồm pin)', 'VNĐ/kWp' );
						self::field( $s, 'price_offgrid', 'Đơn giá hệ độc lập (chưa gồm pin)', 'VNĐ/kWp' );
						self::field( $s, 'battery_price', 'Giá pin lưu trữ', 'VNĐ/kWh' );
						self::field( $s, 'surcharge_3pha', 'Phụ phí hệ 3 pha', '%' );
						self::field( $s, 'sell_price', 'Giá bán điện dư cho EVN', 'VNĐ/kWh', 'Để 0 nếu không bán điện dư.' );
						self::field( $s, 'sell_cap', 'Tỷ lệ điện dư tối đa được bán', '% sản lượng' );
						self::field( $s, 'degradation', 'Suy giảm hiệu suất tấm pin mỗi năm', '%' );
						self::field( $s, 'price_increase', 'Giá điện tăng mỗi năm (dự kiến)', '%' );
						self::field( $s, 'years', 'Số năm tính hiệu quả đầu tư', 'năm' );
						?>
					</table>
				</div>

				<div class="mgsc-tab" id="tab-form" hidden>
					<table class="form-table">
						<?php
						self::field( $s, 'title', 'Tiêu đề công cụ' );
						self::field( $s, 'default_bill', 'Hoá đơn mặc định', 'VNĐ' );
						self::field( $s, 'default_area', 'Diện tích mái mặc định', 'm²' );
						self::field( $s, 'default_day', 'Tỷ lệ dùng ban ngày mặc định', '%' );
						?>
						<tr><th>Tuỳ chọn</th><td>
							<?php
							self::check( $s, 'enable_types', 'Cho khách chọn loại hệ thống (Hoà lưới / Hybrid có pin / Độc lập)' );
							self::check( $s, 'enable_customer', 'Cho khách chọn đối tượng (Hộ gia đình / Kinh doanh)' );
							self::check( $s, 'require_lead', 'Bắt buộc nhập thông tin liên hệ trước khi xem kết quả' );
							self::check( $s, 'lead_email_req', 'Bắt buộc nhập email' );
							self::check( $s, 'lead_address', 'Hiện ô Địa chỉ / Tỉnh thành' );
							?>
						</td></tr>
						<?php
						self::field( $s, 'lead_intro', 'Lời nhắc ở bước liên hệ' );
						self::field( $s, 'hotline', 'Hotline', '', 'Hiện nút gọi ở trang kết quả.' );
						self::field( $s, 'zalo', 'Số / link Zalo', '', 'VD: 0901234567 hoặc https://zalo.me/...' );
						self::field( $s, 'cta_text', 'Chữ nút kêu gọi' );
						self::field( $s, 'cta_url', 'Link nút kêu gọi', '', 'Để trống để ẩn.' );
						self::field( $s, 'accent', 'Màu chủ đạo', '', '', 'color' );
						?>
						<tr><th><label for="mgsc_disclaimer">Ghi chú dưới kết quả</label></th><td><textarea id="mgsc_disclaimer" name="disclaimer" rows="3" class="large-text"><?php echo esc_textarea( $s['disclaimer'] ); ?></textarea></td></tr>
					</table>
				</div>

				<div class="mgsc-tab" id="tab-lead" hidden>
					<table class="form-table">
						<?php self::field( $s, 'admin_email', 'Email nhận thông báo', '', 'Nhiều email cách nhau bởi dấu phẩy.' ); ?>
						<tr><th>Khách hàng</th><td><?php self::check( $s, 'email_customer', 'Gửi kết quả tính toán vào email của khách' ); ?></td></tr>
						<?php self::field( $s, 'webhook', 'Webhook URL', '', 'Gửi lead dạng JSON (POST) tới Google Apps Script, Make, Zapier, n8n, CRM...' ); ?>
					</table>
				</div>

				<div class="mgsc-tab" id="tab-pack" hidden>
					<p class="description">Gợi ý gói/sản phẩm theo công suất tính được. Nếu có WooCommerce, nhập ID sản phẩm để hiện ảnh, giá và nút đặt mua.</p>
					<table class="widefat mgsc-repeat" data-name="packages">
						<thead><tr><th>Từ (kWp)</th><th>Đến (kWp)</th><th>Tên gói</th><th>ID sản phẩm Woo</th><th>Hoặc link</th><th></th></tr></thead>
						<tbody>
						<?php
						$packs = $s['packages'] ? $s['packages'] : array( array( 'min' => '', 'max' => '', 'title' => '', 'product_id' => '', 'url' => '' ) );
						foreach ( $packs as $i => $p ) :
							?>
							<tr>
								<td><input type="number" step="any" min="0" name="packages[<?php echo (int) $i; ?>][min]" value="<?php echo esc_attr( $p['min'] ); ?>"></td>
								<td><input type="number" step="any" min="0" name="packages[<?php echo (int) $i; ?>][max]" value="<?php echo esc_attr( $p['max'] ); ?>"></td>
								<td><input type="text" name="packages[<?php echo (int) $i; ?>][title]" value="<?php echo esc_attr( $p['title'] ); ?>" placeholder="Gói 5kWp hoà lưới"></td>
								<td><input type="number" min="0" name="packages[<?php echo (int) $i; ?>][product_id]" value="<?php echo esc_attr( $p['product_id'] ); ?>"></td>
								<td><input type="url" name="packages[<?php echo (int) $i; ?>][url]" value="<?php echo esc_attr( $p['url'] ); ?>"></td>
								<td><button type="button" class="button-link mgsc-del">&times;</button></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
						<tfoot><tr><td colspan="6"><button type="button" class="button mgsc-add">+ Thêm gói</button></td></tr></tfoot>
					</table>
				</div>
				<?php submit_button( 'Lưu cài đặt' ); ?>
			</form>
		</div>
		<?php
	}
}
