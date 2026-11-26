<?php
defined( 'ABSPATH' ) || exit;

/**
 * Leads: stored as a private custom post type, with admin columns, detail box, CSV export, email + webhook.
 */
class MGSC_Leads {

	const PT = 'mgsc_lead';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_filter( 'manage_' . self::PT . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::PT . '_posts_custom_column', array( __CLASS__, 'column' ), 10, 2 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_boxes' ) );
		add_action( 'save_post_' . self::PT, array( __CLASS__, 'save_status' ) );
		add_action( 'restrict_manage_posts', array( __CLASS__, 'export_button' ) );
		add_action( 'admin_init', array( __CLASS__, 'export_csv' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'filter_status' ) );
	}

	public static function statuses() {
		return array( 'new' => 'Mới', 'contacted' => 'Đã liên hệ', 'surveyed' => 'Đã khảo sát', 'quoted' => 'Đã báo giá', 'won' => 'Đã chốt', 'lost' => 'Không thành công' );
	}

	public static function register_post_type() {
		register_post_type( self::PT, array(
			'labels'          => array(
				'name'          => 'Điện mặt trời',
				'singular_name' => 'Khách hàng',
				'menu_name'     => 'Điện mặt trời',
				'all_items'     => 'Điện mặt trời – Khách hàng',
				'edit_item'     => 'Chi tiết khách hàng',
				'search_items'  => 'Tìm khách hàng',
				'not_found'     => 'Chưa có khách hàng nào',
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'migi',
			'menu_icon'       => 'dashicons-lightbulb',
			'menu_position'   => 56,
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		) );
	}

	/** Saves a lead and fires notifications. Returns post ID. */
	public static function create( array $lead, array $r ) {
		$id = wp_insert_post( array(
			'post_type'   => self::PT,
			'post_status' => 'publish',
			'post_title'  => $lead['name'] . ' – ' . $lead['phone'],
		) );
		if ( ! $id || is_wp_error( $id ) ) {
			return 0;
		}
		foreach ( $lead as $k => $v ) {
			update_post_meta( $id, '_mgsc_' . $k, $v );
		}
		update_post_meta( $id, '_mgsc_status', 'new' );
		update_post_meta( $id, '_mgsc_kwp', $r['kwp'] );
		update_post_meta( $id, '_mgsc_bill', $r['input']['bill'] );
		update_post_meta( $id, '_mgsc_cost', $r['cost'] );
		update_post_meta( $id, '_mgsc_result', wp_slash( wp_json_encode( $r ) ) );

		self::notify( $id, $lead, $r );
		do_action( 'mgsc_lead_created', $id, $lead, $r );
		return $id;
	}

	private static function notify( $id, $lead, $r ) {
		$s       = MGSC_Settings::get();
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$body    = MGSC_Render::email( $r, $lead, $s );

		if ( $s['admin_email'] ) {
			$info  = '<div style="font-family:Arial,sans-serif;max-width:640px;margin:auto"><h3>Khách hàng mới từ công cụ tính điện mặt trời</h3><p>';
			$info .= 'Họ tên: <b>' . esc_html( $lead['name'] ) . '</b><br>SĐT: <b>' . esc_html( $lead['phone'] ) . '</b><br>Email: ' . esc_html( $lead['email'] );
			$info .= $lead['address'] ? '<br>Địa chỉ: ' . esc_html( $lead['address'] ) : '';
			$info .= '<br>Trang: ' . esc_html( $lead['page'] ) . '<br><a href="' . esc_url( admin_url( 'post.php?action=edit&post=' . $id ) ) . '">Xem trong quản trị</a></p></div>';
			wp_mail( array_map( 'trim', explode( ',', $s['admin_email'] ) ), '[Điện mặt trời] ' . $lead['name'] . ' – ' . $lead['phone'] . ' – ' . MGSC_Render::num( $r['kwp'], 2 ) . ' kWp', $info . $body, $headers );
		}
		if ( 'yes' === $s['email_customer'] && is_email( $lead['email'] ) ) {
			wp_mail( $lead['email'], 'Kết quả tính hệ thống điện mặt trời của bạn', $body, $headers );
		}
		if ( $s['webhook'] ) {
			$payload = array(
				'id'      => $id,
				'time'    => current_time( 'mysql' ),
				'lead'    => $lead,
				'input'   => $r['input'],
				'summary' => MGSC_Render::summary( $r ),
				'result'  => array_intersect_key( $r, array_flip( array( 'kwh_month', 'kwp', 'panels', 'inverter', 'battery', 'production', 'bill_after', 'saving_month', 'cost', 'payback', 'roi' ) ) ),
			);
			wp_remote_post( $s['webhook'], array(
				'timeout'  => 5,
				'blocking' => false,
				'headers'  => array( 'Content-Type' => 'application/json' ),
				'body'     => wp_json_encode( $payload ),
			) );
		}
	}

	/* ---------- Admin list ---------- */

	public static function columns( $cols ) {
		return array(
			'cb'           => $cols['cb'],
			'title'        => 'Khách hàng',
			'mgsc_phone'   => 'Điện thoại',
			'mgsc_email'   => 'Email',
			'mgsc_bill'    => 'Hoá đơn/tháng',
			'mgsc_kwp'     => 'Công suất',
			'mgsc_cost'    => 'Chi phí dự kiến',
			'mgsc_status'  => 'Trạng thái',
			'date'         => 'Ngày',
		);
	}

	public static function column( $col, $id ) {
		switch ( $col ) {
			case 'mgsc_phone':
				$p = get_post_meta( $id, '_mgsc_phone', true );
				echo '<a href="tel:' . esc_attr( $p ) . '">' . esc_html( $p ) . '</a>';
				break;
			case 'mgsc_email':
				echo esc_html( get_post_meta( $id, '_mgsc_email', true ) );
				break;
			case 'mgsc_bill':
				echo esc_html( MGSC_Render::money( get_post_meta( $id, '_mgsc_bill', true ) ) );
				break;
			case 'mgsc_kwp':
				echo esc_html( MGSC_Render::num( get_post_meta( $id, '_mgsc_kwp', true ), 2 ) . ' kWp' );
				break;
			case 'mgsc_cost':
				echo esc_html( MGSC_Render::money_short( get_post_meta( $id, '_mgsc_cost', true ) ) );
				break;
			case 'mgsc_status':
				$st = self::statuses();
				$v  = get_post_meta( $id, '_mgsc_status', true );
				echo '<span class="mgsc-status mgsc-status-' . esc_attr( $v ) . '">' . esc_html( isset( $st[ $v ] ) ? $st[ $v ] : $st['new'] ) . '</span>';
				break;
		}
	}

	public static function filter_status( $q ) {
		if ( ! is_admin() || ! $q->is_main_query() || self::PT !== $q->get( 'post_type' ) || empty( $_GET['mgsc_status'] ) ) {
			return;
		}
		$q->set( 'meta_query', array( array( 'key' => '_mgsc_status', 'value' => sanitize_key( $_GET['mgsc_status'] ) ) ) );
	}

	public static function export_button( $post_type ) {
		if ( self::PT !== $post_type ) {
			return;
		}
		$cur = isset( $_GET['mgsc_status'] ) ? sanitize_key( $_GET['mgsc_status'] ) : '';
		echo '<select name="mgsc_status"><option value="">Mọi trạng thái</option>';
		foreach ( self::statuses() as $k => $v ) {
			echo '<option value="' . esc_attr( $k ) . '" ' . selected( $cur, $k, false ) . '>' . esc_html( $v ) . '</option>';
		}
		echo '</select>';
		$url = wp_nonce_url( add_query_arg( array( 'mgsc_export' => 1, 'mgsc_status' => $cur ), admin_url( 'edit.php?post_type=' . self::PT ) ), 'mgsc_export' );
		echo ' <a href="' . esc_url( $url ) . '" class="button">Xuất CSV</a> ';
	}

	public static function export_csv() {
		if ( empty( $_GET['mgsc_export'] ) || ! current_user_can( 'edit_posts' ) || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'mgsc_export' ) ) {
			return;
		}
		$args = array( 'post_type' => self::PT, 'posts_per_page' => -1, 'post_status' => 'any', 'fields' => 'ids' );
		if ( ! empty( $_GET['mgsc_status'] ) ) {
			$args['meta_query'] = array( array( 'key' => '_mgsc_status', 'value' => sanitize_key( $_GET['mgsc_status'] ) ) );
		}
		$ids = get_posts( $args );
		nocache_headers();
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename=khach-hang-dien-mat-troi-' . gmdate( 'Y-m-d' ) . '.csv' );
		$out = fopen( 'php://output', 'w' );
		fwrite( $out, "\xEF\xBB\xBF" ); // UTF-8 BOM so Excel shows Vietnamese correctly.
		fputcsv( $out, array( 'Ngày', 'Họ tên', 'Điện thoại', 'Email', 'Địa chỉ', 'Trạng thái', 'Hoá đơn (đ)', 'Khu vực', 'Pha', 'Loại hệ', 'kWh/tháng', 'Công suất (kWp)', 'Số tấm', 'Inverter (kW)', 'Pin (kWh)', 'Tiết kiệm/tháng (đ)', 'Chi phí (đ)', 'Hoàn vốn (năm)', 'Trang' ) );
		$st = self::statuses();
		foreach ( $ids as $id ) {
			$r = json_decode( (string) get_post_meta( $id, '_mgsc_result', true ), true );
			if ( ! $r ) {
				continue;
			}
			$status = get_post_meta( $id, '_mgsc_status', true );
			fputcsv( $out, array(
				get_the_date( 'Y-m-d H:i', $id ),
				get_post_meta( $id, '_mgsc_name', true ),
				get_post_meta( $id, '_mgsc_phone', true ),
				get_post_meta( $id, '_mgsc_email', true ),
				get_post_meta( $id, '_mgsc_address', true ),
				isset( $st[ $status ] ) ? $st[ $status ] : $st['new'],
				round( $r['input']['bill'] ),
				MGSC_Render::$regions[ $r['input']['region'] ],
				$r['input']['phase'],
				MGSC_Render::$types[ $r['input']['type'] ],
				round( $r['kwh_month'] ),
				round( $r['kwp'], 2 ),
				$r['panels'],
				$r['inverter'],
				$r['battery'],
				round( $r['saving_month'] ),
				round( $r['cost'] ),
				null === $r['payback'] ? '' : round( $r['payback'], 1 ),
				get_post_meta( $id, '_mgsc_page', true ),
			) );
		}
		fclose( $out );
		exit;
	}

