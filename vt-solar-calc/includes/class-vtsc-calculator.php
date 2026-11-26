<?php
defined( 'ABSPATH' ) || exit;

/**
 * Pure calculation engine (no WordPress calls) so it can be unit tested.
 */
class VTSC_Calculator {

	/** @var array */
	private $s;

	public function __construct( array $settings ) {
		$this->s = $settings;
	}

	/* ---------------- EVN tariff ---------------- */

	/** Monthly bill (VNĐ incl. VAT) -> kWh. */
	public function bill_to_kwh( $bill, $customer = 'home' ) {
		$net = max( 0, (float) $bill ) / ( 1 + $this->s['vat'] / 100 );
		if ( 'business' === $customer ) {
			return $this->s['business_price'] > 0 ? $net / $this->s['business_price'] : 0;
		}
		$kwh = 0;
		foreach ( $this->s['tiers'] as $t ) {
			$size  = (float) $t[0];
			$price = (float) $t[1];
			if ( $price <= 0 ) {
				continue;
			}
			$cost = $size * $price;
			if ( $size <= 0 || $net <= $cost ) {
				return $kwh + $net / $price;
			}
			$kwh += $size;
			$net -= $cost;
		}
		// Ran out of tiers (last tier had a size): price the remainder at the last price.
		$last = end( $this->s['tiers'] );
		return $kwh + ( $last ? $net / (float) $last[1] : 0 );
	}

	/** kWh -> monthly bill (VNĐ incl. VAT). */
	public function kwh_to_bill( $kwh, $customer = 'home' ) {
		$kwh = max( 0, (float) $kwh );
		if ( 'business' === $customer ) {
			$net = $kwh * $this->s['business_price'];
		} else {
			$net  = 0;
			$last = 0;
			foreach ( $this->s['tiers'] as $t ) {
				$size = (float) $t[0];
				$last = (float) $t[1];
				$use  = $size <= 0 ? $kwh : min( $kwh, $size );
				$net += $use * $last;
				$kwh -= $use;
				if ( $kwh <= 0 ) {
					break;
				}
			}
			$net += $kwh > 0 ? $kwh * $last : 0;
		}
		return $net * ( 1 + $this->s['vat'] / 100 );
	}

	/* ---------------- Sizing ---------------- */

	private function inverter_size( $kw ) {
		$sizes = array_filter( array_map( 'floatval', explode( ',', (string) $this->s['inverter_sizes'] ) ) );
		sort( $sizes );
		foreach ( $sizes as $size ) {
			if ( $size >= $kw ) {
				return $size;
			}
		}
		return $sizes ? (float) ceil( $kw ) : round( $kw, 1 );
	}

