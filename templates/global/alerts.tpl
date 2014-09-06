{% for alert in page.alerts %}
	<div class="alert alert-{{ alert.type }}">
		<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		{{ markdown(alert.message)|raw }}
	</div>
{% endfor %}