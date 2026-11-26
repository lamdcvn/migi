<?php
defined( 'ABSPATH' ) || exit;

/**
 * Tier data helpers.
 *
 * A tier: array( qty, discount (percent), title, subtitle, badge, badge_color, is_default ).
 * A tier applies when the quantity in cart >= tier qty (highest matching tier wins).
 */
class MGBD_Tiers {

	public static function default_tiers() {
		return array(
			array( 'qty' => 1, 'discount' => 0,  'title' => '', 'subtitle' => 'Giá tiêu chuẩn', 'badge' => '',              'badge_color' => '#7a2e2e', 'is_default' => '' ),
			array( 'qty' => 2, 'discount' => 10, 'title' => '', 'subtitle' => '',               'badge' => 'Phổ biến nhất', 'badge_color' => '#5b5cf0', 'is_default' => '' ),
			array( 'qty' => 3, 'discount' => 20, 'title' => '', 'subtitle' => '',               'badge' => 'Giá tốt nhất',  'badge_color' => '#ff8a00', 'is_default' => 'yes' ),
		);
	}

	public static function settings() {
		return wp_parse_args( (array) get_option( 'mgbd_settings', array() ), array(
			'global_enabled'     => 'yes',
			'heading'            => 'Mua nhiều – tiết kiệm hơn',
			'cart_label'         => 'Deal: Bạn tiết kiệm',
			'show_unit_note'     => 'yes',
			'combine_variations' => 'yes',
		) );
	}

	/**
	 * Sanitize raw POSTed tiers. $default_key is the key of the row chosen as default.
	 */
	public static function sanitize( $raw, $default_key = null ) {
		$out = array();
		if ( ! is_array( $raw ) ) {
			return $out;
		}
		foreach ( $raw as $key => $row ) {
			$qty = isset( $row['qty'] ) ? absint( $row['qty'] ) : 0;
			if ( $qty < 1 ) {
				continue;
			}
			$discount = isset( $row['discount'] ) ? (float) str_replace( ',', '.', $row['discount'] ) : 0;
			$discount = max( 0, min( 100, $discount ) );
			$color    = isset( $row['badge_color'] ) ? sanitize_hex_color( $row['badge_color'] ) : '';
			$out[ $qty ] = array(
				'qty'         => $qty,
				'discount'    => $discount,
				'title'       => isset( $row['title'] ) ? sanitize_text_field( wp_unslash( $row['title'] ) ) : '',
				'subtitle'    => isset( $row['subtitle'] ) ? sanitize_text_field( wp_unslash( $row['subtitle'] ) ) : '',
				'badge'       => isset( $row['badge'] ) ? sanitize_text_field( wp_unslash( $row['badge'] ) ) : '',
				'badge_color' => $color ? $color : '#7a2e2e',
				'is_default'  => ( null !== $default_key && (string) $key === (string) $default_key ) ? 'yes' : '',
			);
		}
		ksort( $out );
		return array_values( $out );
	}

	/**
	 * Tiers for a product (variations use the parent's config). Empty array = no deal.
	 */
	public static function for_product( $product ) {
		if ( is_numeric( $product ) ) {
			$product = wc_get_product( $product );
		}
		if ( ! $product ) {
			return array();
		}
		$id   = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
		$mode = get_post_meta( $id, '_mgbd_mode', true );
		$mode = $mode ? $mode : 'global';

		if ( 'off' === $mode ) {
			$tiers = array();
		} elseif ( 'custom' === $mode ) {
			$tiers = (array) get_post_meta( $id, '_mgbd_tiers', true );
		} else {
			$settings = self::settings();
			$tiers    = 'yes' === $settings['global_enabled'] ? (array) get_option( 'mgbd_global_tiers', array() ) : array();
		}

		$tiers = array_values( array_filter( $tiers, function ( $t ) {
			return is_array( $t ) && ! empty( $t['qty'] );
		} ) );

		// A deal table only makes sense if at least one tier gives a discount.
		$has_discount = false;
		foreach ( $tiers as $t ) {
			if ( (float) $t['discount'] > 0 ) {
				$has_discount = true;
			}
		}
		return apply_filters( 'mgbd_product_tiers', $has_discount ? $tiers : array(), $product );
	}

	public static function match( $tiers, $qty ) {
		$found = null;
		foreach ( $tiers as $t ) {
			if ( $qty >= (int) $t['qty'] ) {
				$found = $t;
			}
		}
		return $found;
	}
}
