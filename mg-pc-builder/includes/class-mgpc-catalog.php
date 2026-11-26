<?php
defined( 'ABSPATH' ) || exit;

/**
 * Product listing, compatibility check and add-to-cart endpoints (WooCommerce wc-ajax, front-end context).
 */
class MGPC_Catalog {

	public static function init() {
		foreach ( array( 'products', 'check', 'cart' ) as $a ) {
			add_action( 'wc_ajax_mgpc_' . $a, array( __CLASS__, 'ajax_' . $a ) );
		}
		add_action( 'save_post_product', array( __CLASS__, 'flush_cache' ) );
		add_action( 'woocommerce_product_set_stock_status', array( __CLASS__, 'flush_cache' ) );
	}

	public static function flush_cache() {
		update_option( 'mgpc_cache_ver', time(), false );
	}

	private static function cache_key( $name ) {
		return 'mgpc_' . md5( $name . '|' . get_option( 'mgpc_cache_ver', 1 ) );
	}

	/** All visible product IDs of a slot (cached). */
	public static function slot_ids( $slot ) {
		$key = self::cache_key( 'ids|' . $slot['key'] . '|' . implode( ',', $slot['cats'] ) );
		$ids = get_transient( $key );
		if ( false === $ids ) {
			$ids = $slot['cats'] ? wc_get_products( array(
				'status'     => 'publish',
				'limit'      => 600,
				'return'     => 'ids',
				'category'   => $slot['cats'],
				'type'       => array( 'simple' ),
				'visibility' => 'catalog',
			) ) : array();
			set_transient( $key, $ids, 6 * HOUR_IN_SECONDS );
		}
		return array_map( 'intval', (array) $ids );
	}

	/** Facets: brand + the most used product attributes of a slot (cached). */
	public static function facets( $slot ) {
		$key = self::cache_key( 'facets|' . $slot['key'] );
		$out = get_transient( $key );
		if ( false !== $out ) {
			return $out;
		}
		$ids = self::slot_ids( $slot );
		$tax = array();
		if ( taxonomy_exists( 'product_brand' ) ) {
			$tax['product_brand'] = 'Thương hiệu';
		}
		foreach ( wc_get_attribute_taxonomies() as $a ) {
			$tax[ wc_attribute_taxonomy_name( $a->attribute_name ) ] = $a->attribute_label;
		}
		$facets = array();
		if ( $ids ) {
			update_object_term_cache( $ids, 'product' );
			foreach ( $tax as $t => $label ) {
				$terms = array();
				$cover = 0;
				foreach ( $ids as $id ) {
					$pt = get_the_terms( $id, $t );
					if ( $pt && ! is_wp_error( $pt ) ) {
						$cover++;
						foreach ( $pt as $term ) {
							$terms[ $term->slug ] = array( 'slug' => $term->slug, 'name' => $term->name, 'count' => ( $terms[ $term->slug ]['count'] ?? 0 ) + 1 );
						}
					}
				}
				if ( count( $terms ) >= 2 && count( $terms ) <= 40 ) {
					uasort( $terms, function ( $a, $b ) {
						return $b['count'] <=> $a['count'] ?: strnatcasecmp( $a['name'], $b['name'] );
					} );
					$facets[] = array( 'tax' => $t, 'label' => $label, 'cover' => $cover, 'terms' => array_values( $terms ) );
				}
			}
			usort( $facets, function ( $a, $b ) {
				return ( 'product_brand' === $b['tax'] ) <=> ( 'product_brand' === $a['tax'] ) ?: $b['cover'] <=> $a['cover'];
			} );
		}
		$out = array_slice( $facets, 0, 5 );
		set_transient( $key, $out, 6 * HOUR_IN_SECONDS );
		return $out;
	}

