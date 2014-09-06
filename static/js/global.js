$.fn.alert = function (options) {
	var defaults = {
		type: 'info',
		message: 'This is a blank alert.',
	};

	var opts = $.extend(defaults, options);
	$(this).empty().append('<div class="alert alert-'+opts.type+'">'+opts.message+'</div>');
};


$(document).ready(function () {
	$.ajaxSetup({
		headers: {
			'X-CSRFToken': __CSRF__
		}
	});

	$('.screenshot-toggle').click(function (evt) {
		evt.preventDefault();
		evt.stopPropagation();
		
		$(this).siblings('.screenshot').toggleClass('hidden');

		return false;
	});

	$('.screenshot').click(function (evt) {
		evt.preventDefault();
		evt.stopPropagation();

		$('#lightbox .modal-content').html('<img src="'+$(this).attr('href')+'" />');
		$('#lightbox').modal();
	});
});

var updateAjaxProfiler = updateAjaxProfiler || function(){};