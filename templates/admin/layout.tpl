<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>{{ page.title }}</title>
	<link href="{{ config('core.static') }}/css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<link href="{{ config('core.static') }}/css/pixel-admin.min.css" rel="stylesheet" type="text/css">
	<link href="{{ config('core.static') }}/css/themes.min.css" rel="stylesheet" type="text/css">
	<link href="{{ config('core.static') }}/css/dataTables.bootstrap.css" rel="stylesheet" type="text/css">
	<style>
	table.table thead .sorting {background: url('{{ config('core.static') }}/imgs/sort_both.png') no-repeat center right;}
	table.table thead .sorting_asc {background: url('{{ config('core.static') }}/imgs/sort_asc.png') no-repeat center right;}
	table.table thead .sorting_desc {background: url('{{ config('core.static') }}/imgs/sort_desc.png') no-repeat center right;}
	table.table thead .sorting_asc_disabled {background: url('{{ config('core.static') }}/imgs/sort_asc_disabled.png') no-repeat center right;}
	table.table thead .sorting_desc_disabled {background: url('{{ config('core.static') }}/imgs/sort_desc_disabled.png') no-repeat center right;}
	</style>
	<link href="{{ config('core.static') }}/css/global.css" rel="stylesheet">
	<link href="{{ config('core.static') }}/css/admin.css" rel="stylesheet" type="text/css">
	<link href="{{ config('core.static') }}/imgs/favicon.ico" rel="shortcut icon" />
	<link href='http://fonts.googleapis.com/css?family=Lato:400,700' rel='stylesheet' type='text/css'>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
	<script src="{{ config('core.static') }}/js/jquery.dataTables.min.js"></script>
	<script src="{{ config('core.static') }}/js/dataTables.bootstrap.js"></script>
	<script type="text/javascript">
		/* String dependencies */
		var coreURL = '{{ config('core.url') }}';
		var __CSRF__ = '{% if user.isLoggedIn() %} {{ user.session.csrf_token }} {% endif %}';
	</script>
</head>
<body class="theme-default">
	<div id="main-wrapper">
		{% include 'admin/navbar.tpl' %}
		{% include 'admin/sidebar.tpl' %}

		<div id="content-wrapper">
			
			<div class="modal fade" id="lightbox" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog text-center">
					<div class="modal-content">
					</div>
				</div>
			</div>	

			{% include 'global/breadcrumbs.tpl' %}
			<div class="page-header">{% block header %}{% endblock %}</div> 

			<div id="alerts">{% include 'global/alerts.tpl' %}</div>
			{% block content %}{% endblock %}
		</div>
		<div id="main-menu-bg"></div>
	</div>

	<script src="{{ config('core.static') }}/js/bootstrap.min.js"></script>
	<script src="{{ config('core.static') }}/js/pixel-admin.min.js"></script>
	<script src="{{ config('core.static') }}/js/global.js"></script>
	<script type="text/javascript">
		window.PixelAdmin.start(function(){});
		$('.details-toggle').click(function (evt) { $(this).find('.details').toggleClass('hidden'); });
		$('.details').click(function (evt) { evt.stopPropagation(); });

		var timeCol = Math.max(
			$('.table-review-details').find('th:contains("Updated")').index(), 
			$('.table-review-details').find('th:contains("Created")').index(),
			0
		);

		$('.table-review-details').DataTable({
			order: [timeCol, 'desc']
		});
	</script>
</body>
</html>