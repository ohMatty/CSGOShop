{% extends 'global/layout.tpl' %}
{% block header %}
<div class="row">
	<div class="col-xs-6">
		<h2 style="margin-top:0;">
			{% if query|length %}
				Search results for: 

				{# Tag buttons #}
				{% for name, val in query %}
					{% for tag in tags %} {% if tag.internal_name == val %} 
					{% set tag_query = '&' ~ name ~ '=' ~ val %}
					<a class="btn btn-default" href="{{ config('core.url') }}/browse?{{ query_string|replace({(tag_query): ''}) }}">{{ tag.name }} <span class="glyphicon glyphicon-remove" style="font-size: .75em;"></span></a>
					{% endif %} {% endfor %}	
				{% endfor %}
				
				{% if query.name %}
				{# Query name button #}
				{% set name_query = '&name=' ~ query.name|url_encode %}
				<a class="btn btn-default" href="{{ config('core.url') }}/browse?{{ query_string|replace({(name_query): ''}) }}">"{{ query.name }}" <span class="glyphicon glyphicon-remove" style="font-size: .75em;"></span></a>
				{% endif %}
			{% else %} 
				Browse Items
			{% endif %}
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
<div id="listings"></div>
<div id="loading" class="col-xs-offset-2 col-xs-8 text-center hidden">
	<p>Please wait a moment while we load the available listings.</p>

	<div class="progress progress-striped active">
		<div class="progress-bar" style="width: 100%"></div>
	</div>
</div>
{% verbatim %}
<script id="template" type="x-tmpl-mustache">
<div class="row">
{{#descriptions}}
	<div class="col-xs-3 {{text_align}}">
		<div class="item well">
			<a style="border-color: #{{name_color}}" 
			{{#stackable}}
				href="{{coreURL}}/listings/{{id}}">
			{{/stackable}}
			{{^stackable}}
				href="{{coreURL}}/listing/{{id}}">
			{{/stackable}}
				{{#is_stattrak}}<div class="stattrak">ST&#x2122;</div>{{/is_stattrak}}
				<img 					
				src="http://cdn.steamcommunity.com/economy/image/{{icon_url}}/150x150" 
				alt="{{name}}" />
			</a>
			
			<div class="info">
				<div class="name" title="{{name}}"><span>{{name_st}}</span></div>
				{{#exterior}}<span>({{exterior}})</span> <br>{{/exterior}}
				{{#stackable}}
				<span>Starting at {{price}}</span>
				{{/stackable}}
				{{^stackable}}
				<span>{{price}}</span>
				{{/stackable}}
			</div>
			{{^stackable}}
			<div class="actions">
				<button class="btn btn-primary btn-cart-add" data-id="{{id}}">Add</button>
			</div>
			{{/stackable}}
			{{#stackable}}
			<div class="actions">
				<div class="input-group">
					<input type="text" class="form-control cart-quantity" placeholder="Qty" value="1" />
					<span class="input-group-btn"><button class="btn btn-primary btn-cart-add" data-id="{{id}}">Add</button></span>
				</div>
			</div>
			{{/stackable}}
		</div>
	</div>
{{/descriptions}}
</div>
<a href="#" class="next" data-query="{{next_query}}"></a>
{% endverbatim %}
</script>
<script src="{{ config('core.static') }}/js/mustache.min.js"></script>
<script src="{{ config('core.static') }}/js/typeahead.bundle.min.js"></script>
<script src="{{ config('core.static') }}/js/search.js"></script>
<script type="text/javascript">
var __TEMPLATE__ = $('#template').html();
var __LOADED__ = 0;
var __OFFSET__ = 0;
Mustache.parse(__TEMPLATE__);

var grabListings = function () {
	__LOADED__ = $('.item').length;

	if(__LOADED__ != 0 && __LOADED__ <= __OFFSET__)
		return;

	__OFFSET__ = __LOADED__;

	var filter = window.location.search.substring(1);
	$('#loading').toggleClass('hidden');
	
	$.ajax({
		type: 'GET',
		url: coreURL+'/data/listings?'+filter+'&offset='+__LOADED__,
		success: function (data) {
			data.coreURL = coreURL;

			var render = Mustache.render(__TEMPLATE__, data);
			$('#listings').append(render);
			$('#loading').toggleClass('hidden');

			updateAjaxProfiler(data.profiler);
		},
	});
};

$(document).scroll(function() {
	var contentHeight = $(".content .container").height();
	var scrollOffset = $(window).scrollTop();
	var windowHeight = $(window).height();
	var bottomPadding = 50;

	if (scrollOffset + bottomPadding > contentHeight - windowHeight)
		grabListings();
});

grabListings();
</script>
{% endblock %}