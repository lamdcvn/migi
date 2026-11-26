<?php
defined( 'ABSPATH' ) || exit;

/**
 * Stores the room breakdown with the cart line (re-calculated on the server, never trusted from the browser)
 * and shows it in cart, checkout, emails and the admin order screen.
 */
class MGMC_Cart {

	public static function init() {
		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_data' ), 10, 3 );
		add_action( 'woocommerce_add_to_cart', array( __CLASS__, 'add_addons' ), 20, 2 );
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
			'type'    => $c['type'],
		);
	}

	/** @var bool True while this class adds add-on lines (so the posted data is not re-applied). */
	private static $adding = false;

	/**
	 * Calculates every line of an estimate: the main product and the chosen add-ons.
	 *
	 * @return array[] product, parsed (mgmc data), units, price (display, per unit), total
	 */
	public static function lines_for( $product, $json, array $addon_ids = array() ) {
		$product = is_numeric( $product ) ? wc_get_product( $product ) : $product;
		$c       = $product ? MGMC_Config::for_product( $product ) : null;
		if ( ! $c ) {
			return array();
		}
		$out = array();
		$ids = array_merge( array( $product->get_id() ), array_intersect( $c['addons'], array_map( 'absint', $addon_ids ) ) );
		foreach ( $ids as $i => $id ) {
			$p = 0 === $i ? $product : wc_get_product( $id );
			if ( ! $p || ( $i > 0 && ! $p->is_type( 'simple' ) ) ) {
				continue;
			}
			$parsed = self::parse( $json, $p );
			if ( ! $parsed ) {
				continue;
			}
			$price = (float) wc_get_price_to_display( $p );
			$out[] = array(
				'product' => $p,
				'main'    => 0 === $i,
				'parsed'  => $parsed,
				'units'   => (int) $parsed['units'],
				'price'   => $price,
				'total'   => $price * (int) $parsed['units'],
			);
		}
		return $out;
	}

	/** After the main product is added, add the ticked add-ons with their calculated quantities. */
	public static function add_addons( $cart_item_key, $product_id ) {
		// phpcs:disable WordPress.Security.NonceVerification -- WooCommerce add-to-cart form.
		if ( self::$adding || empty( $_POST['mgmc_addons'] ) || empty( $_POST['mgmc_data'] ) ) {
			return;
		}
		$posted = absint( $_POST['add-to-cart'] ?? 0 );
		if ( $posted !== (int) $product_id ) {
			return;
		}
		$ids  = json_decode( wp_unslash( $_POST['mgmc_addons'] ), true ); // phpcs:ignore
		$json = wp_unslash( $_POST['mgmc_data'] ); // phpcs:ignore
		// phpcs:enable
		if ( ! is_array( $ids ) || ! $ids ) {
			return;
		}
		self::$adding = true;
		foreach ( self::lines_for( $product_id, $json, $ids ) as $line ) {
			if ( $line['main'] || $line['units'] < 1 || ! $line['product']->is_purchasable() || ! $line['product']->is_in_stock() ) {
				continue;
			}
			$max   = $line['product']->get_max_purchase_quantity();
			$units = $max > 0 ? min( $max, $line['units'] ) : $line['units'];
			WC()->cart->add_to_cart( $line['product']->get_id(), $units, 0, array(), array( 'mgmc' => $line['parsed'] ) );
		}
		self::$adding = false;
	}

	public static function add_data( $data, $product_id, $variation_id ) {
		if ( self::$adding || empty( $_POST['mgmc_data'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- WooCommerce add-to-cart form.
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
		$rooms   = array();
		$measure = isset( $m['type'] ) && 'perimeter' === $m['type'] ? 'm dài' : 'm²';
		foreach ( $m['rooms'] as $i => $r ) {
			$s       = isset( $m['surface'][ $i ] ) ? implode( '+', array_map( function ( $k ) use ( $surf ) {
				return mb_strtolower( $surf[ $k ] );
			}, $m['surface'][ $i ] ) ) : '';
			$rooms[] = sprintf( '%s: %s × %s%s m%s = %s ' . $measure, $r['name'], wc_format_localized_decimal( $r['l'] ), wc_format_localized_decimal( $r['w'] ), $r['h'] > 0 && false !== strpos( $s, 'tường' ) ? ' × ' . wc_format_localized_decimal( $r['h'] ) : '', $s ? ' (' . $s . ')' : '', wc_format_localized_decimal( round( $r['area'], 2 ) ) );
		}
		$summary = sprintf(
			'Tổng %s ' . $measure . '%s · dự phòng %s%% · đề xuất %d %s',
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
