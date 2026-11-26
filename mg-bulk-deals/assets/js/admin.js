jQuery(function ($) {
	$(document).on('change', '#mgbd_mode', function () {
		$('.mgbd-custom-wrap').toggle($(this).val() === 'custom');
	});
	$(document).on('click', '.mgbd-add-row', function () {
		var key = 'n' + Date.now();
		var html = $('#tmpl-mgbd-row').html().split('{{KEY}}').join(key);
		$(this).closest('table').find('tbody').append(html);
	});
	$(document).on('click', '.mgbd-remove-row', function () {
		$(this).closest('tr').remove();
	});
});
