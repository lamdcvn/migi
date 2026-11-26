<?php
defined( 'ABSPATH' ) || exit;

class MGBD_Frontend {

	public static function init() {
		add_action( 'woocommerce_before_add_to_cart_quantity', array( __CLASS__, 'render_box' ) );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'render_unit_note' ), 11 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
	}

	public static function assets() {
		if ( ! is_product() ) {
			return;
		}
		wp_enqueue_style( 'mgbd', MGBD_URL . 'assets/css/frontend.css', array( 'mg-ui' ), MGBD_VERSION );
		wp_enqueue_script( 'mgbd', MGBD_URL . 'assets/js/frontend.js', array( 'jquery' ), MGBD_VERSION, true );
		wp_localize_script( 'mgbd', 'mgbdData', array(
			'currency' => array(
				'symbol'   => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' ),
				'format'   => get_option( 'woocommerce_currency_pos', 'left' ),
				'decimals' => wc_get_price_decimals(),
				'dec'      => wc_get_price_decimal_separator(),
				'thousand' => wc_get_price_thousand_separator(),
			),
			'i18n' => array(
				'item'      => 'sản phẩm',
				'save'      => 'Tiết kiệm %s%',
				'each'      => '%s mỗi sản phẩm',
				'eachOff'   => '%1$s mỗi sản phẩm (giảm %2$s%)',
				'standard'  => 'Giá tiêu chuẩn',
				'noStock'   => 'Không đủ hàng',
			),
		) );
	}

	private static function current_product() {
		global $product;
		if ( ! $product instanceof WC_Product || $product->is_sold_individually() ) {
			return null;
		}
		if ( ! $product->is_type( array( 'simple', 'variable' ) ) && ! apply_filters( 'mgbd_supports_product_type', false, $product ) ) {
			return null;
		}
		return $product;
	}

	public static function render_unit_note() {
		$product = self::current_product();
		$s       = MGBD_Tiers::settings();
		if ( ! $product || 'yes' !== $s['show_unit_note'] || ! MGBD_Tiers::for_product( $product ) ) {
			return;
		}
		echo '<p class="mgbd-unit-note mg-ui" style="display:none"></p>';
	}

	public static function render_box() {
		static $done = array();
		$product = self::current_product();
		if ( ! $product || isset( $done[ $product->get_id() ] ) ) {
			return;
		}
		$tiers = MGBD_Tiers::for_product( $product );
		if ( ! $tiers ) {
			return;
		}
		$done[ $product->get_id() ] = true;

		$s     = MGBD_Tiers::settings();
		$price = (float) wc_get_price_to_display( $product );
		if ( $product->is_type( 'variable' ) ) {
			$price = (float) $product->get_variation_price( 'min', true );
		}
		$max   = $product->get_max_purchase_quantity();
		$img   = wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_gallery_thumbnail' );
		$img   = $img ? $img : wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' );
		$last  = count( $tiers ) - 1;
		$def   = 0;
		foreach ( $tiers as $i => $t ) {
			if ( 'yes' === $t['is_default'] ) {
				$def = $i;
			}
		}
		$json = array();
		foreach ( $tiers as $t ) {
			$json[] = array( 'qty' => (int) $t['qty'], 'discount' => (float) $t['discount'] );
		}
		$uid = 'mgbd-' . $product->get_id();
		?>
		<div class="mgbd mg-ui" data-tiers="<?php echo esc_attr( wp_json_encode( $json ) ); ?>" data-price="<?php echo esc_attr( $price ); ?>" data-max="<?php echo esc_attr( $max ); ?>" data-default="<?php echo esc_attr( $def ); ?>">
			<?php if ( $s['heading'] ) : ?>
				<div class="mgbd-heading"><span><?php echo esc_html( $s['heading'] ); ?></span></div>
			<?php endif; ?>
			<div class="mgbd-options" role="radiogroup">
			<?php foreach ( $tiers as $i => $t ) :
				$qty      = (int) $t['qty'];
				$disc     = (float) $t['discount'];
				$title    = $t['title'] ? $t['title'] : $qty . ( $i === $last && $qty > 1 ? '+' : '' ) . ' sản phẩm';
				$subtitle = $t['subtitle'];
				$unit     = $price * ( 1 - $disc / 100 );
				$thumbs   = min( 3, max( 1, $qty ) );
				?>
				<label class="mgbd-option<?php echo $t['badge'] ? ' has-badge' : ''; ?>" data-index="<?php echo esc_attr( $i ); ?>">
					<?php if ( $t['badge'] ) : ?>
						<span class="mgbd-badge" style="background:<?php echo esc_attr( $t['badge_color'] ); ?>"><?php echo esc_html( $t['badge'] ); ?></span>
					<?php endif; ?>
					<input type="radio" name="<?php echo esc_attr( $uid ); ?>" value="<?php echo esc_attr( $i ); ?>" class="mgbd-radio">
					<span class="mgbd-thumbs mgbd-thumbs-<?php echo esc_attr( $thumbs ); ?>">
						<?php for ( $k = 0; $k < $thumbs; $k++ ) : ?>
							<img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy">
						<?php endfor; ?>
					</span>
					<span class="mgbd-info">
						<span class="mgbd-title"><?php echo esc_html( $title ); ?></span>
						<?php if ( $subtitle ) : ?>
							<span class="mgbd-sub"><?php echo esc_html( $subtitle ); ?></span>
						<?php elseif ( $disc > 0 ) : ?>
							<span class="mgbd-save"><?php echo esc_html( sprintf( 'Tiết kiệm %s%%', wc_format_localized_decimal( $disc ) ) ); ?></span>
						<?php else : ?>
							<span class="mgbd-sub">Giá tiêu chuẩn</span>
						<?php endif; ?>
						<?php if ( $disc > 0 ) : ?>
							<span class="mgbd-each" data-each><?php echo wp_kses_post( wc_price( $unit ) ); ?> mỗi sản phẩm</span>
						<?php endif; ?>
					</span>
					<span class="mgbd-prices">
						<span class="mgbd-total" data-total><?php echo wp_kses_post( wc_price( $unit * $qty ) ); ?></span>
						<?php if ( $disc > 0 ) : ?>
							<del class="mgbd-orig" data-orig><?php echo wp_kses_post( wc_price( $price * $qty ) ); ?></del>
						<?php endif; ?>
					</span>
				</label>
			<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