	/** Card data for the front-end. */
	public static function item( $p ) {
		$price   = (float) wc_get_price_to_display( $p );
		$regular = (float) wc_get_price_to_display( $p, array( 'price' => $p->get_regular_price() ) );
		$img     = wp_get_attachment_image_url( $p->get_image_id(), 'woocommerce_thumbnail' );
		return array(
			'id'      => $p->get_id(),
			'name'    => $p->get_name(),
			'url'     => $p->get_permalink(),
			'img'     => $img ? $img : wc_placeholder_img_src( 'woocommerce_thumbnail' ),
			'price'   => $price,
			'regular' => $regular > $price ? $regular : 0,
			'sale'    => $regular > $price && $regular > 0 ? (int) round( ( 1 - $price / $regular ) * 100 ) : 0,
			'stock'   => $p->is_in_stock() && $p->is_purchasable(),
			'max'     => $p->get_max_purchase_quantity(),
			'chips'   => MGPC_Specs::highlights( $p ),
			'sku'     => $p->get_sku(),
		);
	}

	/**
	 * Parses a posted build: [{slot, id, qty}] → validated parts (product must belong to the slot).
	 *
	 * @return array[] slot, role, qty, name, specs, product
	 */
	public static function parts( $json ) {
		$rows  = is_array( $json ) ? $json : json_decode( (string) $json, true );
		$slots = MGPC_Settings::slots();
		$parts = array();
		foreach ( (array) $rows as $r ) {
			$key = sanitize_key( $r['slot'] ?? '' );
			$id  = absint( $r['id'] ?? 0 );
			if ( ! isset( $slots[ $key ] ) || ! $id || ! in_array( $id, self::slot_ids( $slots[ $key ] ), true ) ) {
				continue;
			}
			$p = wc_get_product( $id );
			if ( ! $p ) {
				continue;
			}
			$qty     = $slots[ $key ]['multi'] ? max( 1, min( 16, absint( $r['qty'] ?? 1 ) ) ) : 1;
			$parts[] = array( 'slot' => $key, 'role' => $slots[ $key ]['role'], 'qty' => $qty, 'name' => $p->get_name(), 'specs' => MGPC_Specs::get( $p ), 'product' => $p );
			if ( count( $parts ) >= 40 ) {
				break;
			}
		}
		return $parts;
	}

	private static function fold( $s ) {
		return mb_strtolower( remove_accents( (string) $s ) );
	}

	public static function ajax_products() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- public read-only catalogue.
		$p     = wp_unslash( $_POST );
		$slots = MGPC_Settings::slots();
		$key   = sanitize_key( $p['slot'] ?? '' );
		if ( ! isset( $slots[ $key ] ) ) {
			wp_send_json_error( array( 'message' => 'Nhóm linh kiện không hợp lệ.' ), 400 );
		}
		$slot    = $slots[ $key ];
		$s       = MGPC_Settings::get();
		$q       = self::fold( sanitize_text_field( $p['q'] ?? '' ) );
		$sort    = sanitize_key( $p['sort'] ?? '' );
		$page    = max( 1, absint( $p['page'] ?? 1 ) );
		$min     = (float) ( $p['min'] ?? 0 );
		$max     = (float) ( $p['max'] ?? 0 );
		$compat  = ! empty( $p['compat'] );
		$filters = array();
		foreach ( (array) json_decode( (string) ( $p['filters'] ?? '{}' ), true ) as $tax => $terms ) {
			$tax = sanitize_key( $tax );
			if ( taxonomy_exists( $tax ) && $terms ) {
				$filters[ $tax ] = array_map( 'sanitize_title', (array) $terms );
			}
		}
		$parts = self::parts( $p['build'] ?? '[]' );
		// phpcs:enable

