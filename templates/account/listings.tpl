{% extends 'global/layout.tpl' %}

{% block header %}
<h2>My Listings</h2>
{% endblock %}

{% block content %}
{% for listing in listings %}
<div class="panel panel-default">
	<div class="panel-heading">
		<div class="row"  style="vertical-align: bottom">
			<div class="col-xs-8">
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
				{% elseif listing.stage == constant('Listing::STAGE_ORDER') %}
					<span class="label label-warning">Pending Order</span>
				{% elseif listing.stage == constant('Listing::STAGE_COMPLETE') %}
					<span class="label label-success">Pending Cashout</span>			
				{% else %}
					<span class="label label-success">Completed - Archived</span>
				{% endif %}
				
				{{ listing.description.name }}
			</div>
			<div class="col-xs-4 text-right">
				<span class="text-muted">Updated {{ relative_time(listing.updated_at) }}</span>
			</div>
		</div>
	</div>
	<div class="panel-body">
		<div class="row">
			<div class="col-xs-3 text-center">
				<img class="small inventory-item" src="http://cdn.steamcommunity.com/economy/image/{{ listing.description.icon_url_large ? listing.description.icon_url_large : listing.description.icon_url }}/200x200" alt="{{ listing.description.name }}" /> <br>
				{% if listing.children %}
					<span class="item-bulk-quantity">x{{ listing.children|length + 1 }}</span>
				{% else %}
				<a class="btn btn-default btn-block" href="{{ config('core.url') }}/listing/{{ hashid(listing.id) }}">Details</a>
				{% endif %}
			</div>
			<div class="col-xs-5 text-center">
				{% if listing.children %}
						<div style="height: 3em;"></div>
						<table class="table text-left col-xs-10">
						<tr><td width="25%" class="text-right">Price:</td><td>{{ money_format(listing.price) }}</td></tr>
						<tr><td class="text-right">Created: </td><td>{{ listing.created_at.format('m/d/Y') }}</td></tr>
						{% if listing.bot %}
						<tr><td class="text-right">Storage Bot: </td><td><a href="http://steamcommunity.com/profiles/{{ listing.bot_id }}">{{ listing.bot.name }}</a></td></tr>
						{% endif %}
					</table>
				{% else %}
					{% if listing.message %}
					<blockquote>{{ listing.message }}</blockquote>
					{% else %}
					<div style="height: 3em;"></div>
					{% endif %}

					<table class="table text-left col-xs-10">
						<tr><td width="25%" class="text-right">Price:</td><td>{{ money_format(listing.price) }}</td></tr>
						<tr><td class="text-right">Created: </td><td>{{ listing.created_at.format('m/d/Y') }}</td></tr>
						{% if listing.bot %}
						<tr><td class="text-right">Storage Bot: </td><td><a href="http://steamcommunity.com/profiles/{{ listing.bot_id }}">{{ listing.bot.name }}</a></td></tr>
						{% endif %}
						{% if listing.screenshot_playside %}
						<tr><td class="text-right">Playside:</td> 
							<td>{{ listing.note_playside }} 
							<button class="btn btn-default btn-xs screenshot-toggle pull-right"><span class="glyphicon glyphicon-camera"></span> </button>
							<a class="screenshot hidden" href="{{ listing.screenshot_playside }}"><img src="{{ imgur_thumb(listing.screenshot_playside) }}" /></a> 
						</td></tr>
						{% endif %}

						{% if listing.screenshot_backside %}
						<tr><td class="text-right">Backside:</td> 
							<td>{{ listing.note_backside }} 
							<button class="btn btn-default btn-xs screenshot-toggle pull-right"><span class="glyphicon glyphicon-camera"></span> </button>
							<a class="screenshot hidden" href="{{ listing.screenshot_backside }}"><img src="{{ imgur_thumb(listing.screenshot_backside) }}" /></a> 
						</td></tr>
						{% endif %}
					</table>
				{% endif %}
			</div>

			<div class="col-xs-3 col-xs-offset-1">
				<div class="btn-group-vertical btn-block">
				{% if listing.stage == constant('Listing::STAGE_REQUEST') or listing.stage == constant('Listing::STAGE_DENY') or listing.stage == constant('Listing::STAGE_CANCEL') %}
				{% if listing.trade_url %}
					<a target="_blank" href="{{ listing.trade_url }}" class="btn btn-primary">Trade Offer</a>
					<a href="#" class="btn btn-default disabled">Code : {{ listing.trade_code }}</a>
					{% else %}
					<a href="#" class="btn btn-default disabled">Waiting for Trade Offer</a>
				{% endif %}
				{% endif %}
				</div>

				<div class="btn-group-vertical btn-block">
				{% if listing.stage <= constant('Listing::STAGE_LIST') %}
				{% if not listing.request_takedown %}
					<a href="{{ config('core.url') }}/takedown/{{ hashid(listing.id) }}" class="btn btn-danger">
						{% if listing.stage == constant('Listing::STAGE_REQUEST') %}
						Cancel
						{% else %}
						Request Cancellation
						{% endif %}
					</a>
				{% else %}
					<a href="#" class="btn btn-default disabled">Requested Cancellation</a>
				{% endif %}
				{% endif %}
				</div>			
			</div>
		</div>
	</div>
</div>
{% endfor %}

<ul class="pager">
	{% if page_num > 0 %}
	<li class="previous"><a href="{{ config('core.url') }}/account/listings?p={{ page_num - 1 }}">&larr; Newer</a></li>
	{% endif %}
	{% if (page_num + 1) < total %}
	<li class="next"><a href="{{ config('core.url') }}/account/listings?p={{ page_num + 1 }}">Older &rarr;</a></li>
	{% endif %}
</ul>
{% endblock %}