<?php
defined( 'ABSPATH' ) || exit;

/**
 * Compatibility engine (pure PHP – no WordPress calls; covered by tests/compat.php).
 *
 * A build is a list of parts: array( 'slot' => key, 'role' => cpu|mainboard|vga|ram|storage|case|psu|cooler|other,
 * 'qty' => int, 'name' => string, 'specs' => MGPC_Specs::normalize() output ).
 * Unknown specs (empty) are never treated as a conflict.
 */
class MGPC_Compat {

	/** @return array|null first part with this role */
	private static function one( $parts, $role ) {
		foreach ( $parts as $p ) {
			if ( $p['role'] === $role ) {
				return $p;
			}
		}
		return null;
	}

	private static function all( $parts, $role ) {
		return array_values( array_filter( $parts, function ( $p ) use ( $role ) {
			return $p['role'] === $role;
		} ) );
	}

	public static function power( $parts, $base_watt ) {
		$w = (float) $base_watt;
		foreach ( $parts as $p ) {
			if ( 'psu' !== $p['role'] ) {
				$w += (float) $p['specs']['watt'] * max( 1, (int) $p['qty'] );
			}
		}
		return $w;
	}

	public static function recommended_psu( $draw, $headroom ) {
		return (int) ( ceil( $draw * ( 1 + $headroom / 100 ) / 50 ) * 50 );
	}

