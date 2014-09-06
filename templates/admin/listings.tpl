{% extends 'admin/layout.tpl' %}
{% block header %}
	<h2>Manage Listings</h2>
{% endblock %}
{% block content %}
{% if listings|length != 0 %}
<table id="listings" class="table table-review-details">
	<thead><th>Stage</th><th>Item</th><th>Updated At</th><th>Price</th><th>Actions</th></thead>
	{% for listing in listings %}
	{% set item = listing.description %}
	<tr>
		<td class="col-xs-2" data-sort="{{ listing.stage }}">
			{% if listing.featured %}<span class="label label-default">FEATURED</span>{% endif %}
			{% if listing.children %}<span class="label label-default">BULK</span>{% endif %}
			
			{% if listing.stage == constant('Listing::STAGE_REQUEST') %}
				<span class="label label-warning">Pending Item Deposit</span>
			{% elseif listing.stage == constant('Listing::STAGE_REVIEW') %}
				{% if listing.checkout == 1 %}
				<span class="label label-warning">Under Review</span>
				{% else %}
				<span class="label label-warning">Pending Review</span>
				{% endif %}
			{% elseif listing.stage == constant('Listing::STAGE_LIST') %}
				<span class="label label-success">Listed</span>
				{% if listing.request_takedown == 1 %}<span class="label label-danger">Cancellation Requested</span>{% endif %}
				{% if listing.bot_id is null %}<span class="label label-warning">Pending Item Storage</span>{% endif %}
			{% elseif listing.stage == constant('Listing::STAGE_DENY') %}
				<span class="label label-danger">Denied</span>
			{% elseif listing.stage == constant('Listing::STAGE_CANCEL') %}
				<span class="label label-danger">Cancelled</span>
			{% elseif listing.stage == constant('Listing::STAGE_DELETE') %}
				<span class="label label-danger">Deleted</span>
			{% elseif listing.stage == constant('Listing::STAGE_COMPLETE') %}
				<span class="label label-success">Complete</span><span class="label label-warning">Pending Cashout</span>
			{% elseif listing.stage == constant('Listing::STAGE_ORDER') %}
				<span class="label label-warning">Pending Order</span>
			{% else %}
				<span class="label label-success">Completed - Archived</span>			
			{% endif %}

		</td>
		<td class="details-toggle col-xs-5">
			{{ item.market_name }}
			{% if listing.children %} x {{ listing.children|length + 1 }}{% endif %}

			<div class="details hidden">
				<hr />
				<table class="table table-striped table-bordered">
					
					<tr><td class="col-xs-4" rowspan="30">
						<img src="http://cdn.steamcommunity.com/economy/image/{{ item.icon_url_large ? item.icon_url_large : item.icon_url }}/300x300" alt="{{ item.market_name }}" />
					</td></tr>
					<tr><td class="text-right">Seller:</td><td><a href="http://steamcommunity.com/profiles/{{ listing.user_id }}">{{ listing.user.name }}</a></td></tr>
					<tr><td class="text-right">Listing ID:</td><td>{{ hashid(listing.id) }} ({{ listing.id }})</td></tr>
					<tr><td class="text-right">Item ID:</td><td>{{ listing.item_id }}</td></tr>
					{% if listing.message %}<tr><td class="text-right">Notes:</td><td>{{ listing.message }}</td></tr>{% endif %}
					{% for dt in item.descriptiontags %}
					{% set tag = dt.tag %}
					<tr><td class="text-right col-xs-2">{{ tag.category_name }}:</td><td>{{ tag.name }}</td></tr>
					{% endfor %}
					<tr><td class="text-right">Storage Bot: </td><td><a href="http://steamcommunity.com/profiles/{{ listing.bot_id }}">{{ listing.bot.name }}</a></td></tr>

					{% if item.stackable != 1 %}
					{% if listing.note_playside or listing.screenshot_playside %}
					<tr><td class="text-right">Playside:</td><td>{{ listing.note_playside|default('--') }} 
						{% if listing.screenshot_playside %}
						<button class="btn btn-default btn-xs screenshot-toggle pull-right"><span class="glyphicon glyphicon-camera"></span> </button>
						<a target="_blank" class="screenshot hidden" href="{{ listing.screenshot_playside }}"><img src="{{ imgur_thumb(listing.screenshot_playside) }}" /></a>
						{% endif %}
					</td></tr>
					{% endif %}
					{% if listing.note_backside or listing.screenshot_backside %}
					<tr><td class="text-right">Backside:</td><td>{{ listing.note_backside|default('--') }} 
						{% if listing.screenshot_backside %}
						<button class="btn btn-default btn-xs screenshot-toggle pull-right"><span class="glyphicon glyphicon-camera"></span> </button>
						<a target="_blank" class="screenshot hidden" href="{{ listing.screenshot_backside }}"><img src="{{ imgur_thumb(listing.screenshot_backside) }}" /></a>
						{% endif %}
					</td></tr>
					{% endif %}
					{% endif %}
				</table>
			</div>
		</td>
		<td class="col-xs-1">{{ listing.updated_at.format('m/d/Y H:i') }}</td>
		<td class="col-xs-1">{{ money_format(listing.price) }}</td>
		<td class="col-xs-2">
			<div class="btn-group btn-group-justified col-xs-6">
			{% if listing.stage == constant('Listing::STAGE_REVIEW') %}
				{% if listing.checkout == 1 %}
				<a href="#" class="btn btn-default btn-sm disabled">{{ listing.checkout_user.name }} Checked Out</a>
				{% else %}
				<a href="{{ config('core.url') }}/admin/listing/{{ listing.id }}/approve" class="btn btn-success btn-sm"> Approve</a>
				<a href="{{ config('core.url') }}/admin/listing/{{ listing.id }}/deny" class="btn btn-danger btn-sm"> Deny</a>
				{% endif %}
			{% elseif listing.stage == constant('Listing::STAGE_LIST') %}
				{% if item.stackable != 1 %}
				<a href="{{ config('core.url') }}/admin/feature/{{ listing.id }}" class="btn btn-default btn-sm"> Toggle Featured</a>
				{% endif %}
				<a href="{{ config('core.url') }}/admin/listing/{{ listing.id }}/cancel" class="btn btn-danger btn-sm">Cancel</a>
			{% endif %}
			</div>
			<span class="hidden">{{ listing.trade_code }}</span>
		</td>
	</tr>
	{% endfor %}
</table>
{% endif %}
{% endblock %}