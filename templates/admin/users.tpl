{% extends 'admin/layout.tpl' %}
{% block header %}
<h2>Manage Users</h2>
{% endblock %}

{% block content %}
<table class="table table-review-details">
	<thead><th>Name</th><th>Rank</th><th width="15%">Actions</th></thead>
	{% for usr in users %}
	<tr><td class="details-toggle">
		<a href="http://steamcommunity.com/profiles/{{ usr.id }}">{{ usr.name }}</a>
		{% set strikes = usr.recent_offenses %}
		{% if strikes.total %} <span class="label label-danger">{{ strikes.total }}</span>{% endif %}
		<div class="details hidden">
			<hr>
			<ul class="list-unstyled">
				{% for listing in strikes.listings_cancelled %}
				<li class="text-danger">Listing #{{ hashid(listing.id) }} cancelled {{ relative_time(listing.updated_at) }}</li>
				{% endfor %}
				{% for order in strikes.orders_cancelled %}
				<li class="text-danger">Order #{{ hashid(order.id) }} cancelled {{ relative_time(order.updated_at) }}</li>
				{% endfor %}
			</ul>
		</div>
		</td>
		<td>{{ usr.rank_label }}</td>
		<td>
			<div class="btn-group btn-group-justified col-xs-6">
				<a class="btn btn-default btn-sm" href="{{ config('core.url') }}/admin/notify/{{ usr.id }}">Notify</a>
				{% if user.isRank('Senior Support Technician') %}
				{% if not usr.isRank('Banned') %}
				<a class="btn btn-danger btn-sm" data-user-name="{{ usr.name }}" href="{{ config('core.url') }}/admin/ban/{{ usr.id }}">Ban</a>
				{% else %}
				<a class="btn btn-default btn-sm" data-user-name="{{ usr.name }}" href="{{ config('core.url') }}/admin/unban/{{ usr.id }}">Unban</a>
				{% endif %}
				{% endif %}
			</div>
		</td>
	</tr>
	{% endfor %}	
</table>

<ul class="pager">
	{% if page_num > 0 %}
	<li class="previous"><a href="{{ config('core.url') }}/admin/users?p={{ page_num - 1 }}">&larr; Prev</a></li>
	{% endif %}
	{% if (page_num + 1) < page_total %}
	<li class="next"><a href="{{ config('core.url') }}/admin/users?p={{ page_num + 1 }}">Next &rarr;</a></li>
	{% endif %}
</ul>
{% endblock %}