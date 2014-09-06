{% extends 'global/layout.tpl' %}

{% block content %}
	<div class="alert alert-info">
		To access the rest of the site, we require all users to login through their Steam account. Using your Steam account to login is much more secure than a username and password and lets us identify you in the Steam Community.
		<br><br>
		<b>Use the login button below!</b>
	</div>
	
	<a href='{{ steamLoginUrl }}'>
		<img src='{{ config('core.static')}}/imgs/steam_login.png'>
	</a>
{% endblock %}