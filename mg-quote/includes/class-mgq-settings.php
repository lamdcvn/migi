<?php
defined( 'ABSPATH' ) || exit;

class MGQ_Settings {

	const OPTION = 'mgq_settings';

	public static function get() {
		return wp_parse_args( (array) get_option( self::OPTION, array() ), array(
			'company'          => get_bloginfo( 'name' ),
			'logo'             => 0,
			'address'          => '',
			'phone'            => '',
			'email'            => get_option( 'admin_email' ),
			'website'          => home_url( '/' ),
			'tax_code'         => '',
			'bank'             => '',
			'bank_account'     => '',
			'bank_holder'      => '',
			'signer'           => '',
			'signer_title'     => 'Giám đốc',
			'prefix'           => 'BG',
			'valid_days'       => 15,
			'vat'              => 8,
			'price_has_vat'    => 'yes',
			'terms'            => "Báo giá có hiệu lực trong thời hạn ghi trên.\nGiá đã bao gồm VAT (nếu có ghi chú).\nThanh toán: chuyển khoản hoặc tiền mặt khi nhận hàng.\nBảo hành theo chính sách của nhà sản xuất.",
			'footer'           => 'Cảm ơn Quý khách đã quan tâm. Mọi thắc mắc vui lòng liên hệ hotline.',
			'accent'           => '#ff8a00',
			'standard_enabled' => 'yes',
			'show_list_price'  => 'yes',
			'email_subject'    => 'Báo giá {number} – {company}',
			'email_body'       => "Kính gửi {customer},\n\n{company} xin gửi Quý khách báo giá {number} (đính kèm PDF).\nQuý khách có thể xem và xác nhận báo giá trực tuyến tại: {link}\n\nTrân trọng,\n{company}",
		) );
	}

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 20 );
	}

	public static function menu() {
		add_submenu_page( 'migi', 'Báo giá – Cài đặt', 'Báo giá – Cài đặt', 'manage_woocommerce', 'mg-quote', array( __CLASS__, 'page' ) );
	}

	public static function logo_url() {
		$id = (int) self::get()['logo'];
		return $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	}

	private static function save( $p ) {
		$s = self::get();
		foreach ( array( 'company', 'address', 'phone', 'tax_code', 'bank', 'bank_account', 'bank_holder', 'signer', 'signer_title', 'prefix', 'email_subject' ) as $k ) {
			$s[ $k ] = sanitize_text_field( $p[ $k ] ?? '' );
		}
		foreach ( array( 'terms', 'footer', 'email_body' ) as $k ) {
			$s[ $k ] = sanitize_textarea_field( $p[ $k ] ?? '' );
		}
		$s['email']   = sanitize_email( $p['email'] ?? '' );
		$s['website'] = esc_url_raw( $p['website'] ?? '' );
		$s['logo']    = absint( $p['logo'] ?? 0 );
		$s['accent']  = sanitize_hex_color( $p['accent'] ?? '' ) ?: '#ff8a00';
		$s['prefix']  = strtoupper( preg_replace( '/[^A-Za-z0-9\-]/', '', $s['prefix'] ) ) ?: 'BG';
		$s['valid_days'] = max( 1, absint( $p['valid_days'] ?? 15 ) );
		$s['vat']        = max( 0, min( 100, (float) str_replace( ',', '.', (string) ( $p['vat'] ?? 0 ) ) ) );
		foreach ( array( 'price_has_vat', 'standard_enabled', 'show_list_price' ) as $k ) {
			$s[ $k ] = empty( $p[ $k ] ) ? 'no' : 'yes';
		}
		update_option( self::OPTION, $s );
	}

	public static function page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		if ( isset( $_POST['mgq_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['mgq_nonce'] ), 'mgq_settings' ) ) {
			self::save( wp_unslash( $_POST ) ); // phpcs:ignore -- sanitized in save().
			echo '<div class="notice notice-success is-dismissible"><p>Đã lưu.</p></div>';
		}
		wp_enqueue_media();
		$s  = self::get();
		$f  = function ( $k, $label, $help = '', $type = 'text' ) use ( $s ) {
			printf( '<tr><th><label for="mgq_%1$s">%2$s</label></th><td><input type="%5$s" class="regular-text" id="mgq_%1$s" name="%1$s" value="%3$s">%4$s</td></tr>', esc_attr( $k ), esc_html( $label ), esc_attr( $s[ $k ] ), $help ? '<p class="description">' . esc_html( $help ) . '</p>' : '', esc_attr( $type ) );
		};
		$ta = function ( $k, $label, $help = '' ) use ( $s ) {
			printf( '<tr><th><label for="mgq_%1$s">%2$s</label></th><td><textarea id="mgq_%1$s" name="%1$s" rows="5" class="large-text">%3$s</textarea>%4$s</td></tr>', esc_attr( $k ), esc_html( $label ), esc_textarea( $s[ $k ] ), $help ? '<p class="description">' . esc_html( $help ) . '</p>' : '' );
		};
		$sw = function ( $k, $label ) use ( $s ) {
			printf( '<label class="mg-admin-switch" style="display:flex;margin:6px 0"><input type="checkbox" name="%1$s" value="1" %2$s><span></span> %3$s</label>', esc_attr( $k ), checked( 'yes', $s[ $k ], false ), esc_html( $label ) );
		};
		$logo = self::logo_url();
		?>
		<div class="wrap mg-admin">
			<?php mg_core_admin_header( 'Báo giá', 'Thông tin công ty in trên PDF, quy tắc đánh số, VAT và mẫu email. Danh sách báo giá: <a href="' . esc_url( admin_url( 'edit.php?post_type=mg_quote' ) ) . '">Migi → Báo giá</a>.', 'dashicons-media-document' ); ?>
			<form method="post">
				<?php wp_nonce_field( 'mgq_settings', 'mgq_nonce' ); ?>
				<nav class="nav-tab-wrapper mgq-tabs">
					<a href="#mgq-company" class="nav-tab nav-tab-active">Công ty &amp; PDF</a>
					<a href="#mgq-rules" class="nav-tab">Đánh số &amp; thuế</a>
					<a href="#mgq-mail" class="nav-tab">Email</a>
				</nav>
				<div class="mgq-tab" id="mgq-company">
					<table class="form-table">
						<tr><th>Logo</th><td>
							<input type="hidden" name="logo" id="mgq_logo" value="<?php echo esc_attr( $s['logo'] ); ?>">
							<span id="mgq_logo_preview" style="display:inline-block;min-height:48px;vertical-align:middle;margin-right:10px"><?php echo $logo ? '<img src="' . esc_url( $logo ) . '" style="max-height:56px">' : ''; ?></span>
							<button type="button" class="button" id="mgq_logo_btn">Chọn logo</button> <button type="button" class="button-link" id="mgq_logo_clear">Xoá</button>
							<p class="description">PNG/JPG nền trong, ngang khoảng 400–600px.</p>
						</td></tr>
						<?php
						$f( 'company', 'Tên công ty' );
						$f( 'address', 'Địa chỉ' );
						$f( 'phone', 'Hotline' );
						$f( 'email', 'Email', '', 'email' );
						$f( 'website', 'Website', '', 'url' );
						$f( 'tax_code', 'Mã số thuế' );
						$f( 'bank', 'Ngân hàng', 'VD: Vietcombank – CN Hà Nội' );
						$f( 'bank_account', 'Số tài khoản' );
						$f( 'bank_holder', 'Chủ tài khoản' );
						$f( 'signer', 'Người ký báo giá' );
						$f( 'signer_title', 'Chức danh' );
						?>
						<tr><th><label for="mgq_accent">Màu nhấn PDF</label></th><td><input type="color" id="mgq_accent" name="accent" value="<?php echo esc_attr( $s['accent'] ); ?>"></td></tr>
						<?php
						$ta( 'terms', 'Điều khoản (mỗi dòng 1 ý)' );
						$ta( 'footer', 'Lời cảm ơn cuối trang' );
						?>
					</table>
				</div>
				<div class="mgq-tab" id="mgq-rules" hidden>
					<table class="form-table">
						<?php
						$f( 'prefix', 'Tiền tố số báo giá', 'Số dạng BG-2611-0001 (tiền tố-năm tháng-số thứ tự).' );
						$f( 'valid_days', 'Hiệu lực mặc định (ngày)', '', 'number' );
						$f( 'vat', 'Thuế VAT (%)' );
						?>
						<tr><th>Tuỳ chọn</th><td>
							<?php
							$sw( 'price_has_vat', 'Giá sản phẩm đã gồm VAT (chỉ hiển thị dòng "đã bao gồm VAT", không cộng thêm)' );
							$sw( 'standard_enabled', 'Cho khách tự tải báo giá chuẩn (giá web) từ Build PC / Tính vật liệu' );
							$sw( 'show_list_price', 'Báo giá cạnh tranh: hiện cột giá niêm yết gạch ngang bên cạnh giá ưu đãi' );
							?>
						</td></tr>
					</table>
				</div>
				<div class="mgq-tab" id="mgq-mail" hidden>
					<table class="form-table">
						<?php
						$f( 'email_subject', 'Tiêu đề email', 'Biến: {number} {company} {customer} {total}' );
						$ta( 'email_body', 'Nội dung email', 'Biến: {number} {company} {customer} {total} {link} {valid}' );
						?>
					</table>
				</div>
				<?php submit_button( 'Lưu cài đặt' ); ?>
			</form>
		</div>
		<script>
		jQuery(function ($) {
			$('.mgq-tabs a').on('click', function (e) { e.preventDefault(); $('.mgq-tabs a').removeClass('nav-tab-active'); $(this).addClass('nav-tab-active'); $('.mgq-tab').prop('hidden', true); $($(this).attr('href')).prop('hidden', false); });
			var frame;
			$('#mgq_logo_btn').on('click', function () {
				frame = frame || wp.media({ title: 'Chọn logo', library: { type: 'image' }, multiple: false });
				frame.off('select').on('select', function () { var a = frame.state().get('selection').first().toJSON(); $('#mgq_logo').val(a.id); $('#mgq_logo_preview').html('<img src="' + (a.sizes && a.sizes.medium ? a.sizes.medium.url : a.url) + '" style="max-height:56px">'); });
				frame.open();
			});
			$('#mgq_logo_clear').on('click', function () { $('#mgq_logo').val(''); $('#mgq_logo_preview').empty(); });
		});
		</script>
		<?php
	}
}
