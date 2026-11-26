/* global vtscData, jQuery */
(function ($) {
	'use strict';

	function fmt(n) { return String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
	function parse(v) { return parseInt(String(v).replace(/\D/g, ''), 10) || 0; }

	function billToKwh(cfg, bill, customer) {
		var net = bill / (1 + cfg.vat / 100), kwh = 0, i, size, price;
		if (customer === 'business') { return cfg.biz ? net / cfg.biz : 0; }
		for (i = 0; i < cfg.tiers.length; i++) {
			size = +cfg.tiers[i][0]; price = +cfg.tiers[i][1];
			if (!size || net <= size * price) { return kwh + net / price; }
			kwh += size; net -= size * price;
		}
		return kwh + net / +cfg.tiers[cfg.tiers.length - 1][1];
	}

	function Calc($root) {
		var self = this;
		this.$root = $root;
		this.$form = $root.find('.vtsc-form');
		this.requireLead = $root.data('require-lead') === 1 || $root.data('require-lead') === '1';
		try { this.cfg = JSON.parse($root.find('.vtsc-tiers').text()); } catch (e) { this.cfg = null; }

		var $bill = this.$form.find('[name=bill]'), $range = this.$form.find('.vtsc-bill-range');
		$bill.on('input', function () {
			var v = parse(this.value);
			this.value = v ? fmt(v) : '';
			$range.val(v);
			self.hint();
		});
		$range.on('input', function () { $bill.val(fmt(this.value)); self.hint(); });
		this.$form.on('change', '[name=customer]', function () { self.hint(); });
		this.$form.find('[name=day]').on('input', function () { self.$form.find('[data-day-out]').text(this.value); });
		this.$form.on('click', '.vtsc-next', function () { if (self.validate(1)) { self.go(2); } });
		this.$form.on('click', '.vtsc-back', function () { self.go(1); });
		this.$form.on('click', '.vtsc-submit', function () { if (self.validate(2)) { self.send(false); } });
		this.$form.on('click', '.vtsc-skip', function () { self.send(true); });
		this.$form.on('click', '.vtsc-restart', function () { self.go(1); });
		this.$form.on('click', '.vtsc-print', function () { self.print(); });
		this.$form.on('submit', function (e) { e.preventDefault(); });
		this.$form.on('input change', 'input', function () { $(this).removeClass('is-invalid'); self.error(''); });
		this.hint();
	}

	Calc.prototype.hint = function () {
		if (!this.cfg) { return; }
		var bill = parse(this.$form.find('[name=bill]').val());
		var customer = this.$form.find('[name=customer]:checked').val() || this.$form.find('[name=customer]').val();
		var kwh = billToKwh(this.cfg, bill, customer);
		this.$form.find('[data-kwh-hint]').text(bill ? '≈ ' + fmt(kwh) + ' kWh/tháng (' + (kwh / 30).toFixed(1).replace('.', ',') + ' kWh/ngày)' : '');
	};

	Calc.prototype.go = function (step) {
		this.$form.find('.vtsc-step').removeClass('is-active');
		this.$form.find('.vtsc-step-' + step).addClass('is-active');
		this.$root.find('.vtsc-steps li').each(function (i) {
			$(this).toggleClass('is-active', i === step - 1).toggleClass('is-done', i < step - 1);
		});
		this.error('');
		var top = this.$root.offset().top - 80;
		if ($(window).scrollTop() > top) { $('html, body').animate({ scrollTop: top }, 250); }
	};

	Calc.prototype.error = function (msg, field) {
		var $e = this.$form.find('.vtsc-error');
		$e.text(msg).prop('hidden', !msg);
		if (field) { this.$form.find('[name=' + field + ']').addClass('is-invalid').trigger('focus'); }
	};

	Calc.prototype.validate = function (step) {
		var f = this.$form;
		if (step === 1) {
			if (parse(f.find('[name=bill]').val()) < 50000) { this.error('Vui lòng nhập hoá đơn tiền điện (tối thiểu 50.000đ).', 'bill'); return false; }
			if (!(parseFloat(f.find('[name=area]').val()) > 0)) { this.error('Vui lòng nhập diện tích mái nhà.', 'area'); return false; }
			return true;
		}
		if ($.trim(f.find('[name=name]').val()).length < 2) { this.error('Vui lòng nhập họ tên.', 'name'); return false; }
		var phone = String(f.find('[name=phone]').val()).replace(/[^0-9+]/g, '');
		if (!/^(0|\+?84)(3|5|7|8|9)\d{8}$/.test(phone)) { this.error('Số điện thoại không hợp lệ.', 'phone'); return false; }
		var $email = f.find('[name=email]'), email = $.trim($email.val());
		if ((email || $email.prop('required')) && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { this.error('Email không hợp lệ.', 'email'); return false; }
		return true;
	};

	Calc.prototype.send = function (skip) {
		var self = this, $btn = this.$form.find(skip ? '.vtsc-skip' : '.vtsc-submit');
		var data = this.$form.serializeArray();
		data.push({ name: 'action', value: 'vtsc_calc' }, { name: 'page', value: window.location.href });
		if (skip) { data.push({ name: 'skip', value: 1 }); }
		$btn.prop('disabled', true).addClass('is-loading');
		$.post(vtscData.ajaxurl, $.param(data))
			.done(function (res) {
				if (res && res.success) {
					self.$form.find('.vtsc-result').html(res.data.html);
					self.go(3);
					self.$root.trigger('vtsc:result', [res.data]);
				} else {
					var d = (res && res.data) || {};
					var field = d.field;
					if (field === 'bill' || field === 'area') { self.go(1); }
					self.error(d.message || 'Có lỗi xảy ra, vui lòng thử lại.', field);
				}
			})
			.fail(function (xhr) {
				var d = xhr.responseJSON && xhr.responseJSON.data;
				self.error((d && d.message) || 'Không kết nối được máy chủ, vui lòng thử lại.');
			})
			.always(function () { $btn.prop('disabled', false).removeClass('is-loading'); });
	};

	Calc.prototype.print = function () {
		$('body').addClass('vtsc-printing');
		this.$root.addClass('vtsc-print-target');
		window.print();
		var $root = this.$root;
		setTimeout(function () { $('body').removeClass('vtsc-printing'); $root.removeClass('vtsc-print-target'); }, 500);
	};

	$(function () {
		$('.vtsc').each(function () { if (!$(this).data('vtsc')) { $(this).data('vtsc', new Calc($(this))); } });
	});
})(jQuery);
