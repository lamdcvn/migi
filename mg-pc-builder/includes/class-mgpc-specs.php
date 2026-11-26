<?php
defined( 'ABSPATH' ) || exit;

/**
 * Compatibility specs per product: entered in the "Build PC" product tab, or read from WooCommerce attributes.
 */
class MGPC_Specs {

	const META = '_mgpc_specs';

	public static function fields() {
		return array(
			'socket'     => array( 'Socket', 'CPU / mainboard: LGA1700, AM5… Tản nhiệt: liệt kê các socket hỗ trợ, cách nhau dấu phẩy.' ),
			'ram_type'   => array( 'Loại RAM', 'DDR4 / DDR5. CPU, mainboard có thể ghi nhiều loại: DDR4,DDR5.' ),
			'ram_slots'  => array( 'Số khe RAM', 'Mainboard: 2 hoặc 4.' ),
			'ram_sticks' => array( 'Số thanh RAM mỗi bộ', 'RAM kit 2×16GB = 2. Mặc định 1.' ),
			'form'       => array( 'Kích thước mainboard', 'Mainboard: ATX / MATX / ITX / EATX. Case: liệt kê các chuẩn hỗ trợ.' ),
			'watt'       => array( 'Điện năng tiêu thụ (W)', 'CPU: TDP; VGA: TBP. Dùng để ước tính công suất nguồn.' ),
			'psu_watt'   => array( 'Công suất nguồn (W)', 'Chỉ cho nguồn máy tính.' ),
			'gpu_length' => array( 'Chiều dài VGA (mm)', 'Card màn hình.' ),
			'max_gpu'    => array( 'VGA dài tối đa (mm)', 'Case: độ dài card màn hình tối đa.' ),
			'igpu'       => array( 'Có GPU tích hợp', 'CPU: yes nếu có đồ hoạ tích hợp (không có "F").' ),
			'highlights' => array( 'Thông số nổi bật', 'Hiện thành nhãn trên thẻ sản phẩm, cách nhau dấu phẩy. VD: 10 nhân, 16 luồng, 4.7GHz.' ),
		);
	}

	public static function init() {
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'panel' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save' ) );
	}

	public static function tab( $tabs ) {
		$tabs['mgpc'] = array( 'label' => 'Build PC', 'target' => 'mgpc_product_data', 'class' => array(), 'priority' => 67 );
		return $tabs;
	}

	public static function panel() {
		global $post;
		$v = (array) get_post_meta( $post->ID, self::META, true );
		echo '<div id="mgpc_product_data" class="panel woocommerce_options_panel mg-admin-panel">';
		wp_nonce_field( 'mgpc_specs', 'mgpc_specs_nonce' );
		echo '<p class="mg-panel-intro"><strong>Migi · Build PC</strong> – thông số dùng để kiểm tra tương thích. Để trống ô nào thì hệ thống thử đọc từ thuộc tính sản phẩm (cấu hình ở Migi → Build PC → Tương thích).</p><div class="options_group">';
		foreach ( self::fields() as $k => $f ) {
			printf(
				'<p class="form-field"><label for="mgpc_spec_%1$s">%2$s</label><input type="text" class="short" id="mgpc_spec_%1$s" name="mgpc_specs[%1$s]" value="%3$s"><span class="description" style="display:block;clear:both;margin-left:150px">%4$s</span></p>',
				esc_attr( $k ),
				esc_html( $f[0] ),
				esc_attr( $v[ $k ] ?? '' ),
				esc_html( $f[1] )
			);
		}
		echo '</div></div>';
	}

