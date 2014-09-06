{% extends 'global/layout.tpl' %}
{% block header %}
	<h2>Open a Ticket</h2>
{% endblock %}
{% block content %}
	<form method="POST" action="" role="form">
		<div class="form-group">
			<input type="text" class="form-control" id="support-subject" name="subject" placeholder="Subject" />
		</div>

		<div class="form-group">
			<select name="category" id="support-category" class="form-control">
				<option value="BUG">Bug Report</option>
				<option value="SHOP">Transaction Issue</option>
				<option value="MISC">Other</option>
			</select>
		</div>

		<div class="form-group">
			<textarea id="support-body" class="form-control" name="body" placeholder="Body" rows="15"></textarea>
		</div>

		<div class="form-group">
			<div class="col-xs-offset-2 col-xs-offset-10 text-right">
				<a class='btn btn-link' href="{{ config('core.url') }}/support">Cancel</a>
				<input type="submit" class="btn btn-primary" />
			</div>
		</div>
	</form>
{% endblock %}