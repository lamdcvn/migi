<?php
defined( 'ABSPATH' ) || exit;

/**
 * Turns a calculation result into HTML (on-page result with charts, and a table version for emails).
 */
class MGSC_Render {

	public static $types     = array( 'ongrid' => 'Hoà lưới bám tải', 'hybrid' => 'Hybrid có pin lưu trữ', 'offgrid' => 'Độc lập (off-grid)' );
	public static $regions   = array( 'nam' => 'Miền Nam', 'trung' => 'Miền Trung', 'bac' => 'Miền Bắc' );
	public static $customers = array( 'home' => 'Hộ gia đình', 'business' => 'Kinh doanh / sản xuất' );

	public static function num( $n, $dec = 0 ) {
		return number_format( (float) $n, $dec, ',', '.' );
	}

	public static function money( $n ) {
		return self::num( round( $n ) ) . ' đ';
	}

	/** Short money: 124,3 triệu / 1,2 tỷ. */
	public static function money_short( $n ) {
		$a = abs( $n );
		if ( $a >= 1e9 ) {
			return self::num( $n / 1e9, 2 ) . ' tỷ';
		}
		if ( $a >= 1e6 ) {
			return self::num( $n / 1e6, 1 ) . ' triệu';
		}
		return self::money( $n );
	}

	public static function payback_text( $p ) {
		if ( null === $p ) {
			return 'Chưa hoàn vốn trong thời gian tính';
		}
		$y = (int) floor( $p );
		$m = (int) round( ( $p - $y ) * 12 );
		if ( 12 === $m ) {
			$y++;
			$m = 0;
		}
		return trim( ( $y ? $y . ' năm ' : '' ) . ( $m ? $m . ' tháng' : '' ) ) ?: 'Dưới 1 tháng';
	}

	/** Key figures as label => value pairs (shared by page, email, admin, webhook). */
	public static function summary( $r ) {
		$rows = array(
			'Điện tiêu thụ ước tính'   => self::num( $r['kwh_month'] ) . ' kWh/tháng (' . self::num( $r['kwh_day'], 1 ) . ' kWh/ngày)',
			'Loại hệ thống'            => self::$types[ $r['input']['type'] ],
			'Công suất đề xuất'        => self::num( $r['kwp'], 2 ) . ' kWp',
			'Số tấm pin'               => $r['panels'] . ' tấm × ' . self::num( $r['panel_w'] ) . ' Wp',
			'Diện tích mái sử dụng'    => self::num( $r['roof_used'], 1 ) . ' m²',
			'Inverter'                 => self::num( $r['inverter'], 1 ) . ' kW – ' . ( 3 === (int) $r['input']['phase'] ? '3 pha' : '1 pha' ),
		);
		if ( $r['battery'] > 0 ) {
			$rows['Pin lưu trữ'] = self::num( $r['battery'], 1 ) . ' kWh';
		}
		$rows += array(
			'Sản lượng điện'           => self::num( $r['production'] ) . ' kWh/tháng',
			'Tỷ lệ tự cung cấp'        => self::num( $r['coverage'] ) . '%',
			'Hoá đơn sau lắp đặt'      => self::money( $r['bill_after'] ) . '/tháng',
			'Tiết kiệm'                => self::money( $r['saving_month'] ) . '/tháng',
			'Chi phí đầu tư dự kiến'   => self::money( $r['cost'] ),
			'Thời gian hoàn vốn'       => self::payback_text( $r['payback'] ),
			'Lợi nhuận ' . ( count( $r['cash_flow'] ) - 1 ) . ' năm' => self::money( $r['profit'] ),
			'Giảm phát thải'           => self::num( $r['co2_year'], 2 ) . ' tấn CO₂/năm',
		);
		return $rows;
	}

