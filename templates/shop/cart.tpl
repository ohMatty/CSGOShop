{% extends 'global/layout.tpl' %}
{% block header %}
	<h2>My Cart</h2>
{% endblock %}

{% block content %}
	{% if old_order %}
	<div class="alert alert-danger">
		<p>You have already reserved an order! Your cart has been updated with this order.</p>
		<p>You have approximately <strong id="time-limit">{{ relative_time(old_order.time_limit) }}</strong> until your order is automatically cancelled.</p>
	</div>
	{% endif %}
	<div class="row">
		<div id="cartLoad" class="col-xs-offset-2 col-xs-8 text-center hidden">
			<p>Please wait a moment while your order is being processed.</p>

			<div class="progress progress-striped active">
				<div class="progress-bar" style="width: 100%"></div>
			</div>
		</div>
	</div>


	<table class="table table-bordered table-cart">
		<thead><th colspan="2">Item</th>{% if not old_order %}<th>Remove</th>{% endif %}<th>Unit Price</th><th>Quantity</th><th>Line Total</th></thead>
		{% if cart.listings|length == 0 and cart.bulk|length == 0 %}
		<tr><td colspan="6">
			<div class="text-center alert alert-warning margin-override">Your cart is currently empty. <a href="{{ config('core.url') }}/browse">Why not check out some neat skins?</a></div>
		</td></tr>

		{% else %}
		<!-- UNIQUE -->
		{% for item in cart.listings %}
		{% set listing = item.listing %}
		<tr>
			<td class="text-center"><img class="small inventory-item" src="http://cdn.steamcommunity.com/economy/image/{{ listing.description.icon_url_large ? listing.description.icon_url_large : listing.description.icon_url }}/100x100" alt="{{ listing.description.market_name }}" /></td>
			<td>{{ listing.description.market_name }}</td>
			
			{% if not old_order %}
			<td class="text-center"><button class="btn btn-default btn-cart-remove" data-type="listings" data-id="{{ hashid(listing.id) }}">Remove</button></td>
			{% endif %}
			
			<td class="text-center">{{ money_format(listing.price) }}</td>
			<td class="text-center">
				{% if not old_order %}
				<input type="text" class="form-control col-xs-1 qty" value="{{ item.qty }}" data-qty="{{ item.qty }}" data-type="listings" data-id="{{ hashid(listing.id) }}">
				 of {{ item.max }} available
				{% else %}
				{{ item.qty }}
				{% endif %}
			</td>
			<td class="text-center">{{ money_format(listing.price) }}</td>
		</tr>
		{% endfor %}

		<!-- BULK  -->
		{% for item in cart.bulk %}
		{% set listing = item.listing %}
		<tr>
			<td class="text-center"><img class="small inventory-item" src="http://cdn.steamcommunity.com/economy/image/{{ listing.description.icon_url_large ? listing.description.icon_url_large : listing.description.icon_url }}/100x100" alt="{{ listing.description.market_name }}" /></td>
			<td>{{ listing.description.market_name }}</td>
			
			{% if not old_order %}
			<td class="text-center"><button class="btn btn-default btn-cart-remove" data-qty="{{ item.qty }}" data-type="bulk" data-id="{{ hashid(listing.id) }}">Remove</button></td>
			{% endif %}

			<td class="text-center">{{ money_format(item.unit_price) }}</td>
			<td class="text-center">
				{% if not old_order %}
				<input type="text" class="form-control col-xs-1 qty" value="{{ item.qty }}" data-qty="{{ item.qty }}" data-type="bulk" data-id="{{ hashid(listing.id) }}">
				 of {{ item.max }} available
				{% else %}
				{{ item.qty }}
				{% endif %}
			</td>
			<td class="text-center">{{ money_format(item.subtotal) }}</td>
		</tr>
		{% endfor %}
		{% endif %}
		<tr><td rowspan="4" colspan="{% if not old_order %}4{% else %}3{% endif %}"></td></tr>
		<tr><td class="text-right"><strong>Subtotal:</strong></td><td class="text-center">{{ money_format(total) }}</td></tr>
		<tr><td class="text-right"><strong>Taxes:</strong></td><td class="text-center">{{ money_format(total_taxed - total) }}</td></tr>
		<tr><td class="text-right"><strong>Total:</strong></td><td class="text-center">{{ money_format(total_taxed) }}</td></tr>
	</table>

	{% if user.isLoggedIn() %}
	<div class="checkout-controls">
		{% if old_order %}
		<a class="btn btn-primary checkout" href="{{ config('core.url') }}/order">Checkout with Paypal</a>
		<a class="btn btn-primary checkout" href="{{ config('core.url') }}/order?checkout=coinbase">Checkout with Coinbase</a>
		<a href="#" class="btn btn-primary stripe">Checkout with Stripe</a>
		<a class="btn default" href="{{ config('core.url') }}/cart/cancel">Cancel Order</a>
		{% elseif cart.bulk|length > 0 or cart.listings|length > 0 %}
		<a class="btn btn-primary checkout" href="{{ config('core.url') }}/order">Checkout with Paypal</a>
		<a class="btn btn-primary checkout" href="{{ config('core.url') }}/order?checkout=coinbase">Checkout with Coinbase</a>
		<a href="#" class="btn btn-primary stripe">Checkout with Stripe</a>
		<a class="btn default" href="{{ config('core.url') }}/cart/empty">Empty Cart</a>
		{% endif %}
	</div>
	{% else %}
	<div>
		<a class="btn btn-default" href="{{ config('core.url') }}/cart/empty">Empty Cart</a>
	</div>
	<br />
	<div class="alert alert-info">
		To access the rest of the site, we require all users to login through their Steam account. Using your Steam account to login is much more secure than a username and password and lets us identify you in the Steam Community.
		<br><br>
		<b>Use the login button below!</b>
		<div class="text-center">
			<a href='{{ steamLoginUrl }}'>
				<img src='{{ config('core.static')}}/imgs/steam_login.png'>
			</a>
		</div>
	</div>
	{% endif %}
	<script src="https://checkout.stripe.com/v2/checkout.js"></script>
	<script type="text/javascript">
	$(document).ready(function () {
		$('.qty').change(function (evt) {
			var $this = $(this);
			var url, qty;
			var diff = parseInt($this.val()) - parseInt($this.data('qty'));
			
			if(diff > 0) {
				qty = diff;
				url = coreURL+'/cart/add';
			}
			else {
				qty = diff * -1;
				url = coreURL+'/cart/del';
			}

			$.ajax({
				type: 'POST',
				url: url,
				data: {
					type: $this.data('type'),
					id: $this.data('id'),
					qty: qty,
				},
				success: function (data) {
					window.location = '';
				}
			})
		});

		$('.checkout').click(function (evt) {
			evt.preventDefault();

			$('.table-cart, .checkout-controls').hide();
			$('#cartLoad').toggleClass('hidden');

			$.ajax({
				type: 'POST',
				url: $(this).attr('href'),
				success: function (data) { window.location = data.url; },
				error: function (jqXHR, status, httperror) { 
					$('#cartLoad').hide();
					var txt = jqXHR.responseJSON.message || 'There was an error generating your order. Please refresh the page and try again.';
					$('#alerts').alert({ type: 'danger', message: txt }); 
					setTimeout(function() { window.location.reload(); }, 5000);
				}
			});
		});

		$('.stripe').click(function (evt) {
			var token = function(res){
				$('.table-cart, .checkout-controls').hide();
				$('#cartLoad').toggleClass('hidden');
				
				$.ajax({
					type: 'POST',
					url: coreURL+'/order?checkout=stripe',
					data: {
						stripe_token: res.id
					},
					success: function (data) { window.location = data.url; },
					error: function (jqXHR, status, httperror) { 
						$('#cartLoad').hide();
						var txt = jqXHR.responseJSON.message || 'There was an error generating your order. Please refresh the page and try again.';
						$('#alerts').alert({ type: 'danger', message: txt }); 
						setTimeout(function() { window.location.reload(); }, 5000);
					}

				});
			};

			StripeCheckout.open({
				key:         stripe_pk,
				address:     true,
				amount:      {{ total_taxed * 100 }},
				currency:    'usd',
				name:        'Stripe Checkout',
				description: '',
				panelLabel:  'Checkout',
				token:       token
			});

			return false;
		});

	});
	</script>

{% endblock %}