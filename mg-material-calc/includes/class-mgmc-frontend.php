<?php
defined( 'ABSPATH' ) || exit;

class MGMC_Frontend {

	public static function init() {
		add_action( 'woocommerce_before_add_to_cart_quantity', array( __CLASS__, 'render_simple' ), 5 );
		add_action( 'woocommerce_before_variations_form', array( __CLASS__, 'render_variable' ), 5 );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'price_m2' ), 12 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register' ) );
		add_shortcode( 'mg_material_calc', array( __CLASS__, 'shortcode' ) );
	}

	public static function register() {
		wp_register_style( 'mgmc', MGMC_URL . 'assets/css/frontend.css', array(), MGMC_VERSION );
		wp_register_script( 'mgmc-engine', MGMC_URL . 'assets/js/mgmc-engine.js', array(), MGMC_VERSION, true );
		wp_register_script( 'mgmc', MGMC_URL . 'assets/js/frontend.js', array( 'jquery', 'mgmc-engine' ), MGMC_VERSION, true );
		$s = MGMC_Config::settings();
		wp_localize_script( 'mgmc', 'mgmcData', array(
			'currency'   => array(
				'symbol'   => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' ),
				'format'   => get_option( 'woocommerce_currency_pos', 'left' ),
				'decimals' => wc_get_price_decimals(),
				'dec'      => wc_get_price_decimal_separator(),
				'thousand' => wc_get_price_thousand_separator(),
			),
			'autoQty'    => 'yes' === $s['auto_qty'],
			'wallHeight' => (float) $s['wall_height'],
			'surfaces'   => MGMC_Config::surfaces(),
		) );
		if ( is_product() ) {
			wp_enqueue_style( 'mgmc' );
		}
	}

	public static function render_simple() {
		global $product;
		if ( $product instanceof WC_Product && $product->is_type( 'simple' ) ) {
			self::box( $product );
		}
	}

	public static function render_variable() {
		global $product;
		if ( $product instanceof WC_Product && $product->is_type( 'variable' ) ) {
			self::box( $product );
		}
	}

	private static function unit_m2_text( $c, $price ) {
		$m2 = MGMC_Engine::unit_m2( $c );
		if ( 'paint' === $c['type'] || $m2 <= 0 || $price <= 0 ) {
			return '';
		}
		return wc_price( $price / $m2 ) . '/m²';
	}

	public static function price_m2() {
		global $product;
		$s = MGMC_Config::settings();
		if ( 'yes' !== $s['show_price_m2'] || ! $product instanceof WC_Product ) {
			return;
		}
		$c = MGMC_Config::for_product( $product );
		if ( ! $c ) {
			return;
		}
		$price = $product->is_type( 'variable' ) ? (float) $product->get_variation_price( 'min', true ) : (float) wc_get_price_to_display( $product );
		$text  = self::unit_m2_text( $c, $price );
		if ( $text ) {
			$m2 = MGMC_Engine::unit_m2( $c );
			echo '<p class="mgmc-price-m2">≈ ' . wp_kses_post( $text ) . ' <span>(1 ' . esc_html( $c['unit'] ) . ' = ' . esc_html( wc_format_localized_decimal( round( $m2, 2 ) ) ) . ' m²)</span></p>';
		}
	}

	public static function box( $product, $standalone = false ) {
		static $done = array();
		$c = MGMC_Config::for_product( $product );
		if ( ! $c || isset( $done[ $product->get_id() ] ) ) {
			return;
		}
		$done[ $product->get_id() ] = true;
		wp_enqueue_style( 'mgmc' );
		wp_enqueue_script( 'mgmc' );

		$s       = MGMC_Config::settings();
		$types   = MGMC_Config::types();
		$price   = $product->is_type( 'variable' ) ? (float) $product->get_variation_price( 'min', true ) : (float) wc_get_price_to_display( $product );
		$reserve = array_unique( array_map( 'floatval', array_merge( explode( ',', $s['reserve_options'] ), array( $c['reserve'] ) ) ) );
		sort( $reserve );
		$js_config = array_intersect_key( $c, array_flip( array( 'type', 'tile_w', 'tile_h', 'joint', 'count', 'per_unit', 'roll_w', 'roll_l', 'repeat', 'panel_w', 'panel_l', 'm2_per_unit', 'coverage', 'coats', 'unit_volume', 'waste_straight', 'waste_diagonal', 'waste_herringbone', 'min_reserve_unit' ) ) );
		$meta    = array(
			'unit'     => $c['unit'],
			'base'     => $types[ $c['type'] ]['base'],
			'surfaces' => $c['surfaces'],
			'default'  => in_array( $types[ $c['type'] ]['default'], $c['surfaces'], true ) ? $types[ $c['type'] ]['default'] : $c['surfaces'][0],
			'unitM2'   => MGMC_Engine::unit_m2( $c ),
			'product'  => $product->get_id(),
		);
		$patterns = in_array( $c['type'], array( 'tile', 'area' ), true ) && in_array( 'floor', $c['surfaces'], true );
		$open     = 'yes' === $s['open_default'] || $standalone;
		?>
		<div class="mgmc<?php echo $open ? ' is-open' : ''; ?>" data-config="<?php echo esc_attr( wp_json_encode( $js_config ) ); ?>" data-meta="<?php echo esc_attr( wp_json_encode( $meta ) ); ?>" data-price="<?php echo esc_attr( $price ); ?>">
			<button type="button" class="mgmc-toggle" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>">
				<span class="mgmc-toggle-icon" aria-hidden="true">📐</span>
				<span class="mgmc-toggle-text"><?php echo esc_html( $s['title'] ); ?></span>
				<span class="mgmc-toggle-summary" data-summary></span>
				<span class="mgmc-chevron" aria-hidden="true"></span>
			</button>
			<div class="mgmc-body">
				<?php if ( $s['intro'] ) : ?><p class="mgmc-intro"><?php echo esc_html( $s['intro'] ); ?></p><?php endif; ?>
				<div class="mgmc-rooms" data-rooms></div>
				<button type="button" class="mgmc-add">+ Thêm phòng</button>
				<div class="mgmc-options">
					<?php if ( $patterns ) : ?>
						<div class="mgmc-opt">
							<span class="mgmc-opt-label">Kiểu lát</span>
							<div class="mgmc-pills">
								<?php foreach ( MGMC_Config::patterns() as $k => $v ) : ?>
									<label><input type="radio" name="mgmc_pattern_<?php echo (int) $product->get_id(); ?>" value="<?php echo esc_attr( $k ); ?>" data-pattern <?php checked( 'straight', $k ); ?>><span><?php echo esc_html( $v ); ?></span></label>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
					<div class="mgmc-opt">
						<label class="mgmc-opt-label" for="mgmc-reserve-<?php echo (int) $product->get_id(); ?>">Dự phòng</label>
						<select id="mgmc-reserve-<?php echo (int) $product->get_id(); ?>" data-reserve>
							<?php foreach ( $reserve as $r ) : ?>
								<option value="<?php echo esc_attr( $r ); ?>" <?php selected( (float) $c['reserve'], $r ); ?>><?php echo esc_html( $r ? '+' . wc_format_localized_decimal( $r ) . '%' : 'Không dự phòng' ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
				<div class="mgmc-result" data-result aria-live="polite"></div>
				<?php if ( $s['note'] ) : ?><p class="mgmc-note"><?php echo esc_html( $s['note'] ); ?></p><?php endif; ?>
				<input type="hidden" name="mgmc_data" value="">
			</div>
		</div>
		<?php
	}

	/** [mg_material_calc id="123"] – calculator + add to cart anywhere (simple products). */
	public static function shortcode( $atts ) {
		$atts    = shortcode_atts( array( 'id' => 0 ), $atts, 'mg_material_calc' );
		$product = wc_get_product( absint( $atts['id'] ) );
		if ( ! $product || ! MGMC_Config::for_product( $product ) ) {
			return current_user_can( 'edit_products' ) ? '<p><em>[mg_material_calc]: sản phẩm không tồn tại hoặc chưa bật "Tính vật liệu".</em></p>' : '';
		}
		ob_start();
		if ( ! $product->is_type( 'simple' ) ) {
			echo '<p><a class="button" href="' . esc_url( $product->get_permalink() ) . '">Tính vật liệu cho ' . esc_html( $product->get_name() ) . '</a></p>';
			return ob_get_clean();
		}
		?>
		<div class="mgmc-shortcode">
			<div class="mgmc-sc-head">
				<?php echo wp_kses_post( $product->get_image( 'woocommerce_gallery_thumbnail' ) ); ?>
				<div><a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="mgmc-sc-name"><?php echo esc_html( $product->get_name() ); ?></a>
				<div class="mgmc-sc-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div></div>
			</div>
			<form class="cart" action="<?php echo esc_url( $product->get_permalink() ); ?>" method="post" enctype="multipart/form-data">
				<?php self::box( $product, true ); ?>
				<div class="mgmc-sc-buy">
					<?php woocommerce_quantity_input( array( 'min_value' => $product->get_min_purchase_quantity(), 'max_value' => $product->get_max_purchase_quantity() ), $product ); ?>
					<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
				</div>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}
}