	/**
	 * @param array $in bill, area, region (nam|trung|bac), phase (1|3), day (0-100), type (ongrid|hybrid|offgrid), customer (home|business)
	 */
	public function calculate( array $in ) {
		$s        = $this->s;
		$bill     = max( 0, (float) $in['bill'] );
		$area     = max( 0, (float) $in['area'] );
		$region   = in_array( $in['region'], array( 'nam', 'trung', 'bac' ), true ) ? $in['region'] : 'nam';
		$phase    = 3 === (int) $in['phase'] ? 3 : 1;
		$day_pct  = max( 0, min( 100, (float) $in['day'] ) ) / 100;
		$type     = in_array( $in['type'], array( 'ongrid', 'hybrid', 'offgrid' ), true ) ? $in['type'] : 'ongrid';
		$customer = 'business' === $in['customer'] ? 'business' : 'home';

		$sun       = (float) $s[ 'sun_' . $region ];
		$pr        = (float) $s['performance'] / 100;
		$yield     = $sun * $pr * 30;                // kWh per kWp per month.
		$kwh       = $this->bill_to_kwh( $bill, $customer );
		$day_kwh   = $kwh * $day_pct;
		$night_kwh = $kwh - $day_kwh;
		$warnings  = array();

		if ( $kwh <= 0 || $yield <= 0 ) {
			return array( 'error' => 'Vui lòng nhập hoá đơn tiền điện hợp lệ.' );
		}

		// Target capacity.
		switch ( $type ) {
			case 'hybrid':
				$target = $kwh / $yield;
				break;
			case 'offgrid':
				$target = $kwh / $yield * 1.2;
				break;
			default:
				$target = $day_kwh / $yield;
		}

		$panel_w    = max( 1, (float) $s['panel_w'] );
		$roof_max   = $s['panel_m2'] > 0 ? (int) floor( $area / (float) $s['panel_m2'] ) : 0;
		$panels     = (int) max( 1, ceil( $target * 1000 / $panel_w - 1e-9 ) );
		$roof_limit = false;
		if ( $roof_max < 1 ) {
			return array( 'error' => sprintf( 'Diện tích mái quá nhỏ, cần tối thiểu %s m² để lắp 1 tấm pin.', $this->num( $s['panel_m2'], 1 ) ) );
		}
		if ( $panels > $roof_max ) {
			$panels     = $roof_max;
			$roof_limit = true;
			$warnings[] = sprintf( 'Diện tích mái chỉ đủ lắp %d tấm pin, hệ thống chưa đáp ứng hết nhu cầu. Cần khoảng %s m² mái để đủ công suất đề xuất.', $roof_max, $this->num( ceil( $target * 1000 / $panel_w ) * $s['panel_m2'], 1 ) );
		}
		$kwp      = $panels * $panel_w / 1000;
		$inverter = $this->inverter_size( 'ongrid' === $type ? $kwp / 1.1 : $kwp );
		if ( 1 === $phase && $inverter > (float) $s['max_1pha_kw'] ) {
			$warnings[] = sprintf( 'Công suất inverter %s kW vượt mức khuyến nghị cho điện 1 pha (%s kW). Nên chuyển sang điện 3 pha.', $this->num( $inverter, 1 ), $this->num( $s['max_1pha_kw'], 1 ) );
		}
		if ( 3 === $phase && $inverter < 5 ) {
			$warnings[] = 'Hệ thống nhỏ, có thể dùng inverter 1 pha để tiết kiệm chi phí.';
		}

		// Production & self consumption (per month).
		$production = $kwp * $yield;
		$battery    = 0;
		$dod        = max( 0.1, (float) $s['battery_dod'] / 100 );
		$module     = max( 0.5, (float) $s['battery_module'] );
		if ( 'hybrid' === $type ) {
			$store_day = min( $night_kwh, max( 0, $production - $day_kwh ) ) / 30;
			$battery   = $store_day > 0 ? ceil( $store_day / $dod / $module ) * $module : 0;
		} elseif ( 'offgrid' === $type ) {
			$battery = ceil( ( $night_kwh / 30 ) * 1.25 / $dod / $module ) * $module;
		}
		$battery_usable = $battery * $dod * 30;

		$direct = min( $production, $day_kwh );
		if ( 'ongrid' === $type ) {
			$self = $direct;
		} else {
			$stored = min( $production - $direct, $battery_usable, $night_kwh );
			$self   = $direct + max( 0, $stored );
		}
		if ( 'offgrid' === $type && $self < $kwh * 0.98 ) {
			$warnings[] = 'Hệ độc lập chưa đáp ứng đủ 100% nhu cầu, cần thêm nguồn dự phòng (lưới điện hoặc máy phát) vào ngày ít nắng.';
		}
		$surplus     = max( 0, $production - $self );
		$sell_kwh    = min( $surplus, $production * (float) $s['sell_cap'] / 100 );
		$sell_money  = $sell_kwh * (float) $s['sell_price'];
		$grid_after  = max( 0, $kwh - $self );
		$bill_after  = 'offgrid' === $type ? 0 : $this->kwh_to_bill( $grid_after, $customer );
		$saving      = max( 0, $bill - $bill_after ) + $sell_money;

		// Cost.
		$unit = (float) $s[ 'price_' . $type ];
		$cost = $kwp * $unit;
		if ( 3 === $phase ) {
			$cost *= 1 + (float) $s['surcharge_3pha'] / 100;
		}
		$battery_cost = $battery * (float) $s['battery_price'];
		$cost        += $battery_cost;

		// Long-term cash flow.
		$years    = (int) $s['years'];
		$deg      = (float) $s['degradation'] / 100;
		$inc      = (float) $s['price_increase'] / 100;
		$cum      = -$cost;
		$flow     = array( 0 => round( $cum ) );
		$payback  = null;
		$total    = 0;
		for ( $y = 1; $y <= $years; $y++ ) {
			$year_saving = $saving * 12 * pow( 1 - $deg, $y - 1 ) * pow( 1 + $inc, $y - 1 );
			if ( null === $payback && $cum + $year_saving >= 0 ) {
				$payback = $y - 1 + ( $year_saving > 0 ? -$cum / $year_saving : 0 );
			}
			$cum         += $year_saving;
			$total       += $year_saving;
			$flow[ $y ]   = round( $cum );
		}

		$co2_year = $self * 12 * (float) $s['co2_factor'] / 1000; // tonnes.

		return array(
			'input'          => compact( 'bill', 'area', 'region', 'phase', 'type', 'customer' ) + array( 'day' => $day_pct * 100 ),
			'kwh_month'      => $kwh,
			'kwh_day'        => $kwh / 30,
			'day_kwh'        => $day_kwh,
			'night_kwh'      => $night_kwh,
			'sun_hours'      => $sun,
			'panels'         => $panels,
			'panel_w'        => $panel_w,
			'kwp'            => $kwp,
			'kwp_needed'     => $target,
			'roof_used'      => $panels * (float) $s['panel_m2'],
			'roof_limited'   => $roof_limit,
			'inverter'       => $inverter,
			'battery'        => $battery,
			'production'     => $production,
			'production_day' => $production / 30,
			'self_use'       => $self,
			'coverage'       => $kwh > 0 ? min( 100, $self / $kwh * 100 ) : 0,
			'surplus'        => $surplus,
			'sell_money'     => $sell_money,
			'bill_after'     => $bill_after,
			'saving_month'   => $saving,
			'saving_year'    => $saving * 12,
			'saving_total'   => $total,
			'cost'           => $cost,
			'battery_cost'   => $battery_cost,
			'payback'        => $payback,
			'roi'            => $cost > 0 ? ( $total - $cost ) / $cost * 100 : 0,
			'profit'         => $total - $cost,
			'cash_flow'      => $flow,
			'co2_year'       => $co2_year,
			'trees'          => (int) round( $co2_year * 1000 / 20 ),
			'warnings'       => $warnings,
		);
	}

	private function num( $n, $dec = 0 ) {
		return number_format( (float) $n, $dec, ',', '.' );
	}
}
