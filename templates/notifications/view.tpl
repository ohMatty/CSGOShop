{% extends 'global/layout.tpl' %}
{% block header %}
	<h2>Notifications</h2>
{% endblock %}
{% block content %}
	<hr>
	<div class='row'>
		<div class="col-lg-1"><b>Title:</b></div>
		<div class="col-lg-11">{{ notification.title }}</div>
	</div>
	<div class='row'>
		<div class="col-lg-1"><b>Sender:</b></div>
		<div class="col-lg-11"><a href='http://steamcommunity.com/profiles/{{ notification.getSender().id }}/'>{{ notification.getSender().name }}</a></div>
	</div>
	<div class='row'>
		<div class="col-lg-1"><b>Date:</b></div>
		<div class="col-lg-11">{{ relative_time(notification.created_at) }}</div>
	</div>
	<br>
	<div class='row'>
		<div class="col-lg-12">
			<div class="well" style='margin-bottom:0'>
				{{ markdown(notification.body)|raw }}
			</div>
		</div>
	</div>
{% endblock %}