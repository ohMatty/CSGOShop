{% extends 'admin/layout.tpl' %}
{% block header %}
	<h2>Manage Pages</h2>
{% endblock %}
{% block content %}
	<table class="table table-striped">
		<thead>
			<tr>
				<th>Title</th>
				<th>Created At</th>
				<th colspan="2">Last Modified</th>
			</tr>
		</thead>
		<tbody>
			{% for page in pages %}
				<tr>
					<td>
						<a href='{{ config('core.url') }}/admin/page/{{ page.id }}'>{{ page.title }}</a>
					</td>
					<td>{{ relative_time(page.created_at) }}</td>
					<td>{{ relative_time(page.updated_at) }}</td>
					<td>{{ page.getUser().name }}</td>
				</tr>
			{% endfor %}
		</tbody>
	</table>
{% endblock %}