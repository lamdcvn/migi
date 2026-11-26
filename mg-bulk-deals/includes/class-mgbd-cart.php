<?php
defined( 'ABSPATH' ) || exit;

/**
 * Applies the tier price to cart items and shows the savings in cart, mini-cart, checkout and orders.
 */
class MGBD_Cart {

	public static function init() {
		add_filter( 'woocommerce_add_cart_item', array( __CLASS__, 'remember_base' ), 20 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( __CLASS__, 'remember_base' ), 20 );
		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'apply_prices' ), 20 );
		add_action( 'woocommerce_cart_loaded_from_session', array( __CLASS__, 'apply_prices' ), 20 );

		add_filter( 'woocommerce_cart_item_price', array( __CLASS__, 'cart_item_price' ), 20, 2 );
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'item_data' ), 20, 2 );
		add_action( 'woocommerce_cart_totals_before_order_total', array( __CLASS__, 'totals_row' ) );
		add_action( 'woocommerce_review_order_before_order_total', array( __CLASS__, 'totals_row' ) );
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'order_item_meta' ), 20, 3 );
	}

	/** Store the product's own price before we touch it. */
	public static function remember_base( $item ) {
		if ( isset( $item['data'] ) && $item['data'] instanceof WC_Product ) {
			$item['mgbd_base'] = (float) $item['data']->get_price( 'edit' );
		}
		return $item;
	}

	private static function group_key( $item ) {
		$s = MGBD_Tiers::settings();
		return 'yes' === $s['combine_variations'] ? (int) $item['product_id'] : ( (int) $item['product_id'] . ':' . (int) $item['variation_id'] );
	}

	public static function apply_prices( $cart ) {
		if ( ! $cart instanceof WC_Cart || ( is_admin() && ! wp_doing_ajax() ) ) {
			return;
		}
		$qty = array();
		foreach ( $cart->get_cart() as $item ) {
			$g         = self::group_key( $item );
			$qty[ $g ] = ( isset( $qty[ $g ] ) ? $qty[ $g ] : 0 ) + (int) $item['quantity'];
		}
		foreach ( $cart->cart_contents as $key => $item ) {
			if ( empty( $item['data'] ) || ! $item['data'] instanceof WC_Product ) {
				continue;
			}
			if ( ! isset( $item['mgbd_base'] ) ) {
				$item['mgbd_base']                       = (float) $item['data']->get_price( 'edit' );
				$cart->cart_contents[ $key ]['mgbd_base'] = $item['mgbd_base'];
			}
			$base = (float) $item['mgbd_base'];
			$tier = $item['data']->is_sold_individually() ? null : MGBD_Tiers::match( MGBD_Tiers::for_product( $item['data'] ), $qty[ self::group_key( $item ) ] );
			$disc = $tier ? (float) $tier['discount'] : 0;

			$cart->cart_contents[ $key ]['mgbd_discount'] = $disc;
			$item['data']->set_price( $disc > 0 ? $base * ( 1 - $disc / 100 ) : $base );
		}
	}

	private static function display( $product, $price ) {
		return (float) wc_get_price_to_display( $product, array( 'price' => $price ) );
	}

	public static function cart_item_price( $html, $item ) {
		if ( empty( $item['mgbd_discount'] ) || ! isset( $item['mgbd_base'] ) ) {
			return $html;
		}
		$p    = $item['data'];
		$base = self::display( $p, $item['mgbd_base'] );
		$now  = self::display( $p, $item['mgbd_base'] * ( 1 - $item['mgbd_discount'] / 100 ) );
		return '<del>' . wc_price( $base ) . '</del> <ins>' . wc_price( $now ) . '</ins>';
	}

	private static function item_saving( $item ) {
		if ( empty( $item['mgbd_discount'] ) || ! isset( $item['mgbd_base'] ) ) {
			return 0;
		}
		$p = $item['data'];
		return ( self::display( $p, $item['mgbd_base'] ) - self::display( $p, $item['mgbd_base'] * ( 1 - $item['mgbd_discount'] / 100 ) ) ) * (int) $item['quantity'];
	}

	public static function item_data( $data, $item ) {
		$saving = self::item_saving( $item );
		if ( $saving > 0 ) {
			$s      = MGBD_Tiers::settings();
			$data[] = array(
				'key'     => $s['cart_label'] ? $s['cart_label'] : 'Deal',
				'value'   => wc_format_localized_decimal( $item['mgbd_discount'] ) . '%',
				'display' => '<span class="mgbd-cart-save">' . wc_price( $saving ) . ' (-' . wc_format_localized_decimal( $item['mgbd_discount'] ) . '%)</span>',
			);
		}
		return $data;
	}

	public static function totals_row() {
		if ( ! WC()->cart ) {
			return;
		}
		$total = 0;
		foreach ( WC()->cart->get_cart() as $item ) {
			$total += self::item_saving( $item );
		}
		if ( $total <= 0 ) {
			return;
		}
		echo '<tr class="mgbd-total-saving"><th>Tiết kiệm từ deal</th><td data-title="Tiết kiệm từ deal"><strong>-' . wp_kses_post( wc_price( $total ) ) . '</strong></td></tr>';
	}

	public static function order_item_meta( $order_item, $cart_item_key, $values ) {
		if ( ! empty( $values['mgbd_discount'] ) && isset( $values['mgbd_base'] ) ) {
			$order_item->add_meta_data( 'Deal số lượng', sprintf( 'Giảm %s%% (giá gốc %s)', wc_format_localized_decimal( $values['mgbd_discount'] ), wp_strip_all_tags( wc_price( $values['mgbd_base'] ) ) ), true );
		}
	}
}
