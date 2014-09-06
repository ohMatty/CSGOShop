var rL_modal = $('#requestListingModal');
var rL_form = $('#requestListingForm');
var rL_load = $('#requestListingLoad');
var rL_alert = $('#requestListingAlert');
var rL_controls = rL_modal.find('.modal-footer');
var screenshots = {};

// Handler for click events on inventory items
var requestListingHandler = function (evt) {
	evt.preventDefault();

	item = $(this);

	// Add item details to modal form
	$('#item').html(item.data('item-name') + '<br /><img src="'+item.find('img').attr('src')+'" />');
	if(item.data('item-stackable') == 1) {
		$('#screenshot_playside').attr('disabled', true);
		$('#screenshot_backside').attr('disabled', true);
		$('#note_playside').attr('disabled', true);
		$('#note_backside').attr('disabled', true);
		$('#message').attr('disabled', true);
		$('#quantity')
			.attr('disabled', false)
			.val(1);
		$('#price')
			.attr('disabled', true)
			.attr('placeholder', item.data('item-price-preset').toFixed(2));
	}
	else {
		$('#quantity')
			.attr('disabled', true)
			.attr('placeholder', 1)
			.val(1);
		$('#price')
			.attr('disabled', false)
			.attr('placeholder', '0.00');
		$('#screenshot_playside').attr('disabled', false);
		$('#screenshot_backside').attr('disabled', false);
		$('#note_playside').attr('disabled', false);
		$('#note_backside').attr('disabled', false);
		$('#message').attr('disabled', false);	
	}

	$('#requestListingSubmit').data('item-id', item.data('item-id'));
	$('#requestListingSubmit').data('item-stackable', item.data('item-stackable'));

	// Clear form and show			
	rL_form.show();
	rL_controls.show();
	rL_load.hide();
	rL_alert.empty();
	rL_modal.find("input[type=text], textarea").val("");
	rL_modal.modal({
		keyboard: false,
		backdrop: 'static'
	});
};

$('input[type="file"]').each(function (idx, el) {
	$(el).on('change', function (evt) {
		var element = $(this);
		var file = $(this)[0].files[0];
		var fr = new FileReader();
		var name = $(this).attr('name');

		rL_controls.hide();
		element.siblings('.progress').show();

		fr.onload = function (e) {
			// remove leading information from base64 string
			screenshots[name] = e.target.result.replace(/^data:image\/(png|jpg|jpeg);base64,/, "");

			element.siblings('.progress').slideUp();
			rL_controls.show();
		};
		fr.onprogress = function (e) {
			var pct = Math.round((e.loaded * 100) / e.total);
			element.siblings('.progress').children('.progress-bar').width(pct+'%');
		};
		fr.readAsDataURL(file);
	});
});

$('#requestListingSubmit').click(function (evt) {
	evt.preventDefault();
	rL_form.hide();
	rL_load.show();
	rL_controls.hide();
	rL_alert.empty();

	var item_id = $(this).data('item-id');			
	var route = ($(this).data('item-stackable') == 1 && parseInt($('#quantity').val()) > 1) ? '/bulk' : '/request';
	$.ajax({
		type: 'POST',
		url: coreURL + route,
		data: {
			'item_id': item_id,
			'quantity': $('#quantity').val(),
			'message': $('#message').val(),
			'price': $('#price').val(),
			'screenshot_playside': screenshots.screenshot_playside,
			'screenshot_backside': screenshots.screenshot_backside,
			'note_playside': $('#note_playside').val(),
			'note_backside': $('#note_backside').val(),
		},
		success: function (data) {
			try {
				JSON.parse(data);
				window.location = coreURL+'/account/listings?new';
			}
			catch(e) {
				rL_load.hide();
				rL_form.show();
				rL_controls.show();
				rL_alert.alert({
					type: 'danger',
					message: 'The server has returned an invalid response to your request. Ensure that your request is within the suggested file limits.'
				});	
			}
		},
		error: function (jqXHR, status, httperror) {
			rL_load.hide();
			rL_form.show();
			rL_controls.show();
			rL_alert.alert({
				type: 'danger',
				message: (jqXHR.responseJSON.message || httperror)
			});
		}
	});

});

// Grab inventory data on page load
var inventoryLoad = $('#inventoryLoad');
var __TEMPLATE__ = $('#template').html();
Mustache.parse(__TEMPLATE__);

$.ajax({
	type: 'GET',
	url: coreURL+'/data/inventory',
	timeout: 10000,	// timeout after 10 seconds
	success: function (data) {
		var content = inventoryLoad.parent();

		// Remove contents and add in inventory items with click bind
		inventoryLoad.remove();
		var render = Mustache.render(__TEMPLATE__, data);
		content.append(render);
		$('.inventory-item').click(requestListingHandler);
		updateAjaxProfiler(data.profiler);
	},
	error: function (jqXHR, status, httperror) {
		inventoryLoad.remove();
		httperror = (httperror == 'timeout') ? 'The request has timed out. Please try again in a few minutes.' : httperror;
		$('#alerts').alert({
			type: jqXHR.responseJSON ? jqXHR.responseJSON.type : 'warning',
			message: jqXHR.responseJSON ? jqXHR.responseJSON.message : (httperror || status)
		});
	},
});