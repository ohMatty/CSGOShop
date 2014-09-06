{% extends 'global/layout.tpl' %}

{% block header %}
{% endblock %}

{% block content %}
{% if user.isLoggedIn() %}
<div class="alert alert-info">
	<h2 style="margin-top:0">Welcome.</h2>
	Thank you for your interest in CSGOShop! <br>
	We are currently building a database of item information before we launch to better organize listings. By signing up with your Steam account, your inventory has been scanned and added to our database. Thank you for your patience as we prepare the site.
	<br><br>
	<p class="text-center">
		<img src="{{ config('core.static')}}/imgs/preregistration.jpg">
		
	</p>
	<h3 class="text-center"><strong>May Gaben Bless This Launch.</strong></h3>
		
</div>
{% else %}
<div class="alert alert-info">
	<h2 style="margin-top:0">Welcome.</h2>
	Thank you for your interest in CSGOShop! <br>
	We are currently building a database of item information before we launch to better organize listings. We invite you to sign up using your Steam account so that your inventory can be scanned and added to our database. Using your Steam account to login is much more secure than a username and password and lets us identify you in the Steam Community.
	<br><br>
	<b>Use the login button below!</b>
	<div class="text-center">
		<a href='{{ steamLoginUrl }}'>
			<img src='{{ config('core.static')}}/imgs/steam_login.png'>
		</a>
	</div>
</div>
{% endif %}
{% endblock %}