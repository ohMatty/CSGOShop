{% extends 'admin/layout.tpl' %}
{% block header %}
	<h2>Bot Simulator</h2>
{% endblock %}
{% block content %}
<h3>Listings</h3>
{% if listings|length == 0 %}
<div class="text-center alert alert-warning">There are currently no listings for the bots to address now.</div>
{% else %}
<table id="listings" class="table table-review-details">
	<thead><th>Stage</th><th>Item</th><th>Created At</th><th>Price</th><th>Actions</th></thead>
	{% for listing in listings %}
	{% set item = listing.description %}
	<tr>
		<td class="col-xs-2" data-sort="{{ listing.stage }}">
			{% if listing.featured %}<span class="label label-default">FEATURED</span>{% endif %}
			{% if listing.children %}<span class="label label-default">BULK</span>{% endif %}
			
			{% if listing.stage == constant('Listing::STAGE_REQUEST') %}
				<span class="label label-warning">Pending Item Deposit</span>
			{% elseif listing.stage == constant('Listing::STAGE_REVIEW') %}
				{% if listing.checkout == 1 %}
				<span class="label label-warning">{{ listing.checkout_user.name }} Reviewing</span>
				{% else %}
				<span class="label label-warning">Pending Review</span>
				{% endif %}
			{% elseif listing.stage == constant('Listing::STAGE_LIST') %}
				<span class="label label-success">Listed</span>
				{% if listing.request_takedown == 1 %}<span class="label label-danger">Cancellation Requested</span>{% endif %}
				{% if listing.bot_id is null %}<span class="label label-warning">Pending Item Storage</span>{% endif %}
			{% elseif listing.stage == constant('Listing::STAGE_DENY') %}
				<span class="label label-danger">Denied</span>
			{% elseif listing.stage == constant('Listing::STAGE_CANCEL') %}
				<span class="label label-danger">Cancelled</span>
			{% elseif listing.stage == constant('Listing::STAGE_DELETE') %}
				<span class="label label-danger">Deleted</span>
			{% elseif listing.stage == constant('Listing::STAGE_COMPLETE') %}
				<span class="label label-success">Pending Cashout</span>			
			{% else %}
				<span class="label label-success">Completed - Archived</span>			
			{% endif %}

		</td>
		<td class="details-toggle col-xs-5">
			{{ item.market_name }}
			{% if listing.children %} x {{ listing.children|length + 1 }}{% endif %}

			<div class="details hidden">
				<hr />
				<table class="table table-striped table-bordered">
					
					<tr><td class="col-xs-4" rowspan="30">
						<img src="http://cdn.steamcommunity.com/economy/image/{{ item.icon_url_large ? item.icon_url_large : item.icon_url }}/300x300" alt="{{ item.market_name }}" />
					</td></tr>
					{% if listing.message %}<tr><td class="text-right">Notes:</td><td>{{ listing.message }}</td></tr>{% endif %}
					{% for dt in item.descriptiontags %}
					{% set tag = dt.tag %}
					<tr><td class="text-right col-xs-2">{{ tag.category_name }}:</td><td>{{ tag.name }}</td></tr>
					{% endfor %}

					{% if item.stackable != 1 %}
					{% if listing.note_playside or listing.screenshot_playside %}
					<tr><td class="text-right">Playside:</td><td>{{ listing.note_playside|default('--') }} 
						{% if listing.screenshot_playside %}
						<span class="glyphicon glyphicon-camera screenshot-toggle pull-right"></span> 
						<a target="_blank" class="screenshot hidden" href="{{ listing.screenshot_playside }}"><img src="{{ imgur_thumb(listing.screenshot_playside) }}" /></a>
						{% endif %}
					</td></tr>
					{% endif %}
					{% if listing.note_backside or listing.screenshot_backside %}
					<tr><td class="text-right">Backside:</td><td>{{ listing.note_backside|default('--') }} 
						{% if listing.screenshot_backside %}
						<span class="glyphicon glyphicon-camera screenshot-toggle pull-right"></span> 
						<a target="_blank" class="screenshot hidden" href="{{ listing.screenshot_backside }}"><img src="{{ imgur_thumb(listing.screenshot_backside) }}" /></a>
						{% endif %}
					</td></tr>
					{% endif %}
					{% endif %}
				</table>
			</div>
		</td>
		<td class="col-xs-1">{{ listing.created_at.format('m/d/Y H:i') }}</td>
		<td class="col-xs-1">{{ money_format(listing.price) }}</td>
		<td class="col-xs-2">
			<div class="btn-group btn-group-justified col-xs-6">
				{% if listing.stage == constant('Listing::STAGE_REQUEST') %}
				<a class="api-call btn btn-default btn-sm" href="{{ config('core.url') }}/api/request" data-data='{"bot_id": "76561198137227550", "key": "abc123", "listing_id": {{ listing.id }}, "trade_url": "this-is-fake.com", "trade_code": "poop"}'>Grab Item</a>
				<a class="api-call btn btn-primary btn-sm" href="{{ config('core.url') }}/api/requestComplete" data-data='{"key": "abc123", "listing_id": {{ listing.id }}}'>Confirm Trade</a>
				{% elseif listing.stage == constant('Listing::STAGE_REVIEW') %}
					{% if listing.checkout == 0 %}
					<a class="api-call btn btn-default btn-sm" href="{{ config('core.url') }}/api/checkout" data-data='{"key": "abc123", "item_id": "{{ listing.item_id }}", "user_id": "76561198034369542"}'>Checkout</a>
					{% else %}
					<a class="api-call btn btn-default btn-sm" href="{{ config('core.url') }}/api/checkin" data-data='{"key": "abc123", "item_id": "{{ listing.item_id }}"}'>Checkin</a>
					{% endif %}
				{% elseif listing.stage == constant('Listing::STAGE_LIST') and listing.bot_id is null %}
				<a class="api-call btn btn-primary btn-sm" href="{{ config('core.url') }}/api/storeComplete" data-data='{"bot_id": "76561198137227550", "key": "abc123", "listing_id": {{ listing.id }}}'>Confirm Stored</a>
				{% elseif listing.stage == constant('Listing::STAGE_DENY') or listing.stage == constant('Listing::STAGE_CANCEL') %}
				<a class="api-call btn btn-default btn-sm" href="{{ config('core.url') }}/api/return" data-data='{"bot_id": "76561198137227550", "key": "abc123", "listing_id": {{ listing.id }}, "trade_url": "this-is-fake.com", "trade_code": "poop"}'>Return Item</a>
				<a class="api-call btn btn-primary btn-sm" href="{{ config('core.url') }}/api/returnComplete" data-data='{"key": "abc123", "listing_id": {{ listing.id }}}'>Confirm Trade</a>
				{% endif %}
			</div>
		</td>
	</tr>
	{% endfor %}
