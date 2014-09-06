var __AJAX__ =  {
	request_time: 0,
	queries: [],
	sql_time: 0,
	sql_queries: [],
	steam_time: 0,
	steam_queries: []
};

$.ajaxSetup({
	beforeSend: function (jqXHR, settings) {
		__AJAX__.queries.push({
			timestamp: new Date, 
			query: jqXHR
		});
	},
	complete: function (jqXHR, textStatus) {
		var latest = __AJAX__.queries[__AJAX__.queries.length - 1].timestamp.getTime();
		var now = new Date().getTime();
		__AJAX__.request_time =  ((now - latest)).toFixed(2);
		$('.profiler-bar #ajax-req').html(__AJAX__.request_time + 'ms / ' + __AJAX__.queries.length + "<span class='label'>AJAX Requests</span>");
	}
});

var updateAjaxProfiler = function (profiler) {
	__AJAX__.sql_queries = profiler.queries;
	__AJAX__.sql_time = profiler.query_time || 0;
	__AJAX__.steam_queries = profiler.steam_requests;
	__AJAX__.steam_time = profiler.steam_time || 0;

	$('.profiler-bar #ajax-sql').html(__AJAX__.sql_time + 'ms / ' + __AJAX__.sql_queries.length + "<span class='label'>AJAX SQL</span>");
	$('.profiler-bar #ajax-steam').html(__AJAX__.steam_time + 'ms / ' + __AJAX__.steam_queries.length + "<span class='label'>AJAX Steam</span>");
}

$(document).ready(function()
{
	$('body').keyup(function(ev)
	{
		if(ev.keyCode == 192)
		{
			if($('body').hasClass('profiler-enabled'))
			{
				$('.profiler-bar').hide()
				$('body').removeClass('profiler-enabled')
				$.cookie('profiler_bar', 0);
			}
			else
			{
				$('.profiler-bar').show()
				$('body').addClass('profiler-enabled')
				$.cookie('profiler_bar', 1);
			}
		}
	});

	if($.cookie('profiler_bar') == 0)
	{
		$('.profiler-bar').hide()
		$('body').removeClass('profiler-enabled')
	}
});

window.onload = function(){
	setTimeout(function()
	{
		var t = performance.timing;
		var totalRenderTime = t.domComplete-t.navigationStart;

		$('#js-profiler-render-time').text(totalRenderTime)
	}, 0);
}