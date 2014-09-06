{% extends 'admin/layout.tpl' %}

{% block header %}
<h2>Notify User</h2>
{% endblock %}

{% block content %}
	<form method="POST" role="form">
		<div class="form-group">
			<select name="title" id="title" class="form-control">
				<option value="MSG">Message</option>
				<option value="REVIEW">Review</option>
				<option value="DENIAL">Denial</option>
				<option value="DELETED">Deletion</option>
			</select>
		</div>

		<div class="form-group">
			<div id="epic-toolbar" class="btn-group btn-toolbar">
				<button type="button" class="btn btn-default" data-command="bold" title="Make selection bold">B</button>
				<button type="button" class="btn btn-default" data-command="italic" title="Make selection italic">I</button>
				<button type="button" class="btn btn-default" data-command="code" title="Make selection inline code block">&gt;</button>

				<button type="button" class="btn btn-default" data-command="list" title="Make selection a list">–</button>
			</div>
		</div>

		<div class="form-group">
			<textarea name="body" id="body" rows="30" style="display: none;"></textarea>
			<div id="epiceditor"></div>
		</div>

		<div class="form-group text-right">
			<a class='btn btn-link' href="{{ config('core.url') }}/admin/pages">Cancel</a>
			<input type="submit" class="btn btn-primary" />
		</div>
	</form>


	<script src="{{ config('core.static') }}/js/epiceditor.min.js"></script>
	<script src="{{ config('core.static') }}/js/epic-toolbar.js"></script>
	<script src="{{ config('core.static') }}/js/epic-commands.js"></script>

	<script type="text/javascript">
		var editor = new EpicEditor({
			clientSideStorage: false,
			textarea: 'body',
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
{% endblock %}