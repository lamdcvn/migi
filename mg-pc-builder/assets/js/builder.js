/* global jQuery */
(function ($) {
	'use strict';

	var ICONS = {
		cpu: '<rect x="6" y="6" width="12" height="12" rx="2"/><rect x="9.5" y="9.5" width="5" height="5" rx="1"/><path d="M9 3v3M15 3v3M9 18v3M15 18v3M3 9h3M3 15h3M18 9h3M18 15h3"/>',
		mainboard: '<rect x="3" y="3" width="18" height="18" rx="2"/><rect x="7" y="7" width="5" height="5" rx="1"/><path d="M15 7h2M15 10h2M7 16h10"/>',
		vga: '<rect x="2" y="7" width="20" height="10" rx="2"/><circle cx="9" cy="12" r="2.5"/><circle cx="16" cy="12" r="2.5"/><path d="M5 17v3"/>',
		ram: '<rect x="2" y="7" width="20" height="9" rx="1.5"/><path d="M6 10v3M10 10v3M14 10v3M18 10v3M5 16v2M19 16v2"/>',
		ssd: '<rect x="3" y="6" width="18" height="12" rx="2"/><path d="M7 10h6M7 14h3"/><circle cx="17" cy="12" r="1"/>',
		hdd: '<rect x="4" y="3" width="16" height="18" rx="2"/><circle cx="12" cy="10" r="4"/><path d="M12 10l3 6"/>',
		'case': '<rect x="6" y="2" width="12" height="20" rx="2"/><circle cx="12" cy="14" r="3"/><path d="M9 6h6"/>',
		psu: '<rect x="3" y="6" width="18" height="12" rx="2"/><circle cx="9" cy="12" r="3"/><path d="M16 10h2M16 14h2"/>',
		cooler: '<circle cx="12" cy="12" r="8"/><path d="M12 4c2 3 2 5 0 8-2-3-2-5 0-8zM20 12c-3 2-5 2-8 0 3-2 5-2 8 0zM12 20c-2-3-2-5 0-8 2 3 2 5 0 8zM4 12c3-2 5-2 8 0-3 2-5 2-8 0z"/>',
		fan: '<rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="12" cy="12" r="2"/><path d="M12 10c0-3 1-4 3-4M14 12c3 0 4 1 4 3M12 14c0 3-1 4-3 4M10 12c-3 0-4-1-4-3"/>',
		monitor: '<rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8M12 17v4"/>',
		mouse: '<rect x="6" y="3" width="12" height="18" rx="6"/><path d="M12 7v4"/>',
		keyboard: '<rect x="2" y="6" width="20" height="12" rx="2"/><path d="M6 10h.01M10 10h.01M14 10h.01M18 10h.01M7 14h10"/>',
		headset: '<path d="M4 15v-3a8 8 0 0 1 16 0v3"/><rect x="2" y="14" width="5" height="6" rx="1.5"/><rect x="17" y="14" width="5" height="6" rx="1.5"/>',
		software: '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18M8 13l-2 2 2 2M16 13l2 2-2 2"/>',
		other: '<rect x="4" y="4" width="16" height="16" rx="3"/><path d="M9 12h6M12 9v6"/>',
		x: '<path d="M6 6l12 12M18 6 6 18"/>', search: '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
		reset: '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/>', print: '<path d="M7 9V3h10v6M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2"/><path d="M7 14h10v7H7z"/>',
		share: '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/>',
		download: '<path d="M12 3v12M7 10l5 5 5-5M5 21h14"/>', cart: '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M2 3h3l2.7 12.4a2 2 0 0 0 2 1.6h8.6a2 2 0 0 0 2-1.5L22 7H6"/>',
		check: '<path d="M5 12.5l4.5 4.5L19 7.5"/>', warn: '<path d="M12 3 2 20h20z"/><path d="M12 10v4M12 17h.01"/>', bolt: '<path d="M13 2 4 14h7l-1 8 9-12h-7z"/>',
		filter: '<path d="M3 5h18M6 12h12M10 19h4"/>', chat: '<path d="M21 12a8 8 0 0 1-11.6 7.1L3 21l1.9-6.4A8 8 0 1 1 21 12z"/>', swap: '<path d="M7 7h13l-3-3M17 17H4l3 3"/>'
	};
	function icon(name, size) {
		return '<svg class="mgpc-i" viewBox="0 0 24 24" width="' + (size || 18) + '" height="' + (size || 18) + '" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + (ICONS[name] || ICONS.other) + '</svg>';
	}
	function esc(s) { return String(s === undefined || s === null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

	var ROLE_CODES = { cpu: ['socket', 'igpu', 'ram_cpu'], mainboard: ['socket', 'ram_type', 'ram_slots', 'form'], ram: ['ram_type', 'ram_slots', 'ram_cpu'], vga: ['gpu_length'], 'case': ['form', 'gpu_length'], psu: ['psu'], cooler: ['cooler'] };
	var BUDGETS = ['Dưới 10 triệu', '10 – 20 triệu', '20 – 30 triệu', '30 – 50 triệu', 'Trên 50 triệu'];

	function Builder($root) {
		this.$root = $root;
		this.cfg = $root.data('config');
		this.C = this.cfg.currency;
		this.slots = this.cfg.slots;
		this.slotMap = {};
		var self = this;
		this.slots.forEach(function (s) { self.slotMap[s.key] = s; });
		this.build = {};
		this.result = null;
		this.picker = null;
		this.bind();
		this.renderPresets();
		this.renderToolbar();
		this.render();
		this.restore();
	}

	Builder.prototype.money = function (n) {
		var C = this.C, parts = Math.abs(n).toFixed(C.decimals).split('.');
		parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, C.thousand);
		var num = parts.join(C.dec), s = C.symbol;
		return (n < 0 ? '-' : '') + ({ right: num + s, left_space: s + ' ' + num, right_space: num + ' ' + s }[C.format] || s + num);
	};
	Builder.prototype.api = function (name, data) {
		return $.ajax({ url: this.cfg.endpoint.replace('%%endpoint%%', name), type: 'POST', data: data, dataType: 'json' });
	};
	Builder.prototype.rows = function () {
		var self = this;
		return this.slots.filter(function (s) { return self.build[s.key]; }).map(function (s) {
			return { slot: s.key, id: self.build[s.key].item.id, qty: self.build[s.key].qty };
		});
	};
	Builder.prototype.total = function () {
		var self = this, t = 0;
		Object.keys(this.build).forEach(function (k) { t += self.build[k].item.price * self.build[k].qty; });
		return t;
	};
	Builder.prototype.toast = function (html, ok) {
		var $t = this.$root.find('[data-toast]');
		$t.html(html).attr('class', 'mgpc-toast is-show' + (ok === false ? ' is-error' : ''));
		clearTimeout(this.toastT);
		this.toastT = setTimeout(function () { $t.removeClass('is-show'); }, 4200);
	};

	/* ---------- State ---------- */

	Builder.prototype.save = function () {
		try { window.localStorage.setItem('mgpc_build', JSON.stringify(this.rows())); } catch (e) { /* private mode */ }
	};
	Builder.prototype.restore = function () {
		var rows = null, m = window.location.search.match(/[?&]build=([^&]+)/);
		if (m) {
			rows = decodeURIComponent(m[1]).split('~').map(function (x) { var p = x.split('.'); return { slot: p[0], id: +p[1], qty: +(p[2] || 1) }; });
		} else {
			try { rows = JSON.parse(window.localStorage.getItem('mgpc_build') || 'null'); } catch (e) { rows = null; }
		}
		if (rows && rows.length) { this.load(rows, !!m); } else { this.refresh(); }
	};
	Builder.prototype.load = function (rows, notify) {
		var self = this;
		this.$root.addClass('is-busy');
		this.api('mgpc_check', { build: JSON.stringify(rows) }).done(function (res) {
			if (!res || !res.success) { return; }
			self.build = {};
			res.data.items.forEach(function (r) { self.build[r.slot] = { qty: r.qty, item: r.item }; });
			self.result = res.data;
			self.save(); self.render();
			if (notify) { self.toast(icon('check') + ' Đã tải cấu hình ' + res.data.items.length + ' linh kiện.'); }
		}).always(function () { self.$root.removeClass('is-busy'); });
	};
	Builder.prototype.refresh = function () {
		var self = this, seq = (this.seq = (this.seq || 0) + 1);
		this.save(); this.render();
		this.api('mgpc_check', { build: JSON.stringify(this.rows()) }).done(function (res) {
			if (seq !== self.seq || !res || !res.success) { return; }
			self.result = res.data; self.render();
		});
	};
	Builder.prototype.set = function (slot, item, qty) {
		if (item) { this.build[slot] = { item: item, qty: qty || (this.build[slot] ? this.build[slot].qty : 1) }; } else { delete this.build[slot]; }
		this.refresh();
	};

	/* ---------- Rendering ---------- */

	Builder.prototype.renderPresets = function () {
		var self = this, p = this.cfg.presets || [];
		if (!p.length) { return; }
		var html = '<div class="mgpc-section-title"><h3>Cấu hình gợi ý theo nhu cầu</h3><span>Chọn nhanh rồi tuỳ chỉnh theo ý bạn</span></div><div class="mgpc-presets__track">' + p.map(function (x, i) {
			return '<button type="button" class="mgpc-preset mgpc-preset--' + esc(x.theme) + '" data-preset="' + i + '">' +
				(x.tag ? '<span class="mgpc-preset__tag">' + esc(x.tag) + '</span>' : '') +
				'<span class="mgpc-preset__title">' + esc(x.title) + '</span>' +
				(x.desc ? '<span class="mgpc-preset__desc">' + esc(x.desc) + '</span>' : '') +
				(x.specs.length ? '<span class="mgpc-preset__specs">' + x.specs.map(function (s) { return '<span>' + esc(s) + '</span>'; }).join('') + '</span>' : '') +
				'<span class="mgpc-preset__foot"><strong>' + self.money(x.total) + '</strong><span class="mgpc-preset__cta">Dùng cấu hình →</span></span>' +
				(x.img ? '<img src="' + esc(x.img) + '" alt="" loading="lazy">' : '') + '</button>';
		}).join('') + '</div>';
		this.$root.find('[data-presets]').html(html).prop('hidden', false);
	};

	Builder.prototype.renderToolbar = function () {
		this.$root.find('[data-toolbar]').html(
			'<div class="mgpc-toolbar__title"><h3>Tự chọn linh kiện</h3><span data-progress></span></div>' +
			'<div class="mgpc-toolbar__actions">' +
			'<button type="button" class="mgpc-tool" data-act="reset">' + icon('reset', 16) + '<span>Chọn lại</span></button>' +
			'<button type="button" class="mgpc-tool" data-act="print">' + icon('print', 16) + '<span>Xem &amp; in</span></button>' +
			'<button type="button" class="mgpc-tool" data-act="share">' + icon('share', 16) + '<span>Chia sẻ</span></button>' +
			'<button type="button" class="mgpc-tool" data-act="csv">' + icon('download', 16) + '<span>Tải file</span></button></div>');
	};

	Builder.prototype.slotIssues = function (slot) {
		var codes = ROLE_CODES[slot.role] || [], out = [];
		if (!this.result || !this.build[slot.key]) { return out; }
		this.result.checks.forEach(function (c) { if (c.level !== 'ok' && codes.indexOf(c.code) !== -1) { out.push(c); } });
		return out;
	};

	Builder.prototype.render = function () {
		var self = this, chosen = 0;
		var html = this.slots.map(function (s, i) {
			var b = self.build[s.key];
			if (b) { chosen++; }
			var issues = self.slotIssues(s), level = issues.some(function (c) { return c.level === 'error'; }) ? 'error' : (issues.length ? 'warn' : '');
			var head = '<span class="mgpc-slot__icon">' + icon(s.icon, 22) + '</span><span class="mgpc-slot__label"><span class="mgpc-slot__num">' + (i + 1) + '</span>' + esc(s.label) + (s.required ? '<em title="Bắt buộc">*</em>' : '') + '</span>';
			if (!b) {
				return '<li class="mgpc-slot" data-slot="' + s.key + '"><div class="mgpc-slot__head">' + head + '<button type="button" class="mg-btn mg-btn--sm mgpc-pick" data-pick="' + s.key + '">' + icon('search', 15) + ' Chọn</button></div></li>';
			}
			var it = b.item;
			return '<li class="mgpc-slot is-filled' + (level ? ' is-' + level : '') + '" data-slot="' + s.key + '"><div class="mgpc-slot__head">' + head +
				'<span class="mgpc-slot__tools"><button type="button" class="mgpc-icon-btn" data-pick="' + s.key + '" title="Đổi linh kiện">' + icon('swap', 16) + '</button>' +
				'<button type="button" class="mgpc-icon-btn mgpc-icon-btn--danger" data-remove="' + s.key + '" title="Bỏ chọn">' + icon('x', 16) + '</button></span></div>' +
				'<div class="mgpc-item"><img src="' + esc(it.img) + '" alt="" loading="lazy"><div class="mgpc-item__body">' +
				'<a class="mgpc-item__name" href="' + esc(it.url) + '" target="_blank" rel="noopener">' + esc(it.name) + '</a>' +
				(it.chips.length ? '<div class="mgpc-chips">' + it.chips.map(function (c) { return '<span>' + esc(c) + '</span>'; }).join('') + '</div>' : '') +
				issues.map(function (c) { return '<p class="mgpc-issue mgpc-issue--' + c.level + '">' + icon(c.level === 'error' ? 'x' : 'warn', 14) + esc(c.text) + '</p>'; }).join('') +
				(!it.stock ? '<p class="mgpc-issue mgpc-issue--error">' + icon('x', 14) + 'Sản phẩm tạm hết hàng.</p>' : '') +
				'</div><div class="mgpc-item__side">' +
				(s.multi ? '<div class="mgpc-qty"><button type="button" data-qty="-1" data-slot-qty="' + s.key + '" aria-label="Giảm">−</button><span>' + b.qty + '</span><button type="button" data-qty="1" data-slot-qty="' + s.key + '" aria-label="Tăng">+</button></div>' : '') +
				'<strong class="mgpc-item__price">' + self.money(it.price * b.qty) + '</strong>' +
				(it.regular ? '<del>' + self.money(it.regular * b.qty) + '</del>' : '') + '</div></div></li>';
		}).join('');
		this.$root.find('[data-slots]').html(html);
		this.$root.find('[data-progress]').text(chosen + '/' + this.slots.length + ' nhóm đã chọn');
		this.renderSummary();
	};

	Builder.prototype.renderSummary = function () {
		var r = this.result, total = this.total(), groups = Object.keys(this.build).length, self = this;
		var disc = r && r.discount && r.discount.active && total > 0 ? Math.round(total * r.discount.percent / 100) : 0;
		var html = '<div class="mgpc-sum__head"><span>Tạm tính</span><small>' + groups + ' nhóm linh kiện</small></div>' +
			'<div class="mgpc-sum__total">' + this.money(total - disc) + '</div>';
		if (disc) { html += '<p class="mgpc-sum__disc">' + icon('check', 14) + ' ' + esc(r.discount.label) + ' −' + r.discount.percent + '%: <strong>−' + this.money(disc) + '</strong></p>'; }
		else if (r && r.discount && total > 0) { html += '<p class="mgpc-sum__hint">Chọn từ ' + r.discount.min + ' nhóm để được ' + esc(r.discount.label) + ' −' + r.discount.percent + '%.</p>'; }

		if (r) {
			var hasPower = Object.keys(this.build).some(function (k) { return ['cpu', 'vga', 'psu'].indexOf(self.slotMap[k] && self.slotMap[k].role) !== -1; });
			if (hasPower) {
				var cap = r.psu || r.recommended, pct = cap ? Math.min(100, Math.round(r.draw / cap * 100)) : 0;
				var lvl = !r.psu ? '' : (r.psu < r.draw ? 'is-error' : (r.psu < r.recommended ? 'is-warn' : 'is-ok'));
				html += '<div class="mgpc-power ' + lvl + '"><div class="mgpc-power__row">' + icon('bolt', 16) + '<span>Điện năng ước tính <strong>~' + Math.round(r.draw) + 'W</strong></span></div>' +
					'<div class="mgpc-power__bar"><span style="width:' + pct + '%"></span></div>' +
					'<div class="mgpc-power__note">' + (r.psu ? 'Nguồn ' + Math.round(r.psu) + 'W · dùng ~' + pct + '%' : 'Nên chọn nguồn từ <strong>' + r.recommended + 'W</strong>') + '</div></div>';
			}
		}
		if (r) {
			var checks = r.checks.slice().sort(function (a, b) { var o = { error: 0, warn: 1, ok: 2 }; return o[a.level] - o[b.level]; });
			var errs = checks.filter(function (c) { return c.level === 'error'; }).length;
			if (checks.length || r.missing.length) {
				html += '<div class="mgpc-checks"><div class="mgpc-checks__head ' + (errs ? 'is-error' : 'is-ok') + '">' + icon(errs ? 'warn' : 'check', 16) + (errs ? errs + ' vấn đề tương thích' : 'Tương thích') + '</div><ul>' +
					checks.map(function (c) { return '<li class="is-' + c.level + '">' + icon(c.level === 'ok' ? 'check' : (c.level === 'error' ? 'x' : 'warn'), 14) + '<span>' + esc(c.text) + '</span></li>'; }).join('') +
					(r.missing.length ? '<li class="is-info">' + icon('other', 14) + '<span>Chưa chọn: ' + esc(r.missing.join(', ')) + '</span></li>' : '') + '</ul></div>';
			}
		}
		var empty = !groups;
		html += '<div class="mgpc-sum__actions"><button type="button" class="mg-btn mg-btn--block" data-act="cart"' + (empty ? ' disabled' : '') + '>' + icon('cart', 18) + ' Thêm cả cấu hình vào giỏ</button>' +
			'<button type="button" class="mg-btn mg-btn--ghost mg-btn--block" data-act="buy"' + (empty ? ' disabled' : '') + '>Mua ngay</button>' +
			(window.MGQ ? '<button type="button" class="mg-btn mg-btn--quiet mg-btn--block" data-act="quote"' + (empty ? ' disabled' : '') + '>' + icon('download', 16) + ' Tải báo giá PDF</button>' : '') +
			(this.cfg.consult ? '<button type="button" class="mg-btn mg-btn--quiet mg-btn--block" data-act="consult">' + icon('chat', 16) + ' Nhận tư vấn cấu hình</button>' : '') + '</div>' +
			(this.cfg.hotline ? '<p class="mgpc-sum__hotline">Hotline tư vấn <a href="tel:' + esc(this.cfg.hotline.replace(/[^0-9+]/g, '')) + '">' + esc(this.cfg.hotline) + '</a>' + (this.cfg.zalo ? ' · <a href="' + esc(this.cfg.zalo) + '" target="_blank" rel="noopener">Zalo</a>' : '') + '</p>' : '') +
			(this.cfg.note ? '<p class="mgpc-sum__note">' + esc(this.cfg.note) + '</p>' : '');
		this.$root.find('[data-summary]').html(html);
		this.$root.find('[data-mobilebar]').html('<div><small>Tạm tính · ' + groups + ' nhóm</small><strong>' + this.money(total - disc) + '</strong></div><button type="button" class="mg-btn mg-btn--sm" data-act="cart"' + (empty ? ' disabled' : '') + '>' + icon('cart', 16) + ' Thêm vào giỏ</button>').toggleClass('is-show', !empty);
	};

	/* ---------- Picker drawer ---------- */

	Builder.prototype.openPicker = function (key, $from) {
		var s = this.slotMap[key];
		this.picker = { slot: key, q: '', sort: '', filters: {}, min: '', max: '', compat: this.cfg.compat, page: 1, items: [], facets: [], from: $from };
		var $d = this.$root.find('[data-drawer]');
		$d.html('<div class="mgpc-drawer__backdrop" data-close></div><div class="mgpc-drawer__panel mg-ui" role="dialog" aria-modal="true" aria-label="Chọn ' + esc(s.label) + '">' +
			'<header class="mgpc-drawer__head"><span class="mgpc-slot__icon">' + icon(s.icon, 20) + '</span><h3>Chọn ' + esc(s.label) + '</h3><button type="button" class="mgpc-icon-btn" data-close aria-label="Đóng">' + icon('x', 18) + '</button></header>' +
			'<div class="mgpc-drawer__bar"><label class="mgpc-search">' + icon('search', 16) + '<input type="search" class="mg-input" data-q placeholder="Tìm theo tên, mã sản phẩm…" autocomplete="off"></label>' +
			'<div class="mgpc-drawer__opts"><div class="mg-seg mg-seg--chips mgpc-sort">' + [['', 'Phổ biến'], ['price_asc', 'Giá thấp → cao'], ['price_desc', 'Giá cao → thấp']].map(function (o, i) {
				return '<label><input type="radio" name="mgpc_sort" value="' + o[0] + '"' + (i ? '' : ' checked') + ' data-sort><span>' + o[1] + '</span></label>';
			}).join('') + '</div>' +
			'<label class="mgpc-switch"><input type="checkbox" data-compat' + (this.picker.compat ? ' checked' : '') + '><span></span>Chỉ hiện linh kiện tương thích</label>' +
			'<button type="button" class="mgpc-filter-toggle" data-filter-toggle>' + icon('filter', 16) + ' Bộ lọc <span data-filter-count></span></button></div></div>' +
			'<div class="mgpc-drawer__body"><aside class="mgpc-filters" data-filters></aside><div class="mgpc-results"><div class="mgpc-results__meta" data-meta></div><div class="mgpc-grid" data-grid></div><div class="mgpc-more" data-more></div></div></div></div>');
		$d.prop('hidden', false);
		$('body').addClass('mgpc-lock');
		requestAnimationFrame(function () { $d.addClass('is-open'); });
		$d.find('[data-q]').trigger('focus');
		this.fetch(false);
	};

	Builder.prototype.closePicker = function () {
		var $d = this.$root.find('[data-drawer]'), from = this.picker && this.picker.from;
		$d.removeClass('is-open');
		$('body').removeClass('mgpc-lock');
		setTimeout(function () { $d.prop('hidden', true).empty(); }, 200);
		this.picker = null;
		if (from && from.length) { from.trigger('focus'); }
	};

	Builder.prototype.fetch = function (append) {
		var self = this, pk = this.picker;
		if (!pk) { return; }
		var $d = this.$root.find('[data-drawer]');
		if (!append) { pk.page = 1; $d.find('[data-grid]').html(this.skeleton()); $d.find('[data-more]').empty(); }
		var seq = (pk.seq = (pk.seq || 0) + 1);
		this.api('mgpc_products', { slot: pk.slot, q: pk.q, sort: pk.sort, page: pk.page, min: pk.min, max: pk.max, compat: pk.compat ? 1 : 0, filters: JSON.stringify(pk.filters), build: JSON.stringify(this.rows()) })
			.done(function (res) {
				if (!self.picker || seq !== pk.seq || !res || !res.success) { return; }
				var d = res.data;
				pk.items = append ? pk.items.concat(d.items) : d.items;
				if (d.facets) { pk.facets = d.facets; self.renderFilters(); }
				$d.find('[data-meta]').html('<strong>' + d.total + '</strong> sản phẩm' + (pk.compat && self.rows().length ? ' tương thích' : ''));
				$d.find('[data-grid]').html(pk.items.length ? pk.items.map(function (it, i) { return self.card(it, i); }).join('') : self.emptyState());
				$d.find('[data-more]').html(d.more ? '<button type="button" class="mg-btn mg-btn--ghost mg-btn--sm" data-load-more>Xem thêm sản phẩm</button>' : '');
			}).fail(function () {
				$d.find('[data-grid]').html('<div class="mgpc-empty"><strong>Không tải được danh sách.</strong><span>Vui lòng thử lại.</span></div>');
			});
	};

	Builder.prototype.skeleton = function () {
		return new Array(6).join('<div class="mgpc-card is-skeleton"><span></span><i></i><i></i><i></i></div>');
	};
	Builder.prototype.emptyState = function () {
		return '<div class="mgpc-empty">' + icon('search', 28) + '<strong>Không có sản phẩm phù hợp</strong><span>' + (this.picker.compat ? 'Thử tắt "Chỉ hiện linh kiện tương thích" hoặc xoá bớt bộ lọc.' : 'Thử xoá bớt bộ lọc hoặc từ khoá.') + '</span></div>';
	};

	Builder.prototype.card = function (it, i) {
		var cur = this.build[this.picker.slot], selected = cur && cur.item.id === it.id, bad = it.conflicts && it.conflicts.length;
		return '<article class="mgpc-card' + (bad ? ' is-conflict' : '') + (selected ? ' is-selected' : '') + '">' +
			(it.sale ? '<span class="mgpc-card__sale">-' + it.sale + '%</span>' : '') + (selected ? '<span class="mgpc-card__current">Đang chọn</span>' : '') +
			'<div class="mgpc-card__img"><img src="' + esc(it.img) + '" alt="" loading="lazy"></div>' +
			'<h4 class="mgpc-card__name" title="' + esc(it.name) + '">' + esc(it.name) + '</h4>' +
			(it.chips.length ? '<div class="mgpc-chips">' + it.chips.map(function (c) { return '<span>' + esc(c) + '</span>'; }).join('') + '</div>' : '') +
			(bad ? '<p class="mgpc-issue mgpc-issue--error">' + icon('x', 14) + esc(it.conflicts[0]) + '</p>' : '') +
			'<div class="mgpc-card__price"><strong>' + this.money(it.price) + '</strong>' + (it.regular ? '<del>' + this.money(it.regular) + '</del>' : '') + '</div>' +
			'<div class="mgpc-card__actions">' + (it.stock ? '<button type="button" class="mg-btn mg-btn--sm mg-btn--block" data-choose="' + i + '">' + (selected ? 'Giữ lựa chọn' : 'Chọn') + '</button>' : '<button type="button" class="mg-btn mg-btn--sm mg-btn--block" disabled>Hết hàng</button>') +
			'<a href="' + esc(it.url) + '" target="_blank" rel="noopener" class="mgpc-card__link">Xem chi tiết</a></div></article>';
	};

	Builder.prototype.renderFilters = function () {
		var pk = this.picker, $f = this.$root.find('[data-filters]');
		var html = '<div class="mgpc-filters__head"><strong>Bộ lọc</strong><button type="button" class="mgpc-link" data-clear-filters>Xoá lọc</button></div>';
		html += pk.facets.map(function (f) {
			var sel = pk.filters[f.tax] || [], many = f.terms.length > 8;
			return '<fieldset class="mgpc-facet' + (many ? ' is-collapsed' : '') + '"><legend>' + esc(f.label) + '</legend><div class="mgpc-facet__terms">' + f.terms.map(function (t) {
				return '<label class="mgpc-check"><input type="checkbox" data-facet="' + esc(f.tax) + '" value="' + esc(t.slug) + '"' + (sel.indexOf(t.slug) !== -1 ? ' checked' : '') + '><span></span>' + esc(t.name) + '<small>' + t.count + '</small></label>';
			}).join('') + '</div>' + (many ? '<button type="button" class="mgpc-link" data-more-terms>Xem thêm ' + (f.terms.length - 8) + '</button>' : '') + '</fieldset>';
		}).join('');
		html += '<fieldset class="mgpc-facet"><legend>Khoảng giá</legend><div class="mgpc-price-range"><input class="mg-input" type="text" inputmode="numeric" data-min placeholder="Từ" value="' + esc(pk.min) + '"><span>–</span><input class="mg-input" type="text" inputmode="numeric" data-max placeholder="Đến" value="' + esc(pk.max) + '"></div>' +
			'<div class="mgpc-price-quick">' + [[0, 3e6, 'Dưới 3tr'], [3e6, 8e6, '3–8tr'], [8e6, 15e6, '8–15tr'], [15e6, 0, 'Trên 15tr']].map(function (r) {
				return '<button type="button" data-range="' + r[0] + ',' + r[1] + '">' + r[2] + '</button>';
			}).join('') + '</div></fieldset>';
		$f.html(html);
		var n = Object.keys(pk.filters).reduce(function (s, k) { return s + pk.filters[k].length; }, 0) + (pk.min || pk.max ? 1 : 0);
		this.$root.find('[data-filter-count]').text(n ? '(' + n + ')' : '');
	};

	/* ---------- Actions ---------- */

	Builder.prototype.bind = function () {
		var self = this, $r = this.$root, timer;
		$r.on('click', '[data-pick]', function () { self.openPicker($(this).data('pick'), $(this)); });
		$r.on('click', '[data-remove]', function () { self.set($(this).data('remove'), null); });
		$r.on('click', '[data-slot-qty]', function () {
			var k = $(this).data('slot-qty'), b = self.build[k];
			if (!b) { return; }
			var q = Math.max(1, Math.min(16, b.qty + (+$(this).data('qty'))));
			if (b.item.max > 0) { q = Math.min(q, b.item.max); }
			if (q !== b.qty) { b.qty = q; self.refresh(); }
		});
		$r.on('click', '[data-preset]', function () {
			var p = self.cfg.presets[+$(this).data('preset')];
			if (Object.keys(self.build).length && !window.confirm('Thay cấu hình hiện tại bằng "' + p.title + '"?')) { return; }
			self.load(p.build, true);
			$('html, body').animate({ scrollTop: self.$root.find('.mgpc-layout').offset().top - 90 }, 300);
		});
		$r.on('click', '[data-act]', function () { self.action($(this).data('act'), $(this)); });
		// Drawer.
		$r.on('click', '[data-close]', function () { self.closePicker(); });
		$(document).on('keydown', function (e) {
			if (e.key === 'Escape') { if (self.picker) { self.closePicker(); } else { self.closeModal(); } }
		});
		$r.on('input', '[data-q]', function () {
			var v = this.value;
			clearTimeout(timer);
			timer = setTimeout(function () { if (self.picker) { self.picker.q = v; self.fetch(false); } }, 300);
		});
		$r.on('change', '[data-sort]', function () { self.picker.sort = this.value; self.fetch(false); });
		$r.on('change', '[data-compat]', function () { self.picker.compat = this.checked; self.fetch(false); });
		$r.on('change', '[data-facet]', function () {
			var tax = $(this).data('facet'), f = self.picker.filters[tax] || [];
			f = this.checked ? f.concat([this.value]) : f.filter(function (x) { return x !== this.value; }, this);
			if (f.length) { self.picker.filters[tax] = f; } else { delete self.picker.filters[tax]; }
			self.renderFilters(); self.fetch(false);
		});
		$r.on('click', '[data-clear-filters]', function () { self.picker.filters = {}; self.picker.min = self.picker.max = ''; self.renderFilters(); self.fetch(false); });
		$r.on('click', '[data-more-terms]', function () { $(this).closest('.mgpc-facet').removeClass('is-collapsed'); $(this).remove(); });
		$r.on('change', '[data-min], [data-max]', function () {
			self.picker.min = String($r.find('[data-min]').val()).replace(/\D/g, '');
			self.picker.max = String($r.find('[data-max]').val()).replace(/\D/g, '');
			self.renderFilters(); self.fetch(false);
		});
		$r.on('click', '[data-range]', function () {
			var p = String($(this).data('range')).split(',');
			self.picker.min = +p[0] ? p[0] : ''; self.picker.max = +p[1] ? p[1] : '';
			self.renderFilters(); self.fetch(false);
		});
		$r.on('click', '[data-filter-toggle]', function () { $r.find('.mgpc-drawer__body').toggleClass('show-filters'); });
		$r.on('click', '[data-load-more]', function () { self.picker.page++; $(this).addClass('is-loading'); self.fetch(true); });
		$r.on('click', '[data-choose]', function () {
			var it = self.picker.items[+$(this).data('choose')], key = self.picker.slot, label = self.slotMap[key].label;
			self.closePicker();
			self.set(key, it);
			self.toast(icon('check') + ' Đã chọn ' + esc(label) + ': ' + esc(it.name));
			var next = self.slots.filter(function (s) { return s.required && !self.build[s.key]; })[0];
			if (next) { $r.find('[data-slot="' + next.key + '"]').addClass('is-next'); setTimeout(function () { $r.find('.is-next').removeClass('is-next'); }, 1800); }
		});
		// Consult modal.
		$r.on('click', '[data-modal-close]', function () { self.closeModal(); });
		$r.on('submit', '[data-consult-form]', function (e) { e.preventDefault(); self.sendConsult($(this)); });
	};

	Builder.prototype.action = function (act, $btn) {
		var self = this;
		if (act === 'reset') {
			if (Object.keys(this.build).length && window.confirm('Xoá toàn bộ linh kiện đã chọn?')) { this.build = {}; this.refresh(); }
		} else if (act === 'share') {
			if (!this.rows().length) { return this.toast('Hãy chọn ít nhất một linh kiện.', false); }
			var url = window.location.href.split('?')[0].split('#')[0] + '?build=' + encodeURIComponent(this.rows().map(function (r) { return r.slot + '.' + r.id + '.' + r.qty; }).join('~'));
			var done = function () { self.toast(icon('check') + ' Đã sao chép link cấu hình.'); };
			if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(url).then(done, function () { window.prompt('Sao chép link cấu hình:', url); }); } else { window.prompt('Sao chép link cấu hình:', url); }
		} else if (act === 'csv') {
			this.downloadCsv();
		} else if (act === 'print') {
			this.print();
		} else if (act === 'cart' || act === 'buy') {
			this.addToCart(act === 'buy', $btn);
		} else if (act === 'consult') {
			this.openConsult();
		} else if (act === 'quote' && window.MGQ) {
			var items = this.slots.filter(function (s) { return self.build[s.key]; }).map(function (s) { return { id: self.build[s.key].item.id, qty: self.build[s.key].qty, unit: 'cái', note: s.label }; });
			$btn.addClass('is-loading');
			window.MGQ.standard(items, { source: 'mgpc', pdf: true, note: 'Báo giá cấu hình PC (' + items.length + ' linh kiện)' })
				.catch(function (e) { self.toast(esc(e.message), false); })
				.then(function () { $btn.removeClass('is-loading'); });
		}
	};

	Builder.prototype.lines = function () {
		var self = this;
		return this.slots.filter(function (s) { return self.build[s.key]; }).map(function (s) {
			var b = self.build[s.key];
			return { slot: s.label, name: b.item.name, sku: b.item.sku, qty: b.qty, price: b.item.price, total: b.item.price * b.qty };
		});
	};

	Builder.prototype.downloadCsv = function () {
		var lines = this.lines();
		if (!lines.length) { return this.toast('Hãy chọn ít nhất một linh kiện.', false); }
		var q = function (v) { return '"' + String(v).replace(/"/g, '""') + '"'; };
		var csv = [['Nhóm', 'Linh kiện', 'Mã', 'Số lượng', 'Đơn giá', 'Thành tiền'].map(q).join(',')].concat(lines.map(function (l) {
			return [l.slot, l.name, l.sku, l.qty, Math.round(l.price), Math.round(l.total)].map(q).join(',');
		})).concat([['', 'Tổng cộng', '', '', '', Math.round(this.total())].map(q).join(',')]).join('\r\n');
		var a = document.createElement('a');
		a.href = URL.createObjectURL(new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8' }));
		a.download = 'cau-hinh-pc-' + new Date().toISOString().slice(0, 10) + '.csv';
		document.body.appendChild(a); a.click(); a.remove();
	};

	Builder.prototype.print = function () {
		var self = this, lines = this.lines();
		if (!lines.length) { return this.toast('Hãy chọn ít nhất một linh kiện.', false); }
		var checks = this.result ? this.result.checks.filter(function (c) { return c.level !== 'ok'; }) : [];
		var html = '<div class="mgpc-print"><h1>' + esc(this.cfg.site) + ' – Cấu hình PC</h1><p>' + new Date().toLocaleString('vi-VN') + '</p><table><thead><tr><th>#</th><th>Nhóm</th><th>Linh kiện</th><th>SL</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead><tbody>' +
			lines.map(function (l, i) { return '<tr><td>' + (i + 1) + '</td><td>' + esc(l.slot) + '</td><td>' + esc(l.name) + (l.sku ? '<br><small>' + esc(l.sku) + '</small>' : '') + '</td><td>' + l.qty + '</td><td>' + self.money(l.price) + '</td><td>' + self.money(l.total) + '</td></tr>'; }).join('') +
			'</tbody><tfoot><tr><td colspan="5">Tổng cộng</td><td>' + this.money(this.total()) + '</td></tr></tfoot></table>' +
			(this.result ? '<p>Điện năng ước tính ~' + Math.round(this.result.draw) + 'W' + (this.result.psu ? ' · Nguồn ' + Math.round(this.result.psu) + 'W' : '') + '</p>' : '') +
			checks.map(function (c) { return '<p>⚠ ' + esc(c.text) + '</p>'; }).join('') + (this.cfg.hotline ? '<p>Hotline: ' + esc(this.cfg.hotline) + '</p>' : '') + '</div>';
		$('.mgpc-print').remove();
		$('body').append(html).addClass('mgpc-printing');
		window.print();
		setTimeout(function () { $('body').removeClass('mgpc-printing'); $('.mgpc-print').remove(); }, 800);
	};

	Builder.prototype.addToCart = function (checkout, $btn) {
		var self = this;
		if (!this.rows().length) { return; }
		var errs = this.result ? this.result.checks.filter(function (c) { return c.level === 'error'; }) : [];
		if (errs.length && !window.confirm('Cấu hình còn ' + errs.length + ' vấn đề tương thích:\n- ' + errs.map(function (c) { return c.text; }).join('\n- ') + '\n\nVẫn thêm vào giỏ?')) { return; }
		$btn.addClass('is-loading');
		this.api('mgpc_cart', { build: JSON.stringify(this.rows()) }).done(function (res) {
			if (!res || !res.success) { return self.toast((res && res.data && res.data.message) || 'Không thêm được vào giỏ.', false); }
			$(document.body).trigger('wc_fragment_refresh').trigger('added_to_cart');
			if (checkout) { window.location.href = res.data.checkout; return; }
			self.toast(icon('check') + ' Đã thêm ' + res.data.added + ' linh kiện vào giỏ (mã ' + esc(res.data.code) + '). <a href="' + esc(res.data.cart) + '">Xem giỏ hàng →</a>' + (res.data.failed.length ? '<br>Không thêm được: ' + esc(res.data.failed.join(', ')) : ''), !res.data.failed.length);
		}).fail(function () { self.toast('Không kết nối được máy chủ, vui lòng thử lại.', false); })
			.always(function () { $btn.removeClass('is-loading'); });
	};

	Builder.prototype.openConsult = function () {
		var $m = this.$root.find('[data-modal]'), n = this.rows().length;
		$m.html('<div class="mgpc-modal__backdrop" data-modal-close></div><form class="mgpc-modal__panel mg-card" data-consult-form role="dialog" aria-modal="true" aria-label="Nhận tư vấn cấu hình">' +
			'<button type="button" class="mgpc-icon-btn mgpc-modal__x" data-modal-close aria-label="Đóng">' + icon('x', 18) + '</button>' +
			'<span class="mg-eyebrow">Tư vấn miễn phí</span><h3>Nhận tư vấn cấu hình</h3><p>' + (n ? 'Kỹ thuật viên sẽ xem cấu hình ' + n + ' linh kiện bạn đã chọn và gọi lại tư vấn.' : 'Cho chúng tôi biết nhu cầu, kỹ thuật viên sẽ đề xuất cấu hình phù hợp.') + '</p>' +
			'<div class="mgpc-form"><input class="mg-input" name="name" placeholder="Họ và tên *" autocomplete="name"><input class="mg-input" name="phone" type="tel" placeholder="Số điện thoại *" autocomplete="tel">' +
			'<select class="mg-input" name="budget"><option value="">Ngân sách dự kiến</option>' + BUDGETS.map(function (b) { return '<option>' + b + '</option>'; }).join('') + '</select>' +
			'<textarea class="mg-input" name="note" placeholder="Nhu cầu: chơi game gì, làm đồ hoạ phần mềm nào…"></textarea><input type="text" name="website" class="mgpc-hp" tabindex="-1" autocomplete="off" aria-hidden="true"></div>' +
			'<div class="mgpc-form__msg" data-msg hidden></div><button type="submit" class="mg-btn mg-btn--block">Gửi yêu cầu tư vấn</button></form>').prop('hidden', false);
		$m.find('[name=name]').trigger('focus');
	};
	Builder.prototype.closeModal = function () { this.$root.find('[data-modal]').prop('hidden', true).empty(); };

	Builder.prototype.sendConsult = function ($f) {
		var self = this, d = {}, $msg = $f.find('[data-msg]'), $btn = $f.find('[type=submit]');
		$f.serializeArray().forEach(function (x) { d[x.name] = $.trim(x.value); });
		var fail = function (t, field) { $msg.attr('class', 'mgpc-form__msg mg-alert mg-alert--error').text(t).prop('hidden', false); if (field) { $f.find('[name=' + field + ']').addClass('is-invalid').trigger('focus'); } };
		$f.find('.is-invalid').removeClass('is-invalid');
		if (d.name.length < 2) { return fail('Vui lòng nhập họ tên.', 'name'); }
		if (!/^(0|\+?84)(3|5|7|8|9)\d{8}$/.test(d.phone.replace(/[^0-9+]/g, ''))) { return fail('Số điện thoại không hợp lệ.', 'phone'); }
		$btn.addClass('is-loading');
		this.api('mgpc_consult', $.extend(d, { build: JSON.stringify(this.rows()), page: window.location.href })).done(function (res) {
			if (res && res.success) {
				$f.find('.mgpc-form, [type=submit]').remove();
				$msg.attr('class', 'mgpc-form__msg mg-alert mg-alert--ok').text(res.data.message).prop('hidden', false);
			} else { fail((res && res.data && res.data.message) || 'Có lỗi xảy ra.', res && res.data && res.data.field); }
		}).fail(function (x) { fail((x.responseJSON && x.responseJSON.data && x.responseJSON.data.message) || 'Không kết nối được máy chủ.'); })
			.always(function () { $btn.removeClass('is-loading'); });
	};

	$(function () { $('.mgpc').each(function () { if (!$(this).data('mgpc')) { $(this).data('mgpc', new Builder($(this))); } }); });
})(jQuery);
