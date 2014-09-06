{% extends 'global/layout.tpl' %}
{% block header %}
	<h2>My Cart</h2>
{% endblock %}
{% block content %}
<script type="text/javascript">
	setTimeout(function () {
		window.location.href = coreURL + '/cart';
	}, 5000);
</script>
{% endblock %}