<?php
defined( 'ABSPATH' ) || exit;

class MGMC_Config {

	const OPTION = 'mgmc_settings';
	const META   = '_mgmc';

	public static function types() {
		return array(
			'tile'      => array( 'label' => 'Gạch ốp lát', 'unit' => 'thùng', 'base' => 'viên', 'surfaces' => array( 'floor', 'wall' ), 'default' => 'floor' ),
			'wallpaper' => array( 'label' => 'Giấy dán tường', 'unit' => 'cuộn', 'base' => 'cuộn', 'surfaces' => array( 'wall', 'ceiling' ), 'default' => 'wall' ),
			'panel'     => array( 'label' => 'Tấm ốp (nano, PVC, lam sóng, gỗ nhựa...)', 'unit' => 'tấm', 'base' => 'tấm', 'surfaces' => array( 'wall', 'ceiling' ), 'default' => 'wall' ),
			'area'      => array( 'label' => 'Vật liệu bán theo m² (sàn gỗ, vinyl, thảm, decal...)', 'unit' => 'hộp', 'base' => 'm²', 'surfaces' => array( 'floor' ), 'default' => 'floor' ),
			'paint'     => array( 'label' => 'Sơn', 'unit' => 'thùng', 'base' => 'lít', 'surfaces' => array( 'wall', 'ceiling' ), 'default' => 'wall' ),
		);
	}

	public static function surfaces() {
		return array( 'floor' => 'Sàn', 'wall' => 'Tường', 'ceiling' => 'Trần' );
	}

	public static function patterns() {
		return array( 'straight' => 'Lát thẳng', 'diagonal' => 'Lát chéo 45°', 'herringbone' => 'Xương cá' );
	}

	public static function settings() {
		return wp_parse_args( (array) get_option( self::OPTION, array() ), array(
			'title'             => 'Tính vật liệu cần mua',
			'intro'             => 'Nhập kích thước từng phòng, hệ thống tự tính số lượng cần mua kèm dự phòng.',
			'reserve_options'   => '0,3,5,8,10,15',
			'reserve'           => 5,
			'waste_straight'    => 7,
			'waste_diagonal'    => 15,
			'waste_herringbone' => 20,
			'wall_height'       => 3,
			'auto_qty'          => 'yes',
			'show_price_m2'     => 'yes',
			'open_default'      => 'yes',
			'note'              => 'Số lượng mang tính tham khảo. Nên mua cùng một lô (cùng mã màu/số lô) để tránh lệch màu và giữ lại phần dự phòng để sửa chữa sau này.',
		) );
	}

	public static function product_defaults() {
		$s = self::settings();
		return array(
			'enabled'           => 'no',
			'type'              => 'tile',
			'unit'              => '',
			'surfaces'          => array(),
			'tile_w'            => 60,
			'tile_h'            => 60,
			'joint'             => 2,
			'count'             => 'area',
			'per_unit'          => 4,
			'roll_w'            => 0.53,
			'roll_l'            => 10,
			'repeat'            => 0,
			'panel_w'           => 40,
			'panel_l'           => 290,
			'm2_per_unit'       => 2.4,
			'coverage'          => 10,
			'coats'             => 2,
			'unit_volume'       => 5,
			'reserve'           => '',
			'waste_straight'    => '',
			'waste_diagonal'    => '',
			'waste_herringbone' => '',
			'min_reserve_unit'  => 'no',
		);
	}

	/** Effective config for a product (variation → parent), or null when the calculator is off. */
	public static function for_product( $product ) {
		if ( is_numeric( $product ) ) {
			$product = wc_get_product( $product );
		}
		if ( ! $product ) {
			return null;
		}
		$id  = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
		$raw = get_post_meta( $id, self::META, true );
		$c   = wp_parse_args( is_array( $raw ) ? $raw : array(), self::product_defaults() );
		if ( 'yes' !== $c['enabled'] ) {
			return null;
		}
		$s     = self::settings();
		$types = self::types();
		$type  = isset( $types[ $c['type'] ] ) ? $c['type'] : 'tile';
		$c['type'] = $type;
		foreach ( array( 'reserve', 'waste_straight', 'waste_diagonal', 'waste_herringbone' ) as $k ) {
			if ( '' === $c[ $k ] || null === $c[ $k ] ) {
				$c[ $k ] = $s[ $k ];
			}
		}
		if ( ! $c['unit'] ) {
			$c['unit'] = $types[ $type ]['unit'];
		}
		$allowed       = $types[ $type ]['surfaces'];
		$c['surfaces'] = array_values( array_intersect( (array) $c['surfaces'], array_keys( self::surfaces() ) ) );
		if ( ! $c['surfaces'] ) {
			$c['surfaces'] = $allowed;
		}
		$c['min_reserve_unit'] = 'yes' === $c['min_reserve_unit'];
		return apply_filters( 'mgmc_product_config', $c, $product );
	}
}
