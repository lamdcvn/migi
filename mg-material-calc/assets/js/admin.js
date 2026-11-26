jQuery(function ($) {
	var $type = $('#mgmc_type');
	function toggle() {
		var t = $type.val();
		$('.mgmc-type').hide().filter('.mgmc-type-' + t).show();
		var $unit = $('#mgmc_unit');
		$unit.attr('placeholder', $type.find(':selected').data('unit'));
	}
	$type.on('change', toggle);
	toggle();
});
