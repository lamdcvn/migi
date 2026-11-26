<?php
defined( 'ABSPATH' ) || exit;

/**
 * Stores the room breakdown with the cart line (re-calculated on the server, never trusted from the browser)
 * and shows it in cart, checkout, emails and the admin order screen.
 */
class MGMC_Cart {

	public static function init() {
		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_data' ), 10, 3 );
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'show' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'order_meta' ), 10, 3 );
	}

	/** Sanitizes posted JSON into rooms + options, recalculates. */
	public static function parse( $json, $product ) {
		$c    = MGMC_Config::for_product( $product );
		$data = json_decode( (string) $json, true );
		if ( ! $c || ! is_array( $data ) || empty( $data['rooms'] ) || ! is_array( $data['rooms'] ) ) {
			return null;
		}
		$rooms = array();
		foreach ( array_slice( $data['rooms'], 0, 50 ) as $i => $r ) {
			if ( ! is_array( $r ) ) {
				continue;
			}
			$room = array( 'name' => isset( $r['name'] ) ? mb_substr( sanitize_text_field( $r['name'] ), 0, 60 ) : '' );
			foreach ( array( 'l', 'w', 'h', 'openings' ) as $k ) {
				$room[ $k ] = isset( $r[ $k ] ) ? min( 1000, MGMC_Engine::f( $r[ $k ] ) ) : 0;
			}
			foreach ( array_keys( MGMC_Config::surfaces() ) as $s ) {
				$room[ $s ] = ! empty( $r[ $s ] ) && in_array( $s, $c['surfaces'], true );
			}
			if ( '' === $room['name'] ) {
				$room['name'] = 'Phòng ' . ( $i + 1 );
			}
			$rooms[] = $room;
		}
		$opt = array(
			'pattern' => isset( $data['pattern'] ) ? sanitize_key( $data['pattern'] ) : 'straight',
			'reserve' => isset( $data['reserve'] ) ? MGMC_Engine::f( $data['reserve'] ) : $c['reserve'],
		);
		$r = MGMC_Engine::calculate( $c, $rooms, $opt );
		if ( $r['units'] <= 0 ) {
			return null;
		}
		// Surfaces of the rooms that were counted (same filter as the engine: area > 0).
		$surfaces = array();
		foreach ( $rooms as $room ) {
			if ( MGMC_Engine::room( $c, $room, 'straight' )['area'] > 0 ) {
				$surfaces[] = array_values( array_filter( array_keys( MGMC_Config::surfaces() ), function ( $s ) use ( $room ) {
					return ! empty( $room[ $s ] );
				} ) );
			}
		}
		return array(
			'rooms'   => $r['rooms'],
			'surface' => $surfaces,
			'pattern' => $r['pattern'],
			'reserve' => $r['reserve_pct'],
			'area'    => $r['area'],
			'units'   => $r['units'],
			'unit'    => $c['unit'],
		);
	}

	public static function add_data( $data, $product_id, $variation_id ) {
		if ( empty( $_POST['mgmc_data'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- WooCommerce add-to-cart form.
			return $data;
		}
		$parsed = self::parse( wp_unslash( $_POST['mgmc_data'] ), $variation_id ? $variation_id : $product_id ); // phpcs:ignore
		if ( $parsed ) {
			$data['mgmc'] = $parsed;
		}
		return $data;
	}

	/** Human readable lines. */
	public static function lines( $m ) {
		$surf  = MGMC_Config::surfaces();
		$pats  = MGMC_Config::patterns();
		$rooms = array();
		foreach ( $m['rooms'] as $i => $r ) {
			$s       = isset( $m['surface'][ $i ] ) ? implode( '+', array_map( function ( $k ) use ( $surf ) {
				return mb_strtolower( $surf[ $k ] );
			}, $m['surface'][ $i ] ) ) : '';
			$rooms[] = sprintf( '%s: %s × %s%s m%s = %s m²', $r['name'], wc_format_localized_decimal( $r['l'] ), wc_format_localized_decimal( $r['w'] ), $r['h'] > 0 && false !== strpos( $s, 'tường' ) ? ' × ' . wc_format_localized_decimal( $r['h'] ) : '', $s ? ' (' . $s . ')' : '', wc_format_localized_decimal( round( $r['area'], 2 ) ) );
		}
		$summary = sprintf(
			'Tổng %s m²%s · dự phòng %s%% · đề xuất %d %s',
			wc_format_localized_decimal( round( $m['area'], 2 ) ),
			'straight' !== $m['pattern'] && isset( $pats[ $m['pattern'] ] ) ? ' · ' . mb_strtolower( $pats[ $m['pattern'] ] ) : '',
			wc_format_localized_decimal( $m['reserve'] ),
			$m['units'],
			$m['unit']
		);
		return array( 'rooms' => $rooms, 'summary' => $summary );
	}

	public static function show( $item_data, $item ) {
		if ( empty( $item['mgmc'] ) ) {
			return $item_data;
		}
		$l           = self::lines( $item['mgmc'] );
		$item_data[] = array(
			'key'     => 'Kích thước phòng',
			'value'   => implode( '; ', $l['rooms'] ),
			'display' => '<span class="mgmc-cart-rooms">' . implode( '<br>', array_map( 'esc_html', $l['rooms'] ) ) . '</span>',
		);
		$item_data[] = array( 'key' => 'Tính vật liệu', 'value' => $l['summary'] );
		if ( (int) $item['quantity'] < (int) $item['mgmc']['units'] ) {
			$item_data[] = array( 'key' => 'Lưu ý', 'value' => sprintf( 'Số lượng ít hơn mức đề xuất (%d %s)', $item['mgmc']['units'], $item['mgmc']['unit'] ) );
		}
		return $item_data;
	}

	public static function order_meta( $order_item, $key, $values ) {
		if ( empty( $values['mgmc'] ) ) {
			return;
		}
		$l = self::lines( $values['mgmc'] );
		$order_item->add_meta_data( 'Kích thước phòng', implode( "\n", $l['rooms'] ), true );
		$order_item->add_meta_data( 'Tính vật liệu', $l['summary'], true );
		$order_item->add_meta_data( '_mgmc', $values['mgmc'], true );
	}
}
