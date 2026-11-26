<?php
defined( 'ABSPATH' ) || exit;

/**
 * Quote requests: the customer leaves contact details with the estimate; stored as a private post type,
 * emailed to the shop (and the customer), pushed to a webhook, managed under Migi → Báo giá vật liệu.
 */
class MGMC_Quotes {

	const PT = 'mgmc_quote';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'wp_ajax_mgmc_quote', array( __CLASS__, 'ajax' ) );
		add_action( 'wp_ajax_nopriv_mgmc_quote', array( __CLASS__, 'ajax' ) );
		add_filter( 'manage_' . self::PT . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::PT . '_posts_custom_column', array( __CLASS__, 'column' ), 10, 2 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'boxes' ) );
		add_action( 'save_post_' . self::PT, array( __CLASS__, 'save' ) );
		add_action( 'restrict_manage_posts', array( __CLASS__, 'toolbar' ) );
		add_action( 'admin_init', array( __CLASS__, 'export' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'filter' ) );
	}

	public static function statuses() {
		return array( 'new' => 'Mới', 'contacted' => 'Đã liên hệ', 'quoted' => 'Đã báo giá', 'won' => 'Đã chốt', 'lost' => 'Không thành công' );
	}

	public static function register() {
		register_post_type( self::PT, array(
			'labels'          => array(
				'name'          => 'Báo giá vật liệu',
				'singular_name' => 'Báo giá',
				'all_items'     => 'Báo giá vật liệu',
				'edit_item'     => 'Chi tiết báo giá',
				'search_items'  => 'Tìm báo giá',
				'not_found'     => 'Chưa có yêu cầu báo giá nào',
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'migi',
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		) );
	}

	private static function ip() {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	}

	private static function rate_ok( $limit, $window ) {
		$key = 'mgmc_rl_' . md5( self::ip() );
		$n   = (int) get_transient( $key );
		if ( $n >= $limit ) {
			return false;
		}
		set_transient( $key, $n + 1, $window );
		return true;
	}

	/**
	 * Public endpoint without nonce on purpose (product pages are usually page-cached, a stale nonce would
	 * break the form). Protected by honeypot, per-IP rate limit and full server-side recalculation.
	 */
	public static function ajax() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$p = wp_unslash( $_POST );
		if ( ! empty( $p['website'] ) ) {
			wp_send_json_error( array( 'message' => 'Yêu cầu không hợp lệ.' ), 400 );
		}
		$lead = array(
			'name'    => sanitize_text_field( $p['name'] ?? '' ),
			'phone'   => preg_replace( '/[^0-9+]/', '', (string) ( $p['phone'] ?? '' ) ),
			'email'   => sanitize_email( $p['email'] ?? '' ),
			'address' => sanitize_text_field( $p['address'] ?? '' ),
			'note'    => sanitize_textarea_field( $p['note'] ?? '' ),
			'page'    => esc_url_raw( $p['page'] ?? '' ),
			'ip'      => self::ip(),
		);
		if ( mb_strlen( $lead['name'] ) < 2 ) {
			wp_send_json_error( array( 'message' => 'Vui lòng nhập họ tên.', 'field' => 'name' ) );
		}
		if ( ! preg_match( '/^(0|\+?84)(3|5|7|8|9)\d{8}$/', $lead['phone'] ) ) {
			wp_send_json_error( array( 'message' => 'Số điện thoại không hợp lệ.', 'field' => 'phone' ) );
		}
		if ( '' !== trim( (string) ( $p['email'] ?? '' ) ) && ! is_email( $lead['email'] ) ) {
			wp_send_json_error( array( 'message' => 'Email không hợp lệ.', 'field' => 'email' ) );
		}
		$addons = json_decode( (string) ( $p['addons'] ?? '[]' ), true );
		$lines  = MGMC_Cart::lines_for( absint( $p['product'] ?? 0 ), (string) ( $p['data'] ?? '' ), is_array( $addons ) ? $addons : array() );
		if ( ! $lines ) {
			wp_send_json_error( array( 'message' => 'Vui lòng nhập kích thước phòng trước khi yêu cầu báo giá.' ) );
		}
		if ( ! self::rate_ok( 8, HOUR_IN_SECONDS ) ) {
			wp_send_json_error( array( 'message' => 'Bạn đã gửi nhiều yêu cầu, vui lòng thử lại sau hoặc gọi hotline.' ), 429 );
		}
		// phpcs:enable

		$id = self::create( $lead, $lines );
		$s  = MGMC_Config::settings();
		wp_send_json_success( array(
			'code'    => self::code( $id ),
			'message' => sprintf( 'Đã gửi yêu cầu báo giá %s. Chúng tôi sẽ liên hệ %s sớm nhất.%s', self::code( $id ), $lead['phone'], $s['hotline'] ? ' Cần gấp, gọi ' . $s['hotline'] . '.' : '' ),
		) );
	}

	public static function code( $id ) {
		return 'BG' . str_pad( (string) $id, 5, '0', STR_PAD_LEFT );
	}

	/** Serializable snapshot of the lines. */
	private static function snapshot( $lines ) {
		$out = array();
		foreach ( $lines as $l ) {
			$out[] = array(
				'id'     => $l['product']->get_id(),
				'name'   => $l['product']->get_name(),
				'sku'    => $l['product']->get_sku(),
				'units'  => $l['units'],
				'unit'   => $l['parsed']['unit'],
				'price'  => $l['price'],
				'total'  => $l['total'],
				'main'   => $l['main'],
				'parsed' => $l['parsed'],
			);
		}
		return $out;
	}

	public static function create( $lead, $lines ) {
		$snap  = self::snapshot( $lines );
		$total = array_sum( wp_list_pluck( $snap, 'total' ) );
		$id    = wp_insert_post( array( 'post_type' => self::PT, 'post_status' => 'publish', 'post_title' => $lead['name'] . ' – ' . $lead['phone'] ) );
		if ( ! $id || is_wp_error( $id ) ) {
			return 0;
		}
		foreach ( $lead as $k => $v ) {
			update_post_meta( $id, '_mgmc_' . $k, $v );
		}
		update_post_meta( $id, '_mgmc_status', 'new' );
		update_post_meta( $id, '_mgmc_total', $total );
		update_post_meta( $id, '_mgmc_area', $snap[0]['parsed']['area'] );
		update_post_meta( $id, '_mgmc_lines', wp_slash( wp_json_encode( $snap ) ) );
		self::notify( $id, $lead, $snap, $total );
		do_action( 'mgmc_quote_created', $id, $lead, $snap );
		return $id;
	}

	/** Email-safe HTML table of the estimate. */
	public static function table( $snap, $total ) {
		$rooms = MGMC_Cart::lines( $snap[0]['parsed'] );
		$html  = '<p style="margin:0 0 6px;color:#667085">' . implode( '<br>', array_map( 'esc_html', $rooms['rooms'] ) ) . '</p>';
		$html .= '<p style="margin:0 0 14px;color:#667085">' . esc_html( $rooms['summary'] ) . '</p>';
		$html .= '<table style="width:100%;border-collapse:collapse;font-size:14px"><tr style="background:#fff6e6"><th style="text-align:left;padding:10px">Sản phẩm</th><th style="text-align:right;padding:10px">Số lượng</th><th style="text-align:right;padding:10px">Đơn giá</th><th style="text-align:right;padding:10px">Thành tiền</th></tr>';
		foreach ( $snap as $l ) {
			$html .= sprintf(
				'<tr><td style="padding:10px;border-bottom:1px solid #eee">%s%s</td><td style="padding:10px;border-bottom:1px solid #eee;text-align:right">%d %s</td><td style="padding:10px;border-bottom:1px solid #eee;text-align:right">%s</td><td style="padding:10px;border-bottom:1px solid #eee;text-align:right;font-weight:700">%s</td></tr>',
				esc_html( $l['name'] ),
				$l['sku'] ? ' <small style="color:#999">(' . esc_html( $l['sku'] ) . ')</small>' : '',
				$l['units'],
				esc_html( $l['unit'] ),
				wp_strip_all_tags( wc_price( $l['price'] ) ),
				wp_strip_all_tags( wc_price( $l['total'] ) )
			);
		}
		$html .= '<tr><td colspan="3" style="padding:12px 10px;text-align:right;font-weight:700">Tạm tính</td><td style="padding:12px 10px;text-align:right;font-weight:800;color:#ff8a00;font-size:16px">' . wp_strip_all_tags( wc_price( $total ) ) . '</td></tr></table>';
		return $html;
	}

	private static function email_wrap( $title, $body ) {
		return '<div style="font-family:Manrope,Arial,sans-serif;max-width:640px;margin:auto;color:#101828;padding:24px;background:#fff;border:1px solid #eee;border-radius:16px">'
			. '<p style="margin:0;color:#ff8a00;font-weight:800;letter-spacing:.1em;font-size:11px">' . esc_html( strtoupper( get_bloginfo( 'name' ) ) ) . '</p>'
			. '<h2 style="margin:4px 0 16px;font-size:20px">' . esc_html( $title ) . '</h2>' . $body . '</div>';
	}

	private static function notify( $id, $lead, $snap, $total ) {
		$s       = MGMC_Config::settings();
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$table   = self::table( $snap, $total );
		$code    = self::code( $id );
		$to      = $s['admin_email'] ? $s['admin_email'] : get_option( 'admin_email' );
		$info    = '<p>Khách: <b>' . esc_html( $lead['name'] ) . '</b> · <a href="tel:' . esc_attr( $lead['phone'] ) . '">' . esc_html( $lead['phone'] ) . '</a>' . ( $lead['email'] ? ' · ' . esc_html( $lead['email'] ) : '' ) . ( $lead['address'] ? '<br>Công trình: ' . esc_html( $lead['address'] ) : '' ) . ( $lead['note'] ? '<br>Ghi chú: ' . nl2br( esc_html( $lead['note'] ) ) : '' ) . '</p>';
		$info   .= '<p><a href="' . esc_url( admin_url( 'post.php?action=edit&post=' . $id ) ) . '">Mở trong quản trị</a></p>';
		wp_mail( array_map( 'trim', explode( ',', $to ) ), sprintf( '[Báo giá %s] %s – %s – %s', $code, $lead['name'], $lead['phone'], wp_strip_all_tags( wc_price( $total ) ) ), self::email_wrap( 'Yêu cầu báo giá ' . $code, $info . $table ), $headers );

		if ( 'yes' === $s['email_customer'] && is_email( $lead['email'] ) ) {
			$body = '<p>Xin chào <b>' . esc_html( $lead['name'] ) . '</b>, cảm ơn bạn đã quan tâm. Dưới đây là bảng tính vật liệu tạm tính, chúng tôi sẽ liên hệ để xác nhận giá và tư vấn thêm.</p>' . $table;
			$body .= $s['hotline'] ? '<p style="margin-top:16px">Hotline: <b>' . esc_html( $s['hotline'] ) . '</b></p>' : '';
			$body .= $s['note'] ? '<p style="color:#888;font-size:12px">' . esc_html( $s['note'] ) . '</p>' : '';
			wp_mail( $lead['email'], 'Báo giá vật liệu ' . $code . ' – ' . get_bloginfo( 'name' ), self::email_wrap( 'Báo giá ' . $code, $body ), $headers );
		}
		if ( $s['webhook'] ) {
			wp_remote_post( $s['webhook'], array(
				'timeout'  => 5,
				'blocking' => false,
				'headers'  => array( 'Content-Type' => 'application/json' ),
				'body'     => wp_json_encode( array(
					'code'  => $code,
					'time'  => current_time( 'mysql' ),
					'lead'  => $lead,
					'total' => $total,
					'rooms' => MGMC_Cart::lines( $snap[0]['parsed'] ),
					'items' => array_map( function ( $l ) {
						unset( $l['parsed'] );
						return $l;
					}, $snap ),
				) ),
			) );
		}
	}

	/* ---------- Admin ---------- */

	public static function columns( $cols ) {
		return array(
			'cb'          => $cols['cb'],
			'title'       => 'Khách hàng',
			'mgmc_code'   => 'Mã',
			'mgmc_phone'  => 'Điện thoại',
			'mgmc_items'  => 'Sản phẩm',
			'mgmc_total'  => 'Tạm tính',
			'mgmc_status' => 'Trạng thái',
			'date'        => 'Ngày',
		);
	}

	public static function column( $col, $id ) {
		switch ( $col ) {
			case 'mgmc_code':
				echo '<strong>' . esc_html( self::code( $id ) ) . '</strong>';
				break;
			case 'mgmc_phone':
				$p = get_post_meta( $id, '_mgmc_phone', true );
				echo '<a href="tel:' . esc_attr( $p ) . '">' . esc_html( $p ) . '</a>';
				break;
			case 'mgmc_items':
				$snap = json_decode( (string) get_post_meta( $id, '_mgmc_lines', true ), true );
				foreach ( (array) $snap as $l ) {
					echo esc_html( $l['units'] . ' ' . $l['unit'] . ' · ' . $l['name'] ) . '<br>';
				}
				break;
			case 'mgmc_total':
				echo wp_kses_post( wc_price( (float) get_post_meta( $id, '_mgmc_total', true ) ) );
				break;
			case 'mgmc_status':
				$st = self::statuses();
				$v  = get_post_meta( $id, '_mgmc_status', true );
				$v  = isset( $st[ $v ] ) ? $v : 'new';
				echo '<span class="mg-status mg-status--' . esc_attr( $v ) . '">' . esc_html( $st[ $v ] ) . '</span>';
				break;
		}
	}

	public static function boxes() {
		add_meta_box( 'mgmc_quote_info', 'Khách hàng', array( __CLASS__, 'box_info' ), self::PT, 'side', 'high' );
		add_meta_box( 'mgmc_quote_lines', 'Bảng tính vật liệu', array( __CLASS__, 'box_lines' ), self::PT, 'normal', 'high' );
	}

	public static function box_info( $post ) {
		wp_nonce_field( 'mgmc_quote_save', 'mgmc_quote_nonce' );
		echo '<p><strong>Mã:</strong> ' . esc_html( self::code( $post->ID ) ) . '</p>';
		foreach ( array( 'name' => 'Họ tên', 'phone' => 'Điện thoại', 'email' => 'Email', 'address' => 'Công trình', 'note' => 'Ghi chú của khách', 'page' => 'Trang gửi' ) as $k => $label ) {
			$v = get_post_meta( $post->ID, '_mgmc_' . $k, true );
			if ( '' !== (string) $v ) {
				echo '<p><strong>' . esc_html( $label ) . ':</strong><br>' . nl2br( esc_html( $v ) ) . '</p>';
			}
		}
		$cur = get_post_meta( $post->ID, '_mgmc_status', true );
		echo '<p><label><strong>Trạng thái</strong><br><select name="mgmc_status" style="width:100%">';
		foreach ( self::statuses() as $k => $v ) {
			echo '<option value="' . esc_attr( $k ) . '" ' . selected( $cur, $k, false ) . '>' . esc_html( $v ) . '</option>';
		}
		echo '</select></label></p><p><label><strong>Ghi chú nội bộ</strong><br><textarea name="mgmc_admin_note" rows="4" style="width:100%">' . esc_textarea( get_post_meta( $post->ID, '_mgmc_admin_note', true ) ) . '</textarea></label></p>';
	}

	public static function box_lines( $post ) {
		$snap = json_decode( (string) get_post_meta( $post->ID, '_mgmc_lines', true ), true );
		if ( ! $snap ) {
			echo '<p>Không có dữ liệu.</p>';
			return;
		}
		echo wp_kses_post( self::table( $snap, (float) get_post_meta( $post->ID, '_mgmc_total', true ) ) );
	}

	public static function save( $id ) {
		if ( ! isset( $_POST['mgmc_quote_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mgmc_quote_nonce'] ), 'mgmc_quote_save' ) || ! current_user_can( 'edit_post', $id ) ) {
			return;
		}
		$st = sanitize_key( $_POST['mgmc_status'] ?? 'new' );
		update_post_meta( $id, '_mgmc_status', array_key_exists( $st, self::statuses() ) ? $st : 'new' );
		update_post_meta( $id, '_mgmc_admin_note', sanitize_textarea_field( wp_unslash( $_POST['mgmc_admin_note'] ?? '' ) ) );
	}

	public static function filter( $q ) {
		if ( is_admin() && $q->is_main_query() && self::PT === $q->get( 'post_type' ) && ! empty( $_GET['mgmc_status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$q->set( 'meta_query', array( array( 'key' => '_mgmc_status', 'value' => sanitize_key( $_GET['mgmc_status'] ) ) ) ); // phpcs:ignore
		}
	}

	public static function toolbar( $pt ) {
		if ( self::PT !== $pt ) {
			return;
		}
		$cur = sanitize_key( $_GET['mgmc_status'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification
		echo '<select name="mgmc_status"><option value="">Mọi trạng thái</option>';
		foreach ( self::statuses() as $k => $v ) {
			echo '<option value="' . esc_attr( $k ) . '" ' . selected( $cur, $k, false ) . '>' . esc_html( $v ) . '</option>';
		}
		echo '</select> <a class="button" href="' . esc_url( wp_nonce_url( add_query_arg( array( 'mgmc_export' => 1, 'mgmc_status' => $cur ), admin_url( 'edit.php?post_type=' . self::PT ) ), 'mgmc_export' ) ) . '">Xuất CSV</a> ';
	}

	public static function export() {
		if ( empty( $_GET['mgmc_export'] ) || ! current_user_can( 'edit_posts' ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ?? '' ), 'mgmc_export' ) ) {
			return;
		}
		$args = array( 'post_type' => self::PT, 'posts_per_page' => -1, 'fields' => 'ids' );
		if ( ! empty( $_GET['mgmc_status'] ) ) {
			$args['meta_query'] = array( array( 'key' => '_mgmc_status', 'value' => sanitize_key( $_GET['mgmc_status'] ) ) );
		}
		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename=bao-gia-vat-lieu-' . gmdate( 'Y-m-d' ) . '.csv' );
		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" );
		fputcsv( $out, array( 'Mã', 'Ngày', 'Họ tên', 'Điện thoại', 'Email', 'Công trình', 'Trạng thái', 'Diện tích', 'Sản phẩm', 'Tạm tính', 'Ghi chú khách', 'Ghi chú nội bộ' ) );
		$st = self::statuses();
		foreach ( get_posts( $args ) as $id ) {
			$snap  = (array) json_decode( (string) get_post_meta( $id, '_mgmc_lines', true ), true );
			$items = implode( '; ', array_map( function ( $l ) {
				return $l['units'] . ' ' . $l['unit'] . ' ' . $l['name'];
			}, $snap ) );
			$s     = get_post_meta( $id, '_mgmc_status', true );
			fputcsv( $out, array( self::code( $id ), get_the_date( 'Y-m-d H:i', $id ), get_post_meta( $id, '_mgmc_name', true ), get_post_meta( $id, '_mgmc_phone', true ), get_post_meta( $id, '_mgmc_email', true ), get_post_meta( $id, '_mgmc_address', true ), $st[ $s ] ?? $st['new'], round( (float) get_post_meta( $id, '_mgmc_area', true ), 2 ), $items, round( (float) get_post_meta( $id, '_mgmc_total', true ) ), get_post_meta( $id, '_mgmc_note', true ), get_post_meta( $id, '_mgmc_admin_note', true ) ) );
		}
		fclose( $out );
		exit;
	}
}
