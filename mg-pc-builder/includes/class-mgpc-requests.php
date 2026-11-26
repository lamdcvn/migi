<?php
defined( 'ABSPATH' ) || exit;

/** "Nhận tư vấn cấu hình": customer leaves a phone number with the current build. */
class MGPC_Requests {

	const PT = 'mgpc_request';

	public static function statuses() {
		return array( 'new' => 'Mới', 'contacted' => 'Đã liên hệ', 'quoted' => 'Đã báo giá', 'won' => 'Đã chốt', 'lost' => 'Không thành công' );
	}

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'wc_ajax_mgpc_consult', array( __CLASS__, 'ajax' ) );
		add_filter( 'manage_' . self::PT . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::PT . '_posts_custom_column', array( __CLASS__, 'column' ), 10, 2 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'boxes' ) );
		add_action( 'save_post_' . self::PT, array( __CLASS__, 'save' ) );
	}

	public static function register() {
		register_post_type( self::PT, array(
			'labels'       => array( 'name' => 'Yêu cầu tư vấn Build PC', 'singular_name' => 'Yêu cầu tư vấn', 'all_items' => 'Build PC – Yêu cầu tư vấn', 'edit_item' => 'Chi tiết yêu cầu', 'not_found' => 'Chưa có yêu cầu nào' ),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => 'migi',
			'supports'     => array( 'title' ),
			'capabilities' => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap' => true,
		) );
	}

	private static function ip() {
		return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	}

	/** Public endpoint (no nonce: pages are often cached) – honeypot + rate limit + server-side build parsing. */
	public static function ajax() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$p = wp_unslash( $_POST );
		if ( ! empty( $p['website'] ) ) {
			wp_send_json_error( array( 'message' => 'Yêu cầu không hợp lệ.' ), 400 );
		}
		$lead = array(
			'name'   => sanitize_text_field( $p['name'] ?? '' ),
			'phone'  => preg_replace( '/[^0-9+]/', '', (string) ( $p['phone'] ?? '' ) ),
			'budget' => sanitize_text_field( $p['budget'] ?? '' ),
			'note'   => sanitize_textarea_field( $p['note'] ?? '' ),
			'page'   => esc_url_raw( $p['page'] ?? '' ),
			'ip'     => self::ip(),
		);
		// phpcs:enable
		if ( mb_strlen( $lead['name'] ) < 2 ) {
			wp_send_json_error( array( 'message' => 'Vui lòng nhập họ tên.', 'field' => 'name' ) );
		}
		if ( ! preg_match( '/^(0|\+?84)(3|5|7|8|9)\d{8}$/', $lead['phone'] ) ) {
			wp_send_json_error( array( 'message' => 'Số điện thoại không hợp lệ.', 'field' => 'phone' ) );
		}
		$rl = 'mgpc_rl_' . md5( $lead['ip'] );
		if ( (int) get_transient( $rl ) >= 6 ) {
			wp_send_json_error( array( 'message' => 'Bạn đã gửi nhiều yêu cầu, vui lòng thử lại sau hoặc gọi hotline.' ), 429 );
		}
		set_transient( $rl, (int) get_transient( $rl ) + 1, HOUR_IN_SECONDS );

		$parts = MGPC_Catalog::parts( $p['build'] ?? '[]' );
		$lines = array();
		$total = 0;
		$slots = MGPC_Settings::slots();
		foreach ( $parts as $pt ) {
			$price   = (float) wc_get_price_to_display( $pt['product'] );
			$lines[] = array( 'slot' => $slots[ $pt['slot'] ]['label'], 'id' => $pt['product']->get_id(), 'name' => $pt['name'], 'qty' => $pt['qty'], 'price' => $price );
			$total  += $price * $pt['qty'];
		}
		$id = wp_insert_post( array( 'post_type' => self::PT, 'post_status' => 'publish', 'post_title' => $lead['name'] . ' – ' . $lead['phone'] ) );
		if ( ! $id || is_wp_error( $id ) ) {
			wp_send_json_error( array( 'message' => 'Không lưu được yêu cầu, vui lòng thử lại.' ) );
		}
		foreach ( $lead as $k => $v ) {
			update_post_meta( $id, '_mgpc_' . $k, $v );
		}
		update_post_meta( $id, '_mgpc_status', 'new' );
		update_post_meta( $id, '_mgpc_total', $total );
		update_post_meta( $id, '_mgpc_lines', wp_slash( wp_json_encode( $lines ) ) );
		self::notify( $id, $lead, $lines, $total );
		$s = MGPC_Settings::get();
		wp_send_json_success( array( 'message' => 'Đã gửi! Kỹ thuật viên sẽ gọi lại cho bạn sớm nhất.' . ( $s['hotline'] ? ' Cần gấp, gọi ' . $s['hotline'] . '.' : '' ) ) );
	}

	public static function table( $lines, $total ) {
		if ( ! $lines ) {
			return '<p><em>Khách chưa chọn linh kiện nào – cần tư vấn từ đầu.</em></p>';
		}
		$h = '<table style="width:100%;border-collapse:collapse;font-size:14px"><tr style="background:#fff6e6"><th style="text-align:left;padding:8px">Nhóm</th><th style="text-align:left;padding:8px">Linh kiện</th><th style="text-align:right;padding:8px">SL</th><th style="text-align:right;padding:8px">Thành tiền</th></tr>';
		foreach ( $lines as $l ) {
			$h .= sprintf( '<tr><td style="padding:8px;border-bottom:1px solid #eee;color:#667085">%s</td><td style="padding:8px;border-bottom:1px solid #eee">%s</td><td style="padding:8px;border-bottom:1px solid #eee;text-align:right">%d</td><td style="padding:8px;border-bottom:1px solid #eee;text-align:right">%s</td></tr>', esc_html( $l['slot'] ), esc_html( $l['name'] ), $l['qty'], wp_strip_all_tags( wc_price( $l['price'] * $l['qty'] ) ) );
		}
		return $h . '<tr><td colspan="3" style="padding:10px 8px;text-align:right;font-weight:700">Tạm tính</td><td style="padding:10px 8px;text-align:right;font-weight:800;color:#ff8a00">' . wp_strip_all_tags( wc_price( $total ) ) . '</td></tr></table>';
	}

	private static function notify( $id, $lead, $lines, $total ) {
		$s    = MGPC_Settings::get();
		$to   = $s['admin_email'] ? $s['admin_email'] : get_option( 'admin_email' );
		$body = '<div style="font-family:Manrope,Arial,sans-serif;max-width:640px;margin:auto;color:#101828"><h2>Yêu cầu tư vấn Build PC</h2><p>' . esc_html( $lead['name'] ) . ' · <a href="tel:' . esc_attr( $lead['phone'] ) . '">' . esc_html( $lead['phone'] ) . '</a>' . ( $lead['budget'] ? '<br>Ngân sách: ' . esc_html( $lead['budget'] ) : '' ) . ( $lead['note'] ? '<br>Nhu cầu: ' . nl2br( esc_html( $lead['note'] ) ) : '' ) . '</p>' . self::table( $lines, $total ) . '<p><a href="' . esc_url( admin_url( 'post.php?action=edit&post=' . $id ) ) . '">Mở trong quản trị</a></p></div>';
		wp_mail( array_map( 'trim', explode( ',', $to ) ), '[Build PC] ' . $lead['name'] . ' – ' . $lead['phone'], $body, array( 'Content-Type: text/html; charset=UTF-8' ) );
		if ( $s['webhook'] ) {
			wp_remote_post( $s['webhook'], array( 'timeout' => 5, 'blocking' => false, 'headers' => array( 'Content-Type' => 'application/json' ), 'body' => wp_json_encode( array( 'id' => $id, 'lead' => $lead, 'items' => $lines, 'total' => $total ) ) ) );
		}
	}

	public static function columns( $cols ) {
		return array( 'cb' => $cols['cb'], 'title' => 'Khách hàng', 'mgpc_phone' => 'Điện thoại', 'mgpc_budget' => 'Ngân sách', 'mgpc_total' => 'Cấu hình', 'mgpc_status' => 'Trạng thái', 'date' => 'Ngày' );
	}

	public static function column( $col, $id ) {
		if ( 'mgpc_phone' === $col ) {
			$p = get_post_meta( $id, '_mgpc_phone', true );
			echo '<a href="tel:' . esc_attr( $p ) . '">' . esc_html( $p ) . '</a>';
		} elseif ( 'mgpc_budget' === $col ) {
			echo esc_html( get_post_meta( $id, '_mgpc_budget', true ) );
		} elseif ( 'mgpc_total' === $col ) {
			$n = count( (array) json_decode( (string) get_post_meta( $id, '_mgpc_lines', true ), true ) );
			echo esc_html( $n . ' linh kiện · ' ) . wp_kses_post( wc_price( (float) get_post_meta( $id, '_mgpc_total', true ) ) );
		} elseif ( 'mgpc_status' === $col ) {
			$st = self::statuses();
			$v  = get_post_meta( $id, '_mgpc_status', true );
			$v  = isset( $st[ $v ] ) ? $v : 'new';
			echo '<span class="mg-status mg-status--' . esc_attr( $v ) . '">' . esc_html( $st[ $v ] ) . '</span>';
		}
	}

	public static function boxes() {
		add_meta_box( 'mgpc_req', 'Cấu hình khách chọn', function ( $post ) {
			echo wp_kses_post( self::table( (array) json_decode( (string) get_post_meta( $post->ID, '_mgpc_lines', true ), true ), (float) get_post_meta( $post->ID, '_mgpc_total', true ) ) );
		}, self::PT, 'normal', 'high' );
		add_meta_box( 'mgpc_req_info', 'Khách hàng', array( __CLASS__, 'box_info' ), self::PT, 'side', 'high' );
	}

	public static function box_info( $post ) {
		wp_nonce_field( 'mgpc_req', 'mgpc_req_nonce' );
		foreach ( array( 'name' => 'Họ tên', 'phone' => 'Điện thoại', 'budget' => 'Ngân sách', 'note' => 'Nhu cầu', 'page' => 'Trang gửi' ) as $k => $l ) {
			$v = get_post_meta( $post->ID, '_mgpc_' . $k, true );
			if ( '' !== (string) $v ) {
				echo '<p><strong>' . esc_html( $l ) . ':</strong><br>' . nl2br( esc_html( $v ) ) . '</p>';
			}
		}
		$cur = get_post_meta( $post->ID, '_mgpc_status', true );
		echo '<p><label><strong>Trạng thái</strong><br><select name="mgpc_status" style="width:100%">';
		foreach ( self::statuses() as $k => $v ) {
			echo '<option value="' . esc_attr( $k ) . '" ' . selected( $cur, $k, false ) . '>' . esc_html( $v ) . '</option>';
		}
		echo '</select></label></p><p><label><strong>Ghi chú nội bộ</strong><br><textarea name="mgpc_admin_note" rows="4" style="width:100%">' . esc_textarea( get_post_meta( $post->ID, '_mgpc_admin_note', true ) ) . '</textarea></label></p>';
	}

	public static function save( $id ) {
		if ( ! isset( $_POST['mgpc_req_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mgpc_req_nonce'] ), 'mgpc_req' ) || ! current_user_can( 'edit_post', $id ) ) {
			return;
		}
		$st = sanitize_key( $_POST['mgpc_status'] ?? 'new' );
		update_post_meta( $id, '_mgpc_status', array_key_exists( $st, self::statuses() ) ? $st : 'new' );
		update_post_meta( $id, '_mgpc_admin_note', sanitize_textarea_field( wp_unslash( $_POST['mgpc_admin_note'] ?? '' ) ) );
	}
}
