jQuery(function ($) {
	$('.vtsc-tabs a').on('click', function (e) {
		e.preventDefault();
		$('.vtsc-tabs a').removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		$('.vtsc-tab').prop('hidden', true);
		$($(this).attr('href')).prop('hidden', false);
	});
	$(document).on('click', '.vtsc-add', function () {
		var $body = $(this).closest('table').find('tbody'), $last = $body.find('tr').last();
		var $row = $last.clone(), idx = Date.now();
		$row.find('input').each(function () {
			this.name = this.name.replace(/\[[^\]]+\]/, '[' + idx + ']');
			this.value = '';
		});
		$body.append($row);
		renumber($body);
	});
	$(document).on('click', '.vtsc-del', function () {
		var $body = $(this).closest('tbody');
		if ($body.find('tr').length > 1) { $(this).closest('tr').remove(); } else { $(this).closest('tr').find('input').val(''); }
		renumber($body);
	});
	function renumber($body) { $body.find('.vtsc-idx').each(function (i) { $(this).text(i + 1); }); }
});
