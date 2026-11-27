<?php
defined( 'ABSPATH' ) || exit;

class MGQ_Admin {

	public static function init() {
		add_action( 'add_meta_boxes_' . MGQ_Quote::PT, array( __CLASS__, 'boxes' ) );
		add_action( 'save_post_' . MGQ_Quote::PT, array( __CLASS__, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'wp_ajax_mgq_product', array( __CLASS__, 'ajax_product' ) );
		add_action( 'admin_post_mgq_pdf', array( __CLASS__, 'download_pdf' ) );
		add_filter( 'manage_' . MGQ_Quote::PT . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . MGQ_Quote::PT . '_posts_custom_column', array( __CLASS__, 'column' ), 10, 2 );
		add_filter( 'post_row_actions', array( __CLASS__, 'row_actions' ), 10, 2 );
		add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
		add_filter( 'enter_title_here', function ( $t, $post ) {
			return MGQ_Quote::PT === $post->post_type ? 'Tên gợi nhớ (VD: Công trình nhà anh Nam – Cầu Giấy)' : $t;
		}, 10, 2 );
	}

	public static function can_price() {
		return current_user_can( 'manage_woocommerce' );
	}

	public static function assets() {
		$screen = get_current_screen();
		if ( ! $screen || MGQ_Quote::PT !== $screen->post_type || 'post' !== $screen->base ) {
			return;
		}
		wp_enqueue_style( 'woocommerce_admin_styles' );
		wp_enqueue_script( 'mgq-admin', MGQ_URL . 'assets/js/admin-quote.js', array( 'jquery', 'jquery-ui-sortable', 'wc-enhanced-select' ), MGQ_VERSION, true );
		wp_localize_script( 'mgq-admin', 'mgqAdmin', array( 'nonce' => wp_create_nonce( 'mgq_product' ), 'canPrice' => self::can_price() ) );
		wp_enqueue_style( 'mgq-admin', MGQ_URL . 'assets/css/admin-quote.css', array(), MGQ_VERSION );
	}

	public static function boxes( $post ) {
		add_meta_box( 'mgq_lines', 'Sản phẩm & giá', array( __CLASS__, 'box_lines' ), MGQ_Quote::PT, 'normal', 'high' );
		add_meta_box( 'mgq_customer', 'Khách hàng', array( __CLASS__, 'box_customer' ), MGQ_Quote::PT, 'normal', 'high' );
		add_meta_box( 'mgq_actions', 'Báo giá', array( __CLASS__, 'box_actions' ), MGQ_Quote::PT, 'side', 'high' );
		add_meta_box( 'mgq_history', 'Lịch sử', array( __CLASS__, 'box_history' ), MGQ_Quote::PT, 'side', 'low' );
	}

	private static function q( $post ) {
		return 'auto-draft' === $post->post_status ? MGQ_Quote::get( 0 ) : MGQ_Quote::get( $post->ID );
	}

	public static function box_customer( $post ) {
		$c = self::q( $post )['customer'];
		echo '<div class="mgq-grid">';
		foreach ( array( 'name' => 'Họ tên người liên hệ', 'phone' => 'Điện thoại', 'email' => 'Email', 'company' => 'Công ty', 'tax_code' => 'Mã số thuế', 'address' => 'Địa chỉ / công trình' ) as $k => $l ) {
			printf( '<label><span>%s</span><input type="%s" name="mgq[customer][%s]" value="%s"></label>', esc_html( $l ), 'email' === $k ? 'email' : 'text', esc_attr( $k ), esc_attr( $c[ $k ] ) );
		}
		echo '</div>';
	}

	public static function box_lines( $post ) {
		$q   = self::q( $post );
		$can = self::can_price();
		wp_nonce_field( 'mgq_save', 'mgq_nonce' );
		?>
		<div class="mgq-type">
			<?php foreach ( MGQ_Quote::types() as $k => $v ) : ?>
				<label class="mgq-type__opt"><input type="radio" name="mgq[type]" value="<?php echo esc_attr( $k ); ?>" <?php checked( $q['type'], $k ); ?> <?php disabled( ! $can ); ?>>
					<span><strong><?php echo esc_html( $v ); ?></strong><small><?php echo 'standard' === $k ? 'Giá lấy theo giá bán trên web tại thời điểm lưu, không sửa được.' : 'Quản trị viên tự nhập giá ưu đãi cho từng dòng.'; ?></small></span></label>
			<?php endforeach; ?>
		</div>
		<?php if ( ! $can ) : ?><p class="mgq-locked">🔒 Chỉ quản lý cửa hàng mới được sửa giá và nội dung báo giá.</p><?php endif; ?>
		<table class="widefat mgq-lines" data-can="<?php echo $can ? '1' : '0'; ?>">
			<thead><tr><th class="mgq-drag"></th><th>Sản phẩm / dịch vụ</th><th style="width:70px">ĐVT</th><th style="width:70px">SL</th><th style="width:120px">Giá niêm yết</th><th style="width:130px">Đơn giá báo</th><th style="width:120px" class="r">Thành tiền</th><th style="width:28px"></th></tr></thead>
			<tbody>
			<?php foreach ( array_values( $q['lines'] ) as $i => $l ) { self::row( $i, $l ); } ?>
			</tbody>
		</table>
		<script type="text/html" id="tmpl-mgq-row"><?php self::row( '__i__', array() ); ?></script>
		<?php if ( $can ) : ?>
			<div class="mgq-add">
				<select class="wc-product-search mgq-add-product" style="width:420px" data-placeholder="+ Thêm sản phẩm (gõ tên hoặc mã)…" data-action="woocommerce_json_search_products_and_variations"></select>
				<button type="button" class="button mgq-add-custom">+ Dòng tự nhập (công lắp, vận chuyển…)</button>
			</div>
		<?php endif; ?>
		<div class="mgq-bottom">
			<div class="mgq-grid mgq-grid--3">
				<label><span>Chiết khấu</span><span class="mgq-inline"><input type="number" step="any" min="0" name="mgq[discount][value]" value="<?php echo esc_attr( $q['discount']['value'] ); ?>" data-calc>
					<select name="mgq[discount][type]" data-calc><option value="amount" <?php selected( $q['discount']['type'], 'amount' ); ?>>₫</option><option value="percent" <?php selected( $q['discount']['type'], 'percent' ); ?>>%</option></select></span></label>
				<label><span>Phí vận chuyển / lắp đặt</span><input type="number" step="any" min="0" name="mgq[shipping]" value="<?php echo esc_attr( $q['shipping'] ); ?>" data-calc></label>
				<label><span>VAT (%)</span><span class="mgq-inline"><input type="number" step="any" min="0" max="100" name="mgq[vat]" value="<?php echo esc_attr( $q['vat'] ); ?>" data-calc>
					<select name="mgq[vat_included]" data-calc><option value="1" <?php selected( $q['vat_included'] ); ?>>đã gồm trong giá</option><option value="0" <?php selected( ! $q['vat_included'] ); ?>>cộng thêm</option></select></span></label>
				<label><span>Hiệu lực đến</span><input type="date" name="mgq[valid_until]" value="<?php echo esc_attr( $q['valid_until'] ); ?>"></label>
				<label class="mgq-span2"><span>Lời dẫn trên báo giá (tuỳ chọn)</span><input type="text" name="mgq[note]" value="<?php echo esc_attr( $q['note'] ); ?>" placeholder="VD: Báo giá vật liệu ốp lát công trình biệt thự Vinhomes Riverside"></label>
			</div>
			<table class="mgq-totals" data-totals>
				<tr><td>Cộng tiền hàng</td><td data-t="subtotal"></td></tr>
				<tr><td>Chiết khấu</td><td data-t="discount"></td></tr>
				<tr><td>Vận chuyển / lắp đặt</td><td data-t="shipping"></td></tr>
				<tr><td data-t="vatlabel">VAT</td><td data-t="vat"></td></tr>
				<tr class="mgq-grand"><td>Tổng cộng</td><td data-t="total"></td></tr>
				<tr><td colspan="2" class="mgq-saving" data-t="saving"></td></tr>
			</table>
		</div>
		<?php
	}

	private static function row( $i, $l ) {
		$l = wp_parse_args( $l, array( 'product_id' => 0, 'name' => '', 'sku' => '', 'unit' => 'cái', 'qty' => 1, 'list_price' => 0, 'price' => 0, 'note' => '' ) );
		$n = 'mgq[lines][' . $i . ']';
		?>
		<tr class="mgq-row">
			<td class="mgq-drag" title="Kéo để sắp xếp">⋮⋮</td>
			<td><input type="hidden" name="<?php echo esc_attr( $n ); ?>[product_id]" value="<?php echo esc_attr( $l['product_id'] ); ?>" data-f="product_id">
				<input type="hidden" name="<?php echo esc_attr( $n ); ?>[sku]" value="<?php echo esc_attr( $l['sku'] ); ?>" data-f="sku">
				<input type="text" class="mgq-name" name="<?php echo esc_attr( $n ); ?>[name]" value="<?php echo esc_attr( $l['name'] ); ?>" placeholder="Tên sản phẩm / dịch vụ" data-f="name">
				<input type="text" class="mgq-note" name="<?php echo esc_attr( $n ); ?>[note]" value="<?php echo esc_attr( $l['note'] ); ?>" placeholder="Ghi chú dòng (bảo hành, xuất xứ…)" data-f="note">
				<?php if ( $l['product_id'] ) : ?><small class="mgq-sku">#<?php echo (int) $l['product_id']; ?><?php echo $l['sku'] ? ' · ' . esc_html( $l['sku'] ) : ''; ?></small><?php endif; ?></td>
			<td><input type="text" name="<?php echo esc_attr( $n ); ?>[unit]" value="<?php echo esc_attr( $l['unit'] ); ?>" data-f="unit"></td>
			<td><input type="number" step="any" min="0" name="<?php echo esc_attr( $n ); ?>[qty]" value="<?php echo esc_attr( $l['qty'] ); ?>" data-f="qty" data-calc></td>
			<td><input type="number" step="any" min="0" name="<?php echo esc_attr( $n ); ?>[list_price]" value="<?php echo esc_attr( $l['list_price'] ); ?>" data-f="list_price" data-calc <?php echo $l['product_id'] ? 'readonly' : ''; ?>></td>
			<td><input type="number" step="any" min="0" class="mgq-price" name="<?php echo esc_attr( $n ); ?>[price]" value="<?php echo esc_attr( $l['price'] ); ?>" data-f="price" data-calc></td>
			<td class="r"><strong data-line-total></strong><small data-line-save></small></td>
			<td><button type="button" class="button-link mgq-del" aria-label="Xoá dòng">&times;</button></td>
		</tr>
		<?php
	}

	public static function box_actions( $post ) {
		$q   = self::q( $post );
		$new = 'auto-draft' === $post->post_status;
		echo '<div class="mgq-side">';
		echo '<p class="mgq-number">' . ( $new ? 'Số báo giá được tạo khi lưu' : '<strong>' . esc_html( $q['number'] ) . '</strong>' ) . '</p>';
		echo '<p><label>Trạng thái<br><select name="mgq[status]" style="width:100%">';
		foreach ( MGQ_Quote::statuses() as $k => $v ) {
			echo '<option value="' . esc_attr( $k ) . '" ' . selected( $q['status'], $k, false ) . '>' . esc_html( $v ) . '</option>';
		}
		echo '</select></label></p>';
		if ( ! $new ) {
			$pdf = wp_nonce_url( admin_url( 'admin-post.php?action=mgq_pdf&id=' . $post->ID ), 'mgq_pdf_' . $post->ID );
			echo '<p class="mgq-btns"><a class="button" target="_blank" href="' . esc_url( MGQ_Quote::public_url( $q ) ) . '">Xem như khách</a> <a class="button" href="' . esc_url( $pdf ) . '">Tải PDF</a></p>';
			echo '<p><input type="text" readonly class="widefat mgq-link" value="' . esc_attr( MGQ_Quote::public_url( $q ) ) . '" onclick="this.select()"><small>Link gửi khách (Zalo, Messenger…)</small></p>';
			if ( self::can_price() ) {
				echo '<p><button type="submit" name="mgq_action" value="send" class="button button-primary widefat"' . ( is_email( $q['customer']['email'] ) ? '' : ' disabled title="Nhập email khách hàng trước"' ) . '>Lưu &amp; gửi email kèm PDF</button></p>';
				if ( $q['order_id'] && wc_get_order( $q['order_id'] ) ) {
					echo '<p>Đơn hàng: <a href="' . esc_url( wc_get_order( $q['order_id'] )->get_edit_order_url() ) . '">#' . (int) $q['order_id'] . '</a></p>';
				} else {
					echo '<p><button type="submit" name="mgq_action" value="order" class="button widefat">Lưu &amp; tạo đơn hàng từ báo giá</button></p>';
				}
			}
		}
		echo '</div>';
	}

	public static function box_history( $post ) {
		$h = array_reverse( self::q( $post )['history'] );
		if ( ! $h ) {
			echo '<p class="description">Chưa có.</p>';
			return;
		}
		echo '<ul class="mgq-history">';
		foreach ( $h as $e ) {
			$u = $e['u'] ? get_userdata( $e['u'] ) : null;
			echo '<li><span>' . esc_html( mysql2date( 'd/m H:i', $e['t'] ) ) . '</span> ' . esc_html( $e['m'] ) . ( $u ? ' <em>– ' . esc_html( $u->display_name ) . '</em>' : '' ) . '</li>';
		}
		echo '</ul>';
	}

	/** Sanitizes posted lines. Standard quotes always take the live web price. */
	public static function clean_lines( $rows, $type ) {
		$out = array();
		foreach ( (array) $rows as $r ) {
			$name = sanitize_text_field( $r['name'] ?? '' );
			$pid  = absint( $r['product_id'] ?? 0 );
			$p    = $pid ? wc_get_product( $pid ) : null;
			if ( '' === $name && ! $p ) {
				continue;
			}
			$list  = max( 0, (float) ( $r['list_price'] ?? 0 ) );
			$price = max( 0, (float) ( $r['price'] ?? 0 ) );
			if ( $p ) {
				$list = (float) wc_get_price_to_display( $p );
				$name = $name ? $name : $p->get_name();
			}
			if ( 'standard' === $type ) {
				$price = $p ? $list : $price;
			}
			$out[] = array(
				'product_id' => $p ? $pid : 0,
				'name'       => $name,
				'sku'        => $p ? $p->get_sku() : sanitize_text_field( $r['sku'] ?? '' ),
				'unit'       => sanitize_text_field( $r['unit'] ?? 'cái' ) ?: 'cái',
				'qty'        => max( 0, (float) ( $r['qty'] ?? 1 ) ),
				'list_price' => $list ? $list : $price,
				'price'      => $price,
				'note'       => sanitize_text_field( $r['note'] ?? '' ),
			);
		}
		return $out;
	}

	public static function save( $id, $post ) {
		if ( ! isset( $_POST['mgq_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mgq_nonce'] ), 'mgq_save' ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
			return;
		}
		if ( ! self::can_price() ) {
			return; // Only shop managers may change quotes / prices.
		}
		$in   = (array) wp_unslash( $_POST['mgq'] ?? array() ); // phpcs:ignore -- sanitized below.
		$q    = MGQ_Quote::get( $id );
		$old  = $q;
		$type = sanitize_key( $in['type'] ?? $q['type'] );
		$q['type']  = array_key_exists( $type, MGQ_Quote::types() ) ? $type : 'custom';
		$st         = sanitize_key( $in['status'] ?? 'draft' );
		$q['status'] = array_key_exists( $st, MGQ_Quote::statuses() ) ? $st : 'draft';
		foreach ( array_keys( $q['customer'] ) as $k ) {
			$q['customer'][ $k ] = 'email' === $k ? sanitize_email( $in['customer'][ $k ] ?? '' ) : sanitize_text_field( $in['customer'][ $k ] ?? '' );
		}
		$q['lines']        = self::clean_lines( $in['lines'] ?? array(), $q['type'] );
		$q['discount']     = array( 'type' => 'percent' === ( $in['discount']['type'] ?? '' ) ? 'percent' : 'amount', 'value' => max( 0, (float) ( $in['discount']['value'] ?? 0 ) ) );
		$q['shipping']     = max( 0, (float) ( $in['shipping'] ?? 0 ) );
		$q['vat']          = max( 0, min( 100, (float) ( $in['vat'] ?? 0 ) ) );
		$q['vat_included'] = ! empty( $in['vat_included'] );
		$q['valid_until']  = preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) ( $in['valid_until'] ?? '' ) ) ? $in['valid_until'] : $q['valid_until'];
		$q['note']         = sanitize_text_field( $in['note'] ?? '' );
		if ( ! $q['number'] ) {
			$q['number'] = MGQ_Quote::next_number();
			$q['token']  = wp_generate_password( 24, false, false );
			$q['history'][] = array( 't' => current_time( 'mysql' ), 'u' => get_current_user_id(), 'm' => 'Tạo báo giá ' . ( 'custom' === $q['type'] ? 'cạnh tranh' : 'chuẩn' ) );
		} elseif ( $old['totals']['total'] !== MGQ_Quote::totals( $q['lines'], $q['discount'], $q['shipping'], $q['vat'], $q['vat_included'] )['total'] ) {
			$q['history'][] = array( 't' => current_time( 'mysql' ), 'u' => get_current_user_id(), 'm' => 'Cập nhật giá: ' . wp_strip_all_tags( wc_price( MGQ_Quote::totals( $q['lines'], $q['discount'], $q['shipping'], $q['vat'], $q['vat_included'] )['total'] ) ) );
		}
		MGQ_Quote::save( $id, $q );
		if ( '' === trim( $post->post_title ) || 0 === strpos( $post->post_title, 'Bản nháp tự động' ) ) {
			remove_action( 'save_post_' . MGQ_Quote::PT, array( __CLASS__, 'save' ), 10 );
			wp_update_post( array( 'ID' => $id, 'post_title' => $q['number'] . ( $q['customer']['name'] ? ' – ' . $q['customer']['name'] : '' ) ) );
			add_action( 'save_post_' . MGQ_Quote::PT, array( __CLASS__, 'save' ), 10, 2 );
		}
		$action = sanitize_key( $_POST['mgq_action'] ?? '' );
		if ( 'send' === $action ) {
			$r = MGQ_Public::send( $id );
			self::flash( is_wp_error( $r ) ? 'Không gửi được email: ' . $r->get_error_message() : 'Đã gửi báo giá kèm PDF tới ' . $q['customer']['email'] . '.', ! is_wp_error( $r ) );
		} elseif ( 'order' === $action ) {
			$r = self::create_order( $id );
			self::flash( is_wp_error( $r ) ? $r->get_error_message() : 'Đã tạo đơn hàng #' . $r . ' từ báo giá.', ! is_wp_error( $r ) );
		}
	}

	private static function flash( $msg, $ok ) {
		set_transient( 'mgq_notice_' . get_current_user_id(), array( 'm' => $msg, 'ok' => $ok ), 60 );
	}

	public static function notices() {
		$n = get_transient( 'mgq_notice_' . get_current_user_id() );
		if ( $n ) {
			delete_transient( 'mgq_notice_' . get_current_user_id() );
			echo '<div class="notice notice-' . ( $n['ok'] ? 'success' : 'error' ) . ' is-dismissible"><p>' . esc_html( $n['m'] ) . '</p></div>';
		}
	}

	/** Builds a pending WooCommerce order with the quoted prices. */
	public static function create_order( $id ) {
		$q = MGQ_Quote::get( $id );
		if ( ! $q['lines'] ) {
			return new WP_Error( 'mgq', 'Báo giá chưa có sản phẩm.' );
		}
		$order = wc_create_order( array( 'created_via' => 'mg-quote' ) );
		if ( is_wp_error( $order ) ) {
			return $order;
		}
		foreach ( $q['lines'] as $l ) {
			$total = (float) $l['qty'] * (float) $l['price'];
			$p     = $l['product_id'] ? wc_get_product( $l['product_id'] ) : null;
			if ( $p ) {
				$item = new WC_Order_Item_Product();
				$item->set_product( $p );
				$item->set_quantity( $l['qty'] );
				$item->set_subtotal( $total );
				$item->set_total( $total );
				$item->set_name( $l['name'] );
			} else {
				$item = new WC_Order_Item_Fee();
				$item->set_name( $l['name'] . ( (float) $l['qty'] != 1 ? ' × ' . wc_format_localized_decimal( $l['qty'] ) : '' ) );
				$item->set_total( $total );
			}
			$order->add_item( $item );
		}
		$t = $q['totals'];
		if ( $t['discount'] > 0 ) {
			$fee = new WC_Order_Item_Fee();
			$fee->set_name( 'Chiết khấu báo giá ' . $q['number'] );
			$fee->set_total( -$t['discount'] );
			$order->add_item( $fee );
		}
		if ( $t['shipping'] > 0 ) {
			$ship = new WC_Order_Item_Shipping();
			$ship->set_method_title( 'Vận chuyển / lắp đặt' );
			$ship->set_total( $t['shipping'] );
			$order->add_item( $ship );
		}
		if ( ! $t['vat_included'] && $t['vat'] > 0 ) {
			$fee = new WC_Order_Item_Fee();
			$fee->set_name( 'VAT ' . wc_format_localized_decimal( $t['vat_rate'] ) . '%' );
			$fee->set_total( $t['vat'] );
			$order->add_item( $fee );
		}
		$c     = $q['customer'];
		$parts = explode( ' ', trim( $c['name'] ) );
		$order->set_billing_first_name( array_pop( $parts ) );
		$order->set_billing_last_name( implode( ' ', $parts ) );
		$order->set_billing_phone( $c['phone'] );
		$order->set_billing_email( $c['email'] );
		$order->set_billing_company( $c['company'] );
		$order->set_billing_address_1( $c['address'] );
		$order->calculate_totals( false );
		$order->set_status( 'pending', 'Tạo từ báo giá ' . $q['number'] . '.' );
		$order->update_meta_data( '_mgq_quote', $id );
		$order->save();
		$q['order_id'] = $order->get_id();
		$q['status']   = 'ordered';
		$q['history'][] = array( 't' => current_time( 'mysql' ), 'u' => get_current_user_id(), 'm' => 'Tạo đơn hàng #' . $order->get_id() );
		MGQ_Quote::save( $id, $q );
		return $order->get_id();
	}

	public static function ajax_product() {
		check_ajax_referer( 'mgq_product', 'nonce' );
		if ( ! self::can_price() ) {
			wp_send_json_error( null, 403 );
		}
		$p = wc_get_product( absint( $_POST['id'] ?? 0 ) );
		if ( ! $p ) {
			wp_send_json_error();
		}
		$unit = '';
		if ( function_exists( 'mgmc_unit_for' ) ) {
			$unit = mgmc_unit_for( $p );
		}
		wp_send_json_success( array( 'product_id' => $p->get_id(), 'name' => $p->get_name(), 'sku' => $p->get_sku(), 'unit' => $unit ? $unit : 'cái', 'qty' => 1, 'list_price' => (float) wc_get_price_to_display( $p ), 'price' => (float) wc_get_price_to_display( $p ), 'note' => '' ) );
	}

	public static function download_pdf() {
		$id = absint( $_GET['id'] ?? 0 );
		check_admin_referer( 'mgq_pdf_' . $id );
		if ( ! current_user_can( 'edit_post', $id ) ) {
			wp_die( 'Không có quyền.' );
		}
		MGQ_PDF::stream( MGQ_Quote::get( $id ) );
	}

	public static function columns( $cols ) {
		return array( 'cb' => $cols['cb'], 'title' => 'Báo giá', 'mgq_type' => 'Loại', 'mgq_customer' => 'Khách hàng', 'mgq_total' => 'Tổng cộng', 'mgq_status' => 'Trạng thái', 'date' => 'Ngày' );
	}

	public static function column( $col, $id ) {
		$q = MGQ_Quote::get( $id );
		if ( 'mgq_type' === $col ) {
			echo 'custom' === $q['type'] ? '<span class="mg-status mg-status--quoted">Cạnh tranh</span>' : '<span class="mg-status">Chuẩn</span>';
		} elseif ( 'mgq_customer' === $col ) {
			echo esc_html( trim( $q['customer']['name'] . ( $q['customer']['phone'] ? ' · ' . $q['customer']['phone'] : '' ) ) ?: '—' );
		} elseif ( 'mgq_total' === $col ) {
			echo '<strong>' . wp_kses_post( wc_price( $q['totals']['total'] ) ) . '</strong>';
		} elseif ( 'mgq_status' === $col ) {
			$map = array( 'accepted' => 'won', 'ordered' => 'won', 'rejected' => 'lost', 'expired' => 'lost', 'sent' => 'quoted', 'viewed' => 'contacted' );
			echo '<span class="mg-status mg-status--' . esc_attr( $map[ $q['status'] ] ?? 'new' ) . '">' . esc_html( MGQ_Quote::statuses()[ $q['status'] ] ) . '</span>';
		}
	}

	public static function row_actions( $actions, $post ) {
		if ( MGQ_Quote::PT === $post->post_type ) {
			$actions['mgq_pdf'] = '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=mgq_pdf&id=' . $post->ID ), 'mgq_pdf_' . $post->ID ) ) . '">Tải PDF</a>';
			$actions['mgq_view'] = '<a target="_blank" href="' . esc_url( MGQ_Quote::public_url( MGQ_Quote::get( $post->ID ) ) ) . '">Xem</a>';
		}
		return $actions;
	}
}
