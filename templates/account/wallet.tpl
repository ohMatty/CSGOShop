{% extends 'global/layout.tpl' %}

{% block header %}
<h2>My Wallet</h2>
{% endblock %}

{% block content %}
	{% if items|length != 0 %}
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="row"  style="vertical-align: bottom">
				<div class="col-xs-8">
					<span class="label label-default">My Wallet</span>
				</div>
				<div class="col-xs-4 text-right">
					<!-- <span class="text-muted">Now</span> -->
				</div>
			</div>
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-xs-3 text-center order-pane">
					<div>
					<span class="glyphicon glyphicon-barcode" style="font-size: 4.5em"></span> <br>
					Total: <strong>{{ money_format(total_taxed) }}</strong>
					</div>
				</div>

				<div class="col-xs-6 text-center review-pane">
					<table class="table table-condensed table-raw text-left">

						<thead><th class="col-xs-2">Listing ID</th><th class="col-xs-8">Item</th><th class="col-xs-1">Price</th><th class="col-xs-1">Line Total</th></thead>
						{% for i in items %}
						{% set listing = i.listing %}
						{% set item = listing.description %}
							<tr>
								{% if item.stackable == 1 %}
								<td>--</td>
								{% else %}
								<td>{{ hashid(listing.id) }}</td>
								{% endif %}
								<td>{{ listing.description.market_name }} {% if item.stackable == 1 %} x {{ i.qty }} {% endif %}</td>
								<td>{{ money_format(i.unit_price) }}</td>
								<td>{{ money_format(i.subtotal) }}</td>
							</tr>
						{% endfor %}
						<tr class="text-danger"><td>--</td><td colspan="2">TAX</td><td>{{ money_format(total - total_taxed) }}</td></tr>
					</table>
				</div>	
				<div class="col-xs-3 text-center">
					<div class="btn-group-vertical btn-block">
						{% if user.cooldown == false %}
						<a id="paypal-cashout" class="btn btn-primary {% if user.paypal is empty %} disabled {% endif %}" href="#">Cash Out w/ Paypal</a>
						<a id="coinbase-cashout" class="btn btn-primary" href="#">Cash Out w/ Coinbase</a>
						<a id="stripe-cashout" class="btn btn-primary" href="#">Cash Out w/ Stripe</a>
						{% endif %}
					</div>
				</div>
			</div>
		</div>
	</div>

	{% endif %}
	<hr>
	<h2>Cashout History</h2>

	{% for cashout_request in cashout_requests %}
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="row"  style="vertical-align: bottom">
				<div class="col-xs-8">
					{% if cashout_request.status == constant('CashoutRequest::STATUS_REQUEST') %}
					<span class="label label-warning">Requested</span>
					{% elseif cashout_request.status == constant('CashoutRequest::STATUS_PAID') %}
					<span class="label label-success">Paid</span>
					{% endif %}
				</div>
				<div class="col-xs-4 text-right">
					<span class="text-muted">Updated {{ relative_time(cashout_request.updated_at) }}</span>
				</div>
			</div>
		</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-xs-3 text-center order-pane">
					<ul class="list-unstyled">
						<li>Request <strong>#{{ hashid(cashout_request.id) }}</strong></li>
						{% if cashout_request.provider == 'coinbase' %}
						<li>Gateway: <strong>Coinbase</strong></li>
						{% elseif cashout_request.provider == 'stripe' %}
						<li>Gateway: <strong>Stripe</strong></li>
						{% else %}
						<li>Gateway: <strong>PayPal</strong></li>
						{% endif %}
						<li>{{ order.created_at.format('m/d/Y H:i:s') }}</li>
						<li>{{ cashout_request.created_at.format('m/d/Y H:i:s') }}</li>
						<li>Total: <strong>{{ money_format(cashout_request.total_taxed) }}</strong></li>
					</ul>
				</div>
				<div class="col-xs-6 text-center review-pane">
					<table class="table table-condensed table-raw text-left">
						<thead><th class="col-xs-2">Listing ID</th><th class="col-xs-9">Item (ID)</th><th class="col-xs-1">Price</th></thead>
						{% for listing in cashout_request.listings %}
						{% set item = listing.description %}
						<tr><td>{{ hashid(listing.id) }}</td><td>{{ item.market_name }} ({{ listing.item_id }})</td><td>{{ money_format(listing.price) }}</td></tr>
						{% endfor %}
						<tr class="text-danger"><td>--</td><td colspan="1">TAX</td><td>{{ money_format(cashout_request.total - cashout_request.total_taxed) }}</td></tr>
					</table>
				</div>	
			</div>
		</div>
	</div>
	{% endfor %}

	<ul class="pager">
		{% if page_num > 0 %}
		<li class="previous"><a href="{{ config('core.url') }}/account/wallet?p={{ page_num - 1 }}">&larr; Newer</a></li>
		{% endif %}
		{% if (page_num + 1) < page_total %}
		<li class="next"><a href="{{ config('core.url') }}/account/wallet?p={{ page_num + 1 }}">Older &rarr;</a></li>
		{% endif %}
	</ul>

	<script src="https://checkout.stripe.com/v2/checkout.js"></script>
	<script type="text/javascript">
	var cashoutHandler = function(provider, id) {
		$.ajax({
			type: 'POST',
			url: coreURL+'/account/cashout',
			data: {
				provider: provider,
				provider_identifier: id
			},
			success: function (data) {
				window.location = '';
			},
			error: function (jqXHR, status, httperror) {
				$('#alerts').alert({
					type: jqXHR.responseJSON ? jqXHR.responseJSON.type : 'warning',
					message: jqXHR.responseJSON ? jqXHR.responseJSON.message : (httperror || status)
				});
			}
		});
	};

	$('#paypal-cashout').click(function (evt) {
		evt.preventDefault();

		cashoutHandler('paypal', '{{ user.paypal }}');
	});

	$('#coinbase-cashout').click(function (evt) {
		evt.preventDefault();

		cashoutHandler('coinbase', '');
	});

	$('#stripe-cashout').click(function (evt) {
		evt.preventDefault();

		var token = function(res){
			cashoutHandler('stripe', res.id);
		};

		StripeCheckout.open({
			key:         stripe_pk,
			address:     true,
			amount:      {{ total_taxed * 100 }},
			currency:    'usd',
			name:        'Stripe Cashout',
			description: '',
			panelLabel:  'Request',
			token:       token
		});
	});
	</script>
{% endblock %}