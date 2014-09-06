<ol class="breadcrumb">
	{% for breadcrumb in page.breadcrumbs %}
		<li><a href="{{ config('core.url') }}/{{ breadcrumb.link }}">{{ breadcrumb.text }}</a></li>
	{% endfor %}
</ol>