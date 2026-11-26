/* global vtbdData, jQuery */
(function ($) {
	'use strict';
	var C = vtbdData.currency, T = vtbdData.i18n;

	function money(n) {
		var neg = n < 0; n = Math.abs(n);
		var parts = n.toFixed(C.decimals).split('.');
		parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, C.thousand);
		var num = parts.join(C.dec), s = C.symbol, out;
		switch (C.format) {
			case 'right': out = num + s; break;
			case 'left_space': out = s + ' ' + num; break;
			case 'right_space': out = num + ' ' + s; break;
			default: out = s + num;
		}
		return '<span class="woocommerce-Price-amount amount">' + (neg ? '-' : '') + out + '</span>';
	}
	function pct(d) { return String(+d.toFixed(2)).replace('.', C.dec); }

	function Box($el) {
		this.$el = $el;
		this.tiers = $el.data('tiers') || [];
		this.price = parseFloat($el.data('price')) || 0;
		this.max = parseInt($el.data('max'), 10);
		this.$form = $el.closest('form.cart');
		this.$qty = this.$form.find('input.qty, input[name="quantity"]').first();
		this.$scope = $el.closest('.product, .product-container, .type-product');
		if (!this.$scope.length) { this.$scope = $(document.body); }
		this.$note = this.$scope.find('.vtbd-unit-note').first();
		this.bind();
		this.render();
		this.select(parseInt($el.data('default'), 10) || 0, true);
	}

	Box.prototype.bind = function () {
		var self = this;
		this.$el.on('change', '.vtbd-radio', function () {
			self.select(parseInt(this.value, 10), true);
		});
		this.$form.on('change input', 'input.qty, input[name="quantity"]', function () {
			self.syncFromQty();
		});
		// Flatsome / theme +/- buttons may update the value without firing change in time.
		this.$form.on('click', '.plus, .minus, .ux-quantity__button', function () {
			setTimeout(function () { self.syncFromQty(); }, 30);
		});
		var $vform = this.$el.closest('.variations_form');
		if ($vform.length) {
			$vform.on('found_variation', function (e, v) {
				if (v && v.display_price !== undefined) { self.price = parseFloat(v.display_price); }
				self.max = v && v.max_qty ? parseInt(v.max_qty, 10) : -1;
				self.render(); self.syncFromQty();
			});
		}
	};

	Box.prototype.tierIndexFor = function (q) {
		var idx = -1;
		for (var i = 0; i < this.tiers.length; i++) { if (q >= this.tiers[i].qty) { idx = i; } }
		return idx;
	};

	Box.prototype.render = function () {
		var self = this;
		this.$el.find('.vtbd-option').each(function () {
			var i = parseInt($(this).data('index'), 10), t = self.tiers[i];
			if (!t) { return; }
			var unit = self.price * (1 - t.discount / 100);
			$(this).find('[data-total]').html(money(unit * t.qty));
			$(this).find('[data-orig]').html(money(self.price * t.qty));
			$(this).find('[data-each]').html(T.each.replace('%s', money(unit)));
			var out = self.max > 0 && t.qty > self.max;
			$(this).toggleClass('is-disabled', out).find('.vtbd-radio').prop('disabled', out);
		});
	};

	Box.prototype.select = function (i, setQty) {
		var t = this.tiers[i];
		if (!t) { return; }
		var $opt = this.$el.find('.vtbd-option[data-index="' + i + '"]');
		if ($opt.hasClass('is-disabled')) { return; }
		this.$el.find('.vtbd-option').removeClass('is-selected');
		$opt.addClass('is-selected').find('.vtbd-radio').prop('checked', true);
		if (setQty && this.$qty.length && parseInt(this.$qty.val(), 10) !== t.qty) {
			this.$qty.val(t.qty);
			this.lock = true;
			this.$qty.trigger('change');
			this.lock = false;
		}
		this.updateNote(t);
	};

	Box.prototype.syncFromQty = function () {
		if (this.lock || !this.$qty.length) { return; }
		var q = parseInt(this.$qty.val(), 10) || 1, i = this.tierIndexFor(q);
		this.$el.find('.vtbd-option').removeClass('is-selected');
		this.$el.find('.vtbd-radio').prop('checked', false);
		if (i >= 0) {
			this.$el.find('.vtbd-option[data-index="' + i + '"]').addClass('is-selected').find('.vtbd-radio').prop('checked', true);
			this.updateNote(this.tiers[i]);
		} else {
			this.updateNote(null);
		}
	};

	Box.prototype.updateNote = function (t) {
		if (!this.$note.length) { return; }
		if (!t || !t.discount) { this.$note.hide(); return; }
		var unit = this.price * (1 - t.discount / 100);
		this.$note.html(T.eachOff.replace('%1$s', money(unit)).replace('%2$s', pct(t.discount))).show();
	};

	function boot() {
		$('.vtbd').each(function () {
			var $el = $(this);
			if (!$el.data('vtbdBox')) { $el.data('vtbdBox', new Box($el)); }
		});
	}
	$(boot);
	$(document.body).on('quick-view-opened flatsome-quickview-ready', boot);
})(jQuery);
