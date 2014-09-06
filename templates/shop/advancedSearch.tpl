<div class="row">
	<div class="col-xs-12">
		<div class="well advanced-search-form" style="display:none;">
			<form class="" action="{{ config('core.url') }}/browse"> 
			<div class="row">
			{% for category in categories %}
			<div class="col-xs-3">
				<ul class="category list-unstyled">
					<li><strong>{{ category.category }}</strong></li>
					{% for tag in tags %}
					{% if tag.category == category.category %}
					<li>
						<input id="tag_{{ loop.index }}" name="tag_{{ loop.index }}" type="checkbox" value="{{ tag.internal_name }}" />
						<label for="tag_{{ loop.index }}">{{ tag.name }}</label>
					</li>
					{% endif %}
					{% endfor %}
				</ul>
			</div>
			{% endfor %}
			</div>

			<div class="row">
				<div class="col-xs-3 col-xs-offset-9 text-right">
					<input class="btn btn-primary btn-block" style="margin-right: 20px;" type="submit" />
				</div>
			</div>
			</form>
		</div>
	</div>
</div>