	public static function save( $id ) {
		if ( ! isset( $_POST['mgpc_specs_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mgpc_specs_nonce'] ), 'mgpc_specs' ) ) {
			return;
		}
		$in  = (array) wp_unslash( $_POST['mgpc_specs'] ?? array() ); // phpcs:ignore -- sanitized below.
		$out = array();
		foreach ( array_keys( self::fields() ) as $k ) {
			$val = sanitize_text_field( $in[ $k ] ?? '' );
			if ( '' !== $val ) {
				$out[ $k ] = $val;
			}
		}
		update_post_meta( $id, self::META, $out );
		MGPC_Catalog::flush_cache();
	}

	/* ---------- Normalisation (pure) ---------- */

	public static function norm_token( $v ) {
		$v = strtoupper( preg_replace( '/[\s_\-]+/', '', remove_accents( (string) $v ) ) );
		$map = array( 'MICROATX' => 'MATX', 'MICRO' => 'MATX', 'MATX' => 'MATX', 'UATX' => 'MATX', 'MINIITX' => 'ITX', 'MITX' => 'ITX', 'EXTENDEDATX' => 'EATX', 'SOCKETAM4' => 'AM4', 'SOCKETAM5' => 'AM5' );
		$v   = preg_replace( '/^SOCKET/', '', $v );
		return $map[ $v ] ?? $v;
	}

	/** "LGA 1700, AM5" → ['LGA1700','AM5']. */
	public static function norm_list( $v ) {
		$parts = is_array( $v ) ? $v : preg_split( '/[,;\/|]+/', (string) $v );
		return array_values( array_unique( array_filter( array_map( array( __CLASS__, 'norm_token' ), $parts ) ) ) );
	}

	public static function norm_num( $v ) {
		return preg_match( '/(\d+(?:[.,]\d+)?)/', (string) $v, $m ) ? (float) str_replace( ',', '.', $m[1] ) : 0;
	}

	/** Normalized specs array used by MGPC_Compat. */
	public static function normalize( array $raw ) {
		return array(
			'socket'     => self::norm_list( $raw['socket'] ?? '' ),
			'ram_type'   => self::norm_list( $raw['ram_type'] ?? '' ),
			'ram_slots'  => (int) self::norm_num( $raw['ram_slots'] ?? 0 ),
			'ram_sticks' => max( 1, (int) self::norm_num( $raw['ram_sticks'] ?? 1 ) ),
			'form'       => self::norm_list( $raw['form'] ?? '' ),
			'watt'       => self::norm_num( $raw['watt'] ?? 0 ),
			'psu_watt'   => self::norm_num( $raw['psu_watt'] ?? 0 ),
			'gpu_length' => self::norm_num( $raw['gpu_length'] ?? 0 ),
			'max_gpu'    => self::norm_num( $raw['max_gpu'] ?? 0 ),
			'igpu'       => in_array( strtolower( (string) ( $raw['igpu'] ?? '' ) ), array( 'yes', 'co', 'có', '1', 'true' ), true ),
		);
	}

	/* ---------- Reading ---------- */

	private static function attr_values( $product, $slugs ) {
		foreach ( array_filter( array_map( 'trim', explode( ',', (string) $slugs ) ) ) as $slug ) {
			$val = $product->get_attribute( $slug );
			if ( '' !== (string) $val ) {
				return str_replace( ' | ', ',', $val );
			}
		}
		return '';
	}

	/** Raw specs (meta, falling back to attributes). */
	public static function raw( $product ) {
		$id   = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
		$meta = (array) get_post_meta( $id, self::META, true );
		$s    = MGPC_Settings::get();
		$map  = array( 'socket' => 'attr_socket', 'ram_type' => 'attr_ram_type', 'form' => 'attr_form', 'watt' => 'attr_watt', 'psu_watt' => 'attr_psu_watt' );
		foreach ( $map as $k => $opt ) {
			if ( empty( $meta[ $k ] ) ) {
				$meta[ $k ] = self::attr_values( $product, $s[ $opt ] );
			}
		}
		return $meta;
	}

	public static function get( $product ) {
		static $cache = array();
		$id = $product->get_id();
		if ( ! isset( $cache[ $id ] ) ) {
			$cache[ $id ] = self::normalize( self::raw( $product ) );
		}
		return $cache[ $id ];
	}

	/** Up to 4 short spec chips for product cards. */
	public static function highlights( $product ) {
		$raw = self::raw( $product );
		if ( ! empty( $raw['highlights'] ) ) {
			return array_slice( array_filter( array_map( 'trim', explode( ',', $raw['highlights'] ) ) ), 0, 4 );
		}
		$out = array();
		foreach ( array( 'socket', 'ram_type', 'form' ) as $k ) {
			if ( ! empty( $raw[ $k ] ) ) {
				$out[] = $raw[ $k ];
			}
		}
		if ( ! empty( $raw['psu_watt'] ) ) {
			$out[] = self::norm_num( $raw['psu_watt'] ) . 'W';
		} elseif ( ! empty( $raw['watt'] ) ) {
			$out[] = 'TDP ' . self::norm_num( $raw['watt'] ) . 'W';
		}
		if ( count( $out ) < 2 ) {
			foreach ( $product->get_attributes() as $attr ) {
				if ( count( $out ) >= 4 ) {
					break;
				}
				if ( $attr->get_visible() ) {
					$val = $product->get_attribute( $attr->get_name() );
					if ( $val && mb_strlen( $val ) <= 28 ) {
						$out[] = $val;
					}
				}
			}
		}
		return array_slice( array_values( array_unique( $out ) ), 0, 4 );
	}
}
