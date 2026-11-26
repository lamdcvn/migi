jQuery(function ($) {
	$(document).on('change', '#vtbd_mode', function () {
		$('.vtbd-custom-wrap').toggle($(this).val() === 'custom');
	});
	$(document).on('click', '.vtbd-add-row', function () {
		var key = 'n' + Date.now();
		var html = $('#tmpl-vtbd-row').html().split('{{KEY}}').join(key);
		$(this).closest('table').find('tbody').append(html);
	});
	$(document).on('click', '.vtbd-remove-row', function () {
		$(this).closest('tr').remove();
	});
});
