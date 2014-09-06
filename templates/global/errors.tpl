{% for error in page.errors %}
	<div class="alert alert-{{ error.type }}">
		{{ error.message }}
	</div>
{% endfor %}