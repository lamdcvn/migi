/* global mgmcData, MGMCEngine, jQuery */
(function ($) {
	'use strict';
	var C = mgmcData.currency;
	var ICON_X = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>';

	function money(n) {
		var parts = Math.abs(n).toFixed(C.decimals).split('.');
		parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, C.thousand);
		var num = parts.join(C.dec), s = C.symbol;
		var out = { right: num + s, left_space: s + ' ' + num, right_space: num + ' ' + s }[C.format] || s + num;
		return (n < 0 ? '-' : '') + out;
	}
	function short(n) {
		if (n >= 1e9) { return num(n / 1e9, 2) + ' tỷ'; }
		if (n >= 1e6) { return num(n / 1e6, 1) + ' triệu'; }
		return money(n);
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
	function qtyText(q, base) {
		var discrete = base === 'viên' || base === 'tấm' || base === 'thanh';
		return num(q, discrete ? 0 : (base === 'm²' ? 2 : 1)) + ' ' + base;
	}
	/** Keep only the surfaces a product applies to. */
	function mask(rooms, surfaces) {
		return rooms.map(function (r) {
			var m = $.extend({}, r);
			Object.keys(mgmcData.surfaces).forEach(function (s) { m[s] = !!r[s] && surfaces.indexOf(s) !== -1; });
			return m;
		});
	}

	function Calc($el) {
		this.$el = $el;
		this.config = $el.data('config');
		this.meta = $el.data('meta');
		this.addons = $el.data('addons') || [];
		this.price = parseFloat($el.data('price')) || 0;
		this.$form = $el.closest('form');
		this.$qty = this.$form.find('input.qty, input[name="quantity"]').first();
		this.$rooms = $el.find('[data-rooms]');
		this.storeKey = 'mgmc_' + this.meta.product;
		this.addonOff = {};
		this.renderPresets();
		this.bind();
		this.restore();
		this.update();
	}

	Calc.prototype.roomHtml = function (r, i) {
		var m = this.meta, chips = '';
		m.all.forEach(function (s) {
			chips += '<label><input type="checkbox" data-k="' + s + '"' + (r[s] ? ' checked' : '') + '><span>' + esc(mgmcData.surfaces[s]) + '</span></label>';
		});
		function field(k, label, ph, cls) {
			return '<label class="mgmc-field' + (cls ? ' ' + cls : '') + '"><span class="mgmc-field__label">' + label + '</span>' +
				'<input class="mg-input" type="text" inputmode="decimal" autocomplete="off" data-k="' + k + '" value="' + esc(r[k] || '') + '" placeholder="' + ph + '"></label>';
		}
		return '<div class="mgmc-room mg-panel">' +
			'<div class="mgmc-room__head">' +
			'<input type="text" class="mgmc-room__name" data-k="name" value="' + esc(r.name || '') + '" placeholder="Phòng ' + (i + 1) + '" aria-label="Tên phòng">' +
			'<span class="mgmc-room__area" data-out></span>' +
			'<button type="button" class="mgmc-room__remove" aria-label="Xoá phòng">' + ICON_X + '</button></div>' +
			'<div class="mg-seg mg-seg--chips mgmc-room__surfaces"' + (m.all.length === 1 ? ' hidden' : '') + '>' + chips + '</div>' +
			'<div class="mgmc-room__dims">' +
			field('l', 'Dài (m)', '0') + field('w', 'Rộng (m)', '0') +
			field('h', 'Cao (m)', num(mgmcData.wallHeight, 2), 'mgmc-wall-only') + field('openings', 'Trừ cửa (m²)', '0', 'mgmc-wall-only') +
			'</div></div>';
	};

	Calc.prototype.newRoom = function (preset) {
		var r = $.extend({}, preset || {}); r[this.meta['default']] = true; return r;
	};

	Calc.prototype.renderPresets = function () {
		var html = (mgmcData.presets || []).map(function (p, i) {
			return '<button type="button" class="mgmc-preset" data-preset="' + i + '">' + esc(p.name) + ' <small>' + num(p.l, 2) + '×' + num(p.w, 2) + '</small></button>';
		}).join('');
		this.$el.find('[data-presets]').html(html ? '<span class="mgmc-presets__label">Thêm nhanh:</span>' + html : '');
	};

	Calc.prototype.restore = function () {
		var rooms = null, self = this;
		try { rooms = JSON.parse(window.localStorage.getItem(this.storeKey) || 'null'); } catch (e) { rooms = null; }
		if (!rooms || !rooms.length) { rooms = [this.newRoom()]; }
		this.$rooms.html(rooms.map(function (r, i) { return self.roomHtml(r, i); }).join(''));
	};

	Calc.prototype.readRooms = function () {
		var all = this.meta.all;
		return this.$rooms.find('.mgmc-room').map(function (i) {
			var $r = $(this), r = { name: $.trim($r.find('[data-k=name]').val()) || 'Phòng ' + (i + 1) };
			['l', 'w', 'h', 'openings'].forEach(function (k) { r[k] = $.trim($r.find('[data-k=' + k + ']').val()); });
			all.forEach(function (s) { r[s] = $r.find('[data-k=' + s + ']').is(':checked'); });
			if (!val(r.h) && r.wall) { r.h = String(mgmcData.wallHeight); }
			$r.toggleClass('has-wall', !!r.wall);
			return r;
		}).get();
	};

	Calc.prototype.bind = function () {
		var self = this;
		this.$el.on('click', '.mgmc-head', function () {
			var open = !self.$el.hasClass('is-open');
			self.$el.toggleClass('is-open', open);
			$(this).attr('aria-expanded', open ? 'true' : 'false');
		});
		this.$el.on('click', '.mgmc-add, .mgmc-preset', function () {
			var p = $(this).data('preset'), preset = p !== undefined ? mgmcData.presets[p] : null;
			var $empty = self.$rooms.find('.mgmc-room').filter(function () { return !val($(this).find('[data-k=l]').val()) && !val($(this).find('[data-k=w]').val()); }).first();
			if (preset && $empty.length) {
				$empty.find('[data-k=name]').val(preset.name); $empty.find('[data-k=l]').val(num(preset.l, 2)); $empty.find('[data-k=w]').val(num(preset.w, 2));
			} else {
				var n = self.$rooms.find('.mgmc-room').length;
				self.$rooms.append(self.roomHtml(self.newRoom(preset ? { name: preset.name, l: num(preset.l, 2), w: num(preset.w, 2) } : null), n));
				if (!preset) { self.$rooms.find('.mgmc-room').last().find('[data-k=l]').trigger('focus'); }
			}
			self.update();
		});
		this.$el.on('click', '.mgmc-room__remove', function () {
			var $rooms = self.$rooms.find('.mgmc-room');
			if ($rooms.length > 1) { $(this).closest('.mgmc-room').remove(); } else { $rooms.find('.mg-input').val(''); $rooms.find('[data-k=name]').val(''); }
			self.update();
		});
		this.$el.on('input change', '.mgmc-room input, [data-pattern], [data-reserve]', function () { self.update(); });
		this.$el.on('change', '[data-addon]', function () {
			self.addonOff[$(this).data('addon')] = !this.checked;
			self.update();
		});
		this.$el.on('click', '.mgmc-quote-open', function () {
			var $q = self.$el.find('[data-quote]');
			$q.prop('hidden', !$q.prop('hidden'));
			if (!$q.prop('hidden')) { $q.find('[data-q=name]').trigger('focus'); }
		});
		this.$el.on('click', '.mgmc-quote-cancel', function () { self.$el.find('[data-quote]').prop('hidden', true); });
		this.$el.on('click', '.mgmc-quote-send', function () { self.sendQuote($(this)); });
		this.$el.on('click', '.mgmc-print', function () { self.print(); });
		this.$el.on('click', '.mgmc-pdf', function () { self.pdf($(this)); });
		this.$el.on('keydown', 'input', function (e) { if (e.key === 'Enter') { e.preventDefault(); } });
		this.$form.on('change input', 'input.qty, input[name="quantity"]', function () { if (!self.applying) { self.qtyHint(); } });
		var $v = this.$el.closest('.variations_form');
		$v.on('found_variation', function (e, v) { if (v && v.display_price !== undefined) { self.price = +v.display_price; self.update(); } });
	};

	Calc.prototype.dealPrice = function (units) {
		var tiers = this.$form.find('.mgbd').first().data('tiers'), disc = 0;
		if (!tiers || !tiers.length) { return null; }
		tiers.forEach(function (t) { if (units >= t.qty) { disc = +t.discount; } });
		return disc > 0 ? { pct: disc, total: units * this.price * (1 - disc / 100) } : null;
	};

	Calc.prototype.update = function () {
		var rooms = this.readRooms(), self = this, m = this.meta;
		var opt = { pattern: this.$el.find('[data-pattern]:checked').val() || 'straight', reserve: this.$el.find('[data-reserve]:checked').val() || 0 };
		var main = MGMCEngine.calculate(this.config, mask(rooms, m.surfaces), opt, this.price);
		this.result = main;
		try { window.localStorage.setItem(this.storeKey, JSON.stringify(rooms)); } catch (e) { /* private mode */ }

		// Room chips: area across every surface the room uses (union of products).
		var totalArea = 0, counted = 0;
		this.$rooms.find('.mgmc-room').each(function (i) {
			var r = rooms[i], area = 0;
			if (val(r.l) && val(r.w)) {
				area = (r.floor ? val(r.l) * val(r.w) : 0) + (r.ceiling ? val(r.l) * val(r.w) : 0) + (r.wall ? Math.max(0, 2 * (val(r.l) + val(r.w)) * val(r.h) - val(r.openings)) : 0);
			}
			totalArea += area; if (area > 0) { counted++; }
			$(this).find('[data-out]').text(area > 0 ? num(area, 2) + ' m²' : '').toggleClass('is-empty', !(area > 0));
		});
		this.$el.find('[data-rooms-total]').text(counted ? counted + ' phòng · ' + num(totalArea, 2) + ' m²' : '');

		// Add-ons.
		var addonLines = this.addons.map(function (a) {
			var r = MGMCEngine.calculate(a.config, mask(rooms, a.surfaces), { pattern: opt.pattern, reserve: opt.reserve }, a.price);
			return { a: a, r: r, on: r.units > 0 && a.buyable && !self.addonOff[a.id] };
		});
		this.renderAddons(addonLines);

		var $res = this.$el.find('[data-result]'), $hidden = this.$el.find('input[name=mgmc_data]');
		var anyAddon = addonLines.some(function (l) { return l.r.units > 0; });
		if (!main.units && !anyAddon) {
			$res.html('<div class="mgmc-empty"><strong>Nhập chiều dài và chiều rộng phòng</strong><span>Kết quả, số lượng cần mua và dự phòng hiện ngay tại đây.</span></div>');
			this.$el.find('[data-summary]').text(this.$el.find('[data-summary]').data('intro') || this.$el.find('[data-summary]').text());
			this.$el.find('[data-grand]').prop('hidden', true);
			$hidden.val(''); this.$el.find('input[name=mgmc_addons]').val('');
			this.qtyHint();
			return;
		}
		$hidden.val(JSON.stringify({ rooms: rooms, pattern: opt.pattern, reserve: opt.reserve }));

		var html = '';
		if (main.units) {
			var measure = this.config.type === 'perimeter' ? ' m dài' : ' m²';
			var buyNote = m.unitM2 > 0 && this.config.type !== 'paint' ? '≈ ' + num(main.material_m2, 2) + ' m² vật liệu' : (this.config.type === 'paint' ? '≈ ' + num(main.units * this.config.unit_volume, 1) + ' lít' : '');
			var deal = this.dealPrice(main.units);
			var mainTotal = deal ? deal.total : main.price;
			this.mainTotal = mainTotal;
			html += '<div class="mgmc-stats">' +
				'<div class="mg-stat"><span class="mg-stat__label">' + (this.config.type === 'perimeter' ? 'Chiều dài' : 'Diện tích') + '</span><span class="mg-stat__value">' + num(main.area, 2) + measure + '</span><span class="mg-stat__note">' + main.rooms.length + ' phòng</span></div>' +
				'<div class="mg-stat"><span class="mg-stat__label">Cần dùng</span><span class="mg-stat__value">' + qtyText(main.need, m.base) + '</span><span class="mg-stat__note">đã tính hao hụt cắt</span></div>' +
				'<div class="mg-stat"><span class="mg-stat__label">Dự phòng ' + num(main.reserve_pct) + '%</span><span class="mg-stat__value">+' + qtyText(main.reserve, m.base) + '</span><span class="mg-stat__note">' + (main.units_reserve > 0 ? '= ' + main.units_reserve + ' ' + esc(m.unit) : 'gộp trong số lẻ') + '</span></div>' +
				'<div class="mg-stat mg-stat--accent"><span class="mg-stat__label">Nên mua</span><span class="mg-stat__value">' + main.units + ' ' + esc(m.unit) + '</span><span class="mg-stat__note">' + buyNote + '</span></div>' +
				'</div>';
			if (this.price > 0) {
				html += '<div class="mgmc-line-total"><div><span class="mgmc-line-total__label">' + esc(m.name) + '</span>' +
					(deal ? '<span class="mgmc-deal">Deal mua nhiều −' + num(deal.pct) + '%</span>' : '') +
					'<span class="mgmc-line-total__per">≈ ' + money(mainTotal / Math.max(main.area, 0.01)) + (this.config.type === 'perimeter' ? '/m' : '/m²') + ' thi công</span></div>' +
					'<div class="mgmc-line-total__price">' + (deal ? '<del>' + money(main.price) + '</del>' : '') + '<strong>' + money(mainTotal) + '</strong></div></div>';
			}
			if (main.rooms.length > 1) {
				html += '<details class="mgmc-detail"><summary>Chi tiết theo phòng</summary><table>' + main.rooms.map(function (x) {
					return '<tr><td>' + esc(x.name) + '</td><td>' + num(x.l, 2) + ' × ' + num(x.w, 2) + (x.h ? ' × ' + num(x.h, 2) : '') + ' m</td><td>' + num(x.area, 2) + measure + '</td></tr>';
				}).join('') + '</table></details>';
			}
		} else {
			this.mainTotal = 0;
			html += '<div class="mg-alert mg-alert--warn">' + esc(m.name) + ' dùng cho: ' + m.surfaces.map(function (s) { return mgmcData.surfaces[s]; }).join(', ') + '. Hãy chọn bề mặt này ở ít nhất một phòng.</div>';
		}
		$res.html(html);

		// Grand total & hidden add-ons field.
		var chosen = addonLines.filter(function (l) { return l.on; });
		this.$el.find('input[name=mgmc_addons]').val(chosen.length ? JSON.stringify(chosen.map(function (l) { return l.a.id; })) : '');
		var grand = (this.mainTotal || 0) + chosen.reduce(function (s, l) { return s + l.r.price; }, 0);
		var $g = this.$el.find('[data-grand]');
		if (chosen.length && this.price > 0) {
			$g.html('<span>Tổng tạm tính <small>' + (chosen.length + (main.units ? 1 : 0)) + ' sản phẩm · thêm vào giỏ cùng lúc</small></span><strong>' + money(grand) + '</strong>').prop('hidden', false);
		} else { $g.prop('hidden', true); }
		this.$el.find('[data-summary]').text(main.units ? main.units + ' ' + m.unit + (grand > 0 ? ' · ' + short(grand) : '') + (chosen.length ? ' (+' + chosen.length + ' vật tư)' : '') : '');

		this.chosen = chosen;
		this.$el.find('.mgmc-pdf').prop('hidden', !window.MGQ).prop('disabled', !main.units && !chosen.length);
		if (mgmcData.autoQty && main.units) { this.applyQty(); } else { this.qtyHint(); }
	};

	/** Standard quote PDF (web prices) through Migi Quote, when installed. */
	Calc.prototype.pdf = function ($btn) {
		if (!window.MGQ || !this.result) { return; }
		var m = this.meta, r = this.result, items = [];
		var note = r.rooms.map(function (x) { return x.name + ' ' + num(x.area, 2) + ' m²'; }).join(', ');
		if (r.units) { items.push({ id: m.product, qty: r.units, unit: m.unit, note: note + ' · dự phòng ' + num(r.reserve_pct) + '%' }); }
		(this.chosen || []).forEach(function (l) { items.push({ id: l.a.id, qty: l.r.units, unit: l.a.unit, note: 'Vật tư đi kèm' }); });
		$btn.addClass('is-loading');
		window.MGQ.standard(items, { source: 'mgmc', pdf: true, note: 'Báo giá vật liệu ' + m.name + ' – tổng ' + num(r.area, 2) + ' m² thi công' })
			.catch(function (e) { window.alert(e.message); })
			.then(function () { $btn.removeClass('is-loading'); });
	};

	Calc.prototype.renderAddons = function (lines) {
		var $wrap = this.$el.find('[data-addons-list]');
		if (!$wrap.length) { return; }
		var any = lines.some(function (l) { return l.r.units > 0; });
		$wrap.prop('hidden', !lines.length);
		var html = lines.map(function (l) {
			var a = l.a, r = l.r, ok = r.units > 0;
			var desc = ok ? r.units + ' ' + esc(a.unit) + (a.base !== a.unit ? ' · ' + qtyText(r.total, a.base) : '') : 'Chọn bề mặt ' + a.surfaces.map(function (s) { return mgmcData.surfaces[s].toLowerCase(); }).join('/') + ' để tính';
			return '<label class="mgmc-addon' + (ok ? '' : ' is-off') + (l.on ? ' is-on' : '') + '">' +
				'<input type="checkbox" data-addon="' + a.id + '"' + (l.on ? ' checked' : '') + (ok && a.buyable ? '' : ' disabled') + '>' +
				'<span class="mgmc-addon__check" aria-hidden="true"></span>' +
				'<img src="' + esc(a.img) + '" alt="" loading="lazy">' +
				'<span class="mgmc-addon__text"><a href="' + esc(a.url) + '" target="_blank" rel="noopener">' + esc(a.name) + '</a><small>' + desc + (a.buyable ? '' : ' · tạm hết hàng') + '</small></span>' +
				'<span class="mgmc-addon__price">' + (ok && a.price > 0 ? money(r.price) : '') + '</span></label>';
		}).join('');
		this.$el.find('[data-addon-items]').html(html);
		$wrap.toggleClass('has-items', any);
	};

	Calc.prototype.applyQty = function () {
		if (!this.$qty.length || !this.result || !this.result.units) { return; }
		var max = parseInt(this.$qty.attr('max'), 10), u = this.result.units;
		if (max > 0 && u > max) { u = max; }
		if (parseInt(this.$qty.val(), 10) !== u) {
			this.applying = true;
			this.$qty.val(u).trigger('change');
			this.applying = false;
		}
		this.qtyHint();
	};

	Calc.prototype.qtyHint = function () {
		var $h = this.$el.find('[data-qty-hint]'), r = this.result;
		if (!r || !r.units || !this.$qty.length) { $h.text('').attr('class', 'mgmc-qty-hint'); return; }
		var q = parseInt(this.$qty.val(), 10) || 0, max = parseInt(this.$qty.attr('max'), 10), u = this.meta.unit, msg = '', cls = '';
		if (max > 0 && r.units > max) { msg = 'Cửa hàng còn ' + max + ' ' + u + ', bạn cần ' + r.units + ' ' + u + '. Hãy gửi yêu cầu báo giá để đặt thêm cùng lô.'; cls = 'is-warn'; }
		else if (q < r.units) { msg = 'Số lượng đang chọn (' + q + ') ít hơn đề xuất ' + r.units + ' ' + u + '.'; cls = 'is-warn'; }
		else { msg = 'Đã điền ' + q + ' ' + u + ' vào ô số lượng.'; cls = 'is-ok'; }
		$h.text(msg).attr('class', 'mgmc-qty-hint ' + cls);
	};

	Calc.prototype.sendQuote = function ($btn) {
		var self = this, $q = this.$el.find('[data-quote]'), $msg = $q.find('[data-quote-msg]');
		var d = {};
		$q.find('[data-q]').each(function () { d[$(this).data('q')] = $.trim($(this).val()); });
		function fail(text, field) {
			$msg.attr('class', 'mgmc-quote__msg mg-alert mg-alert--error').text(text).prop('hidden', false);
			if (field) { $q.find('[data-q=' + field + ']').addClass('is-invalid').trigger('focus'); }
		}
		$q.find('.is-invalid').removeClass('is-invalid');
		if (!this.$el.find('input[name=mgmc_data]').val()) { return fail('Vui lòng nhập kích thước phòng trước.'); }
		if (d.name.length < 2) { return fail('Vui lòng nhập họ tên.', 'name'); }
		if (!/^(0|\+?84)(3|5|7|8|9)\d{8}$/.test(d.phone.replace(/[^0-9+]/g, ''))) { return fail('Số điện thoại không hợp lệ.', 'phone'); }
		if (d.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(d.email)) { return fail('Email không hợp lệ.', 'email'); }
		$btn.addClass('is-loading');
		$.post(mgmcData.ajaxurl, $.extend(d, {
			action: 'mgmc_quote', product: this.meta.product, page: window.location.href,
			data: this.$el.find('input[name=mgmc_data]').val(), addons: this.$el.find('input[name=mgmc_addons]').val() || '[]'
		})).done(function (res) {
			if (res && res.success) {
				$q.find('.mgmc-quote__grid, .mgmc-quote__actions').prop('hidden', true);
				$msg.attr('class', 'mgmc-quote__msg mg-alert mg-alert--ok').text(res.data.message).prop('hidden', false);
				self.$el.trigger('mgmc:quote', [res.data]);
			} else {
				fail((res && res.data && res.data.message) || 'Có lỗi xảy ra, vui lòng thử lại.', res && res.data && res.data.field);
			}
		}).fail(function (xhr) {
			fail((xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) || 'Không kết nối được máy chủ, vui lòng thử lại.');
		}).always(function () { $btn.removeClass('is-loading'); });
	};

	Calc.prototype.print = function () {
		var $root = this.$el;
		$('body').addClass('mgmc-printing'); $root.addClass('mgmc-print-target is-open');
		window.print();
		setTimeout(function () { $('body').removeClass('mgmc-printing'); $root.removeClass('mgmc-print-target'); }, 500);
	};

	$(function () {
		$('.mgmc').each(function () {
			var $s = $(this).find('[data-summary]'); $s.data('intro', $s.text());
			if (!$(this).data('mgmc')) { $(this).data('mgmc', new Calc($(this))); }
		});
	});
})(jQuery);