	/* ---------- Edit screen ---------- */

	public static function meta_boxes() {
		add_meta_box( 'mgsc_lead_info', 'Thông tin khách hàng', array( __CLASS__, 'box_info' ), self::PT, 'side', 'high' );
		add_meta_box( 'mgsc_lead_result', 'Kết quả tính toán', array( __CLASS__, 'box_result' ), self::PT, 'normal', 'high' );
	}

	public static function box_info( $post ) {
		wp_nonce_field( 'mgsc_lead_status', 'mgsc_lead_nonce' );
		$status = get_post_meta( $post->ID, '_mgsc_status', true );
		foreach ( array( 'name' => 'Họ tên', 'phone' => 'Điện thoại', 'email' => 'Email', 'address' => 'Địa chỉ', 'page' => 'Trang gửi', 'ip' => 'IP' ) as $k => $label ) {
			$v = get_post_meta( $post->ID, '_mgsc_' . $k, true );
			if ( '' !== $v ) {
				echo '<p><strong>' . esc_html( $label ) . ':</strong><br>' . esc_html( $v ) . '</p>';
			}
		}
		echo '<p><label><strong>Trạng thái</strong><br><select name="mgsc_status" style="width:100%">';
		foreach ( self::statuses() as $k => $v ) {
			echo '<option value="' . esc_attr( $k ) . '" ' . selected( $status, $k, false ) . '>' . esc_html( $v ) . '</option>';
		}
		echo '</select></label></p><p><label><strong>Ghi chú</strong><br><textarea name="mgsc_note" rows="4" style="width:100%">' . esc_textarea( get_post_meta( $post->ID, '_mgsc_note', true ) ) . '</textarea></label></p>';
	}

	public static function box_result( $post ) {
		$r = json_decode( (string) get_post_meta( $post->ID, '_mgsc_result', true ), true );
		if ( ! $r ) {
			echo '<p>Không có dữ liệu.</p>';
			return;
		}
		echo wp_kses_post( MGSC_Render::email( $r, null, MGSC_Settings::get() ) );
	}

	public static function save_status( $id ) {
		if ( ! isset( $_POST['mgsc_lead_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mgsc_lead_nonce'] ), 'mgsc_lead_status' ) || ! current_user_can( 'edit_post', $id ) ) {
			return;
		}
		$st = isset( $_POST['mgsc_status'] ) ? sanitize_key( $_POST['mgsc_status'] ) : 'new';
		update_post_meta( $id, '_mgsc_status', array_key_exists( $st, self::statuses() ) ? $st : 'new' );
		update_post_meta( $id, '_mgsc_note', isset( $_POST['mgsc_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['mgsc_note'] ) ) : '' );
	}
}