	/**
	 * @return array checks: list of array( level => ok|warn|error, code, text ), draw, recommended, psu
	 */
	public static function check( array $parts, $headroom = 30, $base_watt = 60 ) {
		$c      = array();
		$cpu    = self::one( $parts, 'cpu' );
		$mb     = self::one( $parts, 'mainboard' );
		$case   = self::one( $parts, 'case' );
		$psu    = self::one( $parts, 'psu' );
		$cooler = self::one( $parts, 'cooler' );
		$vgas   = self::all( $parts, 'vga' );
		$rams   = self::all( $parts, 'ram' );
		$add    = function ( $level, $code, $text ) use ( &$c ) {
			$c[] = array( 'level' => $level, 'code' => $code, 'text' => $text );
		};

		// CPU ↔ mainboard socket.
		if ( $cpu && $mb && $cpu['specs']['socket'] && $mb['specs']['socket'] ) {
			$common = array_intersect( $cpu['specs']['socket'], $mb['specs']['socket'] );
			if ( $common ) {
				$add( 'ok', 'socket', 'CPU và mainboard cùng socket ' . reset( $common ) . '.' );
			} else {
				$add( 'error', 'socket', sprintf( 'Socket không khớp: CPU %s, mainboard %s.', implode( '/', $cpu['specs']['socket'] ), implode( '/', $mb['specs']['socket'] ) ) );
			}
		}

		// Cooler ↔ CPU socket.
		$cpu_socket = $cpu && $cpu['specs']['socket'] ? $cpu['specs']['socket'] : ( $mb ? $mb['specs']['socket'] : array() );
		if ( $cooler && $cooler['specs']['socket'] && $cpu_socket ) {
			if ( array_intersect( $cooler['specs']['socket'], $cpu_socket ) ) {
				$add( 'ok', 'cooler', 'Tản nhiệt hỗ trợ socket ' . reset( $cpu_socket ) . '.' );
			} else {
				$add( 'error', 'cooler', sprintf( 'Tản nhiệt không hỗ trợ socket %s.', implode( '/', $cpu_socket ) ) );
			}
		}

		// RAM type & slots.
		if ( $rams ) {
			$bad = array();
			foreach ( $rams as $r ) {
				if ( $mb && $r['specs']['ram_type'] && $mb['specs']['ram_type'] && ! array_intersect( $r['specs']['ram_type'], $mb['specs']['ram_type'] ) ) {
					$bad[] = $r;
				}
			}
			if ( $bad ) {
				$add( 'error', 'ram_type', sprintf( 'RAM %s không đúng chuẩn mainboard (%s).', implode( '/', $bad[0]['specs']['ram_type'] ), implode( '/', $mb['specs']['ram_type'] ) ) );
			} elseif ( $mb && $mb['specs']['ram_type'] && $rams[0]['specs']['ram_type'] ) {
				$add( 'ok', 'ram_type', 'RAM ' . reset( $rams[0]['specs']['ram_type'] ) . ' tương thích mainboard.' );
			}
			if ( $cpu && $cpu['specs']['ram_type'] && $rams[0]['specs']['ram_type'] && ! array_intersect( $rams[0]['specs']['ram_type'], $cpu['specs']['ram_type'] ) ) {
				$add( 'warn', 'ram_cpu', sprintf( 'CPU chỉ hỗ trợ %s.', implode( '/', $cpu['specs']['ram_type'] ) ) );
			}
			$sticks = 0;
			foreach ( $rams as $r ) {
				$sticks += max( 1, (int) $r['qty'] ) * max( 1, (int) $r['specs']['ram_sticks'] );
			}
			if ( $mb && $mb['specs']['ram_slots'] > 0 ) {
				if ( $sticks > $mb['specs']['ram_slots'] ) {
					$add( 'error', 'ram_slots', sprintf( 'Cần %d khe RAM nhưng mainboard chỉ có %d khe.', $sticks, $mb['specs']['ram_slots'] ) );
				} else {
					$add( 'ok', 'ram_slots', sprintf( 'Dùng %d/%d khe RAM.', $sticks, $mb['specs']['ram_slots'] ) );
				}
			}
		}

		// Mainboard ↔ case form factor.
		if ( $mb && $case && $mb['specs']['form'] && $case['specs']['form'] ) {
			if ( array_intersect( array( $mb['specs']['form'][0] ), $case['specs']['form'] ) ) {
				$add( 'ok', 'form', 'Case hỗ trợ mainboard ' . $mb['specs']['form'][0] . '.' );
			} else {
				$add( 'error', 'form', sprintf( 'Case không hỗ trợ mainboard %s (chỉ %s).', $mb['specs']['form'][0], implode( '/', $case['specs']['form'] ) ) );
			}
		}

		// GPU length ↔ case.
		foreach ( $vgas as $v ) {
			if ( $case && $v['specs']['gpu_length'] > 0 && $case['specs']['max_gpu'] > 0 ) {
				if ( $v['specs']['gpu_length'] > $case['specs']['max_gpu'] ) {
					$add( 'error', 'gpu_length', sprintf( 'Card màn hình dài %dmm, case chỉ chứa tối đa %dmm.', $v['specs']['gpu_length'], $case['specs']['max_gpu'] ) );
				} else {
					$add( 'ok', 'gpu_length', sprintf( 'Card màn hình %dmm vừa case (tối đa %dmm).', $v['specs']['gpu_length'], $case['specs']['max_gpu'] ) );
				}
			}
		}

		// Integrated graphics.
		if ( $cpu && ! $vgas && ! $cpu['specs']['igpu'] ) {
			$add( 'warn', 'igpu', 'CPU không có đồ hoạ tích hợp (hoặc chưa rõ): cần thêm card màn hình để xuất hình.' );
		}

		// Power.
		$draw = self::power( $parts, $base_watt );
		$rec  = self::recommended_psu( $draw, $headroom );
		$pw   = $psu ? (float) $psu['specs']['psu_watt'] : 0;
		if ( $psu && $pw > 0 && ( $cpu || $vgas ) ) {
			if ( $pw < $draw ) {
				$add( 'error', 'psu', sprintf( 'Nguồn %dW không đủ cho mức tiêu thụ ước tính %dW.', $pw, $draw ) );
			} elseif ( $pw < $rec ) {
				$add( 'warn', 'psu', sprintf( 'Nguồn %dW hơi sát, nên dùng từ %dW.', $pw, $rec ) );
			} else {
				$add( 'ok', 'psu', sprintf( 'Nguồn %dW đủ cho mức tiêu thụ ~%dW.', $pw, $draw ) );
			}
		}

		return array( 'checks' => $c, 'draw' => $draw, 'recommended' => $rec, 'psu' => $pw );
	}

	/**
	 * Hard conflicts a candidate would create if put in $slot (replacing what is there).
	 *
	 * @return string[] error texts (empty = compatible)
	 */
	public static function conflicts( array $parts, array $candidate, $headroom = 30, $base_watt = 60 ) {
		$others = array_values( array_filter( $parts, function ( $p ) use ( $candidate ) {
			return $p['slot'] !== $candidate['slot'];
		} ) );
		$before = array();
		foreach ( self::check( $others, $headroom, $base_watt )['checks'] as $ch ) {
			if ( 'error' === $ch['level'] ) {
				$before[ $ch['code'] . $ch['text'] ] = true;
			}
		}
		$out = array();
		foreach ( self::check( array_merge( $others, array( $candidate ) ), $headroom, $base_watt )['checks'] as $ch ) {
			// Only conflicts introduced by the candidate (existing ones are not its fault).
			if ( 'error' === $ch['level'] && ! isset( $before[ $ch['code'] . $ch['text'] ] ) ) {
				$out[] = $ch['text'];
			}
		}
		return $out;
	}
}
