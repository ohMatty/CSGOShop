{% extends 'global/layout.tpl' %}
{% block header %}
	<h2>
		Support Ticket #{{ hashid(ticket.id) }} 
			{% if ticket.getOwner().id == user.id or user.isRank('Support Technician') %}
			<span class="pull-right">
				{% if ticket.status != constant('SupportTicket::STATUS_CLOSED') %}
					<a href='{{ config('core.url') }}/support/close/{{ hashid(ticket.id) }}' class='btn btn-danger'>Close Ticket</a>
				{% else %}
					<a href='{{ config('core.url') }}/support/open/{{ hashid(ticket.id) }}' class='btn btn-success'>Open Ticket</a>
				{% endif %}
			</span>
			{% endif %}
	</h2>
{% endblock %}
{% block content %}
	<hr>
	<div class="panel panel-default">
		<div class="panel-heading">
			<div class="row"  style="vertical-align: bottom">
				<div class="col-xs-8">
					{% if ticket.status == constant('SupportTicket::STATUS_OPEN') %}
					<span class="label label-success">Open</span>
					{% elseif ticket.status == constant('SupportTicket::STATUS_CLOSED') %}
					<span class="label label-danger">Closed</span>
					{% elseif ticket.status == constant('SupportTicket::STATUS_STAFFREPLY') %}
					<span class="label label-warning">Waiting for Staff Reply</span>
					{% else %}
					<span class="label label-warning">Waiting for User Reply</span>
					{% endif %}
				</div>
				<div class="col-xs-4 text-right">
					<span class="text-muted">
					{% if ticket.last_reply %} Last reply {{ relative_time(ticket.last_reply) }} {% else %} -- {% endif %}
					</span>
				</div>
			</div>
		</div>
		<div class="panel-body">
			<h3 style="margin-top: 0">{{ ticket.subject }}</h3>
			<table class="table table-bordered">
				<tr><td width="40%">User:</td><td>
					<a href='http://steamcommunity.com/profiles/{{ ticket.getOwner().id }}/'>{{ ticket.getOwner().name }}</a>
				</td></tr>
				<tr><td>Created At:</td><td>{{ relative_time(ticket.created_at) }}</td></tr>
				<tr><td>Last Reply:</td><td>{% if ticket.last_reply %} {{ relative_time(ticket.last_reply) }} {% else %} -- {% endif %}</td></tr>
			</table>
			<hr>
			{{ markdown(ticket.body)|raw }}
		</div>
	</div>

	{% for reply in replys %}
		<div class="panel {% if reply.getOwner().isRank('Support Technician') %}panel-danger{% else %}panel-default{% endif %}">
			<div class="panel-heading">
				<a href='http://steamcommunity.com/profiles/{{ reply.getOwner().id }}/'>{{ reply.getOwner().name }}</a>
				<span class="label label-default pull-right">{{ reply.getOwner().getRank() }}</span>
				<small>(<i>{{ relative_time(reply.created_at) }}</i>)</small>
			</div>
			<div class="panel-body">
				{{ markdown(reply.body)|raw }}
			</div>
		</div>
	{% endfor %}

	{% if ticket.status != constant('SupportTicket::STATUS_CLOSED') %}
		<hr>
		<div class="panel panel-default" style='margin-bottom:0'>
			<div class="panel-heading">
			<h4>Reply to support ticket</h4>
			</div>
			<div class="panel-body">
				<form method='POST' action='{{ config('core.url') }}/support/reply/{{ hashid(ticket.id) }}'>
					<div class="form-group">
						<div id="epic-toolbar" class="btn-group btn-toolbar">
							<button type="button" class="btn btn-default" data-command="bold" title="Make selection bold">B</button>
							<button type="button" class="btn btn-default" data-command="italic" title="Make selection italic">I</button>
							<button type="button" class="btn btn-default" data-command="code" title="Make selection inline code block">&gt;</button>

							<button type="button" class="btn btn-default" data-command="list" title="Make selection a list">–</button>
						</div>
					</div>

					<div class="form-group">
						<textarea id="reply-body" class="form-control" name="body" placeholder="Body" style="display: none;"></textarea>
						<div id="epiceditor"></div>
					</div>

					<div class="form-group">
						<div class="col-xs-offset-2 col-xs-offset-10 text-right">
							<input type="submit" class="btn btn-primary" value="Reply" />
						</div>
					</div>
				</form>				
			</div>
		</div>
		<script src="{{ config('core.static') }}/js/epiceditor.min.js"></script>
		<script src="{{ config('core.static') }}/js/epic-toolbar.js"></script>
		<script src="{{ config('core.static') }}/js/epic-commands.js"></script>
		<script type="text/javascript">
			var editor = new EpicEditor({
				textarea: 'reply-body',
				clientSideStorage: false,
				theme: {
					base: '{{ config('core.static')}}/css/epiceditor/base/epiceditor.css',
					preview: '{{ config('core.static')}}/css/epiceditor/preview/github.css',
					editor: '{{ config('core.static')}}/css/epiceditor/editor/epic-dark.css',
				},
				button: {
					fullscreen: false
				}
			}).load();
			var commands = window.DefaultCommands;
			var toolbar = new Toolbar('epic-toolbar', editor, commands);
		</script>		
	{% endif %}
{% endblock %}