var SUGGESTION_ENGINE = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
	queryTokenizer: Bloodhound.tokenizers.whitespace,
	prefetch: {
		url: coreURL+'/items.json',
		ttl: 10000,	// cache for 10s
		// Bloodhound expects objects, so we map strings into JS objects
		filter: function (list) {
			return $.map(list, function(item) { return {value: item}; });
		}
	}

});
SUGGESTION_ENGINE.initialize();

$('.typeahead').typeahead({
	items: 10,
	hint: true,
	highlight: true,
	minLength: 1,
},
{
	name: 'listings',
	displayKey: 'value',
	source: SUGGESTION_ENGINE.ttAdapter()
})
.keypress(function (evt) {
	if(evt.keyCode == 13)
		$('.search-go').trigger('click');
});

$('.search-go').click(function (evt) {
	evt.preventDefault();

	var item = $('input[name=item_name]').val();
	window.location.href = coreURL+'/browse?name=' + item + '&' + $('.advanced-search-form form').serialize();
});

$('.advanced-search-form form').submit(function (evt) {
	evt.preventDefault();

	var item = $('input[name=item_name]').val();
	window.location.href = coreURL+'/browse?name=' + item + '&' + $(this).serialize();
})

$('.advanced-search-toggle').click(function (evt) {
	evt.preventDefault();

	$('.advanced-search-form').slideToggle();
})