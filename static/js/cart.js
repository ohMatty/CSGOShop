$(document).ready(function () {
	$('.btn-cart-remove').click(function (evt) {
		evt.preventDefault();

		$.ajax({
			type: 'POST',
			url: coreURL+'/cart/del',
			data: {
				type: $(this).data('type'),
				id: $(this).data('id'),
				qty: $(this).data('qty')
			},
			success: function () {
				window.location = '';
			}
		});
	});

	$(document).on('click', '.btn-cart-add', function (evt) {
		evt.preventDefault();

		var btn = $(this),
			id = btn.data('id'),
			qty = btn.parent().siblings('input.cart-quantity')[0];

		if(btn.prop('disabled'))
			return;

		$.ajax({
			type: 'POST',
			url: coreURL+'/cart/add',
			data: {
				id: id,
				qty: qty ? (qty.value||1) : 1
			},
			success : function (data) {
				if(data.added != true || data.remaining == 0) {
					btn.prop('disabled', true);
				}

				$('.cart-items-total').text(data.items);
			}
		});
	});
});