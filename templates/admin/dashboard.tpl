{% extends 'admin/layout.tpl' %}

{% block header %}
<h2>Dashboard</h2>
{% endblock %}

{% block content %}
{% if user.isRank('Lead Developer') %}
<div class="row">
	<div class="col-xs-3 text-center">
		<a class="dashboard-widget" href="{{ config('core.url') }}/admin/users/urgent">
		<div class="panel panel-danger">
			<div class="panel-heading"><p class="glyphicon glyphicon-user" style="font-size: 5em; padding: 0.5em"></p></div>
			<div class="panel-body">
				<h1>{{ users|length }} users</h1>
				<p>are flagged for offenses in the past 30 days</p>
			</div>
		</div>
		</a>
	</div>
	
	<div class="col-xs-3 text-center">
		<a class="dashboard-widget" href="{{ config('core.url') }}/admin/listings/urgent">
		<div class="panel panel-warning">
			<div class="panel-heading"><p class="glyphicon glyphicon-list" style="font-size: 5em; padding: 0.5em"></p></div>
			<div class="panel-body">
				<h1>{{ listings|length }} listings</h1>
				<p>are pending review</p>
			</div>
		</div>
		</a>
	</div>

	<div class="col-xs-3 text-center">
		<a class="dashboard-widget" href="{{ config('core.url') }}/admin/orders/urgent">
		<div class="panel panel-warning">
			<div class="panel-heading"><p class="glyphicon glyphicon-shopping-cart" style="font-size: 5em; padding: 0.5em"></p></div>
			<div class="panel-body">
				<h1>{{ orders|length }} orders</h1>
				<p>are pending review</p>
			</div>
		</div>
		</a>
	</div>

	<div class="col-xs-3 text-center">
		<a class="dashboard-widget" href="{{ config('core.url') }}/admin/cashouts/urgent">
		<div class="panel panel-warning">
			<div class="panel-heading"><p class="glyphicon glyphicon-usd" style="font-size: 5em; padding: 0.5em"></p></div>
			<div class="panel-body">
				<h1>{{ cashout_requests|length }} cashouts</h1>
				<p>are pending review</p>
			</div>
		</div>
		</a>
	</div>
</div>
<hr>
<div class="row">
	<div class="col-xs-4">
		<h3>Statistics</h3>
		<p>
			{{ money_format(taxed_income) }} earned in taxes in <strong>the past 30 days</strong>. <br />
			{{ money_format(total_spent) }} spent by users in <strong>the past 30 days</strong>. <br />
			{{ orders_latest|length }} orders filled in <strong>the past 30 days</strong>. 
		</p>
	</div>
</div>
{% endif %}
{% endblock content %}