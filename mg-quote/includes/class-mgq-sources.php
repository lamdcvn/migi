<?php
defined( 'ABSPATH' ) || exit;

/** Turns leads from other Migi plugins into custom (competitive) quotes. */
class MGQ_Sources {

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'boxes' ) );
		add_action( 'admin_post_mgq_from_source', array( __CLASS__, 'convert' ) );
	}

	/** post type => label */
	public static function types() {
		return array( 'mgmc_quote' => 'Yêu cầu báo giá vật liệu', 'mgpc_request' => 'Yêu cầu tư vấn Build PC', 'mgsc_lead' => 'Khách điện mặt trời' );
	}

	public static function boxes() {
		foreach ( array_keys( self::types() ) as $pt ) {
			add_meta_box( 'mgq_source', 'Báo giá cạnh tranh', array( __CLASS__, 'box' ), $pt, 'side', 'high' );
		}
	}

	public static function box( $post ) {
		$linked = get_post_meta( $post->ID, '_mgq_quote_id', true );
		if ( $linked && get_post( $linked ) ) {
			$q = MGQ_Quote::get( $linked );
			echo '<p>Đã tạo báo giá <a href="' . esc_url( get_edit_post_link( $linked ) ) . '"><strong>' . esc_html( $q['number'] ) . '</strong></a> – ' . esc_html( MGQ_Quote::statuses()[ $q['status'] ] ) . '</p>';
		}
		if ( current_user_can( 'manage_woocommerce' ) ) {
			$url = wp_nonce_url( admin_url( 'admin-post.php?action=mgq_from_source&id=' . $post->ID ), 'mgq_from_' . $post->ID );
			echo '<p><a class="button button-primary widefat" style="text-align:center" href="' . esc_url( $url ) . '">' . ( $linked ? 'Tạo thêm báo giá' : 'Tạo báo giá cạnh tranh' ) . '</a></p><p class="description">Chép thông tin khách & sản phẩm sang báo giá mới để bạn nhập giá ưu đãi, xuất PDF và gửi khách.</p>';
		}
	}

	/** Lines + customer from a lead post. */
	public static function extract( $post ) {
		$m     = function ( $k ) use ( $post ) {
			return (string) get_post_meta( $post->ID, $k, true );
		};
		$lines = array();
		$cust  = array( 'name' => '', 'phone' => '', 'email' => '', 'company' => '', 'tax_code' => '', 'address' => '' );
		$note  = '';
		if ( 'mgmc_quote' === $post->post_type ) {
			$cust = array_merge( $cust, array( 'name' => $m( '_mgmc_name' ), 'phone' => $m( '_mgmc_phone' ), 'email' => $m( '_mgmc_email' ), 'address' => $m( '_mgmc_address' ) ) );
			foreach ( (array) json_decode( $m( '_mgmc_lines' ), true ) as $l ) {
				$lines[] = array( 'id' => $l['id'] ?? 0, 'qty' => $l['units'] ?? 1, 'unit' => $l['unit'] ?? '', 'name' => $l['name'] ?? '', 'price' => $l['price'] ?? 0 );
			}
			$note = 'Báo giá vật liệu theo diện tích ' . wc_format_localized_decimal( round( (float) $m( '_mgmc_area' ), 2 ) ) . ' m²';
		} elseif ( 'mgpc_request' === $post->post_type ) {
			$cust = array_merge( $cust, array( 'name' => $m( '_mgpc_name' ), 'phone' => $m( '_mgpc_phone' ) ) );
			foreach ( (array) json_decode( $m( '_mgpc_lines' ), true ) as $l ) {
				$lines[] = array( 'id' => $l['id'] ?? 0, 'qty' => $l['qty'] ?? 1, 'unit' => 'cái', 'name' => $l['name'] ?? '', 'price' => $l['price'] ?? 0, 'note' => $l['slot'] ?? '' );
			}
			$note = 'Báo giá cấu hình PC' . ( $m( '_mgpc_budget' ) ? ' – ngân sách ' . $m( '_mgpc_budget' ) : '' );
		} elseif ( 'mgsc_lead' === $post->post_type ) {
			$cust = array_merge( $cust, array( 'name' => $m( '_mgsc_name' ), 'phone' => $m( '_mgsc_phone' ), 'email' => $m( '_mgsc_email' ), 'address' => $m( '_mgsc_address' ) ) );
			$kwp  = (float) $m( '_mgsc_kwp' );
			$lines[] = array( 'id' => 0, 'qty' => 1, 'unit' => 'hệ', 'name' => 'Hệ thống điện mặt trời ' . wc_format_localized_decimal( round( $kwp, 2 ) ) . ' kWp (trọn gói)', 'price' => (float) $m( '_mgsc_cost' ) );
			$note = 'Báo giá lắp đặt hệ thống điện mặt trời';
		}
		$out = array();
		foreach ( $lines as $l ) {
			$p = ! empty( $l['id'] ) ? wc_get_product( $l['id'] ) : null;
			if ( $p ) {
				$row = MGQ_Quote::line_from_product( $p, $l['qty'], $l['unit'], $l['note'] ?? '' );
			} else {
				$row = array( 'product_id' => 0, 'name' => $l['name'], 'sku' => '', 'unit' => $l['unit'] ?: 'cái', 'qty' => (float) $l['qty'], 'list_price' => (float) $l['price'], 'price' => (float) $l['price'], 'note' => $l['note'] ?? '' );
			}
			$out[] = $row;
		}
		return array( 'customer' => $cust, 'lines' => $out, 'note' => $note );
	}

	public static function convert() {
		$id = absint( $_GET['id'] ?? 0 );
		check_admin_referer( 'mgq_from_' . $id );
		$post = get_post( $id );
		if ( ! $post || ! current_user_can( 'manage_woocommerce' ) || ! array_key_exists( $post->post_type, self::types() ) ) {
			wp_die( 'Không có quyền.' );
		}
		$data  = self::extract( $post );
		$quote = MGQ_Quote::create( array( 'type' => 'custom', 'status' => 'draft', 'customer' => $data['customer'], 'lines' => $data['lines'], 'note' => $data['note'], 'source' => array( 'type' => $post->post_type, 'id' => $id ) ) );
		update_post_meta( $id, '_mgq_quote_id', $quote );
		wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $quote ) );
		exit;
	}
}
