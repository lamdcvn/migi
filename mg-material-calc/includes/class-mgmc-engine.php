<?php
defined( 'ABSPATH' ) || exit;

/**
 * Material calculation engine (pure PHP, mirrored 1:1 in assets/js/mgmc-engine.js – keep both in sync;
 * tests/engine-parity compares them).
 *
 * Config (per product):
 *   type: tile | wallpaper | panel | area | paint
 *   tile_w, tile_h (cm), joint (mm), per_unit (pieces per box),
 *   count: grid (count cut pieces, default) | area (area ÷ tile + waste %) – tile
 *   roll_w, roll_l (m), repeat (cm)                                    – wallpaper (unit = roll)
 *   panel_w, panel_l (cm), per_unit (panels per pack)                  – panel
 *   m2_per_unit                                                        – area (sàn gỗ, vinyl, thảm...)
 *   coverage (m²/L/coat), coats, unit_volume (L)                       – paint
 *   waste_diagonal, waste_herringbone, waste_straight (%)              – pattern loss for area-based counting
 * Room: name, l, w, h (m), floor, wall, ceiling (bool), openings (m²)
 * Options: pattern (straight|diagonal|herringbone), reserve (%)
 */
class MGMC_Engine {

	public static function f( $v ) {
		return max( 0, (float) str_replace( ',', '.', (string) $v ) );
	}

	/** Pieces of size a×b (m) to cover L×W, best orientation, counting every cut piece as whole. */
	public static function grid( $l, $w, $a, $b ) {
		if ( $l <= 0 || $w <= 0 || $a <= 0 || $b <= 0 ) {
			return 0;
		}
		$e  = 1e-9;
		$p1 = ceil( $l / $a - $e ) * ceil( $w / $b - $e );
		$p2 = ceil( $l / $b - $e ) * ceil( $w / $a - $e );
		return (int) min( $p1, $p2 );
	}

	public static function pattern_waste( $c, $pattern ) {
		$key = 'waste_' . ( in_array( $pattern, array( 'diagonal', 'herringbone' ), true ) ? $pattern : 'straight' );
		return isset( $c[ $key ] ) ? self::f( $c[ $key ] ) / 100 : 0;
	}

	/** Returns room result: area (m²) and need (base quantity) + text detail. */
	public static function room( array $c, array $r, $pattern ) {
		$l     = self::f( $r['l'] );
		$w     = self::f( $r['w'] );
		$h     = self::f( isset( $r['h'] ) ? $r['h'] : 0 );
		$open  = self::f( isset( $r['openings'] ) ? $r['openings'] : 0 );
		$floor = ! empty( $r['floor'] );
		$wall  = ! empty( $r['wall'] ) && $h > 0;
		$ceil  = ! empty( $r['ceiling'] );
		$perim = 2 * ( $l + $w );
		$wall_area = $wall ? max( 0, $perim * $h - $open ) : 0;
		$flat_area = ( $floor ? $l * $w : 0 ) + ( $ceil ? $l * $w : 0 );
		$area      = $flat_area + $wall_area;
		$need      = 0;
		$e         = 1e-9;
		$flats     = ( $floor ? 1 : 0 ) + ( $ceil ? 1 : 0 );
		$diag      = 'straight' !== $pattern;

		switch ( $c['type'] ) {
			case 'tile':
				$a = self::f( $c['tile_w'] ) / 100 + self::f( $c['joint'] ) / 1000;
				$b = self::f( $c['tile_h'] ) / 100 + self::f( $c['joint'] ) / 1000;
				if ( $a <= 0 || $b <= 0 ) {
					break;
				}
				if ( $diag || ( isset( $c['count'] ) && 'area' === $c['count'] ) ) {
					$need = $area > 0 ? ceil( $area / ( $a * $b ) * ( 1 + self::pattern_waste( $c, $pattern ) ) - $e ) : 0;
				} else {
					$need = $flats * self::grid( $l, $w, $a, $b );
					if ( $wall ) {
						$need += max( 0, self::grid( $perim, $h, $a, $b ) - floor( $open / ( $a * $b ) + $e ) );
					}
				}
				break;

			case 'panel':
				$a = self::f( $c['panel_w'] ) / 100;
				$b = self::f( $c['panel_l'] ) / 100;
				if ( $a <= 0 || $b <= 0 ) {
					break;
				}
				$need = $flats * self::grid( $l, $w, $a, $b );
				if ( $wall ) {
					$net_len = max( 0, $perim - $open / $h );
					$need   += ceil( $net_len / $a - $e ) * ceil( $h / $b - $e );
				}
				break;

			case 'wallpaper':
				$rw  = self::f( $c['roll_w'] );
				$rl  = self::f( $c['roll_l'] );
				$rep = self::f( $c['repeat'] ) / 100;
				if ( $rw <= 0 || $rl <= 0 ) {
					break;
				}
				$fraction = 0;
				if ( $wall ) {
					$strip    = $h + $rep + 0.05;
					$per_roll = max( 1, floor( $rl / $strip + $e ) );
					$strips   = ceil( max( 0, $perim - $open / $h ) / $rw - $e );
					$fraction += $strips / $per_roll;
				}
				if ( $flats ) {
					$long     = max( $l, $w );
					$short    = min( $l, $w );
					$strip    = $long + $rep + 0.05;
					$per_roll = $strip > $rl ? 0 : max( 1, floor( $rl / $strip + $e ) );
					$strips   = ceil( $short / $rw - $e ) * $flats;
					// Strip longer than a roll: fall back to area.
					$fraction += $per_roll ? $strips / $per_roll : ( $flat_area * 1.15 ) / ( $rw * $rl );
				}
				$need = $fraction;
				break;

			case 'paint':
				$cov  = self::f( $c['coverage'] );
				$need = $cov > 0 ? $area * max( 1, self::f( $c['coats'] ) ) / $cov : 0;
				break;

			default: // area.
				$need = $area * ( 1 + self::pattern_waste( $c, $pattern ) );
		}

		return array(
			'name' => isset( $r['name'] ) ? (string) $r['name'] : '',
			'l'    => $l,
			'w'    => $w,
			'h'    => $h,
			'area' => $area,
			'need' => $need,
		);
	}

