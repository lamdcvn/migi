<?php
defined( 'ABSPATH' ) || exit;

/** Marks cart lines that came from a build and applies the optional Build PC discount. */
class MGPC_Cart {

	public static function init() {
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'item_data' ), 10, 2 );
		add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'discount' ) );
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'order_meta' ), 10, 3 );
	}

	public static function item_data( $data, $item ) {
		if ( ! empty( $item['mgpc']['code'] ) ) {
			$slots  = MGPC_Settings::slots();
			$slot   = $slots[ $item['mgpc']['slot'] ]['label'] ?? '';
			$data[] = array( 'key' => 'Build PC', 'value' => $item['mgpc']['code'] . ( $slot ? ' · ' . $slot : '' ) );
		}
		return $data;
	}

	/** Totals per build code: [code => ['groups' => n distinct slots in cart, 'subtotal' => …]]. */
	public static function builds( $cart ) {
		$b = array();
		foreach ( $cart->get_cart() as $item ) {
			if ( empty( $item['mgpc']['code'] ) ) {
				continue;
			}
			$code                       = $item['mgpc']['code'];
			$b[ $code ]['slots'][ $item['mgpc']['slot'] ] = true;
			$b[ $code ]['subtotal']     = ( $b[ $code ]['subtotal'] ?? 0 ) + (float) $item['line_subtotal'];
		}
		return $b;
	}

	public static function discount( $cart ) {
		$s = MGPC_Settings::get();
		if ( 'yes' !== $s['discount_enabled'] || (float) $s['discount_percent'] <= 0 ) {
			return;
		}
		foreach ( self::builds( $cart ) as $code => $b ) {
			if ( count( $b['slots'] ) >= (int) $s['discount_min'] ) {
				$cart->add_fee( $s['discount_label'] . ' (' . $code . ')', -round( $b['subtotal'] * (float) $s['discount_percent'] / 100, wc_get_price_decimals() ), false );
			}
		}
	}

	public static function order_meta( $order_item, $key, $values ) {
		if ( ! empty( $values['mgpc']['code'] ) ) {
			$order_item->add_meta_data( 'Build PC', $values['mgpc']['code'], true );
		}
	}
}