		$ids = self::slot_ids( $slot );
		if ( $filters ) {
			update_object_term_cache( $ids, 'product' );
		}
		$rows = array();
		foreach ( $ids as $id ) {
			$prod = wc_get_product( $id );
			if ( ! $prod ) {
				continue;
			}
			if ( '' !== $q && false === strpos( self::fold( $prod->get_name() . ' ' . $prod->get_sku() ), $q ) ) {
				continue;
			}
			$price = (float) wc_get_price_to_display( $prod );
			if ( ( $min > 0 && $price < $min ) || ( $max > 0 && $price > $max ) ) {
				continue;
			}
			foreach ( $filters as $tax => $terms ) {
				$pt = wp_list_pluck( (array) get_the_terms( $id, $tax ), 'slug' );
				if ( ! array_intersect( $terms, $pt ) ) {
					continue 2;
				}
			}
			$conf = MGPC_Compat::conflicts( $parts, array( 'slot' => $key, 'role' => $slot['role'], 'qty' => 1, 'name' => $prod->get_name(), 'specs' => MGPC_Specs::get( $prod ) ), $s['psu_headroom'], $s['base_watt'] );
			if ( $compat && $conf ) {
				continue;
			}
			$rows[] = array( 'p' => $prod, 'price' => $price, 'conf' => $conf, 'stock' => $prod->is_in_stock() );
		}
		usort( $rows, function ( $a, $b ) use ( $sort ) {
			if ( $a['stock'] !== $b['stock'] ) {
				return $b['stock'] <=> $a['stock'];
			}
			if ( 'price_asc' === $sort ) {
				return $a['price'] <=> $b['price'];
			}
			if ( 'price_desc' === $sort ) {
				return $b['price'] <=> $a['price'];
			}
			return $b['p']->get_total_sales() <=> $a['p']->get_total_sales() ?: $a['price'] <=> $b['price'];
		} );
		$per   = (int) $s['per_page'];
		$total = count( $rows );
		$items = array();
		foreach ( array_slice( $rows, ( $page - 1 ) * $per, $per ) as $r ) {
			$items[] = self::item( $r['p'] ) + array( 'conflicts' => $r['conf'] );
		}
		wp_send_json_success( array(
			'items'  => $items,
			'total'  => $total,
			'page'   => $page,
			'more'   => $page * $per < $total,
			'facets' => 1 === $page ? self::facets( $slot ) : null,
		) );
	}

	/** Full check of a build + item data (used after every change, and to load shared links/presets). */
	public static function check_payload( $parts ) {
		$s      = MGPC_Settings::get();
		$res    = MGPC_Compat::check( $parts, $s['psu_headroom'], $s['base_watt'] );
		$chosen = array_unique( wp_list_pluck( $parts, 'slot' ) );
		$miss   = array();
		foreach ( MGPC_Settings::slots() as $k => $slot ) {
			if ( $slot['required'] && ! in_array( $k, $chosen, true ) ) {
				$miss[] = $slot['label'];
			}
		}
		$items = array();
		foreach ( $parts as $pt ) {
			$items[] = array( 'slot' => $pt['slot'], 'qty' => $pt['qty'], 'item' => self::item( $pt['product'] ) );
		}
		$groups = count( $chosen );
		return $res + array(
			'missing'  => $miss,
			'items'    => $items,
			'discount' => 'yes' === $s['discount_enabled'] ? array( 'percent' => (float) $s['discount_percent'], 'min' => (int) $s['discount_min'], 'active' => $groups >= (int) $s['discount_min'], 'label' => $s['discount_label'] ) : null,
		);
	}

	public static function ajax_check() {
		$parts = self::parts( wp_unslash( $_POST['build'] ?? '[]' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- read-only.
		wp_send_json_success( self::check_payload( $parts ) );
	}

	/** Adds the whole build to the cart. */
	public static function ajax_cart() {
		$parts = self::parts( wp_unslash( $_POST['build'] ?? '[]' ) ); // phpcs:ignore WordPress.Security.NonceVerification -- same as WooCommerce add-to-cart links.
		if ( ! $parts ) {
			wp_send_json_error( array( 'message' => 'Cấu hình đang trống.' ) );
		}
		$code   = 'PC' . strtoupper( substr( md5( wp_json_encode( wp_list_pluck( $parts, 'qty', 'slot' ) ) . microtime() ), 0, 6 ) );
		$groups = count( array_unique( wp_list_pluck( $parts, 'slot' ) ) );
		$added  = 0;
		$failed = array();
		foreach ( $parts as $pt ) {
			$key = WC()->cart->add_to_cart( $pt['product']->get_id(), $pt['qty'], 0, array(), array( 'mgpc' => array( 'code' => $code, 'slot' => $pt['slot'], 'groups' => $groups ) ) );
			if ( $key ) {
				$added++;
			} else {
				$failed[] = $pt['product']->get_name();
			}
		}
		wc_clear_notices();
		wp_send_json_success( array(
			'added'    => $added,
			'failed'   => $failed,
			'code'     => $code,
			'cart'     => wc_get_cart_url(),
			'checkout' => wc_get_checkout_url(),
			'count'    => WC()->cart->get_cart_contents_count(),
		) );
	}
}
