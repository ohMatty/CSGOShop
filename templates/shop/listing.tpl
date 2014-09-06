{% extends 'global/layout.tpl' %}
{% set item = listing.description %}
{% block header %}
	<h2>{{ item.market_name }}
		<small>
		{% if listing.stage == constant('Listing::STAGE_REQUEST') %}
		Pending Item Storage
		{% elseif listing.stage == constant('Listing::STAGE_REVIEW') %}
		Pending Review
		{% elseif listing.stage == constant('Listing::STAGE_LIST') %}
		{% elseif listing.stage == constant('Listing::STAGE_DENY') or listing.stage == constant('Listing::STAGE_DELETE') %}
		Denied
		{% else %}
		Sold
		{% endif %}
			
		{% if item.stackable == 1 %}<a href="{{ config('core.url') }}/listings/{{ hashid(listing.id) }}">See related listings</a>{% endif %}
		</small>
	</h2>
{% endblock %}
{% block content %}
	{% if listing.stage == constant('Listing::STAGE_LIST') %}
	<div class="pull-right">
		<button class="btn btn-primary btn-cart-add" data-id="{{ hashid(listing.id) }}">Add to Cart</button>
	</div>
	{% endif %}

	<h3>Price: {{ money_format(listing.price) }} </h3>

		
	<div class="row">
		<div class="item-information col-xs-6">
			<table class="table table-striped table-bordered">
				<thead><th class="text-center" colspan="2">Item Information</th></thead>
				
				<tr><td class="text-right col-xs-3">Seller:</td><td><a href="http://steamcommunity.com/profiles/{{ listing.user.id }}">{{ listing.user.name }}</a></td></tr>

				{% for dt in listing.description.descriptiontags %}
				{% set tag = dt.tag %}
				<tr><td class="text-right">{{ tag.category_name }}:</td><td>{{ tag.name }}</td></tr>
				{% endfor %}

				<tr><td class="text-right">Storage Bot: </td><td><a href="http://steamcommunity.com/profiles/{{ listing.bot_id }}">{{ listing.bot.name }}</a></td></tr>

				{% if item.stackable != 1 %}
				{% if listing.note_playside or listing.screenshot_playside %}
				<tr><td class="text-right">Playside:</td><td>{{ listing.note_playside|default('--') }} 
					{% if listing.screenshot_playside %}
					<button class="btn btn-default btn-xs screenshot-toggle pull-right"><span class="glyphicon glyphicon-camera"></span></button>
					<a target="_blank" class="screenshot hidden" href="{{ listing.screenshot_playside }}"><img src="{{ imgur_thumb(listing.screenshot_playside) }}" /></a>
					{% endif %}
				</td></tr>
				{% endif %}
				{% if listing.note_backside or listing.screenshot_backside %}
				<tr><td class="text-right">Backside:</td><td>{{ listing.note_backside|default('--') }} 
					{% if listing.screenshot_backside %}
					<button class="btn btn-default btn-xs screenshot-toggle pull-right"><span class="glyphicon glyphicon-camera"></span></button>
					<a target="_blank" class="screenshot hidden" href="{{ listing.screenshot_backside }}"><img src="{{ imgur_thumb(listing.screenshot_backside) }}" /></a>
					{% endif %}
				</td></tr>
				{% endif %}
				{% endif %}
			</table>
		</div>

		<div class="col-xs-6 well item-misc">
			<div class="text-center">
				<img src="http://cdn.steamcommunity.com/economy/image/{{ item.icon_url_large ? item.icon_url_large : item.icon_url }}/300x300" alt="{{ item.name }}" />
				<br />
				{% if listing.inspect_url %}
				<a class="btn btn-default" href="{{ listing.inspect_url }}">Inspect in Game</a>
				{% endif %}
			</div>

			<h4>Notes</h4>
			<blockquote>{{ listing.message|default('...') }}</blockquote>
		</div>
	</div>
{% endblock %}