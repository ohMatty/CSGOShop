{% extends 'admin/layout.tpl' %}
{% block header %}
	<h2>Manage Cashouts</h2>
{% endblock %}
{% block content %}
{% if cashout_requests|length != 0 %}
<table id="orders" class="table table-review-details">
	<thead><th>Status</th><th>User</th><th>Request</th><th>Created At</th><th>Payment</th><th>Actions</th></thead>
	{% for cashout_request in cashout_requests %}
	<tr>
		<td class="col-xs-2" data-sort="{{ listing.stage }}">
			{% if cashout_request.status == constant('CashoutRequest::STATUS_REQUEST') %}
			<span class="label label-warning">Requested</span>
			{% elseif cashout_request.status == constant('CashoutRequest::STATUS_PAID') %}
			<span class="label label-success">Paid</span>
			{% endif %}
		</td>
		<td class="col-xs-2">{{ cashout_request.user.name }}</td>
		<td class="details-toggle col-xs-4">
			#{{ hashid(cashout_request.id) }}

			<div class="details hidden">
				{% if cashout_request.provider == 'coinbase' %}
				<p type="text"><strong>BTC Address:</strong> {{ cashout_request.token }}</p>
				{% endif %}
				<hr />
				<table class="table table-condensed table-raw text-left">
					<thead><th class="col-xs-2">Listing ID</th><th class="col-xs-9">Item (ID)</th><th class="col-xs-1">Price</th></thead>
					{% for listing in cashout_request.listings %}
					{% set item = listing.description %}
					<tr><td>{{ hashid(listing.id) }}</td><td>{{ item.market_name }} ({{ listing.item_id }})</td><td>{{ money_format(listing.price) }}</td></tr>
					{% endfor %}
					<tr class="text-danger"><td>--</td><td>TAX</td><td>{{ money_format(cashout_request.total - cashout_request.total_taxed) }}</td></tr>
				</table>
			</div>
		</td>
		<td class="col-xs-1">{{ cashout_request.created_at.format('m/d/Y H:i') }}</td>
		<td class="col-xs-1">
			{{ money_format(cashout_request.total_taxed) }} via 
			{% if cashout_request.provider == 'coinbase' %}
			Coinbase
			{% elseif cashout_request.provider == 'stripe' %}
			Stripe
			{% else %}
			PayPal
			{% endif %}
		</td>
		<td class="col-xs-3">
			<div class="btn-group btn-group-justified col-xs-6">
			{% if cashout_request.status == constant('CashoutRequest::STATUS_REQUEST') %}
				{% if cashout_request.provider == 'coinbase' %}
				<a target="_blank" href="http://coinbase.com" class="btn btn-primary btn-sm">Pay w/ Coinbase</a>
				{% elseif cashout_request.provider == 'paypal' %}
				<a href="{{ paypal_cashout_url }}{{ cashout_request.token }}" class="btn btn-primary btn-sm">Pay w/ Paypal</a>
				{% else %}
				<a href="{{ config('core.url') }}/admin/permitCashout/{{ hashid(cashout_request.id) }}" class="btn btn-primary btn-sm">Pay w/ Stripe</a>
				{% endif %}
					
				<a href="{{ config('core.url') }}/admin/processCashout?cashout_id={{ hashid(cashout_request.id) }}" class="btn btn-default btn-sm">Resolved</a>
			{% else %}
				<!-- TODO: INVOICE -->
				<a class="btn btn-default btn-sm" href="{{ config('core.url') }}/account/cashout/{{ hashid(cashout_request.id) }}">Invoice</a>
			{% endif %}
			</div>
		</td>
	</tr>
	{% endfor %}
</table>
{% endif %}
{% endblock %}