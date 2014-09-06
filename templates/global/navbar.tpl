<nav class="navbar navbar-default navbar-fixed-top">
	<div class="container">
		<div class="navbar-header">
			<div class="navbar-brand">
				<a href="{{ config('core.url') }}"><img src="{{ config('core.static') }}/imgs/logo.png"></a>
			</div>
		</div>

		<ul class='nav navbar-nav navbar-right'>
		</ul>

		<ul class="nav navbar-nav">
			<li{{ page.activeTab == 'featured' ? ' class="active"' : ''}}>
				<a href="{{ config('core.url') }}/">Featured</a>
			</li>
			<li{{ page.activeTab == 'browse' ? ' class="active"' : ''}}>
				<a href="{{ config('core.url') }}/browse">Browse</a>
			</li>
			<li{{ page.activeTab == 'support' ? ' class="active"' : ''}}>
				<a href="{{ config('core.url') }}/support">Support</a>
			</li>
			<li class="dropdown{{ page.activeTab == 'about' ? ' active' : ''}}">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown"> About <b class='caret'></b></a>
				<ul class="dropdown-menu">
					<li><a href="{{ config('core.url') }}/help">FAQ</a></li>
					<li><a href="{{ config('core.url') }}/affiliates">Affiliates</a></li>
					<li><a href="{{ config('core.url') }}/staff">Staff</a></li>
					<li><a href="{{ config('core.url') }}/bots">Bot Status</a></li>
				</ul>
			</li>

		</ul>

		<ul class='nav navbar-nav navbar-right'>
		{% if user.isLoggedIn() %}
			<li class="dropdown">
				<a href="{{ config('core.url') }}/notifications"><span class='notification-count badge'><span class="notification-count-value">{{ notification_count }}</span></span></a>
			</li>
			<li class="dropdown{{ page.activeTab == 'account' ? ' active' : ''}}">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown"> {{ user.name }} <b class='caret'></b></a>
				<ul class="dropdown-menu">
					<li><a href="{{ config('core.url') }}/account/inventory">Inventory</a></li>
					<li><a href="{{ config('core.url') }}/account/listings">My Listings</a></li>
					<li><a href="{{ config('core.url') }}/account/wallet">My Wallet</a></li>
					<li><a href="{{ config('core.url') }}/account/orders/active">Active Orders</a></li>
					<li><a href="{{ config('core.url') }}/account/orders">Order History</a></li>
					<li><a href="{{ config('core.url') }}/account/settings">Settings</a></li>
					<li class="divider"></li>
					<li><a href="{{ config('core.url') }}/account/logout">Logout</a></li>
				</ul>
			</li>
		{% else %}
			<li{{ page.activeTab == 'account' ? ' class="active"' : ''}}><a href="{{ config('core.url') }}/account/login">Login</a></li>
		{% endif %}
			<li class="dropdown{{ page.activeTab == 'cart' ? ' active' : ''}}">
				<a href="{{ config('core.url') }}/cart"><span class="glyphicon glyphicon-shopping-cart"></span> Cart <span class='cart-items-total badge'>{{ cart_count }}</span></a>
			</li>
		</ul>
	</div>
</nav>