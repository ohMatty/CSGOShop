{% extends 'global/layout.tpl' %}

{% block header %}
<div class="row">
	<div class="col-xs-6">
		<h2 style="margin-top:0;">Featured Items
		<a style="padding: 6px 12px; line-height: 1.42857143; font-size: 14px; font-weight: bold;" href="{{ config('core.url') }}/browse">Browse Items</a>
		</h2>
	</div>
	<div class="col-xs-4 col-xs-push-2 text-right">
		<div class="search btn-toolbar text-left">
			<div class="btn-group">
				<input type="text" name="item_name" class="typeahead form-control" placeholder="Search all listings.." />
			</div>
			<div class="btn-group">
				<a href="#" class="btn btn-default search-go">
				<span class="glyphicon glyphicon-search"></span>
				</a>
				<div class="btn btn-default advanced-search-toggle">
				<span class="glyphicon glyphicon-chevron-down"></span>
				</div>
			</div>
		</div>
	</div>
</div>
{% include 'shop/advancedSearch.tpl' %}
{% endblock %}

{% block content %}
<div class="row">
{% for listing in listings %}
	{% set item = listing.description %}
	{% if loop.index % 4 == 1 %}
	<div class="col-xs-3 text-left">
	{% elseif loop.index is divisibleby(4) %}
	<div class="col-xs-3 text-right">
	{% else %}
	<div class="col-xs-3 text-center">
	{% endif %}
		<div class="item well">
			<a 
				style="border-color: #{{ listing.description.name_color == 'D2D2D2' ? '000000' : listing.description.name_color }}" 
				href="{{ config('core.url') }}/listing/{{ hashid(listing.id) }}">
				{% if item.is_stattrak %}<div class="stattrak">ST&#x2122;</div>{% endif %}
				<img 					
				src="http://cdn.steamcommunity.com/economy/image/{{ item.icon_url_large ? item.icon_url_large : item.icon_url }}/150x150" 
				alt="{{ item.name }}" />
			</a>
			
			<div class="info">
				<div class="name" title="{{ item.name }}"><span>{{ item.name_st }}</span></div>
				{% if item.exterior %}<span>({{ item.exterior }})</span> <br>{% endif %}
				<span>{{ money_format(listing.price) }}</span>
			</div>
			<div class="actions">
				<button class="btn btn-primary btn-cart-add" data-id="{{ hashid(listing.id) }}">Add</button>
			</div>
		</div>
	</div>
{% endfor %}
</div>

<script src="{{ config('core.static') }}/js/typeahead.bundle.min.js"></script>
<script src="{{ config('core.static') }}/js/search.js"></script>
{% endblock %}