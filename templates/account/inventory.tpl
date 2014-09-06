{% extends 'global/layout.tpl' %}
{% block header%}
<h2>
	Inventory
	<small>Last Updated {{ relative_time(user.updated_at) }}</small>
	{% if user.isSiteDeveloper() %}<small style='font-size:12px;'><a href="{{ config('core.url') }}/account/inventory?force_refresh">Force Refresh</a></small>{% endif %}
</h2>
{% endblock %}

{% block content %}
<div id="inventoryLoad" class="col-xs-offset-2 col-xs-8 text-center">
	<p>Your inventory is being loaded, please wait.</p>

	<div class="progress progress-striped active">
		<div class="progress-bar" style="width: 100%"></div>
	</div>
</div>

<div class="modal fade" id="requestListingModal" tabindex="-1" role="dialog" aria-labelledby="requestListingLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <!-- <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button> -->
        <h4 class="modal-title" id="requestListingLabel">Request a Listing</h4>
      </div>
      <div class="modal-body">
		<div id="requestListingAlert"></div>

		<div id="requestListingForm" class="form-horizontal">
			<div class="form-group">
				<label for="item" class="control-label col-xs-3">Item: </label>
				<p id="item" name="item" class="form-control-static col-xs-9"></p>
			</div>

			<div class="form-group">
				<label for="price" class="control-label col-xs-3">Price ($USD): </label>
				<div class="col-xs-4">
					<input id="price" name="price" type="text" class="form-control" placeholder="0.00" autocomplete="off" />
				</div>
			</div>

			<div class="form-group">
				<label for="quantity" class="control-label col-xs-3">Quantity: </label>
				<div class="col-xs-4">
					<input id="quantity" name="quantity" type="number" class="form-control" placeholder="1" autocomplete="off" />
				</div>
			</div>

			<div class="form-group">
				<label for="note_playside" class="control-label col-xs-3 text-right">Playside Screenshot:<br /><span class="text-muted">(2MB)</span></label>
				<div class="col-xs-6">
					<input id="note_playside" name="note_playside" type="text" class="form-control" placeholder="Playside Pattern" autocomplete="off" /> 
					<input id="screenshot_playside" name="screenshot_playside" type="file" class="form-control" placeholder="Playside Screenshot" />
					<div class="progress progress-striped active" style="display: none;">
						<div class="progress-bar" style="width: 0%"></div>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label for="note_playside" class="control-label col-xs-3 text-right">Backside Screenshot:<br /><span class="text-muted">(2MB)</span></label>
				<div class="col-xs-6">
					<input id="note_backside" name="note_backside" type="text" class="form-control" placeholder="Backside Pattern" autocomplete="off" />
					<input id="screenshot_backside" name="screenshot_backside" type="file" class="form-control" placeholder="Backside Screenshot" />
					<div class="progress progress-striped active" style="display: none;">
						<div class="progress-bar" style="width: 0%"></div>
					</div>
				</div>
			</div>

			<div class="form-group">
				<label for="message" class="control-label col-xs-3">Notes: </label>
				<div class="col-xs-8">
					<textarea id="message" name="message" class="form-control" rows="5" placeholder="Message"></textarea>
				</div>
			</div>
		</div>

		<div id="requestListingLoad" class="text-center" style="display:none;">
			<p>Your request is being processed, please wait.</p>

			<div class="progress progress-striped active">
				<div class="progress-bar" style="width: 100%"></div>
			</div>
		</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button id="requestListingSubmit" type="button" class="btn btn-primary">Request</button>
      </div>
    </div>
  </div>
</div>	

<script id="template" type="x-tmpl-mustache">
{% verbatim %}
{{#items}}
<div class="inventory-item" 
	data-item-id="{{ id }}" 
	data-item-name="{{ market_name }}" 
	data-item-stackable="{{ stackable }}"
	data-item-price-preset="{{ price_preset }}">
	<a href="#" title="{{ market_name }}">
		<img src="http://cdn.steamcommunity.com/economy/image/{{ icon }}/100x100" alt="{{ market_name }}" />
	</a>
</div>
{{/items}}
{% endverbatim %}
</script>
<script src="{{ config('core.static') }}/js/mustache.min.js"></script>
<script src="{{ config('core.static') }}/js/listings.js"></script>
{% endblock %}