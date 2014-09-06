{% extends 'admin/layout.tpl' %}
{% block header %}
	<h2>Manage Orders</h2>
{% endblock %}
{% block content %}
{% if orders|length != 0 %}
<table id="orders" class="table table-review-details">
	<thead><th>Status</th><th>User</th><th>Order</th><th>Updated At</th><th>Payment</th><th>Actions</th></thead>
	{% for order in orders %}
	<tr>
		<td class="col-xs-2" data-sort="{{ order.status }}">
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
		<td class="col-xs-1">{{ order.updated_at.format('m/d/Y H:i') }}</td>
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
			{% if order.status == constant('Order::STATUS_PAID') %}
				{% if user.isRank('Managing Director') %}
				<a class="btn btn-default btn-sm" href="{{ config('core.url') }}/admin/confirm/{{ order.id }}">Confirm payment</a> 
				{% endif %}
			{% elseif order.status == constant('Order::STATUS_PAID_CONFIRM') %}
				<a class="btn btn-default btn-sm" href="{{ config('core.url') }}/account/invoice/{{ hashid(order.id) }}">Invoice</a>
			{% endif %}
			</div>
			<span class="hidden">{{ (order.listings|first).trade_code }}</span>
		</td>
	</tr>
	{% endfor %}
</table>
{% endif %}
{% endblock %}