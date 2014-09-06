{% extends 'global/layout.tpl' %}
{% block header %}
	<h2>Settings</h2>
{% endblock %}

{% block content %}
	<form method="POST" action="" role="form" class="form-horizontal">
		<div class="form-group">
			<label for="trade_url" class="control-label col-xs-2">Trade URL: </label>
			<div class="col-xs-10">
				<input id="trade_url" name="trade_url" type="text" class="form-control" placeholder="http://steamcommunity.com/tradeoffer/new/?partner=####&token=####" value="{{ user.trade_url }}"/>			
			</div>
		</div>

		<div class="form-group">
			<label for="paypal_email" class="control-label col-xs-2">PayPal Email: </label>
			<div class="col-xs-10">
				<input id="paypal_email" name="paypal_email" type="text" class="form-control" placeholder="test-email@email-provider.com" value="{{ user.paypal }}"/>			
			</div>
		</div>

		<div class="form-group">
			<div class="col-xs-12 text-right">
				<a class='btn btn-link' href="{{ config('core.url') }}/">Cancel</a>
				<input type="submit" class="btn btn-primary" value="Save" />
			</div>
		</div>
	</form>
{% endblock %}