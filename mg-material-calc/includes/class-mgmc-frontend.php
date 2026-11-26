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
		wp_register_style( 'mgmc', MGMC_URL . 'assets/css/frontend.css', array( 'mg-ui' ), MGMC_VERSION );
		wp_register_script( 'mgmc-engine', MGMC_URL . 'assets/js/mgmc-engine.js', array(), MGMC_VERSION, true );
		wp_register_script( 'mgmc', MGMC_URL . 'assets/js/frontend.js', array( 'jquery', 'mgmc-engine' ), MGMC_VERSION, true );
		$s = MGMC_Config::settings();
		wp_localize_script( 'mgmc', 'mgmcData', array(
			'ajaxurl'    => admin_url( 'admin-ajax.php' ),
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
			'presets'    => self::presets(),
		) );
		if ( is_product() ) {
			wp_enqueue_style( 'mgmc' );
		}
	}

	/** Quick-add room presets: name|length|width. */
	public static function presets() {
		$out = array();
		foreach ( preg_split( '/\r?\n/', (string) MGMC_Config::settings()['presets'] ) as $line ) {
			$p = array_map( 'trim', explode( '|', $line ) );
			if ( 3 === count( $p ) && MGMC_Engine::f( $p[1] ) > 0 && MGMC_Engine::f( $p[2] ) > 0 ) {
				$out[] = array( 'name' => $p[0], 'l' => MGMC_Engine::f( $p[1] ), 'w' => MGMC_Engine::f( $p[2] ) );
			}
		}
		return $out;
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

	public static function display_price( $product ) {
		return $product->is_type( 'variable' ) ? (float) $product->get_variation_price( 'min', true ) : (float) wc_get_price_to_display( $product );
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
		$m2    = MGMC_Engine::unit_m2( $c );
		$price = self::display_price( $product );
		if ( in_array( $c['type'], array( 'paint', 'perimeter', 'consumable' ), true ) || $m2 <= 0 || $price <= 0 ) {
			return;
		}
		echo '<p class="mgmc-price-m2 mg-ui"><span class="mgmc-price-m2__value">≈ ' . wp_kses_post( wc_price( $price / $m2 ) ) . '/m²</span> <span class="mgmc-price-m2__unit">1 ' . esc_html( $c['unit'] ) . ' = ' . esc_html( wc_format_localized_decimal( round( $m2, 2 ) ) ) . ' m²</span></p>';
	}

	/** JS config subset of a product config. */
	public static function js_config( $c ) {
		return array_intersect_key( $c, array_flip( array( 'type', 'tile_w', 'tile_h', 'joint', 'count', 'per_unit', 'roll_w', 'roll_l', 'repeat', 'panel_w', 'panel_l', 'm2_per_unit', 'coverage', 'coats', 'unit_volume', 'rate', 'unit_size', 'piece_l', 'waste_straight', 'waste_diagonal', 'waste_herringbone', 'min_reserve_unit' ) ) );
	}

	/** Add-on products shown with the calculator. */
	public static function addons( $c ) {
		$out = array();
		foreach ( $c['addons'] as $id ) {
			$p  = wc_get_product( $id );
			$ac = $p ? MGMC_Config::for_product( $p ) : null;
			if ( ! $p || ! $ac || ! $p->is_type( 'simple' ) || ! $p->is_visible() ) {
				continue;
			}
			$img   = wp_get_attachment_image_url( $p->get_image_id(), 'woocommerce_gallery_thumbnail' );
			$out[] = array(
				'id'       => $p->get_id(),
				'name'     => $p->get_name(),
				'url'      => $p->get_permalink(),
				'img'      => $img ? $img : wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' ),
				'price'    => (float) wc_get_price_to_display( $p ),
				'unit'     => $ac['unit'],
				'base'     => $ac['base'],
				'surfaces' => $ac['surfaces'],
				'reserve'  => (float) $ac['reserve'],
				'config'   => self::js_config( $ac ),
				'buyable'  => $p->is_purchasable() && $p->is_in_stock(),
				'max'      => $p->get_max_purchase_quantity(),
			);
		}
		return $out;
	}

	private static function icon( $name ) {
		$icons = array(
			'ruler'  => '<path d="M3 17.25 17.25 3 21 6.75 6.75 21z"/><path d="m7.5 12.75 1.5 1.5m1.5-4.5 1.5 1.5m1.5-4.5 1.5 1.5"/>',
			'plus'   => '<path d="M12 5v14M5 12h14"/>',
			'x'      => '<path d="M6 6l12 12M18 6 6 18"/>',
			'doc'    => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>',
			'print'  => '<path d="M7 9V3h10v6M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2"/><path d="M7 14h10v7H7z"/>',
			'chev'   => '<path d="m6 9 6 6 6-6"/>',
			'layers' => '<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/>',
		);
		return '<svg class="mg-icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $icons[ $name ] . '</svg>';
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
		$addons  = self::addons( $c );
		$reserve = array_unique( array_map( 'floatval', array_merge( explode( ',', $s['reserve_options'] ), array( $c['reserve'] ) ) ) );
		sort( $reserve );
		$surfaces = $c['surfaces'];
		foreach ( $addons as $a ) {
			$surfaces = array_merge( $surfaces, $a['surfaces'] );
		}
		$surfaces = array_values( array_intersect( array_keys( MGMC_Config::surfaces() ), $surfaces ) );
		$meta     = array(
			'unit'     => $c['unit'],
			'base'     => $c['base'],
			'surfaces' => $c['surfaces'],
			'all'      => $surfaces,
			'default'  => in_array( $types[ $c['type'] ]['default'], $c['surfaces'], true ) ? $types[ $c['type'] ]['default'] : $c['surfaces'][0],
			'unitM2'   => MGMC_Engine::unit_m2( $c ),
			'product'  => $product->get_id(),
			'name'     => $product->get_name(),
			'quote'    => 'yes' === $s['quote_enabled'],
		);
		$patterns = in_array( $c['type'], array( 'tile', 'area' ), true ) && in_array( 'floor', $c['surfaces'], true );
		$open     = 'yes' === $s['open_default'] || $standalone;
		$pid      = (int) $product->get_id();
		?>
		<div class="mgmc mg-ui mg-card<?php echo $open ? ' is-open' : ''; ?>" data-config="<?php echo esc_attr( wp_json_encode( self::js_config( $c ) ) ); ?>" data-meta="<?php echo esc_attr( wp_json_encode( $meta ) ); ?>" data-addons="<?php echo esc_attr( wp_json_encode( $addons ) ); ?>" data-price="<?php echo esc_attr( self::display_price( $product ) ); ?>">
			<button type="button" class="mgmc-head" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>" aria-controls="mgmc-body-<?php echo $pid; ?>">
				<span class="mgmc-head__icon"><?php echo self::icon( 'ruler' ); // phpcs:ignore -- static SVG. ?></span>
				<span class="mgmc-head__text">
					<span class="mgmc-head__title"><?php echo esc_html( $s['title'] ); ?></span>
					<span class="mgmc-head__sub" data-summary><?php echo esc_html( $s['intro'] ); ?></span>
				</span>
				<span class="mgmc-head__chev"><?php echo self::icon( 'chev' ); // phpcs:ignore ?></span>
			</button>
			<div class="mgmc-body" id="mgmc-body-<?php echo $pid; ?>">
				<div class="mgmc-section">
					<div class="mgmc-section__head">
						<span class="mgmc-step">1</span><span class="mgmc-section__title">Kích thước phòng</span>
						<span class="mgmc-rooms-total" data-rooms-total></span>
					</div>
					<div class="mgmc-rooms" data-rooms></div>
					<div class="mgmc-add-row">
						<button type="button" class="mgmc-add"><?php echo self::icon( 'plus' ); // phpcs:ignore ?> Thêm phòng</button>
						<div class="mgmc-presets" data-presets></div>
					</div>
				</div>

				<div class="mgmc-section mgmc-options">
					<div class="mgmc-section__head"><span class="mgmc-step">2</span><span class="mgmc-section__title">Tuỳ chọn</span></div>
					<?php if ( $patterns ) : ?>
						<div class="mgmc-opt">
							<span class="mg-label">Kiểu lát</span>
							<div class="mg-seg">
								<?php foreach ( MGMC_Config::patterns() as $k => $v ) : ?>
									<label><input type="radio" name="mgmc_pattern_<?php echo $pid; ?>" value="<?php echo esc_attr( $k ); ?>" data-pattern <?php checked( 'straight', $k ); ?>><span><?php echo esc_html( $v ); ?></span></label>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
					<div class="mgmc-opt">
						<span class="mg-label">Dự phòng <span class="mgmc-tip" title="Phần mua dư để bù gạch vỡ, sai số thi công và giữ lại sửa chữa sau này (cùng lô màu).">?</span></span>
						<div class="mg-seg mg-seg--chips">
							<?php foreach ( $reserve as $r ) : ?>
								<label><input type="radio" name="mgmc_reserve_<?php echo $pid; ?>" value="<?php echo esc_attr( $r ); ?>" data-reserve <?php checked( (float) $c['reserve'], $r ); ?>><span><?php echo esc_html( $r ? wc_format_localized_decimal( $r ) . '%' : 'Không' ); ?></span></label>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="mgmc-section mgmc-result-wrap">
					<div class="mgmc-section__head"><span class="mgmc-step">3</span><span class="mgmc-section__title">Kết quả</span></div>
					<div class="mgmc-result" data-result aria-live="polite"></div>
					<?php if ( $addons ) : ?>
						<div class="mgmc-addons" data-addons-list hidden>
							<div class="mgmc-addons__head"><?php echo self::icon( 'layers' ); // phpcs:ignore ?> Phối kèm &amp; vật tư đi kèm</div>
							<div class="mgmc-addons__items" data-addon-items></div>
						</div>
					<?php endif; ?>
					<div class="mgmc-grand" data-grand hidden></div>
					<p class="mgmc-qty-hint" data-qty-hint></p>
					<div class="mgmc-actions">
						<?php if ( $meta['quote'] ) : ?>
							<button type="button" class="mg-btn mg-btn--ghost mg-btn--sm mgmc-quote-open"><?php echo self::icon( 'doc' ); // phpcs:ignore ?> <?php echo esc_html( $s['quote_button'] ); ?></button>
						<?php endif; ?>
						<button type="button" class="mg-btn mg-btn--quiet mg-btn--sm mgmc-print"><?php echo self::icon( 'print' ); // phpcs:ignore ?> In bảng tính</button>
					</div>
					<?php if ( $meta['quote'] ) : ?>
						<div class="mgmc-quote mg-panel" data-quote hidden>
							<p class="mgmc-quote__title">Nhận báo giá chi tiết</p>
							<p class="mgmc-quote__sub"><?php echo esc_html( $s['quote_intro'] ); ?></p>
							<div class="mgmc-quote__grid">
								<input class="mg-input" type="text" data-q="name" placeholder="Họ và tên *" autocomplete="name">
								<input class="mg-input" type="tel" data-q="phone" placeholder="Số điện thoại *" autocomplete="tel">
								<input class="mg-input" type="email" data-q="email" placeholder="Email (nhận báo giá qua email)" autocomplete="email">
								<input class="mg-input" type="text" data-q="address" placeholder="Địa chỉ công trình" autocomplete="street-address">
								<textarea class="mg-input" data-q="note" placeholder="Ghi chú (thời gian thi công, cần tư vấn thêm...)"></textarea>
								<input type="text" data-q="website" class="mgmc-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
							</div>
							<div class="mgmc-quote__msg" data-quote-msg hidden></div>
							<div class="mgmc-quote__actions">
								<button type="button" class="mg-btn mg-btn--quiet mg-btn--sm mgmc-quote-cancel">Đóng</button>
								<button type="button" class="mg-btn mg-btn--sm mgmc-quote-send">Gửi yêu cầu báo giá</button>
							</div>
						</div>
					<?php endif; ?>
				</div>
				<?php if ( $s['note'] ) : ?><p class="mgmc-note"><?php echo esc_html( $s['note'] ); ?></p><?php endif; ?>
				<input type="hidden" name="mgmc_data" value="">
				<input type="hidden" name="mgmc_addons" value="">
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
			echo '<p class="mg-ui"><a class="mg-btn" href="' . esc_url( $product->get_permalink() ) . '">Tính vật liệu cho ' . esc_html( $product->get_name() ) . '</a></p>';
			return ob_get_clean();
		}
		?>
		<div class="mgmc-shortcode mg-ui">
			<div class="mgmc-sc-head">
				<?php echo wp_kses_post( $product->get_image( 'woocommerce_gallery_thumbnail' ) ); ?>
				<div>
					<span class="mg-eyebrow">Tính vật liệu</span>
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="mgmc-sc-name"><?php echo esc_html( $product->get_name() ); ?></a>
					<div class="mgmc-sc-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
				</div>
			</div>
			<form class="cart" action="<?php echo esc_url( $product->get_permalink() ); ?>" method="post" enctype="multipart/form-data">
				<?php self::box( $product, true ); ?>
				<div class="mgmc-sc-buy">
					<?php woocommerce_quantity_input( array( 'min_value' => $product->get_min_purchase_quantity(), 'max_value' => $product->get_max_purchase_quantity() ), $product ); ?>
					<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="mg-btn single_add_to_cart_button"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
				</div>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}
}
