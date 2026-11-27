/* global jQuery, mgqAdmin */
jQuery(function ($) {
	var $t = $('.mgq-lines'), $body = $t.find('tbody'), can = $t.data('can') === 1 || $t.data('can') === '1';
	function fmt(n) { return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' ₫'; }
	function num(v) { var n = parseFloat(String(v || '').replace(',', '.')); return isFinite(n) ? n : 0; }
	function type() { return $('[name="mgq[type]"]:checked').val() || 'custom'; }

	function renumber() {
		$body.find('tr').each(function (i) {
			$(this).find('[name^="mgq[lines]"]').each(function () { this.name = this.name.replace(/^mgq\[lines\]\[[^\]]*\]/, 'mgq[lines][' + i + ']'); });
		});
	}
	function lockPrices() {
		var std = type() === 'standard';
		$body.find('tr').each(function () {
			var $r = $(this), isProduct = num($r.find('[data-f=product_id]').val()) > 0;
			$r.find('.mgq-price').prop('readonly', !can || (std && isProduct));
			if (std && isProduct) { $r.find('.mgq-price').val($r.find('[data-f=list_price]').val()); }
		});
		$('.mgq-lines').toggleClass('is-standard', std);
		if (!can) { $('#mgq_lines, #mgq_customer').find('input, select').not('[type=hidden]').prop('disabled', true); }
	}
	function calc() {
		var sub = 0, list = 0;
		$body.find('tr').each(function () {
			var $r = $(this), q = num($r.find('[data-f=qty]').val()), p = num($r.find('[data-f=price]').val()), lp = num($r.find('[data-f=list_price]').val()) || p;
			sub += q * p; list += q * lp;
			$r.find('[data-line-total]').text(fmt(q * p));
			$r.find('[data-line-save]').text(lp > p ? '−' + Math.round((1 - p / lp) * 100) + '%' : '');
		});
		var dv = num($('[name="mgq[discount][value]"]').val()), dt = $('[name="mgq[discount][type]"]').val();
		var disc = Math.round(dt === 'percent' ? sub * Math.min(100, dv) / 100 : Math.min(sub, dv));
		var ship = Math.round(num($('[name="mgq[shipping]"]').val())), base = sub - disc + ship;
		var rate = num($('[name="mgq[vat]"]').val()), incl = $('[name="mgq[vat_included]"]').val() === '1';
		var vat = incl ? Math.round(base - base / (1 + rate / 100)) : Math.round(base * rate / 100), total = incl ? base : base + vat;
		var $x = $('[data-totals]');
		$x.find('[data-t=subtotal]').text(fmt(sub));
		$x.find('[data-t=discount]').text(disc ? '−' + fmt(disc) : '—');
		$x.find('[data-t=shipping]').text(ship ? fmt(ship) : '—');
		$x.find('[data-t=vatlabel]').text((incl ? 'Trong đó VAT ' : 'VAT ') + rate + '%');
		$x.find('[data-t=vat]').text(rate ? fmt(vat) : '—');
		$x.find('[data-t=total]').text(fmt(total));
		var saving = Math.max(0, list - sub) + disc;
		$x.find('[data-t=saving]').text(saving > 0 ? 'Khách tiết kiệm ' + fmt(saving) + ' so với giá niêm yết' : '');
	}
	function addRow(data) {
		var $r = $($('#tmpl-mgq-row').html().split('__i__').join($body.find('tr').length));
		Object.keys(data || {}).forEach(function (k) { $r.find('[data-f=' + k + ']').val(data[k]); });
		if (data && data.product_id) { $r.find('[data-f=list_price]').prop('readonly', true); $r.find('.mgq-name').after('<small class="mgq-sku">#' + data.product_id + (data.sku ? ' · ' + data.sku : '') + '</small>'); }
		$body.append($r); renumber(); lockPrices(); calc();
		$r.find(data && data.product_id ? '[data-f=qty]' : '.mgq-name').trigger('focus');
	}

	$body.sortable({ handle: '.mgq-drag', axis: 'y', update: renumber });
	$(document).on('input change', '[data-calc]', calc);
	$(document).on('change', '[name="mgq[type]"]', function () { lockPrices(); calc(); });
	$body.on('click', '.mgq-del', function () { $(this).closest('tr').remove(); renumber(); calc(); });
	$('.mgq-add-custom').on('click', function () { addRow({ unit: 'gói', qty: 1 }); });
	$('.mgq-add-product').on('change', function () {
		var id = $(this).val(), $s = $(this);
		if (!id) { return; }
		$.post(ajaxurl, { action: 'mgq_product', nonce: mgqAdmin.nonce, id: id }).done(function (res) {
			if (res && res.success) { addRow(res.data); }
			$s.val(null).trigger('change.select2');
		});
	});
	lockPrices(); calc();
});
