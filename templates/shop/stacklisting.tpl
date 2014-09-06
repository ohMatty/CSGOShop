{% extends 'global/layout.tpl' %}
{% set listing = listings|first %}
{% set item = listing.description %}
{% block header %}
	<h2>{{ item.name }}</h2>
{% endblock %}
{% block content %}

	<div class="pull-right col-xs-2" style="padding:0; margin:0">
		<div class="input-group">
			<input type="text" class="form-control cart-quantity" placeholder="Qty" value="1" />
			<span class="input-group-btn"><button class="btn btn-primary btn-cart-add" data-id="{{ hashid((listings|first).id) }}">Add to Cart</button></span>
		</div>
	</div>

	<h3>Price: {{ money_format(listing.price) }} </h3>

	<div class="item-information col-xs-6">
		<table class="table table-striped table-bordered">
			<thead><th class="text-center" colspan="2">Item Information</th></thead>
			{% for dt in listing.description.descriptiontags %}
			{% set tag = dt.tag %}
			<tr><td class="text-right">{{ tag.category_name }}:</td><td>{{ tag.name }}</td></tr>
			{% endfor %}
		</table>
	</div>

	<div class="col-xs-6 well item-misc">
		<div class="text-center">
			<img src="http://cdn.steamcommunity.com/economy/image/{{ item.icon_url_large ? item.icon_url_large : item.icon_url }}/300x300" alt="{{ item.name }}" />
		</div>
	</div>


	<table class="table table-bordered">
		<thead><th style="width: 10%;">Original ID</th><th style="width: 25%;">Storage Bot</th><th style="width: 35%;">Seller</th><th style="width: 15%;">Price</th><th>Add to Cart</th></thead>
		{% for listing in listings %}
		<tr>
			<td>{{ listing.item_id }}</td>
			<td><a href="http://steamcommunity.com/profiles/{{ listing.bot_id }}">{{ listing.bot.name }}</a></td>
			<td><a href="http://steamcommunity.com/profiles/{{ listing.user.id }}">{{ listing.user.name }}</a></td>
			<td>{{ money_format(listing.price) }}</td>
			<td>
				<button class="btn btn-primary btn-cart-add" data-id="{{ hashid(listing.id) }}">Add</button>
			</td>
		</tr>
		{% endfor %}
	</table>

<ul class="pager">
	{% if page_num > 0 %}
	<li class="previous"><a href="{{ config('core.url') }}/listings/{{ hashid((listings|first).id) }}?p={{ page_num - 1 }}">&larr; Prev</a></li>
	{% endif %}
	{% if (page_num + 1) < total %}
	<li class="next"><a href="{{ config('core.url') }}/listings/{{ hashid((listings|first).id) }}?p={{ page_num + 1 }}">Next &rarr;</a></li>
	{% endif %}
</ul>
{% endblock %}