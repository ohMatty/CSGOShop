<div id="main-menu" role="navigation">
	<div id="main-menu-inner" style="overflow: hidden; width: auto; height: 100%;">
		<ul class="navigation">
			<li><a href="{{ config('core.url') }}/admin"><span class="menu-icon glyphicon glyphicon-dashboard"></span> Dashboard</a></li>	
			{% if user.isRank('Lead Developer') %}
			<li><a href="{{ config('core.url') }}/admin/pages"><span class="menu-icon glyphicon glyphicon-font"></span> Pages</a></li>
			<li><a href="{{ config('core.url') }}/admin/bots"><span class="menu-icon glyphicon glyphicon-list-alt"></span> Bot Simulator</a></li>
			{% endif %}
			{% if user.isRank('Senior Support Technician') %}
			<li class="mm-dropdown mm-dropdown-root">
				<a href="#"><span class="menu-icon glyphicon glyphicon-list"></span> Listings</a>
				<ul class="mmc-dropdown-delay animated fadeInLeft">
					<li><a href="{{ config('core.url') }}/admin/listings/urgent"><span class="mm-text">Urgent Requests</span></a></li>
					<li><a href="{{ config('core.url') }}/admin/listings"><span class="mm-text">Manage All</span></a></li>
				</ul>
			</li>
			<li class="mm-dropdown mm-dropdown-root">
				<a href="{{ config('core.url') }}/admin/orders"><span class="menu-icon glyphicon glyphicon-shopping-cart"></span> Orders</a>
				<ul class="mmc-dropdown-delay animated fadeInLeft">
					<li><a href="{{ config('core.url') }}/admin/orders/urgent"><span class="mm-text">Confirm Payments</span></a></li>
					<li><a href="{{ config('core.url') }}/admin/orders"><span class="mm-text">Manage All</span></a></li>
				</ul>
			</li>
			{% endif %}
			{% if user.isRank('Managing Director') %}
			<li class="mm-dropdown mm-dropdown-root">
				<a href="{{ config('core.url') }}/admin/cashouts"><span class="menu-icon glyphicon glyphicon-usd"></span> Cashouts</a>
				<ul class="mmc-dropdown-delay animated fadeInLeft">
					<li><a href="{{ config('core.url') }}/admin/cashouts/urgent"><span class="mm-text">Urgent Requests</span></a></li>
					<li><a href="{{ config('core.url') }}/admin/cashouts"><span class="mm-text">Manage All</span></a></li>
				</ul>
			</li>
			{% endif %}
			{% if user.isRank('Support Technician') %}
			<li class="mm-dropdown mm-dropdown-root">
				<a href="{{ config('core.url') }}/admin/users"><span class="menu-icon glyphicon glyphicon-user"></span> Users</a>
				<ul class="mmc-dropdown-delay animated fadeInLeft">
					<li><a href="{{ config('core.url') }}/admin/users/urgent"><span class="mm-text">Flagged Users</span></a></li>
					<li><a href="{{ config('core.url') }}/admin/users"><span class="mm-text">Manage All</span></a></li>
				</ul>
			</li>
			{% endif %}
			<li><a href="{{ config('core.url') }}/admin/tickets"><span class="menu-icon glyphicon glyphicon-earphone"></span> Support Tickets</a></li>
		</ul>
	</div>
</div>