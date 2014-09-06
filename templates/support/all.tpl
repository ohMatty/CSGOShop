{% extends 'global/layout.tpl' %}
{% block header %}
	<h2>Support Tickets</h2>
{% endblock %}
{% block content %}
	<hr>
		
	<table class="table table-striped table-bordered" style='margin-bottom:0'>
		<thead>
			<tr>
				<th>ID</th>
				<th>Subject</th>
				<th>Created At</th>
				<th>Last Reply</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
			{% if tickets|length == 0 %}
			<tr><td colspan="5">
				<div class="text-center alert alert-info">You have not opened any support tickets.</div>
			</td></tr>
			{% endif %}
			{% for ticket in tickets %}
				<tr>
					<td>{{ hashid(ticket.id) }}</td>
					<td>
						<a href='{{ config('core.url') }}/support/view/{{ hashid(ticket.id) }}'>{{ ticket.subject }}</a>
					</td>
					<td>{{ relative_time(ticket.created_at) }}</td>
					<td>{% if ticket.last_reply %} {{ relative_time(ticket.last_reply) }} {% else %} -- {% endif %}</td>
					<td>{{ ticket.getStatus() }}</td>
				</tr>
			{% endfor %}
		</tbody>
	</table>
	<hr />
		
	<div class="clearfix">
		<a class="btn btn-primary pull-right" href="{{ config('core.url') }}/support/create">Open a Ticket</a>
	</div>
{% endblock %}