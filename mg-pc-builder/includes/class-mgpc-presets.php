<?php
defined( 'ABSPATH' ) || exit;

/** Suggested builds ("Cấu hình gợi ý") managed as a private post type. */
class MGPC_Presets {

	const PT = 'mgpc_preset';

	public static function themes() {
		return array( 'night' => 'Xanh đêm', 'violet' => 'Tím', 'teal' => 'Xanh ngọc', 'sunset' => 'Cam hoàng hôn' );
	}

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'boxes' ) );
		add_action( 'save_post_' . self::PT, array( __CLASS__, 'save' ) );
	}

	public static function register() {
		register_post_type( self::PT, array(
			'labels'       => array( 'name' => 'Cấu hình gợi ý', 'singular_name' => 'Cấu hình gợi ý', 'all_items' => 'Build PC – Cấu hình gợi ý', 'add_new_item' => 'Thêm cấu hình gợi ý', 'edit_item' => 'Sửa cấu hình gợi ý', 'add_new' => 'Thêm mới' ),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => 'migi',
			'supports'     => array( 'title', 'excerpt', 'thumbnail', 'page-attributes' ),
		) );
	}

	public static function boxes() {
		add_meta_box( 'mgpc_preset_items', 'Linh kiện trong cấu hình', array( __CLASS__, 'box' ), self::PT, 'normal', 'high' );
		add_meta_box( 'mgpc_preset_style', 'Hiển thị', array( __CLASS__, 'box_style' ), self::PT, 'side' );
	}

	public static function box( $post ) {
		wp_nonce_field( 'mgpc_preset', 'mgpc_preset_nonce' );
		$items = array();
		foreach ( (array) get_post_meta( $post->ID, '_mgpc_items', true ) as $it ) {
			$items[ $it['slot'] ] = $it;
		}
		echo '<p class="description">Chọn sản phẩm cho từng nhóm (gõ để tìm). Mô tả ngắn lấy từ ô "Tóm tắt", ảnh từ "Ảnh đại diện".</p><table class="widefat striped"><tbody>';
		foreach ( MGPC_Settings::slots() as $k => $slot ) {
			$it = $items[ $k ] ?? array( 'id' => 0, 'qty' => 1 );
			$p  = $it['id'] ? wc_get_product( $it['id'] ) : null;
			echo '<tr><th style="width:160px">' . esc_html( $slot['label'] ) . '</th><td><select class="wc-product-search" style="width:100%" name="mgpc_items[' . esc_attr( $k ) . '][id]" data-placeholder="Không chọn" data-allow_clear="true" data-action="woocommerce_json_search_products">';
			if ( $p ) {
				echo '<option value="' . esc_attr( $p->get_id() ) . '" selected>' . esc_html( wp_strip_all_tags( $p->get_formatted_name() ) ) . '</option>';
			}
			echo '</select></td><td style="width:90px">' . ( $slot['multi'] ? '<input type="number" min="1" max="16" name="mgpc_items[' . esc_attr( $k ) . '][qty]" value="' . esc_attr( max( 1, (int) $it['qty'] ) ) . '" style="width:70px"> ' : '' ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}

	public static function box_style( $post ) {
		$tag   = get_post_meta( $post->ID, '_mgpc_tag', true );
		$theme = get_post_meta( $post->ID, '_mgpc_theme', true );
		echo '<p><label>Nhãn (VD: Gaming, Đồ hoạ)<br><input type="text" name="mgpc_tag" value="' . esc_attr( $tag ) . '" style="width:100%"></label></p><p><label>Màu thẻ<br><select name="mgpc_theme" style="width:100%">';
		foreach ( self::themes() as $k => $v ) {
			echo '<option value="' . esc_attr( $k ) . '" ' . selected( $theme, $k, false ) . '>' . esc_html( $v ) . '</option>';
		}
		echo '</select></label></p><p class="description">Thứ tự hiển thị: ô "Thứ tự" trong khung Thuộc tính.</p>';
	}

	public static function save( $id ) {
		if ( ! isset( $_POST['mgpc_preset_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mgpc_preset_nonce'] ), 'mgpc_preset' ) || ! current_user_can( 'edit_post', $id ) ) {
			return;
		}
		$items = array();
		foreach ( (array) wp_unslash( $_POST['mgpc_items'] ?? array() ) as $slot => $it ) { // phpcs:ignore -- sanitized below.
			$pid = absint( $it['id'] ?? 0 );
			if ( $pid ) {
				$items[] = array( 'slot' => sanitize_key( $slot ), 'id' => $pid, 'qty' => max( 1, min( 16, absint( $it['qty'] ?? 1 ) ) ) );
			}
		}
		update_post_meta( $id, '_mgpc_items', $items );
		update_post_meta( $id, '_mgpc_tag', sanitize_text_field( wp_unslash( $_POST['mgpc_tag'] ?? '' ) ) );
		$theme = sanitize_key( $_POST['mgpc_theme'] ?? 'night' );
		update_post_meta( $id, '_mgpc_theme', array_key_exists( $theme, self::themes() ) ? $theme : 'night' );
	}

	/** Front-end data for preset cards. */
	public static function all() {
		$out = array();
		foreach ( get_posts( array( 'post_type' => self::PT, 'posts_per_page' => 12, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ) ) ) as $post ) {
			$items = (array) get_post_meta( $post->ID, '_mgpc_items', true );
			if ( ! $items ) {
				continue;
			}
			$total = 0;
			$specs = array();
			$slots = MGPC_Settings::slots();
			foreach ( $items as $it ) {
				$p = wc_get_product( $it['id'] );
				if ( $p ) {
					$total += (float) wc_get_price_to_display( $p ) * max( 1, (int) $it['qty'] );
					if ( in_array( $slots[ $it['slot'] ]['role'] ?? '', array( 'cpu', 'vga', 'ram' ), true ) ) {
						$specs[] = $p->get_name();
					}
				}
			}
			$out[] = array(
				'id'    => $post->ID,
				'title' => get_the_title( $post ),
				'desc'  => wp_strip_all_tags( get_the_excerpt( $post ) ),
				'img'   => get_the_post_thumbnail_url( $post, 'medium' ),
				'tag'   => get_post_meta( $post->ID, '_mgpc_tag', true ),
				'theme' => get_post_meta( $post->ID, '_mgpc_theme', true ) ?: 'night',
				'build' => $items,
				'total' => $total,
				'specs' => array_slice( $specs, 0, 3 ),
			);
		}
		return $out;
	}
}