</table>
{% endif %}


<hr />

<h3>Orders
<a class="api-call btn btn-warning btn-xs" href="{{ config('core.url') }}/api/cleanup" data-data='{"key": "abc123"}'>Cleanup Cancelled Orders</a>
</h3>
{% if orders|length == 0 %}
<div class="text-center alert alert-warning">There are currently no orders for the bots to address now.</div>
{% else %}
<table id="orders" class="table table-review-details">
	<thead><th>Status</th><th>User</th><th>Order</th><th>Created At</th><th>Payment</th><th>Actions</th></thead>
	{% for order in orders %}
	<tr>
		<td class="col-xs-2" data-sort="{{ listing.stage }}">
			{% if order.status == constant('Order::STATUS_UNPAID') %}
				<span class="label label-warning">Unpaid</span>
				{% if order.expired %}<span class="label label-default">CLEANUP</span>{% endif %}
			{% elseif order.status == constant('Order::STATUS_CANCELLED') %}
				<span class="label label-danger">Cancelled</span>
			{% elseif order.status == constant('Order::STATUS_PAID') %}
				<span class="label label-success">Paid - Pending Verification</span>
			{% elseif not order.isComplete %}
				<span class="label label-success">Paid - In Transit</span>
			{% else %}
				<span class="label label-success">Completed - Archived</span>
			{% endif %}

		</td>
		<td class="col-xs-2">{{ order.user.name }}</td>
		<td class="details-toggle col-xs-4">
			#{{ hashid(order.id) }}

			<div class="details hidden">
				<hr />
				<table class="table table-condensed table-raw text-left">
					<thead><th class="col-xs-2">Listing ID</th><th class="col-xs-6">Item (ID)</th><th class="col-xs-3">Seller</th><th class="col-xs-1">Price</th></thead>
					{% for listing in order.listings %}
					{% set item = listing.description %}
					<tr><td>{{ hashid(listing.id) }}</td><td>{{ item.market_name }} ({{ listing.item_id }})</td><td>{{ listing.user.name }}</td><td>{{ money_format(listing.price) }}</td></tr>
					{% endfor %}
					<tr><td>--</td><td>TAX</td><td>CSGOShop.com</td><td>{{ money_format(order.total_taxed - order.total) }}</td></tr>
				</table>
			</div>
		</td>
		<td class="col-xs-1">{{ order.created_at.format('m/d/Y H:i') }}</td>
		<td class="col-xs-1">
			{{ money_format(order.total_taxed) }} via 
			{% if order.provider == 'coinbase' %}
			Coinbase
			{% elseif order.provider == 'stripe' %}
			Stripe
			{% else %}
			PayPal
			{% endif %}
		</td>
		<td class="col-xs-3">
			<div class="btn-group btn-group-justified col-xs-6">
			{% if order.status == constant('Order::STATUS_PAID_CONFIRM') %}
				<a class="api-call btn btn-default btn-sm" href="{{ config('core.url') }}/api/transfer" data-data='{"bot_id": "76561198137227550", "key": "abc123", "order_id": {{ order.id }}, "trade_url": "this-is-fake.com", "trade_code": "poop"}'>Send Item</a>
				<a class="api-call btn btn-primary btn-sm" href="{{ config('core.url') }}/api/transferComplete" data-data='{"key": "abc123", "order_id": {{ order.id }}}'>Confirm Trade</a>
			{% endif %}
			</div>
		</td>
	</tr>
	{% endfor %}
</table>
{% endif %}

<script	type="text/javascript">
	$(document).ready(function () {
		$('.api-call').click(function (evt) {
			evt.preventDefault();

			var signature = '';
			var dom = $(this);

			$.ajax({
				type: 'POST',
				url: '{{ config('core.url') }}/api/generateSignature',
				data: dom.data('data'),
				success: function (data) {
					signature = data.signature;

					$.ajax({
						type: 'POST',
						url: dom.attr('href')+'?sig='+signature,
						data: dom.data('data'),
						error: function () {
							// TODO: Retry if timeout
						},
						success: function (data) {
							location.reload();
						}
					});
				}
			});
		});
	});
</script>

{% endblock %}