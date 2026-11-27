<?php
defined( 'ABSPATH' ) || exit;

/** Customer-facing link (?mg-quote=TOKEN), PDF download, accept button, email, and the standard-quote endpoint. */
class MGQ_Public {

	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'route' ), 1 );
		add_action( 'wc_ajax_mgq_standard', array( __CLASS__, 'ajax_standard' ) );
		add_action( 'wp_footer', array( __CLASS__, 'front_api' ), 5 );
	}

	public static function route() {
		if ( empty( $_GET['mg-quote'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		$id = MGQ_Quote::by_token( sanitize_text_field( wp_unslash( $_GET['mg-quote'] ) ) ); // phpcs:ignore
		if ( ! $id ) {
			wp_die( 'Báo giá không tồn tại hoặc đã bị huỷ.', 'Báo giá', array( 'response' => 404 ) );
		}
		$q        = MGQ_Quote::get( $id );
		$is_staff = current_user_can( 'edit_post', $id );

		if ( ! empty( $_GET['mgq_accept'] ) ) { // phpcs:ignore
			check_admin_referer( 'mgq_accept_' . $id );
			if ( in_array( $q['status'], array( 'draft', 'sent', 'viewed' ), true ) ) {
				$q['status']    = 'accepted';
				$q['history'][] = array( 't' => current_time( 'mysql' ), 'u' => 0, 'm' => 'Khách bấm đồng ý báo giá' );
				MGQ_Quote::save( $id, $q );
				$s = MGQ_Settings::get();
				wp_mail( $s['email'] ? $s['email'] : get_option( 'admin_email' ), '[Báo giá ' . $q['number'] . '] Khách đã đồng ý', 'Khách ' . $q['customer']['name'] . ' (' . $q['customer']['phone'] . ') đã đồng ý báo giá ' . $q['number'] . ' – ' . wp_strip_all_tags( wc_price( $q['totals']['total'] ) ) . "\n" . admin_url( 'post.php?action=edit&post=' . $id ) );
			}
			wp_safe_redirect( MGQ_Quote::public_url( $q ) );
			exit;
		}
		if ( ! $is_staff && in_array( $q['status'], array( 'sent', 'draft' ), true ) && 'custom' === $q['type'] ) {
			$q['status']    = 'viewed';
			$q['history'][] = array( 't' => current_time( 'mysql' ), 'u' => 0, 'm' => 'Khách mở link báo giá' );
			MGQ_Quote::save( $id, $q );
		}
		header( 'X-Robots-Tag: noindex, nofollow', true );
		if ( 'pdf' === ( $_GET['format'] ?? '' ) ) { // phpcs:ignore
			MGQ_PDF::stream( $q );
		}
		nocache_headers();
		header( 'Content-Type: text/html; charset=utf-8' );
		echo MGQ_PDF::html( $q, 'web' ); // phpcs:ignore -- template escapes its output.
		exit;
	}

	private static function fill( $tpl, $q ) {
		$s = MGQ_Settings::get();
		return strtr( $tpl, array(
			'{number}'   => $q['number'],
			'{company}'  => $s['company'],
			'{customer}' => $q['customer']['name'] ? $q['customer']['name'] : 'Quý khách',
			'{total}'    => wp_strip_all_tags( wc_price( $q['totals']['total'] ) ),
			'{link}'     => MGQ_Quote::public_url( $q ),
			'{valid}'    => $q['valid_until'] ? date_i18n( 'd/m/Y', strtotime( $q['valid_until'] ) ) : '',
		) );
	}

	/** Emails the quote with the PDF attached. */
	public static function send( $id ) {
		$q = MGQ_Quote::get( $id );
		if ( ! is_email( $q['customer']['email'] ) ) {
			return new WP_Error( 'mgq', 'Email khách hàng không hợp lệ.' );
		}
		$s     = MGQ_Settings::get();
		$file  = MGQ_PDF::file( $q );
		$att   = is_wp_error( $file ) ? array() : array( $file );
		$body  = nl2br( esc_html( self::fill( $s['email_body'], $q ) ) );
		$body  = make_clickable( $body );
		$html  = '<div style="font-family:Arial,sans-serif;font-size:14px;line-height:1.6;color:#1f2430;max-width:600px">' . $body . '</div>';
		$from  = $s['email'] && is_email( $s['email'] ) ? array( 'Reply-To: ' . $s['company'] . ' <' . $s['email'] . '>' ) : array();
		$ok    = wp_mail( $q['customer']['email'], self::fill( $s['email_subject'], $q ), $html, array_merge( array( 'Content-Type: text/html; charset=UTF-8' ), $from ), $att );
		if ( $att ) {
			wp_delete_file( $file );
		}
		if ( ! $ok ) {
			return new WP_Error( 'mgq', 'wp_mail() thất bại – kiểm tra cấu hình gửi mail (SMTP).' );
		}
		$q             = MGQ_Quote::get( $id );
		$q['status']   = in_array( $q['status'], array( 'draft', 'expired' ), true ) ? 'sent' : $q['status'];
		$q['history'][] = array( 't' => current_time( 'mysql' ), 'u' => get_current_user_id(), 'm' => 'Gửi email tới ' . $q['customer']['email'] . ( $att ? ' (kèm PDF)' : ' (không tạo được PDF, chỉ gửi link)' ) );
		MGQ_Quote::save( $id, $q );
		return true;
	}

	/**
	 * Public: creates a STANDARD quote (live web prices) from a list of products.
	 * POST items=[{id, qty, unit?, note?}], source, name?, phone?, email?
	 */
	public static function ajax_standard() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- public, rate limited, server-side prices only.
		if ( ! function_exists( 'mgq_enabled' ) || ! mgq_enabled() ) {
			wp_send_json_error( array( 'message' => 'Chức năng báo giá đang tắt.' ), 403 );
		}
		$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		$rl = 'mgq_rl_' . md5( $ip );
		if ( (int) get_transient( $rl ) >= 15 ) {
			wp_send_json_error( array( 'message' => 'Bạn tạo quá nhiều báo giá, vui lòng thử lại sau.' ), 429 );
		}
		$items = json_decode( wp_unslash( $_POST['items'] ?? '[]' ), true ); // phpcs:ignore
		$lines = array();
		foreach ( array_slice( (array) $items, 0, 60 ) as $it ) {
			$p = wc_get_product( absint( $it['id'] ?? 0 ) );
			if ( ! $p || 'publish' !== get_post_status( $p->get_parent_id() ? $p->get_parent_id() : $p->get_id() ) || ! $p->is_purchasable() ) {
				continue;
			}
			$lines[] = MGQ_Quote::line_from_product( $p, max( 1, min( 9999, (float) ( $it['qty'] ?? 1 ) ) ), sanitize_text_field( $it['unit'] ?? '' ), mb_substr( sanitize_text_field( $it['note'] ?? '' ), 0, 160 ) );
		}
		if ( ! $lines ) {
			wp_send_json_error( array( 'message' => 'Chưa có sản phẩm để báo giá.' ) );
		}
		set_transient( $rl, (int) get_transient( $rl ) + 1, HOUR_IN_SECONDS );
		$id = MGQ_Quote::create( array(
			'type'     => 'standard',
			'status'   => 'sent',
			'lines'    => $lines,
			'note'     => mb_substr( sanitize_text_field( wp_unslash( $_POST['note'] ?? '' ) ), 0, 200 ),
			'source'   => array( 'type' => sanitize_key( $_POST['source'] ?? '' ), 'page' => esc_url_raw( wp_unslash( $_POST['page'] ?? '' ) ) ),
			'customer' => array(
				'name'     => sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) ),
				'phone'    => preg_replace( '/[^0-9+]/', '', (string) wp_unslash( $_POST['phone'] ?? '' ) ),
				'email'    => sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ),
				'company'  => '',
				'tax_code' => '',
				'address'  => '',
			),
		) );
		// phpcs:enable
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => 'Không tạo được báo giá.' ) );
		}
		$q = MGQ_Quote::get( $id );
		wp_send_json_success( array( 'number' => $q['number'], 'url' => MGQ_Quote::public_url( $q ), 'pdf' => MGQ_Quote::pdf_url( $q ) ) );
	}

	/** Tiny JS API used by Build PC / Material calculator: MGQ.standard(items, opts). */
	public static function front_api() {
		if ( ! mgq_enabled() || ! ( wp_script_is( 'mgmc', 'enqueued' ) || wp_script_is( 'mgpc', 'enqueued' ) ) ) {
			return;
		}
		$url = WC_AJAX::get_endpoint( 'mgq_standard' );
		?>
		<script>
		window.MGQ = { standard: function (items, opts) {
			opts = opts || {};
			var win = window.open('about:blank', '_blank');
			if (win) { win.document.write('<p style="font-family:Arial;padding:24px">Đang tạo báo giá…</p>'); }
			var body = new URLSearchParams({ items: JSON.stringify(items), source: opts.source || '', note: opts.note || '', page: location.href });
			return fetch(<?php echo wp_json_encode( $url ); ?>, { method: 'POST', body: body, credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (res) {
				if (!res || !res.success) { throw new Error((res && res.data && res.data.message) || 'Không tạo được báo giá'); }
				if (win) { win.location.href = opts.pdf ? res.data.pdf : res.data.url; } else { location.href = opts.pdf ? res.data.pdf : res.data.url; }
				return res.data;
			}).catch(function (e) { if (win) { win.close(); } throw e; });
		} };
		</script>
		<?php
	}
}
