{% extends 'global/layout.tpl' %}

{% block content %}
	<div class="page-header">
		<h1>Staff Team</h1>
	</div>
	<div class="row">
		{% for user in staff %}
			<div class="col-lg-6">
				<div class="well well">
					<div class='pull-left'>
						<img src="{{ user.getAvatar() }}" class='bot-avatar img-rounded'>
					</div>
					<b><a href='http://steamcommunity.com/profiles/{{ user.id }}/'>{{ user.name }}</a></b>
					<div>
						<span class="text-muted">{{ user.getRank() }}</span>
					</div>
					<div>
						<strong>{{ user.getStatus()|raw }}</strong>
					</div>
				</div>
			</div>
		{% endfor %}
	</div>
{% endblock %}