	public static function result( $r, $s ) {
		ob_start();
		$years = count( $r['cash_flow'] ) - 1;
		?>
		<div class="mgsc-result-inner">
			<?php foreach ( $r['warnings'] as $w ) : ?>
				<div class="mgsc-warning"><?php echo esc_html( $w ); ?></div>
			<?php endforeach; ?>

			<div class="mgsc-kpis">
				<div class="mgsc-kpi"><span class="mgsc-kpi-label">Công suất đề xuất</span><strong><?php echo esc_html( self::num( $r['kwp'], 2 ) ); ?> kWp</strong><small><?php echo esc_html( $r['panels'] . ' tấm pin ' . self::num( $r['panel_w'] ) . 'Wp' ); ?></small></div>
				<div class="mgsc-kpi"><span class="mgsc-kpi-label">Tiết kiệm mỗi tháng</span><strong><?php echo esc_html( self::money_short( $r['saving_month'] ) ); ?></strong><small><?php echo esc_html( self::money_short( $r['saving_year'] ) ); ?>/năm</small></div>
				<div class="mgsc-kpi"><span class="mgsc-kpi-label">Chi phí đầu tư</span><strong><?php echo esc_html( self::money_short( $r['cost'] ) ); ?></strong><small>dự kiến, trọn gói</small></div>
				<div class="mgsc-kpi"><span class="mgsc-kpi-label">Hoàn vốn</span><strong><?php echo esc_html( self::payback_text( $r['payback'] ) ); ?></strong><small>ROI <?php echo esc_html( self::num( $r['roi'] ) ); ?>% / <?php echo (int) $years; ?> năm</small></div>
			</div>

			<div class="mgsc-grid">
				<div class="mgsc-card">
					<h4>Hoá đơn tiền điện hàng tháng</h4>
					<?php echo self::bill_chart( $r ); // phpcs:ignore -- built from numbers only. ?>
				</div>
				<div class="mgsc-card">
					<h4>Dòng tiền tích luỹ <?php echo (int) $years; ?> năm</h4>
					<?php echo self::flow_chart( $r['cash_flow'] ); // phpcs:ignore -- built from numbers only. ?>
				</div>
			</div>

			<div class="mgsc-card">
				<h4>Chi tiết hệ thống</h4>
				<table class="mgsc-table">
					<?php foreach ( self::summary( $r ) as $k => $v ) : ?>
						<tr><th><?php echo esc_html( $k ); ?></th><td><?php echo esc_html( $v ); ?></td></tr>
					<?php endforeach; ?>
				</table>
			</div>

			<div class="mgsc-eco">
				<span>🌱 Mỗi năm giảm <strong><?php echo esc_html( self::num( $r['co2_year'], 2 ) ); ?> tấn CO₂</strong>, tương đương trồng <strong><?php echo esc_html( self::num( $r['trees'] ) ); ?> cây xanh</strong>.</span>
			</div>

			<?php echo self::package( $r, $s ); // phpcs:ignore -- escaped inside. ?>

			<div class="mgsc-actions">
				<?php if ( $s['cta_url'] ) : ?>
					<a class="mgsc-btn" href="<?php echo esc_url( $s['cta_url'] ); ?>"><?php echo esc_html( $s['cta_text'] ); ?></a>
				<?php endif; ?>
				<?php if ( $s['hotline'] ) : ?>
					<a class="mgsc-btn mgsc-btn-outline" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $s['hotline'] ) ); ?>">📞 <?php echo esc_html( $s['hotline'] ); ?></a>
				<?php endif; ?>
				<?php if ( $s['zalo'] ) : ?>
					<a class="mgsc-btn mgsc-btn-outline" target="_blank" rel="noopener" href="<?php echo esc_url( 0 === strpos( $s['zalo'], 'http' ) ? $s['zalo'] : 'https://zalo.me/' . preg_replace( '/\D/', '', $s['zalo'] ) ); ?>">Chat Zalo</a>
				<?php endif; ?>
				<button type="button" class="mgsc-btn mgsc-btn-outline mgsc-print">In / Lưu PDF</button>
				<button type="button" class="mgsc-btn mgsc-btn-link mgsc-restart">Tính lại</button>
			</div>
			<?php if ( $s['disclaimer'] ) : ?>
				<p class="mgsc-disclaimer"><?php echo esc_html( $s['disclaimer'] ); ?></p>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	private static function bill_chart( $r ) {
		$before = (float) $r['input']['bill'];
		$after  = (float) $r['bill_after'];
		$max    = max( 1, $before );
		$h      = 150;
		$hb     = $before / $max * $h;
		$ha     = $after / $max * $h;
		$pct    = $before > 0 ? ( 1 - $after / $before ) * 100 : 0;
		return '<svg class="mgsc-chart" viewBox="0 0 300 210" role="img" aria-label="Hoá đơn trước và sau">'
			. '<line x1="20" y1="180" x2="280" y2="180" class="mgsc-axis"/>'
			. sprintf( '<rect x="55" y="%.1f" width="70" height="%.1f" rx="4" class="mgsc-bar-before"/>', 180 - $hb, $hb )
			. sprintf( '<rect x="175" y="%.1f" width="70" height="%.1f" rx="4" class="mgsc-bar-after"/>', 180 - $ha, max( 1, $ha ) )
			. sprintf( '<text x="90" y="%.1f" class="mgsc-val">%s</text>', 172 - $hb, esc_html( self::money_short( $before ) ) )
			. sprintf( '<text x="210" y="%.1f" class="mgsc-val">%s</text>', 172 - $ha, esc_html( self::money_short( $after ) ) )
			. '<text x="90" y="198" class="mgsc-lbl">Hiện tại</text><text x="210" y="198" class="mgsc-lbl">Sau lắp đặt</text>'
			. sprintf( '<text x="292" y="14" class="mgsc-val mgsc-good mgsc-end">Giảm %s%%</text>', esc_html( self::num( $pct ) ) )
			. '</svg>';
	}

	private static function flow_chart( $flow ) {
		$w   = 300;
		$h   = 210;
		$pad = 26;
		$min = min( $flow );
		$max = max( max( $flow ), 1 );
		$n   = count( $flow ) - 1;
		$x   = function ( $i ) use ( $n, $w, $pad ) {
			return $pad + ( $w - 2 * $pad ) * ( $n > 0 ? $i / $n : 0 );
		};
		$y   = function ( $v ) use ( $min, $max, $h ) {
			return 20 + ( $h - 50 ) * ( 1 - ( $v - $min ) / max( 1, $max - $min ) );
		};
		$pts = array();
		foreach ( $flow as $i => $v ) {
			$pts[] = sprintf( '%.1f,%.1f', $x( $i ), $y( $v ) );
		}
		$zero = $y( 0 );
		$svg  = '<svg class="mgsc-chart" viewBox="0 0 ' . $w . ' ' . $h . '" role="img" aria-label="Dòng tiền tích luỹ">';
		$svg .= sprintf( '<line x1="%d" y1="%.1f" x2="%d" y2="%.1f" class="mgsc-axis"/>', $pad, $zero, $w - $pad, $zero );
		$svg .= sprintf( '<polygon points="%.1f,%.1f %s %.1f,%.1f" class="mgsc-area"/>', $x( 0 ), $zero, implode( ' ', $pts ), $x( $n ), $zero );
		$svg .= '<polyline points="' . implode( ' ', $pts ) . '" class="mgsc-line"/>';
		$svg .= sprintf( '<text x="%.1f" y="%.1f" class="mgsc-val mgsc-end">%s</text>', $x( $n ), $y( end( $flow ) ) - 8, esc_html( self::money_short( end( $flow ) ) ) );
		$svg .= sprintf( '<text x="%.1f" y="%.1f" class="mgsc-val mgsc-bad mgsc-start">%s</text>', $x( 0 ) + 4, min( $h - 34, $y( $flow[0] ) + 16 ), esc_html( self::money_short( $flow[0] ) ) );
		foreach ( array( 0 => 'mgsc-start', (int) round( $n / 2 ) => '', $n => 'mgsc-end' ) as $i => $cls ) {
			$svg .= sprintf( '<text x="%.1f" y="%d" class="mgsc-lbl %s">Năm %d</text>', $x( $i ) + ( 'mgsc-start' === $cls ? -12 : ( 'mgsc-end' === $cls ? 12 : 0 ) ), $h - 6, $cls, $i );
		}
		return $svg . '</svg>';
	}

	private static function package( $r, $s ) {
		foreach ( (array) $s['packages'] as $p ) {
			$max = (float) $p['max'] > 0 ? (float) $p['max'] : INF;
			if ( $r['kwp'] < (float) $p['min'] || $r['kwp'] > $max ) {
				continue;
			}
			$title = $p['title'];
			$url   = $p['url'];
			$img   = '';
			$price = '';
			$add   = '';
			if ( $p['product_id'] && function_exists( 'wc_get_product' ) ) {
				$product = wc_get_product( $p['product_id'] );
				if ( $product ) {
					$title = $title ? $title : $product->get_name();
					$url   = $url ? $url : $product->get_permalink();
					$img   = $product->get_image( 'woocommerce_thumbnail' );
					$price = $product->get_price_html();
					if ( $product->is_purchasable() && $product->is_in_stock() && $product->is_type( 'simple' ) ) {
						$add = add_query_arg( 'add-to-cart', $product->get_id(), wc_get_cart_url() );
					}
				}
			}
			ob_start();
			?>
			<div class="mgsc-card mgsc-package">
				<?php echo wp_kses_post( $img ); ?>
				<div class="mgsc-package-body">
					<span class="mgsc-kpi-label">Gói phù hợp với bạn</span>
					<h4><?php echo esc_html( $title ); ?></h4>
					<?php if ( $price ) : ?><div class="mgsc-package-price"><?php echo wp_kses_post( $price ); ?></div><?php endif; ?>
					<div class="mgsc-package-actions">
						<?php if ( $url ) : ?><a class="mgsc-btn mgsc-btn-outline" href="<?php echo esc_url( $url ); ?>">Xem chi tiết</a><?php endif; ?>
						<?php if ( $add ) : ?><a class="mgsc-btn" href="<?php echo esc_url( $add ); ?>">Đặt mua gói này</a><?php endif; ?>
					</div>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}
		return '';
	}

	/** Table HTML for emails (no SVG, inline styles). */
	public static function email( $r, $lead, $s ) {
		$rows = '';
		foreach ( self::summary( $r ) as $k => $v ) {
			$rows .= '<tr><td style="padding:8px 10px;border-bottom:1px solid #eee;color:#555">' . esc_html( $k ) . '</td><td style="padding:8px 10px;border-bottom:1px solid #eee;font-weight:600">' . esc_html( $v ) . '</td></tr>';
		}
		$warn = '';
		foreach ( $r['warnings'] as $w ) {
			$warn .= '<p style="background:#fff7e0;border-left:4px solid #f0b400;padding:8px 10px;margin:8px 0">' . esc_html( $w ) . '</p>';
		}
		$in = '<p style="color:#555">Hoá đơn: <b>' . esc_html( self::money( $r['input']['bill'] ) ) . '</b> · Mái: <b>' . esc_html( self::num( $r['input']['area'], 1 ) ) . ' m²</b> · ' . esc_html( self::$regions[ $r['input']['region'] ] ) . ' · Điện ' . (int) $r['input']['phase'] . ' pha · Dùng ban ngày ' . esc_html( self::num( $r['input']['day'] ) ) . '% · ' . esc_html( self::$customers[ $r['input']['customer'] ] ) . '</p>';
		return '<div style="font-family:Arial,sans-serif;max-width:640px;margin:auto;color:#222">'
			. '<h2 style="color:' . esc_attr( $s['accent'] ) . '">Kết quả tính hệ thống điện mặt trời</h2>'
			. ( $lead ? '<p>Xin chào <b>' . esc_html( $lead['name'] ) . '</b>,</p>' : '' )
			. $in . $warn
			. '<table style="border-collapse:collapse;width:100%">' . $rows . '</table>'
			. ( $s['disclaimer'] ? '<p style="color:#888;font-size:12px;margin-top:16px">' . esc_html( $s['disclaimer'] ) . '</p>' : '' )
			. ( $s['hotline'] ? '<p>Hotline tư vấn: <b>' . esc_html( $s['hotline'] ) . '</b></p>' : '' )
			. '</div>';
	}
}
