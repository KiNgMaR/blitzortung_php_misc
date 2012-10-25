var onLocationAvailable = function(o, auto)
{
	$.mobile.pageLoading(true); // stop loader
	if(o.error)
	{
		if(auto)
		{
			alert('We were unable to determine your location. Please enter it manually.');
			$.mobile.changePage($('#activity-location-query'));
		}
		else
		{
			alert('Your input could not be mapped to a location. Please try again.');
		}
		return;
	}

	$.mobile.changePage('/m/activity_result?lat=' +
		escape(o.place.centroid.latitude) + '&lon=' + escape(o.place.centroid.longitude));
};

function onCurrentLocationButtonClick()
{
	$.mobile.pageLoading();
	yqlgeo.get('visitor', function(o) { onLocationAvailable(o, true); });
}

$('#activity-location-query').live('pagecreate',function(event)
{
	$('#location-submit').click(function()
	{
		var locStr = $('#location-input').val();

		$.mobile.pageLoading();
		yqlgeo.get(locStr, function(o) { onLocationAvailable(o, false); });
	});
});
