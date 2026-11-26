<?php
defined( 'ABSPATH' ) || exit;

class MGSC_Shortcode {

	public static function init() {
		add_shortcode( 'mg_solar_calc', array( __CLASS__, 'render' ) );
		add_shortcode( 'vt_solar_calc', array( __CLASS__, 'render' ) ); // Old name, kept so existing pages keep working.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
		add_action( 'wp_ajax_mgsc_calc', array( __CLASS__, 'ajax' ) );
		add_action( 'wp_ajax_nopriv_mgsc_calc', array( __CLASS__, 'ajax' ) );
	}

	public static function register_assets() {
		wp_register_style( 'mgsc', MGSC_URL . 'assets/css/solar.css', array(), MGSC_VERSION );
		wp_register_script( 'mgsc', MGSC_URL . 'assets/js/solar.js', array( 'jquery' ), MGSC_VERSION, true );
		wp_localize_script( 'mgsc', 'mgscData', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}

	public static function render( $atts ) {
		$s    = MGSC_Settings::get();
		$atts = shortcode_atts( array( 'title' => $s['title'], 'type' => '' ), $atts, 'mg_solar_calc' );
		wp_enqueue_style( 'mgsc' );
		wp_enqueue_script( 'mgsc' );
		static $n = 0;
		$n++;
		$uid          = 'mgsc-' . $n;
		$fixed_type   = array_key_exists( $atts['type'], MGSC_Render::$types ) ? $atts['type'] : '';
		$require_lead = 'yes' === $s['require_lead'];
		ob_start();
		?>
		<div class="mgsc" id="<?php echo esc_attr( $uid ); ?>" style="--mgsc-accent:<?php echo esc_attr( $s['accent'] ); ?>" data-require-lead="<?php echo $require_lead ? '1' : '0'; ?>">
			<?php if ( $atts['title'] ) : ?>
				<h3 class="mgsc-title"><?php echo esc_html( $atts['title'] ); ?></h3>
			<?php endif; ?>
			<ol class="mgsc-steps">
				<li class="is-active">Thông tin sử dụng điện</li>
				<li>Thông tin liên hệ</li>
				<li>Kết quả</li>
			</ol>
			<form class="mgsc-form" novalidate>
				<input type="text" name="website" class="mgsc-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
				<div class="mgsc-step mgsc-step-1 is-active">
					<div class="mgsc-row">
						<div class="mgsc-field">
							<label for="<?php echo esc_attr( $uid ); ?>-bill">Hoá đơn tiền điện hàng tháng (VNĐ)</label>
							<input type="text" inputmode="numeric" id="<?php echo esc_attr( $uid ); ?>-bill" name="bill" class="mgsc-money" value="<?php echo esc_attr( MGSC_Render::num( $s['default_bill'] ) ); ?>" required>
							<input type="range" class="mgsc-bill-range" min="300000" max="20000000" step="100000" value="<?php echo esc_attr( $s['default_bill'] ); ?>" aria-label="Kéo để chọn hoá đơn">
							<small class="mgsc-hint" data-kwh-hint></small>
						</div>
						<div class="mgsc-field">
							<label for="<?php echo esc_attr( $uid ); ?>-area">Diện tích mái nhà (m²)</label>
							<input type="number" min="1" step="any" id="<?php echo esc_attr( $uid ); ?>-area" name="area" value="<?php echo esc_attr( $s['default_area'] ); ?>" required>
							<small class="mgsc-hint">Phần mái thông thoáng, ít bị che nắng</small>
						</div>
					</div>
					<div class="mgsc-row">
						<div class="mgsc-field">
							<span class="mgsc-label">Khu vực sinh sống</span>
							<div class="mgsc-pills">
								<?php foreach ( MGSC_Render::$regions as $k => $v ) : ?>
									<label><input type="radio" name="region" value="<?php echo esc_attr( $k ); ?>" <?php checked( 'nam', $k ); ?>><span><?php echo esc_html( $v ); ?></span></label>
								<?php endforeach; ?>
							</div>
						</div>
						<div class="mgsc-field">
							<span class="mgsc-label">Loại mạng điện</span>
							<div class="mgsc-pills">
								<label><input type="radio" name="phase" value="1" checked><span>Một pha</span></label>
								<label><input type="radio" name="phase" value="3"><span>Ba pha</span></label>
							</div>
						</div>
					</div>
					<?php if ( 'yes' === $s['enable_types'] && ! $fixed_type ) : ?>
						<div class="mgsc-field">
							<span class="mgsc-label">Loại hệ thống</span>
							<div class="mgsc-pills mgsc-pills-wide">
								<?php
								$desc = array( 'ongrid' => 'Chi phí thấp, dùng điện ban ngày', 'hybrid' => 'Có pin, dùng cả ban đêm & khi mất điện', 'offgrid' => 'Không cần lưới điện' );
								foreach ( MGSC_Render::$types as $k => $v ) :
									?>
									<label><input type="radio" name="type" value="<?php echo esc_attr( $k ); ?>" <?php checked( 'ongrid', $k ); ?>><span><strong><?php echo esc_html( $v ); ?></strong><small><?php echo esc_html( $desc[ $k ] ); ?></small></span></label>
								<?php endforeach; ?>
							</div>
						</div>
					<?php else : ?>
						<input type="hidden" name="type" value="<?php echo esc_attr( $fixed_type ? $fixed_type : 'ongrid' ); ?>">
					<?php endif; ?>
					<div class="mgsc-row">
						<?php if ( 'yes' === $s['enable_customer'] ) : ?>
							<div class="mgsc-field">
								<span class="mgsc-label">Đối tượng sử dụng</span>
								<div class="mgsc-pills">
									<?php foreach ( MGSC_Render::$customers as $k => $v ) : ?>
										<label><input type="radio" name="customer" value="<?php echo esc_attr( $k ); ?>" <?php checked( 'home', $k ); ?>><span><?php echo esc_html( $v ); ?></span></label>
									<?php endforeach; ?>
								</div>
							</div>
						<?php else : ?>
							<input type="hidden" name="customer" value="home">
						<?php endif; ?>
						<div class="mgsc-field">
							<label for="<?php echo esc_attr( $uid ); ?>-day">Tỷ lệ dùng điện ban ngày: <output data-day-out><?php echo (int) $s['default_day']; ?></output>%</label>
							<input type="range" min="10" max="100" step="5" id="<?php echo esc_attr( $uid ); ?>-day" name="day" value="<?php echo esc_attr( $s['default_day'] ); ?>">
							<small class="mgsc-hint">Đi làm cả ngày ~30–40%, ở nhà/kinh doanh ban ngày ~60–80%</small>
						</div>
					</div>
					<div class="mgsc-nav">
						<button type="button" class="mgsc-btn mgsc-next">Tính toán</button>
					</div>
				</div>

				<div class="mgsc-step mgsc-step-2">
					<p class="mgsc-intro"><?php echo esc_html( $s['lead_intro'] ); ?></p>
					<div class="mgsc-field"><input type="text" name="name" placeholder="Họ và tên *" autocomplete="name" required></div>
					<div class="mgsc-field"><input type="tel" name="phone" placeholder="Số điện thoại *" autocomplete="tel" required></div>
					<div class="mgsc-field"><input type="email" name="email" placeholder="Email<?php echo 'yes' === $s['lead_email_req'] ? ' *' : ' (nhận kết quả qua email)'; ?>" autocomplete="email" <?php echo 'yes' === $s['lead_email_req'] ? 'required' : ''; ?>></div>
					<?php if ( 'yes' === $s['lead_address'] ) : ?>
						<div class="mgsc-field"><input type="text" name="address" placeholder="Địa chỉ / Tỉnh thành" autocomplete="street-address"></div>
					<?php endif; ?>
					<div class="mgsc-nav">
						<button type="button" class="mgsc-btn mgsc-btn-outline mgsc-back">Quay lại</button>
						<?php if ( ! $require_lead ) : ?>
							<button type="button" class="mgsc-btn mgsc-btn-link mgsc-skip">Bỏ qua, xem kết quả</button>
						<?php endif; ?>
						<button type="button" class="mgsc-btn mgsc-submit">Nhận kết quả</button>
					</div>
				</div>

				<div class="mgsc-step mgsc-step-3">
					<div class="mgsc-result" aria-live="polite"></div>
				</div>
				<div class="mgsc-error" role="alert" hidden></div>
			</form>
			<script type="application/json" class="mgsc-tiers"><?php echo wp_json_encode( array( 'tiers' => $s['tiers'], 'vat' => (float) $s['vat'], 'biz' => (float) $s['business_price'] ) ); ?></script>
		</div>
		<?php
		return ob_get_clean();
	}

	private static function ip() {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	}

	private static function rate_ok( $bucket, $limit, $window ) {
		$key   = 'mgsc_rl_' . $bucket . '_' . md5( self::ip() );
		$count = (int) get_transient( $key );
		if ( $count >= $limit ) {
			return false;
		}
		set_transient( $key, $count + 1, $window );
		return true;
	}

	/**
	 * Public endpoint. No nonce on purpose: pages with this form are usually full-page cached and a stale
	 * nonce would break the form for visitors. Abuse is limited by honeypot + per-IP rate limits; the
	 * endpoint only computes numbers and stores a lead.
	 */
	public static function ajax() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$p = wp_unslash( $_POST );
		if ( ! empty( $p['website'] ) ) {
			wp_send_json_error( array( 'message' => 'Yêu cầu không hợp lệ.' ), 400 );
		}
		if ( ! self::rate_ok( 'calc', 30, 10 * MINUTE_IN_SECONDS ) ) {
			wp_send_json_error( array( 'message' => 'Bạn thao tác quá nhanh, vui lòng thử lại sau ít phút.' ), 429 );
		}
		$s    = MGSC_Settings::get();
		$bill = isset( $p['bill'] ) ? (float) preg_replace( '/\D/', '', (string) $p['bill'] ) : 0;
		$in   = array(
			'bill'     => min( $bill, 5e9 ),
			'area'     => isset( $p['area'] ) ? min( 100000, (float) str_replace( ',', '.', (string) $p['area'] ) ) : 0,
			'region'   => isset( $p['region'] ) ? sanitize_key( $p['region'] ) : 'nam',
			'phase'    => isset( $p['phase'] ) ? (int) $p['phase'] : 1,
			'day'      => isset( $p['day'] ) ? (float) $p['day'] : 50,
			'type'     => isset( $p['type'] ) ? sanitize_key( $p['type'] ) : 'ongrid',
			'customer' => isset( $p['customer'] ) ? sanitize_key( $p['customer'] ) : 'home',
		);
		if ( 'yes' !== $s['enable_types'] && ! in_array( $in['type'], array( 'ongrid', 'hybrid', 'offgrid' ), true ) ) {
			$in['type'] = 'ongrid';
		}
		if ( $in['bill'] < 50000 ) {
			wp_send_json_error( array( 'message' => 'Vui lòng nhập hoá đơn tiền điện (tối thiểu 50.000đ).', 'field' => 'bill' ) );
		}
		if ( $in['area'] <= 0 ) {
			wp_send_json_error( array( 'message' => 'Vui lòng nhập diện tích mái nhà.', 'field' => 'area' ) );
		}

		$calc = new MGSC_Calculator( $s );
		$r    = $calc->calculate( $in );
		if ( isset( $r['error'] ) ) {
			wp_send_json_error( array( 'message' => $r['error'], 'field' => 'area' ) );
		}

		$skip = ! empty( $p['skip'] ) && 'yes' !== $s['require_lead'];
		if ( ! $skip ) {
			$lead = array(
				'name'    => isset( $p['name'] ) ? sanitize_text_field( $p['name'] ) : '',
				'phone'   => isset( $p['phone'] ) ? preg_replace( '/[^0-9+]/', '', (string) $p['phone'] ) : '',
				'email'   => isset( $p['email'] ) ? sanitize_email( $p['email'] ) : '',
				'address' => isset( $p['address'] ) ? sanitize_text_field( $p['address'] ) : '',
				'page'    => isset( $p['page'] ) ? esc_url_raw( $p['page'] ) : '',
				'ip'      => self::ip(),
			);
			if ( mb_strlen( $lead['name'] ) < 2 ) {
				wp_send_json_error( array( 'message' => 'Vui lòng nhập họ tên.', 'field' => 'name' ) );
			}
			if ( ! preg_match( '/^(0|\+?84)(3|5|7|8|9)\d{8}$/', $lead['phone'] ) ) {
				wp_send_json_error( array( 'message' => 'Số điện thoại không hợp lệ.', 'field' => 'phone' ) );
			}
			if ( ( 'yes' === $s['lead_email_req'] || ( isset( $p['email'] ) && '' !== trim( (string) $p['email'] ) ) ) && ! is_email( $lead['email'] ) ) {
				wp_send_json_error( array( 'message' => 'Email không hợp lệ.', 'field' => 'email' ) );
			}
			// Same phone within 10 minutes: show the result again but do not create a duplicate lead.
			$dupe = 'mgsc_dupe_' . md5( $lead['phone'] );
			if ( ! get_transient( $dupe ) && self::rate_ok( 'lead', 10, HOUR_IN_SECONDS ) ) {
				MGSC_Leads::create( $lead, $r );
				set_transient( $dupe, 1, 10 * MINUTE_IN_SECONDS );
			}
		}

		wp_send_json_success( array( 'html' => MGSC_Render::result( $r, $s ) ) );
		// phpcs:enable
	}
}