	/** Base quantity per sale unit. */
	public static function per_unit( array $c ) {
		switch ( $c['type'] ) {
			case 'tile':
			case 'panel':
				return max( 1, (int) self::f( $c['per_unit'] ) );
			case 'paint':
				return max( 0.1, self::f( $c['unit_volume'] ) );
			case 'wallpaper':
				return 1;
			default:
				return max( 0.01, self::f( $c['m2_per_unit'] ) );
		}
	}

	/** m² covered by one sale unit (for "material m²" and price/m²). */
	public static function unit_m2( array $c ) {
		switch ( $c['type'] ) {
			case 'tile':
				return self::f( $c['tile_w'] ) * self::f( $c['tile_h'] ) / 10000 * self::per_unit( $c );
			case 'panel':
				return self::f( $c['panel_w'] ) * self::f( $c['panel_l'] ) / 10000 * self::per_unit( $c );
			case 'wallpaper':
				return self::f( $c['roll_w'] ) * self::f( $c['roll_l'] );
			case 'paint':
				return self::f( $c['unit_volume'] ) * self::f( $c['coverage'] ) / max( 1, self::f( $c['coats'] ) );
			default:
				return self::f( $c['m2_per_unit'] );
		}
	}

	/**
	 * @return array rooms[], area, need, reserve, total (base qty), units_need, units, units_reserve, material_m2, price, price_m2
	 */
	public static function calculate( array $c, array $rooms, array $opt, $unit_price = 0 ) {
		$pattern = isset( $opt['pattern'] ) && in_array( $opt['pattern'], array( 'diagonal', 'herringbone' ), true ) && in_array( $c['type'], array( 'tile', 'area' ), true ) ? $opt['pattern'] : 'straight';
		$reserve = min( 50, self::f( isset( $opt['reserve'] ) ? $opt['reserve'] : 0 ) ) / 100;
		$e       = 1e-9;
		$out     = array();
		$area    = 0;
		$need    = 0;
		foreach ( array_slice( $rooms, 0, 50 ) as $r ) {
			$res = self::room( $c, $r, $pattern );
			if ( $res['area'] <= 0 ) {
				continue;
			}
			$out[] = $res;
			$area += $res['area'];
			$need += $res['need'];
		}
		$per      = self::per_unit( $c );
		$discrete = in_array( $c['type'], array( 'tile', 'panel' ), true );
		$need_q   = $discrete ? ceil( $need - $e ) : $need;
		$reserve_q = $discrete ? ceil( $need_q * $reserve - $e ) : $need_q * $reserve;
		$total    = $need_q + $reserve_q;
		$units_need = $need_q > 0 ? (int) ceil( $need_q / $per - $e ) : 0;
		$units      = $total > 0 ? (int) ceil( $total / $per - $e ) : 0;
		if ( ! empty( $c['min_reserve_unit'] ) && $units > 0 && $units === $units_need && $reserve > 0 ) {
			$units++;
		}
		$m2 = $units * self::unit_m2( $c );
		return array(
			'pattern'       => $pattern,
			'reserve_pct'   => $reserve * 100,
			'rooms'         => $out,
			'area'          => $area,
			'need'          => $need_q,
			'reserve'       => $reserve_q,
			'total'         => $total,
			'units_need'    => $units_need,
			'units'         => $units,
			'units_reserve' => $units - $units_need,
			'material_m2'   => $m2,
			'price'         => $units * (float) $unit_price,
			'price_m2'      => $area > 0 ? $units * (float) $unit_price / $area : 0,
		);
	}
}
