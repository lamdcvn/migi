jQuery(function ($) {
	// Settings tabs.
	$('.mgpc-tabs a').on('click', function (e) {
		e.preventDefault();
		$('.mgpc-tabs a').removeClass('nav-tab-active'); $(this).addClass('nav-tab-active');
		$('.mgpc-tab').prop('hidden', true); $($(this).attr('href')).prop('hidden', false);
	});
	var $body = $('.mgpc-slots tbody');
	if (!$body.length) { return; }
	function cats($ctx) { $ctx.find('select.mgpc-cats').selectWoo({ width: '100%' }); }
	function renumber() {
		$body.find('tr').each(function (i) {
			$(this).find('[name^="slots["]').each(function () { this.name = this.name.replace(/^slots\[[^\]]*\]/, 'slots[' + i + ']'); });
		});
	}
	cats($body);
	$body.sortable({ handle: '.mgpc-drag', axis: 'y', update: renumber });
	$('.mgpc-add').on('click', function () {
		var $row = $($('#tmpl-mgpc-slot').html().split('__i__').join($body.find('tr').length));
		$body.append($row); cats($row); renumber();
		$row.find('input[type=text]').first().trigger('focus');
	});
	$body.on('click', '.mgpc-del', function () {
		if ($body.find('tr').length > 1) { $(this).closest('tr').remove(); renumber(); }
	});
});
