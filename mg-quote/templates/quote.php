<?php
/**
 * A4 quote document. Used for the PDF (dompdf) and the web/print view.
 * Layout uses tables + simple CSS on purpose (dompdf has no flex/grid).
 *
 * @var array  $q     MGQ_Quote::get()
 * @var array  $s     MGQ_Settings::get()
 * @var string $logo  image src (data URI for PDF)
 * @var string $mode  pdf|web
 */
defined( 'ABSPATH' ) || exit;
$t        = $q['totals'];
$accent   = $s['accent'];
$custom   = 'custom' === $q['type'];
$show_lp  = $custom && 'yes' === $s['show_list_price'] && $t['list_subtotal'] > $t['subtotal'];
$m        = function ( $n ) {
	return number_format( (float) $n, 0, ',', '.' ) . ' ₫';
};
$date     = get_the_date( 'd/m/Y', $q['id'] );
$valid    = $q['valid_until'] ? date_i18n( 'd/m/Y', strtotime( $q['valid_until'] ) ) : '';
$c        = $q['customer'];
$cols     = $show_lp ? 7 : 6;
?><!DOCTYPE html>
<html lang="vi"><head><meta charset="utf-8"><title><?php echo esc_html( $q['number'] ); ?></title>
<style>
@page { margin: 14mm 13mm 16mm; }
* { box-sizing: border-box; }
body { margin: 0; font-family: "DejaVu Sans", Arial, sans-serif; font-size: 10.5px; line-height: 1.45; color: #1f2430; }
table { border-collapse: collapse; width: 100%; }
td, th { vertical-align: top; }
.accent { color: <?php echo esc_attr( $accent ); ?>; }
.head td { padding: 0; }
.logo img { max-height: 58px; max-width: 220px; }
.company { font-size: 15px; font-weight: bold; margin: 0 0 3px; }
.muted { color: #6b7280; }
.small { font-size: 9.5px; }
.doc { text-align: right; }
.doc-title { font-size: 22px; font-weight: bold; letter-spacing: 1px; color: <?php echo esc_attr( $accent ); ?>; margin: 0; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 9px; background: #fff3e0; color: <?php echo esc_attr( $accent ); ?>; font-size: 9px; font-weight: bold; }
.rule { height: 3px; background: <?php echo esc_attr( $accent ); ?>; margin: 10px 0 14px; }
.box { border: 1px solid #e5e7eb; border-radius: 6px; padding: 9px 11px; }
.box h4 { margin: 0 0 5px; font-size: 9px; letter-spacing: .8px; text-transform: uppercase; color: #6b7280; }
.items { margin-top: 14px; }
.items th { background: <?php echo esc_attr( $accent ); ?>; color: #fff; font-size: 9.5px; text-align: left; padding: 7px 6px; }
.items td { padding: 7px 6px; border-bottom: 1px solid #eceef2; }
.items tr.alt td { background: #fafafb; }
.r { text-align: right; } .c { text-align: center; }
.items .name { font-weight: bold; }
.lp { color: #9ca3af; text-decoration: line-through; font-size: 9.5px; }
.totals { width: 46%; margin-left: 54%; margin-top: 10px; }
.totals td { padding: 4px 6px; }
.totals .grand td { border-top: 2px solid <?php echo esc_attr( $accent ); ?>; padding-top: 7px; font-size: 13px; font-weight: bold; }
.totals .grand .r { color: <?php echo esc_attr( $accent ); ?>; }
.words { margin-top: 6px; text-align: right; font-style: italic; color: #374151; }
.save { color: #0d9488; font-weight: bold; }
.section { margin-top: 16px; }
.section h4 { margin: 0 0 5px; font-size: 10px; text-transform: uppercase; letter-spacing: .8px; color: <?php echo esc_attr( $accent ); ?>; }
.terms li { margin: 0 0 2px; }
.sign td { text-align: center; padding-top: 18px; }
.sign .space { height: 60px; }
.foot { margin-top: 18px; padding-top: 8px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 9.5px; }
<?php if ( 'web' === $mode ) : ?>
body { background: #eef0f4; padding: 24px 0 60px; }
.paper { width: 210mm; min-height: 297mm; margin: 0 auto; padding: 14mm 13mm; background: #fff; box-shadow: 0 10px 40px rgba(16,24,40,.12); }
.toolbar { width: 210mm; margin: 0 auto 14px; display: flex; gap: 8px; justify-content: flex-end; font-family: Arial, sans-serif; }
.toolbar a, .toolbar button { display: inline-block; padding: 10px 16px; border-radius: 10px; border: 1px solid #d0d5dd; background: #fff; color: #1f2430; font-size: 13px; font-weight: bold; text-decoration: none; cursor: pointer; }
.toolbar .primary { background: <?php echo esc_attr( $accent ); ?>; border-color: <?php echo esc_attr( $accent ); ?>; color: #fff; }
.toolbar .note { margin-right: auto; align-self: center; color: #475467; font-size: 13px; }
@media (max-width: 820px) { body { padding-top: 12px; } .paper, .toolbar { width: auto; margin: 0 8px; } .paper { min-height: 0; padding: 18px 14px; overflow-x: auto; } .toolbar { flex-wrap: wrap; margin-bottom: 12px; } .toolbar .note { width: 100%; } .items { min-width: 560px; } }
@media print { body { background: #fff; padding: 0; } .toolbar { display: none; } .paper { box-shadow: none; width: auto; min-height: 0; padding: 0; } }
<?php endif; ?>
</style></head><body>
<?php if ( 'web' === $mode ) : ?>
<div class="toolbar">
	<span class="note"><?php echo esc_html( 'Báo giá ' . $q['number'] . ( $valid ? ' · hiệu lực đến ' . $valid : '' ) ); ?></span>
	<a href="<?php echo esc_url( MGQ_Quote::pdf_url( $q ) ); ?>" class="primary">Tải PDF</a>
	<button type="button" onclick="window.print()">In</button>
	<?php if ( in_array( $q['status'], array( 'sent', 'viewed', 'draft' ), true ) && 'custom' === $q['type'] ) : ?>
		<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'mg-quote' => $q['token'], 'mgq_accept' => 1 ), home_url( '/' ) ), 'mgq_accept_' . $q['id'] ) ); ?>">Đồng ý báo giá</a>
	<?php elseif ( 'accepted' === $q['status'] ) : ?>
		<span class="note" style="color:#0d9488;margin-right:0">✓ Quý khách đã đồng ý báo giá</span>
	<?php endif; ?>
</div>
<div class="paper">
<?php endif; ?>

<table class="head"><tr>
	<td style="width:58%">
		<?php if ( $logo ) : ?><div class="logo"><img src="<?php echo esc_attr( $logo ); ?>" alt=""></div><?php endif; ?>
		<p class="company"><?php echo esc_html( $s['company'] ); ?></p>
		<div class="muted small">
			<?php
			$info = array_filter( array(
				$s['address'],
				trim( ( $s['phone'] ? 'Hotline: ' . $s['phone'] : '' ) . ( $s['email'] ? ' · ' . $s['email'] : '' ), ' ·' ),
				trim( ( $s['website'] ? preg_replace( '#^https?://#', '', untrailingslashit( $s['website'] ) ) : '' ) . ( $s['tax_code'] ? ' · MST: ' . $s['tax_code'] : '' ), ' ·' ),
			) );
			echo implode( '<br>', array_map( 'esc_html', $info ) ); // phpcs:ignore -- escaped above.
			?>
		</div>
	</td>
	<td class="doc" style="width:42%">
		<p class="doc-title">BÁO GIÁ</p>
		<div style="margin:4px 0 6px"><span class="badge"><?php echo esc_html( $custom ? 'GIÁ ƯU ĐÃI RIÊNG' : 'GIÁ NIÊM YẾT' ); ?></span></div>
		<table class="small" style="width:auto;margin-left:auto">
			<tr><td class="muted" style="padding:1px 8px 1px 0">Số:</td><td class="r"><strong><?php echo esc_html( $q['number'] ); ?></strong></td></tr>
			<tr><td class="muted" style="padding:1px 8px 1px 0">Ngày lập:</td><td class="r"><?php echo esc_html( $date ); ?></td></tr>
			<?php if ( $valid ) : ?><tr><td class="muted" style="padding:1px 8px 1px 0">Hiệu lực đến:</td><td class="r"><?php echo esc_html( $valid ); ?></td></tr><?php endif; ?>
		</table>
	</td>
</tr></table>
<div class="rule"></div>

<table><tr>
	<td style="width:55%;padding-right:8px">
		<div class="box"><h4>Kính gửi</h4>
			<strong><?php echo esc_html( $c['company'] ? $c['company'] : ( $c['name'] ? $c['name'] : 'Quý khách hàng' ) ); ?></strong><br>
			<?php
			$cl = array_filter( array(
				$c['company'] && $c['name'] ? 'Người liên hệ: ' . $c['name'] : '',
				$c['phone'] ? 'Điện thoại: ' . $c['phone'] : '',
				$c['email'] ? 'Email: ' . $c['email'] : '',
				$c['address'] ? 'Địa chỉ: ' . $c['address'] : '',
				$c['tax_code'] ? 'MST: ' . $c['tax_code'] : '',
			) );
			echo '<span class="small">' . implode( '<br>', array_map( 'esc_html', $cl ) ) . '</span>'; // phpcs:ignore
			?>
		</div>
	</td>
	<td style="width:45%">
		<div class="box"><h4>Nội dung</h4>
			<span class="small"><?php echo esc_html( $q['note'] ? $q['note'] : $s['company'] . ' trân trọng gửi Quý khách báo giá các sản phẩm/dịch vụ như sau:' ); ?></span>
		</div>
	</td>
</tr></table>

<table class="items">
	<thead><tr>
		<th class="c" style="width:5%">STT</th>
		<th>Sản phẩm / Dịch vụ</th>
		<th class="c" style="width:8%">ĐVT</th>
		<th class="c" style="width:7%">SL</th>
		<?php if ( $show_lp ) : ?><th class="r" style="width:13%">Giá niêm yết</th><?php endif; ?>
		<th class="r" style="width:14%"><?php echo $custom ? 'Đơn giá ưu đãi' : 'Đơn giá'; ?></th>
		<th class="r" style="width:16%">Thành tiền</th>
	</tr></thead>
	<tbody>
	<?php foreach ( array_values( $q['lines'] ) as $i => $l ) : ?>
		<tr class="<?php echo $i % 2 ? 'alt' : ''; ?>">
			<td class="c"><?php echo (int) $i + 1; ?></td>
			<td><span class="name"><?php echo esc_html( $l['name'] ); ?></span>
				<?php if ( ! empty( $l['sku'] ) ) : ?><br><span class="muted small">Mã: <?php echo esc_html( $l['sku'] ); ?></span><?php endif; ?>
				<?php if ( ! empty( $l['note'] ) ) : ?><br><span class="muted small"><?php echo esc_html( $l['note'] ); ?></span><?php endif; ?></td>
			<td class="c"><?php echo esc_html( $l['unit'] ); ?></td>
			<td class="c"><?php echo esc_html( wc_format_localized_decimal( (float) $l['qty'] ) ); ?></td>
			<?php if ( $show_lp ) : ?><td class="r"><?php echo (float) $l['list_price'] > (float) $l['price'] ? '<span class="lp">' . esc_html( $m( $l['list_price'] ) ) . '</span>' : esc_html( $m( $l['list_price'] ) ); ?></td><?php endif; ?>
			<td class="r"><?php echo esc_html( $m( $l['price'] ) ); ?></td>
			<td class="r"><strong><?php echo esc_html( $m( (float) $l['qty'] * (float) $l['price'] ) ); ?></strong></td>
		</tr>
	<?php endforeach; ?>
	<?php if ( ! $q['lines'] ) : ?><tr><td colspan="<?php echo (int) $cols; ?>" class="c muted">Chưa có sản phẩm</td></tr><?php endif; ?>
	</tbody>
</table>

<table class="totals">
	<tr><td>Cộng tiền hàng</td><td class="r"><?php echo esc_html( $m( $t['subtotal'] ) ); ?></td></tr>
	<?php if ( $t['discount'] > 0 ) : ?><tr><td>Chiết khấu<?php echo 'percent' === $q['discount']['type'] ? ' (' . esc_html( wc_format_localized_decimal( $q['discount']['value'] ) ) . '%)' : ''; ?></td><td class="r">−<?php echo esc_html( $m( $t['discount'] ) ); ?></td></tr><?php endif; ?>
	<?php if ( $t['shipping'] > 0 ) : ?><tr><td>Vận chuyển / lắp đặt</td><td class="r"><?php echo esc_html( $m( $t['shipping'] ) ); ?></td></tr><?php endif; ?>
	<?php if ( $t['vat_rate'] > 0 ) : ?><tr><td class="muted"><?php echo $t['vat_included'] ? 'Trong đó VAT ' : 'Thuế VAT '; ?><?php echo esc_html( wc_format_localized_decimal( $t['vat_rate'] ) ); ?>%</td><td class="r muted"><?php echo esc_html( $m( $t['vat'] ) ); ?></td></tr><?php endif; ?>
	<tr class="grand"><td>TỔNG CỘNG</td><td class="r"><?php echo esc_html( $m( $t['total'] ) ); ?></td></tr>
	<?php if ( $custom && $t['saving'] > 0 ) : ?><tr><td colspan="2" class="r save">Quý khách tiết kiệm <?php echo esc_html( $m( $t['saving'] ) ); ?> so với giá niêm yết</td></tr><?php endif; ?>
</table>
<p class="words">Bằng chữ: <?php echo esc_html( MGQ_Quote::in_words( $t['total'] ) ); ?>.</p>

<table class="section"><tr>
	<td style="width:58%;padding-right:12px">
		<?php $terms = array_filter( array_map( 'trim', explode( "\n", (string) $s['terms'] ) ) ); ?>
		<?php if ( $terms ) : ?>
			<h4>Điều khoản</h4>
			<ul class="terms small" style="margin:0;padding-left:14px">
				<?php
				foreach ( $terms as $term ) {
					if ( $t['vat_rate'] > 0 && ! $t['vat_included'] ) {
						$term = str_replace( 'Giá đã bao gồm VAT', 'Giá chưa bao gồm VAT', $term );
					}
					echo '<li>' . esc_html( $term ) . '</li>';
				}
				?>
				<?php if ( $valid ) : ?><li><?php echo esc_html( 'Báo giá có hiệu lực đến ngày ' . $valid . '.' ); ?></li><?php endif; ?>
			</ul>
		<?php endif; ?>
	</td>
	<td style="width:42%">
		<?php if ( $s['bank_account'] ) : ?>
			<div class="box"><h4>Thông tin thanh toán</h4><span class="small">
				<?php echo esc_html( $s['bank'] ); ?><br>STK: <strong><?php echo esc_html( $s['bank_account'] ); ?></strong><br><?php echo esc_html( $s['bank_holder'] ); ?><br>Nội dung: <?php echo esc_html( $q['number'] ); ?>
			</span></div>
		<?php endif; ?>
	</td>
</tr></table>

<table class="sign"><tr>
	<td style="width:50%"><strong>KHÁCH HÀNG</strong><br><span class="muted small">(Ký, ghi rõ họ tên)</span><div class="space"></div></td>
	<td style="width:50%"><strong><?php echo esc_html( mb_strtoupper( $s['company'] ) ); ?></strong><br><span class="muted small"><?php echo esc_html( $s['signer_title'] ); ?></span><div class="space"></div><strong><?php echo esc_html( $s['signer'] ); ?></strong></td>
</tr></table>

<?php if ( $s['footer'] ) : ?><div class="foot"><?php echo esc_html( $s['footer'] ); ?><?php echo $s['phone'] ? ' · Hotline ' . esc_html( $s['phone'] ) : ''; ?></div><?php endif; ?>
<?php if ( 'web' === $mode ) : ?></div><?php endif; ?>
</body></html>
