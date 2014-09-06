{% extends 'global/layout.tpl' %}

{% block content %}
	<div class="page-header">
		<h1>{{ pageData.title }}</h1>
	</div>
	{{ markdown(pageData.body)|raw }}
	<small>
		<i>
			(Last updated at {{ relative_time(pageData.updated_at) }} by <a href='http://steamcommunity.com/profiles/{{ pageData.getUser().id }}/'>{{ pageData.getUser().name }}</a>)
		</i>
	</small>
{% endblock %}