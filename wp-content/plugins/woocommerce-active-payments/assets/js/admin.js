(function ($) {
	"use strict";
	$(function () {
		$(".active-payments-main").delegate('td', 'mouseover mouseleave', function (e) {
			if (e.type === 'mouseover') {
				$(this).parent().addClass("hover");
				$("colgroup").eq($(this).index()).addClass("hover");
			} else {
				$(this).parent().removeClass("hover");
				$("colgroup").eq($(this).index()).removeClass("hover");
			}
		});
	});
	jQuery('input[to_number=1]').each(function () {
		jQuery(this).attr('type', 'number');
	});
})(jQuery);
