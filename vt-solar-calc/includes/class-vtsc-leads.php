<?php
defined( 'ABSPATH' ) || exit;

/**
 * Leads: stored as a private custom post type, with admin columns, detail box, CSV export, email + webhook.
 */
class VTSC_Leads {

	const PT = 'vtsc_lead';

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
				'all_items'     => 'Khách hàng (lead)',
				'edit_item'     => 'Chi tiết khách hàng',
				'search_items'  => 'Tìm khách hàng',
				'not_found'     => 'Chưa có khách hàng nào',
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
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
			update_post_meta( $id, '_vtsc_' . $k, $v );
		}
		update_post_meta( $id, '_vtsc_status', 'new' );
		update_post_meta( $id, '_vtsc_kwp', $r['kwp'] );
		update_post_meta( $id, '_vtsc_bill', $r['input']['bill'] );
		update_post_meta( $id, '_vtsc_cost', $r['cost'] );
		update_post_meta( $id, '_vtsc_result', wp_slash( wp_json_encode( $r ) ) );

		self::notify( $id, $lead, $r );
		do_action( 'vtsc_lead_created', $id, $lead, $r );
		return $id;
	}

	private static function notify( $id, $lead, $r ) {
		$s       = VTSC_Settings::get();
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$body    = VTSC_Render::email( $r, $lead, $s );

		if ( $s['admin_email'] ) {
			$info  = '<div style="font-family:Arial,sans-serif;max-width:640px;margin:auto"><h3>Khách hàng mới từ công cụ tính điện mặt trời</h3><p>';
			$info .= 'Họ tên: <b>' . esc_html( $lead['name'] ) . '</b><br>SĐT: <b>' . esc_html( $lead['phone'] ) . '</b><br>Email: ' . esc_html( $lead['email'] );
			$info .= $lead['address'] ? '<br>Địa chỉ: ' . esc_html( $lead['address'] ) : '';
			$info .= '<br>Trang: ' . esc_html( $lead['page'] ) . '<br><a href="' . esc_url( admin_url( 'post.php?action=edit&post=' . $id ) ) . '">Xem trong quản trị</a></p></div>';
			wp_mail( array_map( 'trim', explode( ',', $s['admin_email'] ) ), '[Điện mặt trời] ' . $lead['name'] . ' – ' . $lead['phone'] . ' – ' . VTSC_Render::num( $r['kwp'], 2 ) . ' kWp', $info . $body, $headers );
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
				'summary' => VTSC_Render::summary( $r ),
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
			'vtsc_phone'   => 'Điện thoại',
			'vtsc_email'   => 'Email',
			'vtsc_bill'    => 'Hoá đơn/tháng',
			'vtsc_kwp'     => 'Công suất',
			'vtsc_cost'    => 'Chi phí dự kiến',
			'vtsc_status'  => 'Trạng thái',
			'date'         => 'Ngày',
		);
	}

	public static function column( $col, $id ) {
		switch ( $col ) {
			case 'vtsc_phone':
				$p = get_post_meta( $id, '_vtsc_phone', true );
				echo '<a href="tel:' . esc_attr( $p ) . '">' . esc_html( $p ) . '</a>';
				break;
			case 'vtsc_email':
				echo esc_html( get_post_meta( $id, '_vtsc_email', true ) );
				break;
			case 'vtsc_bill':
				echo esc_html( VTSC_Render::money( get_post_meta( $id, '_vtsc_bill', true ) ) );
				break;
			case 'vtsc_kwp':
				echo esc_html( VTSC_Render::num( get_post_meta( $id, '_vtsc_kwp', true ), 2 ) . ' kWp' );
				break;
			case 'vtsc_cost':
				echo esc_html( VTSC_Render::money_short( get_post_meta( $id, '_vtsc_cost', true ) ) );
				break;
			case 'vtsc_status':
				$st = self::statuses();
				$v  = get_post_meta( $id, '_vtsc_status', true );
				echo '<span class="vtsc-status vtsc-status-' . esc_attr( $v ) . '">' . esc_html( isset( $st[ $v ] ) ? $st[ $v ] : $st['new'] ) . '</span>';
				break;
		}
	}

	public static function filter_status( $q ) {
		if ( ! is_admin() || ! $q->is_main_query() || self::PT !== $q->get( 'post_type' ) || empty( $_GET['vtsc_status'] ) ) {
			return;
		}
		$q->set( 'meta_query', array( array( 'key' => '_vtsc_status', 'value' => sanitize_key( $_GET['vtsc_status'] ) ) ) );
	}

	public static function export_button( $post_type ) {
		if ( self::PT !== $post_type ) {
			return;
		}
		$cur = isset( $_GET['vtsc_status'] ) ? sanitize_key( $_GET['vtsc_status'] ) : '';
		echo '<select name="vtsc_status"><option value="">Mọi trạng thái</option>';
		foreach ( self::statuses() as $k => $v ) {
			echo '<option value="' . esc_attr( $k ) . '" ' . selected( $cur, $k, false ) . '>' . esc_html( $v ) . '</option>';
		}
		echo '</select>';
		$url = wp_nonce_url( add_query_arg( array( 'vtsc_export' => 1, 'vtsc_status' => $cur ), admin_url( 'edit.php?post_type=' . self::PT ) ), 'vtsc_export' );
		echo ' <a href="' . esc_url( $url ) . '" class="button">Xuất CSV</a> ';
	}

	public static function export_csv() {
		if ( empty( $_GET['vtsc_export'] ) || ! current_user_can( 'edit_posts' ) || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'vtsc_export' ) ) {
			return;
		}
		$args = array( 'post_type' => self::PT, 'posts_per_page' => -1, 'post_status' => 'any', 'fields' => 'ids' );
		if ( ! empty( $_GET['vtsc_status'] ) ) {
			$args['meta_query'] = array( array( 'key' => '_vtsc_status', 'value' => sanitize_key( $_GET['vtsc_status'] ) ) );
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
			$r = json_decode( (string) get_post_meta( $id, '_vtsc_result', true ), true );
			if ( ! $r ) {
				continue;
			}
			$status = get_post_meta( $id, '_vtsc_status', true );
			fputcsv( $out, array(
				get_the_date( 'Y-m-d H:i', $id ),
				get_post_meta( $id, '_vtsc_name', true ),
				get_post_meta( $id, '_vtsc_phone', true ),
				get_post_meta( $id, '_vtsc_email', true ),
				get_post_meta( $id, '_vtsc_address', true ),
				isset( $st[ $status ] ) ? $st[ $status ] : $st['new'],
				round( $r['input']['bill'] ),
				VTSC_Render::$regions[ $r['input']['region'] ],
				$r['input']['phase'],
				VTSC_Render::$types[ $r['input']['type'] ],
				round( $r['kwh_month'] ),
				round( $r['kwp'], 2 ),
				$r['panels'],
				$r['inverter'],
				$r['battery'],
				round( $r['saving_month'] ),
				round( $r['cost'] ),
				null === $r['payback'] ? '' : round( $r['payback'], 1 ),
				get_post_meta( $id, '_vtsc_page', true ),
			) );
		}
		fclose( $out );
		exit;
	}

	/* ---------- Edit screen ---------- */

	public static function meta_boxes() {
		add_meta_box( 'vtsc_lead_info', 'Thông tin khách hàng', array( __CLASS__, 'box_info' ), self::PT, 'side', 'high' );
		add_meta_box( 'vtsc_lead_result', 'Kết quả tính toán', array( __CLASS__, 'box_result' ), self::PT, 'normal', 'high' );
	}

	public static function box_info( $post ) {
		wp_nonce_field( 'vtsc_lead_status', 'vtsc_lead_nonce' );
		$status = get_post_meta( $post->ID, '_vtsc_status', true );
		foreach ( array( 'name' => 'Họ tên', 'phone' => 'Điện thoại', 'email' => 'Email', 'address' => 'Địa chỉ', 'page' => 'Trang gửi', 'ip' => 'IP' ) as $k => $label ) {
			$v = get_post_meta( $post->ID, '_vtsc_' . $k, true );
			if ( '' !== $v ) {
				echo '<p><strong>' . esc_html( $label ) . ':</strong><br>' . esc_html( $v ) . '</p>';
			}
		}
		echo '<p><label><strong>Trạng thái</strong><br><select name="vtsc_status" style="width:100%">';
		foreach ( self::statuses() as $k => $v ) {
			echo '<option value="' . esc_attr( $k ) . '" ' . selected( $status, $k, false ) . '>' . esc_html( $v ) . '</option>';
		}
		echo '</select></label></p><p><label><strong>Ghi chú</strong><br><textarea name="vtsc_note" rows="4" style="width:100%">' . esc_textarea( get_post_meta( $post->ID, '_vtsc_note', true ) ) . '</textarea></label></p>';
	}

	public static function box_result( $post ) {
		$r = json_decode( (string) get_post_meta( $post->ID, '_vtsc_result', true ), true );
		if ( ! $r ) {
			echo '<p>Không có dữ liệu.</p>';
			return;
		}
		echo wp_kses_post( VTSC_Render::email( $r, null, VTSC_Settings::get() ) );
	}

	public static function save_status( $id ) {
		if ( ! isset( $_POST['vtsc_lead_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['vtsc_lead_nonce'] ), 'vtsc_lead_status' ) || ! current_user_can( 'edit_post', $id ) ) {
			return;
		}
		$st = isset( $_POST['vtsc_status'] ) ? sanitize_key( $_POST['vtsc_status'] ) : 'new';
		update_post_meta( $id, '_vtsc_status', array_key_exists( $st, self::statuses() ) ? $st : 'new' );
		update_post_meta( $id, '_vtsc_note', isset( $_POST['vtsc_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['vtsc_note'] ) ) : '' );
	}
}
