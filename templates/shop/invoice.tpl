<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>{{ page.title }}</title>

		<link href="{{ config('core.static') }}/css/bootstrap.min.css" rel="stylesheet">
		<link href="{{ config('core.static') }}/css/global.css" rel="stylesheet">
		<link href="{{ config('core.static') }}/imgs/favicon.ico" rel="shortcut icon" />
		<link href='http://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	</head>
	<body>
		<div class="container">
			<div class="invoice">
				<div class="row">
					<div class="brand text-center">
						<a href="{{ config('core.url') }}"><img src="{{ config('core.static') }}/imgs/logo.png"></a>
					</div>
				</div>
			<a href="{{ config('core.url') }}" class="pull-right">Back to CSGOShop.com &rarr;</a>
			<!-- <h4 class="pull-right">{{ order.updated_at.format('db') }}</h4> -->

			<h1 style="margin-top:0">Invoice</h1>
			<ul class="list-unstyled text-muted">
				<li>Order: <strong>#{{ hashid(order.id) }}</strong></li>
				<li>Status: <strong>
					{% if order.status == constant('Order::STATUS_UNPAID') %}
					Unpaid
					{% elseif order.status == constant('Order::STATUS_CANCELLED') %}
					Cancelled
					{% elseif order.status == constant('Order::STATUS_PAID') %}
					Paid - Pending Verification
					{% elseif not order.isComplete %}
					Paid - In Transit
					{% else %}
					Completed - Archived
					{% endif %}
					</strong>
				</li>
				<li>Customer: <strong>{{ order.user.name }} (#{{ order.user.id }})</strong></li>
				{% if order.provider == 'coinbase' %}
				<li>Gateway: <strong>Coinbase</strong></li>
				{% elseif order.provider == 'stripe' %}
				<li>Gateway: <strong>Stripe</strong></li>
				{% else %}
				<li>Gateway: <strong>PayPal</strong></li>
				{% endif %}
				<li>Date: <strong>{{ order.created_at.format('m/d/Y H:i:s') }}</strong></li>
			</ul>
			<div class="clearfix"></div>
			<hr>

			{% set order_table = order.toTable %}
			<h3>Order Summary</h3>
			<table class="table table-bordered table-cart">
				<thead><th colspan="2">Item</th><th>Quantity</th><th>Line Total</th></thead>
				{% for item in order_table.listings %}
				{% set listing = item.listing %}
				<tr>
					<td class="text-center"><img class="small inventory-item" src="http://cdn.steamcommunity.com/economy/image/{{ listing.description.icon_url_large ? listing.description.icon_url_large : listing.description.icon_url }}/100x100" alt="{{ listing.description.market_name }}" /></td>
					<td class="text-center">{{ listing.description.market_name }}</td>
					<td class="text-center">{{ item.qty }}</td>
					<td class="text-center">{{ money_format(listing.price) }}</td>
				</tr>
				{% endfor %}
				{% for item in order_table.bulk %}
				{% set listing = item.listing %}
				<tr>
					<td><img class="small inventory-item" src="http://cdn.steamcommunity.com/economy/image/{{ listing.description.icon_url_large ? listing.description.icon_url_large : listing.description.icon_url }}/100x100" alt="{{ listing.description.market_name }}" /></td>
					<td class="text-center">{{ listing.description.market_name }}</td>
					<td class="text-center">{{ item.qty }}</td>
					<td class="text-center">{{ money_format(item.subtotal) }}</td>
				</tr>
				{% endfor %}
				<tr><td colspan="2"></td><td><strong>Subtotal:</strong></td><td class="text-center">{{ money_format(order.total) }}</td></tr>
				<tr><td colspan="2"></td><td><strong>Taxes:</strong></td><td class="text-center">{{ money_format(order.total_taxed - order.total) }}</td></tr>
				<tr><td colspan="2"></td><td><strong>Total:</strong></td><td class="text-center">{{ money_format(order.total_taxed) }}</td></tr>
			</table>	
				
			</div>
			
		</div>
	</body>
</html>
