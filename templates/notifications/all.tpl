{% extends 'global/layout.tpl' %}
{% block header %}
	<a href="{{ config('core.url') }}/notifications/mark" class=" pull-right" style="margin-top:20px">Mark all as seen</a>
	<h2>Notifications</h2>
{% endblock %}
{% block content %}
{% if notifications|length == 0 %}
<div class="text-center alert alert-warning">You currently have no notifications.</div>
{% endif %}

<table class="table table-hover table-notifs">
{% for notification in notifications %}
<tr {% if notification.seen == 1 %} class="text-muted" {% endif %}><td>
	<span class="pull-right text-muted">{{ relative_time(notification.created_at)}}</span>
	<div class="pull-left">
		{% if notification.title == 'TRADE' %}
		<span class="glyphicon glyphicon-retweet"></span>
		{% elseif notification.title == 'REVIEW' %}
		<span class="glyphicon glyphicon-briefcase"></span>
		{% elseif notification.title == 'APPROVAL' %}
		<span class="glyphicon glyphicon-ok-circle"></span>
		{% elseif notification.title == 'DENIAL' %}
		<span class="glyphicon glyphicon-remove-circle"></span>
		{% elseif notification.title == 'DELETED' %}
		<span class="glyphicon glyphicon-trash"></span>
		{% elseif notification.title == 'MONEY' %}
		<span class="glyphicon glyphicon-usd"></span>
		{% elseif notification.title == 'ORDER' %}
		<span class="glyphicon glyphicon-shopping-cart"></span>
		{% else %}
		<span class="glyphicon glyphicon-envelope"></span>
		{% endif %}
	</div>
	<div class="notification-body col-xs-10">
		{{ markdown(notification.body)|raw }} 
	</div>
</td></tr>
{% endfor %}
</table>

<ul class="pager">
	{% if page_num > 0 %}
	<li class="previous"><a href="{{ config('core.url') }}/notifications?p={{ page_num - 1 }}">&larr; Newer</a></li>
	{% endif %}
	{% if (page_num + 1) < total %}
	<li class="next"><a href="{{ config('core.url') }}/notifications?p={{ page_num + 1 }}">Older &rarr;</a></li>
	{% endif %}
</ul>
{% endblock %}