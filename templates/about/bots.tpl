{% extends 'global/layout.tpl' %}

{% block content %}
	<div class="page-header">
		<h1>Bot Status</h1>
	</div>
	<div class="row">
		{% for bot in bots %}
			<div class="col-lg-6">
				<div class="well well">
					<div class='pull-left'>
						<img src="{{ bot.getAvatar() }}" class='bot-avatar img-rounded'>
					</div>
					<b><a href='http://steamcommunity.com/profiles/{{ bot.id }}/'>{{ bot.getName() }}</a></b>
					<div>
						<span class="text-muted">{{ bot.getType() }}</span>
					</div>
					<div>
						<b>{{ bot.getStatus()|raw }}</b>
					</div>
				</div>
			</div>
		{% endfor %}
	</div>
{% endblock %}