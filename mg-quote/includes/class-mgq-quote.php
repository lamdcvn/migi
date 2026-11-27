<?php
defined( 'ABSPATH' ) || exit;

/**
 * Quote model. Two kinds:
 *  - standard: prices are the live web prices at creation time (customer can generate it).
 *  - custom ("báo giá cạnh tranh"): prices are entered by a shop manager only.
 */
class MGQ_Quote {

	const PT = 'mg_quote';

	public static function statuses() {
		return array( 'draft' => 'Nháp', 'sent' => 'Đã gửi', 'viewed' => 'Khách đã xem', 'accepted' => 'Khách đồng ý', 'rejected' => 'Từ chối', 'ordered' => 'Đã tạo đơn', 'expired' => 'Hết hạn' );
	}

	public static function types() {
		return array( 'standard' => 'Báo giá chuẩn (giá web)', 'custom' => 'Báo giá cạnh tranh (giá tuỳ chỉnh)' );
	}

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	public static function register() {
		register_post_type( self::PT, array(
			'labels'          => array( 'name' => 'Báo giá', 'singular_name' => 'Báo giá', 'all_items' => 'Báo giá', 'add_new' => 'Tạo báo giá', 'add_new_item' => 'Tạo báo giá cạnh tranh', 'edit_item' => 'Sửa báo giá', 'search_items' => 'Tìm báo giá', 'not_found' => 'Chưa có báo giá nào' ),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'migi',
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'manage_woocommerce' ),
			'map_meta_cap'    => true,
		) );
	}

	/* ---------- Pure calculation ---------- */

	/**
	 * @param array $lines    each: qty, price, list_price
	 * @param array $discount type percent|amount, value
	 * @return array subtotal, list_subtotal, saving, discount, after_discount, shipping, vat_rate, vat, total, vat_included
	 */
	public static function totals( array $lines, array $discount = array(), $shipping = 0, $vat = 0, $vat_included = true ) {
		$sub  = 0;
		$list = 0;
		foreach ( $lines as $l ) {
			$q     = max( 0, (float) ( $l['qty'] ?? 0 ) );
			$sub  += $q * max( 0, (float) ( $l['price'] ?? 0 ) );
			$list += $q * max( 0, (float) ( $l['list_price'] ?? ( $l['price'] ?? 0 ) ) );
		}
		$dv   = max( 0, (float) ( $discount['value'] ?? 0 ) );
		$disc = 'percent' === ( $discount['type'] ?? 'amount' ) ? $sub * min( 100, $dv ) / 100 : min( $sub, $dv );
		$disc = round( $disc );
		$after = $sub - $disc;
		$ship  = max( 0, round( (float) $shipping ) );
		$base  = $after + $ship;
		$rate  = max( 0, (float) $vat );
		if ( $vat_included ) {
			$vat_amount = $rate > 0 ? round( $base - $base / ( 1 + $rate / 100 ) ) : 0;
			$total      = $base;
		} else {
			$vat_amount = round( $base * $rate / 100 );
			$total      = $base + $vat_amount;
		}
		return array(
			'subtotal'       => $sub,
			'list_subtotal'  => $list,
			'saving'         => max( 0, $list - $sub ) + $disc,
			'discount'       => $disc,
			'after_discount' => $after,
			'shipping'       => $ship,
			'vat_rate'       => $rate,
			'vat'            => $vat_amount,
			'total'          => $total,
			'vat_included'   => (bool) $vat_included,
		);
	}

	/** Vietnamese amount in words: 1250000 → "Một triệu hai trăm năm mươi nghìn đồng". */
	public static function in_words( $n ) {
		$n = (int) round( $n );
		if ( 0 === $n ) {
			return 'Không đồng';
		}
		$d     = array( 'không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín' );
		$units = array( '', ' nghìn', ' triệu', ' tỷ', ' nghìn tỷ', ' triệu tỷ' );
		$three = function ( $x, $full ) use ( $d ) {
			$h = intdiv( $x, 100 );
			$t = intdiv( $x % 100, 10 );
			$o = $x % 10;
			$s = array();
			if ( $full || $h ) {
				$s[] = $d[ $h ] . ' trăm';
			}
			if ( $t > 1 ) {
				$s[] = $d[ $t ] . ' mươi';
			} elseif ( 1 === $t ) {
				$s[] = 'mười';
			} elseif ( $o && ( $full || $h ) ) {
				$s[] = 'linh';
			}
			if ( $o ) {
				if ( 1 === $o && $t > 1 ) {
					$s[] = 'mốt';
				} elseif ( 5 === $o && $t > 0 ) {
					$s[] = 'lăm';
				} elseif ( 4 === $o && $t > 1 ) {
					$s[] = 'tư';
				} else {
					$s[] = $d[ $o ];
				}
			}
			return implode( ' ', $s );
		};
		$groups = array();
		while ( $n > 0 ) {
			$groups[] = $n % 1000;
			$n        = intdiv( $n, 1000 );
		}
		$out = array();
		for ( $i = count( $groups ) - 1; $i >= 0; $i-- ) {
			if ( 0 === $groups[ $i ] ) {
				continue;
			}
			$out[] = $three( $groups[ $i ], $i < count( $groups ) - 1 ) . $units[ $i ];
		}
		$str = implode( ' ', $out ) . ' đồng';
		return mb_strtoupper( mb_substr( $str, 0, 1 ) ) . mb_substr( $str, 1 );
	}

	/* ---------- Persistence ---------- */

	public static function next_number() {
		$s   = MGQ_Settings::get();
		$ym  = wp_date( 'ym' );
		$key = 'mgq_counter_' . $ym;
		$n   = (int) get_option( $key, 0 ) + 1;
		update_option( $key, $n, false );
		return sprintf( '%s-%s-%04d', $s['prefix'], $ym, $n );
	}

	public static function defaults() {
		$s = MGQ_Settings::get();
		return array(
			'type'         => 'custom',
			'status'       => 'draft',
			'number'       => '',
			'token'        => '',
			'customer'     => array( 'name' => '', 'phone' => '', 'email' => '', 'company' => '', 'tax_code' => '', 'address' => '' ),
			'lines'        => array(),
			'discount'     => array( 'type' => 'amount', 'value' => 0 ),
			'shipping'     => 0,
			'vat'          => (float) $s['vat'],
			'vat_included' => 'yes' === $s['price_has_vat'],
			'valid_until'  => wp_date( 'Y-m-d', time() + DAY_IN_SECONDS * (int) $s['valid_days'] ),
			'note'         => '',
			'source'       => array(),
			'order_id'     => 0,
			'history'      => array(),
		);
	}

	public static function get( $id ) {
		$data = get_post_meta( $id, '_mgq', true );
		$q    = wp_parse_args( is_array( $data ) ? $data : array(), self::defaults() );
		$q['id']       = (int) $id;
		$q['customer'] = wp_parse_args( (array) $q['customer'], self::defaults()['customer'] );
		$q['totals']   = self::totals( $q['lines'], (array) $q['discount'], $q['shipping'], $q['vat'], $q['vat_included'] );
		if ( in_array( $q['status'], array( 'draft', 'sent', 'viewed' ), true ) && $q['valid_until'] && $q['valid_until'] < wp_date( 'Y-m-d' ) ) {
			$q['status'] = 'expired';
		}
		return $q;
	}

	public static function save( $id, array $q ) {
		unset( $q['id'], $q['totals'] );
		update_post_meta( $id, '_mgq', $q );
		update_post_meta( $id, '_mgq_status', $q['status'] );
		update_post_meta( $id, '_mgq_type', $q['type'] );
		update_post_meta( $id, '_mgq_number', $q['number'] );
		update_post_meta( $id, '_mgq_token', $q['token'] );
		update_post_meta( $id, '_mgq_total', self::totals( $q['lines'], (array) $q['discount'], $q['shipping'], $q['vat'], $q['vat_included'] )['total'] );
	}

	public static function log( $id, $text ) {
		$q              = self::get( $id );
		$q['history'][] = array( 't' => current_time( 'mysql' ), 'u' => get_current_user_id(), 'm' => $text );
		$q['history']   = array_slice( $q['history'], -40 );
		self::save( $id, $q );
	}

	/** Creates a quote. $args overrides defaults(). Returns post ID. */
	public static function create( array $args ) {
		$q           = array_replace( self::defaults(), $args );
		$q['number'] = $q['number'] ?: self::next_number();
		$q['token']  = $q['token'] ?: wp_generate_password( 24, false, false );
		$title       = $q['number'] . ( $q['customer']['name'] ?? '' ? ' – ' . $q['customer']['name'] : '' );
		$id          = wp_insert_post( array( 'post_type' => self::PT, 'post_status' => 'publish', 'post_title' => $title ) );
		if ( ! $id || is_wp_error( $id ) ) {
			return 0;
		}
		$q['history'] = array( array( 't' => current_time( 'mysql' ), 'u' => get_current_user_id(), 'm' => 'Tạo báo giá ' . ( 'standard' === $q['type'] ? 'chuẩn' : 'cạnh tranh' ) ) );
		self::save( $id, $q );
		return $id;
	}

	/** Line from a product at its current web price. */
	public static function line_from_product( $product, $qty, $unit = '', $note = '' ) {
		$price = (float) wc_get_price_to_display( $product );
		return array(
			'product_id' => $product->get_id(),
			'name'       => $product->get_name(),
			'sku'        => $product->get_sku(),
			'unit'       => $unit ? $unit : 'cái',
			'qty'        => max( 1, (float) $qty ),
			'list_price' => $price,
			'price'      => $price,
			'note'       => $note,
		);
	}

	public static function by_token( $token ) {
		if ( ! $token || strlen( $token ) < 20 ) {
			return 0;
		}
		$ids = get_posts( array( 'post_type' => self::PT, 'meta_key' => '_mgq_token', 'meta_value' => $token, 'fields' => 'ids', 'posts_per_page' => 1, 'post_status' => 'publish' ) );
		return $ids ? (int) $ids[0] : 0;
	}

	public static function public_url( $q ) {
		return add_query_arg( array( 'mg-quote' => $q['token'] ), home_url( '/' ) );
	}

	public static function pdf_url( $q ) {
		return add_query_arg( array( 'mg-quote' => $q['token'], 'format' => 'pdf' ), home_url( '/' ) );
	}
}
