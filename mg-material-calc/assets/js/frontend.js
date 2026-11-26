/* global mgmcData, MGMCEngine, jQuery */
(function ($) {
	'use strict';
	var C = mgmcData.currency;

	function money(n) {
		var parts = Math.abs(n).toFixed(C.decimals).split('.');
		parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, C.thousand);
		var num = parts.join(C.dec), s = C.symbol;
		var out = { right: num + s, left_space: s + ' ' + num, right_space: num + ' ' + s }[C.format] || s + num;
		return (n < 0 ? '-' : '') + out;
	}
	function num(n, d) {
		d = d === undefined ? 1 : d;
		var v = (+n).toFixed(d);
		if (d > 0) { v = v.replace(/\.?0+$/, ''); }
		var p = v.split('.');
		p[0] = p[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
		return p.join(',');
	}
	function esc(s) { return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
	function val(v) { var n = parseFloat(String(v || '').replace(',', '.')); return n > 0 ? n : 0; }

	function Calc($el) {
		this.$el = $el;
		this.config = $el.data('config');
		this.meta = $el.data('meta');
		this.price = parseFloat($el.data('price')) || 0;
		this.$form = $el.closest('form');
		this.$qty = this.$form.find('input.qty, input[name="quantity"]').first();
		this.$rooms = $el.find('[data-rooms]');
		this.storeKey = 'mgmc_' + this.meta.product;
		this.bind();
		this.restore();
		this.update();
	}

	Calc.prototype.roomHtml = function (r, i) {
		var m = this.meta, single = m.surfaces.length === 1, surf = '';
		m.surfaces.forEach(function (s) {
			surf += '<label class="mgmc-chip"><input type="checkbox" data-k="' + s + '"' + (r[s] ? ' checked' : '') + '><span>' + esc(mgmcData.surfaces[s]) + '</span></label>';
		});
		return '<div class="mgmc-room">' +
			'<div class="mgmc-room-head"><input type="text" class="mgmc-room-name" data-k="name" value="' + esc(r.name || '') + '" placeholder="Phòng ' + (i + 1) + '" aria-label="Tên phòng">' +
			'<span class="mgmc-room-out" data-out></span>' +
			'<button type="button" class="mgmc-remove" aria-label="Xoá phòng">×</button></div>' +
			'<div class="mgmc-room-surfaces"' + (single ? ' hidden' : '') + '>' + surf + '</div>' +
			'<div class="mgmc-room-dims">' +
			'<label><span>Dài (m)</span><input type="text" inputmode="decimal" data-k="l" value="' + esc(r.l || '') + '" placeholder="0"></label>' +
			'<label><span>Rộng (m)</span><input type="text" inputmode="decimal" data-k="w" value="' + esc(r.w || '') + '" placeholder="0"></label>' +
			'<label class="mgmc-wall-only"><span>Cao (m)</span><input type="text" inputmode="decimal" data-k="h" value="' + esc(r.h || '') + '" placeholder="' + num(mgmcData.wallHeight, 2) + '"></label>' +
			'<label class="mgmc-wall-only"><span>Trừ cửa (m²)</span><input type="text" inputmode="decimal" data-k="openings" value="' + esc(r.openings || '') + '" placeholder="0"></label>' +
			'</div></div>';
	};

	Calc.prototype.newRoom = function () {
		var r = {}; r[this.meta['default']] = true; return r;
	};

	Calc.prototype.restore = function () {
		var rooms = null;
		try { rooms = JSON.parse(window.localStorage.getItem(this.storeKey) || 'null'); } catch (e) { rooms = null; }
		if (!rooms || !rooms.length) { rooms = [this.newRoom()]; }
		var self = this;
		this.$rooms.html(rooms.map(function (r, i) { return self.roomHtml(r, i); }).join(''));
	};

	Calc.prototype.readRooms = function () {
		var meta = this.meta;
		return this.$rooms.find('.mgmc-room').map(function (i) {
			var $r = $(this), r = { name: $.trim($r.find('[data-k=name]').val()) || 'Phòng ' + (i + 1) };
			['l', 'w', 'h', 'openings'].forEach(function (k) { r[k] = $r.find('[data-k=' + k + ']').val(); });
			meta.surfaces.forEach(function (s) { r[s] = $r.find('[data-k=' + s + ']').is(':checked'); });
			if (!val(r.h) && r.wall) { r.h = String(mgmcData.wallHeight); }
			$r.toggleClass('has-wall', !!r.wall);
			return r;
		}).get();
	};

	Calc.prototype.bind = function () {
		var self = this;
		this.$el.on('click', '.mgmc-toggle', function () {
			var open = !self.$el.hasClass('is-open');
			self.$el.toggleClass('is-open', open);
			$(this).attr('aria-expanded', open ? 'true' : 'false');
		});
		this.$el.on('click', '.mgmc-add', function () {
			var n = self.$rooms.find('.mgmc-room').length;
			self.$rooms.append(self.roomHtml(self.newRoom(), n));
			self.$rooms.find('.mgmc-room').last().find('[data-k=l]').trigger('focus');
			self.update();
		});
		this.$el.on('click', '.mgmc-remove', function () {
			var $rooms = self.$rooms.find('.mgmc-room');
			if ($rooms.length > 1) { $(this).closest('.mgmc-room').remove(); } else { $rooms.find('input[type=text]').val(''); }
			self.update();
		});
		this.$el.on('input change', 'input, select', function () { self.update(); });
		this.$el.on('click', '.mgmc-apply', function () { self.applyQty(true); });
		this.$form.on('change input', 'input.qty, input[name="quantity"]', function () { if (!self.applying) { self.qtyHint(); } });
		var $v = this.$el.closest('.variations_form');
		if (!$v.length) { $v = this.$form.filter('.variations_form'); }
		$v.on('found_variation', function (e, v) { if (v && v.display_price !== undefined) { self.price = +v.display_price; self.update(); } });
	};

	Calc.prototype.dealPrice = function (units) {
		var $deal = this.$form.find('.mgbd').first(), tiers = $deal.data('tiers'), disc = 0;
		if (!tiers || !tiers.length) { return null; }
		tiers.forEach(function (t) { if (units >= t.qty) { disc = +t.discount; } });
		return disc > 0 ? { pct: disc, total: units * this.price * (1 - disc / 100) } : null;
	};

	Calc.prototype.update = function () {
		var rooms = this.readRooms(), self = this, m = this.meta;
		var opt = { pattern: this.$el.find('[data-pattern]:checked').val() || 'straight', reserve: this.$el.find('[data-reserve]').val() };
		var r = MGMCEngine.calculate(this.config, rooms, opt, this.price);
		this.result = r;
		try { window.localStorage.setItem(this.storeKey, JSON.stringify(rooms)); } catch (e) { /* private mode */ }

		// Per room output.
		this.$rooms.find('.mgmc-room').each(function (i) {
			var room = MGMCEngine.calculate(self.config, [rooms[i]], opt, 0);
			$(this).find('[data-out]').text(room.area > 0 ? num(room.area, 2) + ' m²' : '');
		});

		var $res = this.$el.find('[data-result]'), $hidden = this.$el.find('input[name=mgmc_data]');
		if (!r.units) {
			$res.html('<p class="mgmc-empty">Nhập chiều dài, chiều rộng phòng để xem kết quả.</p>');
			this.$el.find('[data-summary]').text('');
			$hidden.val('');
			return;
		}
		var base = m.base, discrete = base === 'viên' || base === 'tấm';
		var needTxt = discrete ? num(r.need, 0) + ' ' + base : num(r.need, base === 'm²' ? 2 : 1) + ' ' + base;
		var resTxt = discrete ? num(r.reserve, 0) + ' ' + base : num(r.reserve, base === 'm²' ? 2 : 1) + ' ' + base;
		var buyNote = m.unitM2 > 0 && this.config.type !== 'paint' ? ' (' + num(r.material_m2, 2) + ' m²)' : (this.config.type === 'paint' ? ' (' + num(r.units * this.config.unit_volume, 1) + ' lít)' : '');
		var deal = this.dealPrice(r.units);
		var priceHtml = deal ? '<del>' + money(r.price) + '</del> <strong>' + money(deal.total) + '</strong><small>Đã gồm deal mua nhiều -' + num(deal.pct) + '%</small>' : '<strong>' + money(r.price) + '</strong>';
		var total = deal ? deal.total : r.price;

		var html = '<div class="mgmc-summary">' +
			'<div><span>Diện tích thi công</span><b>' + num(r.area, 2) + ' m²</b></div>' +
			'<div><span>Vật liệu cần dùng</span><b>' + needTxt + '</b></div>' +
			'<div><span>Dự phòng ' + num(r.reserve_pct) + '%</span><b>+' + resTxt + '</b></div>' +
			'<div class="mgmc-buy"><span>Nên mua</span><b>' + r.units + ' ' + esc(m.unit) + buyNote + '</b>' +
			(r.units_reserve > 0 ? '<small>gồm ' + r.units_reserve + ' ' + esc(m.unit) + ' dự phòng</small>' : '') + '</div>' +
			(this.price > 0 ? '<div class="mgmc-total"><span>Thành tiền</span>' + priceHtml + (r.area > 0 ? '<small>≈ ' + money(total / r.area) + '/m² thi công</small>' : '') + '</div>' : '') +
			'</div>';
		if (r.rooms.length > 1) {
			html += '<details class="mgmc-detail"><summary>Chi tiết ' + r.rooms.length + ' phòng</summary><table>' + r.rooms.map(function (x) {
				return '<tr><td>' + esc(x.name) + '</td><td>' + num(x.l, 2) + ' × ' + num(x.w, 2) + (x.h ? ' × ' + num(x.h, 2) : '') + ' m</td><td>' + num(x.area, 2) + ' m²</td></tr>';
			}).join('') + '</table></details>';
		}
		html += mgmcData.autoQty ? '<p class="mgmc-qty-hint" data-qty-hint></p>' : '<button type="button" class="mgmc-apply">Chọn ' + r.units + ' ' + esc(m.unit) + ' vào giỏ</button><p class="mgmc-qty-hint" data-qty-hint></p>';
		$res.html(html);
		this.$el.find('[data-summary]').text(r.units + ' ' + m.unit + (this.price > 0 ? ' · ' + money(total) : ''));
		$hidden.val(JSON.stringify({ rooms: rooms, pattern: opt.pattern, reserve: opt.reserve }));
		if (mgmcData.autoQty) { this.applyQty(false); } else { this.qtyHint(); }
	};

	Calc.prototype.applyQty = function (scroll) {
		if (!this.$qty.length || !this.result || !this.result.units) { return; }
		var max = parseInt(this.$qty.attr('max'), 10), u = this.result.units;
		if (max > 0 && u > max) { u = max; }
		if (parseInt(this.$qty.val(), 10) !== u) {
			this.applying = true;
			this.$qty.val(u).trigger('change');
			this.applying = false;
		}
		this.qtyHint();
		if (scroll) { this.$qty.trigger('focus'); }
	};

	Calc.prototype.qtyHint = function () {
		var $h = this.$el.find('[data-qty-hint]'), r = this.result;
		if (!$h.length || !r || !r.units || !this.$qty.length) { return; }
		var q = parseInt(this.$qty.val(), 10) || 0, max = parseInt(this.$qty.attr('max'), 10), u = this.meta.unit;
		var msg = '', cls = '';
		if (max > 0 && r.units > max) { msg = 'Cửa hàng chỉ còn ' + max + ' ' + u + ', cần ' + r.units + ' ' + u + '. Vui lòng liên hệ để đặt thêm.'; cls = 'is-warn'; }
		else if (q < r.units) { msg = 'Số lượng đang chọn (' + q + ') ít hơn mức đề xuất ' + r.units + ' ' + u + '.'; cls = 'is-warn'; }
		else if (q === r.units) { msg = '✓ Đã điền ' + q + ' ' + u + ' vào số lượng.'; cls = 'is-ok'; }
		$h.text(msg).attr('class', 'mgmc-qty-hint ' + cls);
	};

	$(function () {
		$('.mgmc').each(function () { if (!$(this).data('mgmc')) { $(this).data('mgmc', new Calc($(this))); } });
	});
})(jQuery);
