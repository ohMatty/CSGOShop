<div class="profiler-bar">
	<div class="container-fluid">
		<div class="stats pull-right">
			<div class="stat" id="ajax-sql">
				0ms / 0
				<span class='label'>AJAX SQL</span>
			</div>
			<div class="stat" id="ajax-steam">
				0ms / 0
				<span class='label'>AJAX Steam</span>
			</div>
			<div class="stat" id="ajax-req">
				0ms / 0
				<span class='label'>AJAX Requests</span>
			</div>

			<div class="stat">
				{{ page.profiler.query_time }}ms / {{ page.profiler.queries|length }}
				<span class='label'>SQL</span>
			</div>

			<div class="stat">
				{{ page.profiler.steam_request_time ? page.profiler.steam_request_time : 0 }}ms / {{ page.profiler.steam_requests|length }}
				<span class='label'>Steam Requests</span>
			</div>

			<div class="stat">
				<span id='js-profiler-render-time'>0</span>ms
				<span class='label'>Render Time</span>
			</div>
		</div>
	</div>
</div>