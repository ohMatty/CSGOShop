<footer>
	<div class="container">
		<div class="row">
			<div class="column col-lg-5">
				<div class='header'>CSGOSHOP</div>
				<div class='links'>
					<!-- <ul>
						<li><a href="{{ config('core.url') }}/">Featured Items</a></li>
						<li><a href="{{ config('core.url') }}/browse">Browse Items</a></li>
						<li><a href="{{ config('core.url') }}/support">Support Tickets</a></li>
					</ul> -->
					<p>CSGOShop is a marketplace for CS:GO where you can find the rarest of knives, items, and cheap keys and buy them securely with Paypal/Bitcoin/Stripe and quickly receive them via our automated trading bots.</p>
				</div>
			</div>
			<div class="column col-lg-2">
				<div class='header'>About</div>
				<div class='links'>
					<ul>
						{% if user.isRank('Support Technician') %}
						<li><a href="{{ config('core.url') }}/admin">Administration</a></li>
						{% endif %}
						<li><a href="{{ config('core.url') }}/help">FAQ</a></li>
						<li><a href="{{ config('core.url') }}/terms">Terms of Service</a></li>
						<li><a href="{{ config('core.url') }}/privacy">Privacy Policy</a></li>
					</ul>
				</div>
			</div>
			<div class="column col-lg-2">
				<div class='header'>Social</div>
				<div class='links'>
					<ul>
						<li><a href="https://www.facebook.com/csgoshopdotcom">Facebook</a></li>
						<li><a href="https://twitter.com/csgoshopdotcom">Twitter</a></li>
						<li><a href="http://steamcommunity.com/groups/csgoshopdotcom">Steam Group</a></li>
					</ul>
				</div>
			</div>
			<div class="column col-lg-3">
				<div class='header'>Information</div>
				<div class='links'>
					<ul>
						<li>Copyright CSGOShop &copy; 2014</li>
						<li>All Rights Reserved</li>
						<li><a href="http://steampowered.com/">Powered by Steam</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</footer>


