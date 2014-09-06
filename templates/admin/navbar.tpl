<div id="main-navbar" class="navbar navbar-inverse" role="navigation">		
	<div class="navbar-inner">
		<div class="navbar-header" style="padding-left: 10px !important">
			<a class="navbar-brand" href="{{ config('core.url') }}/admin">
				<img src="{{ config('core.static') }}/imgs/logo.png" style="height: 20px">
			</a>
		</div>

		<div id="main-navbar-collapse" class="collapse navbar-collapse main-navbar-collapse">
			<div>
				<div class="right clearfix">
					<ul class="nav navbar-nav pull-right right-navbar-nav">
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">
								<span>{{ user.name }} <b class='caret'></b></span>
							</a>
							<ul class="dropdown-menu">
								<li><a href="{{ config('core.url') }}">Back to CSGOShop &rarr;</a></li>
								<li class="divider"></li>
								<li><a href="{{ config('core.url') }}/account/logout">Logout</a></li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>