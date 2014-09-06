{% extends 'admin/layout.tpl' %}
{% block header %}
	<h2>Support Tickets</h2>
{% endblock %}
{% block content %}
	<table class="table table-review-details">
		<thead>
			<tr>
				<th>Status</th>
				<th>#</th>
				<th>Subject</th>
				<th>User</th>
				<th>Created At</th>
				<th>Last Reply</th>
			</tr>
		</thead>
		<tbody>
			{% for ticket in tickets %}
				<tr>
					<td><span class="label 
						{% if ticket.status == constant('SupportTicket::STATUS_OPEN') %}
						label-success
						{% elseif ticket.status == constant('SupportTicket::STATUS_CLOSED') %}
						label-danger
						{% elseif ticket.status == constant('SupportTicket::STATUS_STAFFREPLY') %}
						label-warning
						{% elseif ticket.status == constant('SupportTicket::STATUS_CUSTOMERREPLY') %}
						label-warning
						{% endif%}
						">{{ ticket.getStatus() }}</span></td>
					<td>{{ hashid(ticket.id) }}</td>
					<td>
						<a href='{{ config('core.url') }}/support/view/{{ hashid(ticket.id) }}'>{{ ticket.subject }}</a>
					</td>
					<td>{{ ticket.user.name }}</td>
					<td>{{ ticket.created_at.format('m/d/Y H:i') }}</td>
					<td>{% if ticket.last_reply %} {{ ticket.last_reply.format('m/d/Y H:i') }} {% else %} -- {% endif %}</td>
				</tr>
			{% endfor %}
		</tbody>
	</table>
{% endblock %}