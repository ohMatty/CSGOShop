{% extends 'global/layout.tpl' %}
{% block header%}
<h2>Terms &amp; Conditions</h2>
{% endblock %}

{% block content %}
<div class="well">
	<div class="row">
		<div class="col-xs-12 terms-review">
			{{ markdown(pageData.body)|raw }}
		</div>
	</div>

	<div class="row">
		<div class="col-xs-5 col-xs-offset-7 text-right">
			<form action="" method="POST">
				<input id="agree" type="checkbox" name="agree">
				<label for="agree">I have read and agree to these terms and conditions.</label>
				<br>
				<input class="btn btn-primary" type="submit" />
			</form>
		</div>
	</div>
</div>
{% endblock %}