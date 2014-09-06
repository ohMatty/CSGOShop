<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="google-site-verification" content="O1rOq1cQa63rbz9Ec2gUZ3R2jbcRPPe_q0P6fjlVt4k" />

		<title>{{ page.title }}</title>

		<link href="{{ config('core.static') }}/css/bootstrap.min.css" rel="stylesheet">
		<link href="{{ config('core.static') }}/css/global.css" rel="stylesheet">
		<link href="{{ config('core.static') }}/imgs/favicon.ico" rel="shortcut icon" />
		<link href='http://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
		{% if user.isSiteDeveloper() %}
			<script src="{{ config('core.static') }}/js/profiler.js"></script>
		{% endif %}
		<script src="{{ config('core.static') }}/js/pusher.min.js"></script>
		<script type="text/javascript">
		/* String dependencies */
		var coreURL = '{{ config('core.url') }}';
		var stripe_pk = '{{ config('stripe.key') }}';
		var __CSRF__ = '{% if user.isLoggedIn() %} {{ user.session.csrf_token }} {% endif %}';
		</script>
		
		<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		
		  ga('create', 'UA-53538454-1', 'auto');
		  ga('send', 'pageview');
		</script>
	</head>
	<body{{ user.isSiteDeveloper() ? ' class="profiler-enabled"' : '' }}>
		{% if user.isSiteDeveloper() %}
			{% include 'profiler/bar.tpl' %}
		{% endif %}

		{% include 'global/navbar.tpl' %}
		{% include 'global/header.tpl' %}

		<section class='content'>
			<div class="container">
				<div class="modal fade" id="lightbox" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog text-center">
						<div class="modal-content">
						</div>
					</div>
				</div>	
				<div>
					{% include 'global/breadcrumbs.tpl' %}
				</div>

				{% block header %}{% endblock %}
				<div id="alerts">
					{% include 'global/alerts.tpl' %}
				</div>

				{% block content %}{% endblock %}

			</div>
		</section>

		{% include 'global/footer.tpl' %}

		<script src="{{ config('core.static') }}/js/bootstrap.min.js"></script>
		<script src="{{ config('core.static') }}/js/cookie.js"></script>
		<script src="{{ config('core.static') }}/js/global.js"></script>
		<script src="{{ config('core.static') }}/js/cart.js"></script>
	</body>
</html>


