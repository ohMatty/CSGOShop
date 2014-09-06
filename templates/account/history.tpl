{% extends 'global/layout.tpl' %}

{% block header %}
	<h2>Order History
	<small><a href="{{ config('core.url') }}/account/orders/active">See active orders</a></small>
	</h2>
{% endblock %}

{% block content %}
	{% for order in orders %}
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="row"  style="vertical-align: bottom">
				<div class="col-xs-8">
					{% if order.status == constant('Order::STATUS_UNPAID') %}
					<span class="label label-warning">Unpaid</span>
					{% elseif order.status == constant('Order::STATUS_CANCELLED') %}
					<span class="label label-danger">Cancelled</span>
					{% elseif order.status == constant('Order::STATUS_PAID') %}
					<span class="label label-success">Paid - Pending Verification</span>
					{% elseif not order.isComplete %}
					<span class="label label-success">Paid - In Transit</span>
					{% else %}
					<span class="label label-success">Completed - Archived</span>
					{% endif %}
				</div>
				<div class="col-xs-4 text-right">
					<span class="text-muted">Updated {{ relative_time(order.updated_at) }}</span>
				</div>
			</div>
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-xs-3 text-center order-pane">
					<ul class="list-unstyled">
						<li>Order <strong>#{{ hashid(order.id) }}</strong></li>
						{% if order.provider == 'coinbase' %}
						<li>Gateway: <strong>Coinbase</strong></li>
						{% elseif order.provider == 'stripe' %}
						<li>Gateway: <strong>Stripe</strong></li>
						{% else %}
						<li>Gateway: <strong>PayPal</strong></li>
						{% endif %}
						<li>{{ order.created_at.format('m/d/Y H:i:s') }}</li>
						<li>Total: <strong>{{ money_format(order.total_taxed) }}</strong></li>
					</ul>
					<hr>
					{% if order.status == constant('Order::STATUS_PAID') or order.status == constant('Order::STATUS_PAID_CONFIRM') %}
					<a class="btn btn-default btn-block" href="{{ config('core.url') }}/account/invoice/{{ hashid(order.id) }}">Invoice</a>
					{% else %}
					<a class="btn btn-default btn-block disabled" href="#">Invoice</a>
					{% endif %}
				</div>

				<div class="col-xs-6 text-center review-pane">
					<table class="table table-condensed table-raw text-left">
						<thead><th class="col-xs-2">Listing ID</th><th class="col-xs-6">Item (ID)</th><th class="col-xs-3">Seller</th><th class="col-xs-1">Price</th></thead>
						{% for listing in order.listings %}
						{% set item = listing.description %}
						<tr><td>{{ hashid(listing.id) }}</td><td>{{ item.market_name }} ({{ listing.item_id }})</td><td>{{ listing.user.name }}</td><td>{{ money_format(listing.price) }}</td></tr>
						{% endfor %}
						<tr><td>--</td><td>TAX</td><td>CSGOShop.com</td><td>{{ money_format(order.total_taxed - order.total) }}</td></tr>
					</table>
				</div>

				<div class="col-xs-3">
					<div class="btn-group-vertical btn-block">
					{% if not order.isComplete and order.status == constant('Order::STATUS_PAID_CONFIRM') %}
						{% if order.trade_url %}
							<a target="_blank" href="//{{ order.trade_url }}" class="btn btn-primary">Trade Offer</a>
							<a href="#" class="btn btn-default disabled">Code: {{ order.trade_code }}</a>
						{% else %}
							<a href="#" class="btn btn-default disabled">Waiting for Trade Offer</a>
						{% endif %}
					{% endif %}
					</div>
				</div>			

			</div>
		</div>
	</div>
	{% endfor %}

	<ul class="pager">
		{% if page_num > 0 %}
		<li class="previous"><a href="{{ config('core.url') }}/account/orders?p={{ page_num - 1 }}">&larr; Newer</a></li>
		{% endif %}
		{% if (page_num + 1) < total %}
		<li class="next"><a href="{{ config('core.url') }}/account/orders?p={{ page_num + 1 }}">Older &rarr;</a></li>
		{% endif %}
	</ul>
{% endblock %}