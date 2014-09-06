{% if descriptions %}
<div class="row">
{% for listings in descriptions %}
	{% set listing = listings|first %}
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
				{% if listing.description.stackable == 1 %}
					href="{{ config('core.url') }}/listings/{{ hashid(listing.id) }}">
				{% else %}
					href="{{ config('core.url') }}/listing/{{ hashid(listing.id) }}">
				{% endif %}
					{% if item.is_stattrak %}<div class="stattrak">ST&#x2122;</div>{% endif %}
					<img 					
					src="http://cdn.steamcommunity.com/economy/image/{{ item.icon_url_large ? item.icon_url_large : item.icon_url }}/150x150" 
					alt="{{ item.name }}" />
				</a>
				
				<div class="info">
					<div class="name" title="{{ item.name }}"><span>{{ item.name_st }}</span></div>
					{% if item.exterior %}<span>({{ item.exterior }})</span> <br>{% endif %}
					{% if listings|length > 1 %}
					<span>Starting at {{ money_format(listing.price) }}</span>
					{% else %}
					<span>{{ money_format(listing.price) }}</span>
					{% endif %}
				</div>
				{% if listings|length == 1 %}	
				<div class="actions">
					<button class="btn btn-primary btn-cart-add" data-id="{{ hashid(listing.id) }}">Add</button>
				</div>
				{% else %}
				<div class="actions">
					<div class="input-group">
						<input type="text" class="form-control cart-quantity" placeholder="Qty" value="1" />
						<span class="input-group-btn"><button class="btn btn-primary btn-cart-add" data-id="{{ hashid(listing.id) }}">Add</button></span>
					</div>
				</div>
				{% endif %}
			</div>
		</div>
{% endfor %}
</div>

<a href="{{ config('core.url') }}/data/listings?{{ query_string }}" class="jscroll-next"></a>
{% endif %}