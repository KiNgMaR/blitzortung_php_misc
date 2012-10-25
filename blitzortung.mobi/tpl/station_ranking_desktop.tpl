<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>{% trans "TOA Ranking" %}{% if station %} - Station {{ station.city }}{% endif %}</title>
	<link rel="stylesheet" type="text/css" href="/js/jui/smoothness/jquery-ui-1.8.12.custom.css" />
	<link rel="stylesheet" type="text/css" href="/js/datatables/css/demo_table_jui.css" />
	<style type="text/css">
	* {
		margin: 0;
		padding: 0;
	}
	body {
		font-family: Arial, sans-serif;
	}
	#section-top, #section-bottom {
		width: 700px;
		overflow: hidden;
	}
	#section-bottom {
		clear: both;
		margin: 1em;
	}
	td, th {
		padding: 3px 10px;
	}
	td {
		line-height: 150%;
	}
	td.center {
		text-align: center;
	}
	.css_right {
		float: right;
	}
	#stations {
		width: 100%;
	}
	#stations th, #stations_wrapper th {
		cursor: pointer;
		font-size: 80%;
	}
	#stations th:first-child, #stations_wrapper th:first-child {
		cursor: default;
	}
{% if scroll_mode %}
	#stations_wrapper td {
		font-size: 90%;
		white-space: nowrap;
	}
{% endif %}
	#stations td.long {
		font-size: 90%;
	}
	.ui-widget {
		font-size: 80%;
	}
	#minimap {
		width: {{ IMG_WIDTH }}px;
		height: {{ IMG_HEIGHT }}px;
		background-repeat: no-repeat;
		border: 1px solid #AAA;
	}
	#minimap img {
		border: none;
		text-decoration: none;
	}
	#section-top {
		margin: 1em;
		margin-bottom: 0;
	}
	#section-top-left {
		float: left;
		width: 100%;
		max-width: 400px;
	}
	#section-top-right {
		float: right;
	}
	#stations td.loc {
		background-position: 5px 50%;
		background-repeat: no-repeat;
		padding-left: 27px;
	}
	h1 {
		font-size: 140%;
		letter-spacing: 3px;
		font-family: "Trebuchet MS Bold", Tahoma, Arial, sans-serif;
	}
	p#subtitle {
		text-align: right;
		font-size: 70%;
		border-top: 1px solid #aaa;
	}
	#summary {
		padding: 0.75em 0.5em 0 0;
		line-height: 150%;
	}
	#station-badge {
		text-align: center;
		margin-top: 30px;
	}
	#station-badge img {
		-moz-box-shadow: 0 0 5px #888;
		-webkit-box-shadow: 0 0 5px#888;
		box-shadow: 0 0 5px #888;
	}
	</style>
</head>
<body>

<div id="section-top">
	<div id="section-top-left">
		<h1>TOA {% trans "Blitzortung" %}</h1>
		<p id="subtitle"><a href="http://www.blitzortung.org/">www.blitzortung.org</a></p>
		{% if has_strikes %}
		<div id="summary">
			<p><strong>{{ pstats.station_count }}</strong> {% trans "stations have located a total of" %} <strong>{{ pstats.strikes_1h }}</strong> {% trans "strikes during the last 60 minutes." %}
			{% trans "The maximum number of stations that have participated in locating a single strike was" %} <strong>{{ pstats.max_stations }}</strong>{% trans "_after_max_stations." %}
				{% trans "The average number of stations per strike was" %} <strong>{{ pstats.avg_stations }}</strong>.</p>
		</div>
		{% if station_badge_url %}
		<div id="station-badge">
			<img src="{{ station_badge_url }}" alt="" width="300" height="70" />
		</div>
		{% endif %}
		{% else %}
		<div class="ui-widget">
			<div class="ui-state-error ui-corner-all" style="margin-top: 10px; padding: 0.7em; line-height: 150%;"> 
				<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: 0.3em;"></span> 
				<strong>{% trans "Good weather alert" %}:</strong> {% trans "No strikes have been located by the network during the last hour." %}</p>
			</div>
		</div>
		{% endif %}
	</div>
	<div id="section-top-right">
		<div id="minimap" style="background-image:url('{{ map_image_url }}')">
			{% if overlay_image_url %}<a href="http://www.blitzortung.org/Webpages/index.php?page=1"><img src="{{ overlay_image_url }}" alt="" /></a>{% endif %}
		</div>
	</div>
</div>

<script type="text/javascript" src="http://code.jquery.com/jquery-1.5.2.min.js"></script>
<script type="text/javascript" src="/js/datatables/js/jquery.dataTables.min.js"></script>

<div id="section-bottom">

	{% if has_strikes %}
	<table id="stations">
		<thead>
			<tr>
				<th>{% trans "No." %}</th>
				<th>{% trans "Location" %}</th>
				<th>{% trans "Strikes" %}</th>
				<th>{% trans "Sig/h" %}</th>
				<th>{% trans "S/S Ratio" %}</th>
				<th>{% trans "Efficiency" %}</th>
			</tr>
		</thead>
		<tbody>
		{% for station in ranking %}
		<tr{% if highlight_station_id == station.my_id %} class="highlighted"{% endif %}>{% spaceless %}
			<td class="center"></td>
			<td class="loc{% if station.city|length > 24 %} long{% endif %}"{% if station.country_code %} style="background-image:url('http://static.irsoft.de/flags/{{ station.country_code }}.png')"{% endif %}>{{ station.city }}</td>
			<td>{{ station.strike_count }} S | {{ station.strike_ratio }} %</td>
			<td>{{ station.signal_count }}</td>
			<td>{{ station.signal_ratio }} %</td>
			<td>{{ station.efficiency }} %</td>
		{% endspaceless %}</tr>
		{% endfor %}
		</tbody>
	</table>

	<div class="ui-widget">
		<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; margin-bottom: 10px; padding: 0.7em; width: 325px; line-height: 150%; float: left;"> 
			<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: 0.3em;"></span>
			<strong>{% trans "S/S Ratio" %}:</strong> {% trans "How many of the sent signals have been used to locate actual strikes (strike/signal ratio)." %}</p>
		</div>
	</div>

	<div class="ui-widget">
		<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; margin-bottom: 10px; padding: 0.7em; width: 325px; line-height: 150%; float: right;"> 
			<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: 0.3em;"></span>
			<strong>{% trans "Efficiency" %}:</strong> {% trans "100% efficiency means that the station participated in every single strike and did not send any noise signals." %}</p>
		</div>
	</div>
	{% endif %}

	<div class="ui-widget">
		<div class="ui-state-highlight ui-corner-all" style="margin-top: 10px; padding: 0.7em; line-height: 150%; clear: both;"> 
			<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: 0.3em;"></span>
			<strong>{% trans "Last update" %}:</strong> {% laststrikeupdate %} - {% trans "Table is based on last hour's data." %}</p>
		</div>
	</div>

	<div class="ui-widget">
		<div class="ui-state-highlight ui-corner-all" style="margin-top: 10px; padding: 0.7em; line-height: 150%; clear: both;"> 
			<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: 0.3em;"></span>
			<strong>{% trans "Copyright" %}:</strong> {% trans "© Data by www.blitzortung.org and participating station owners." %}<br />
			{% trans "This page is being maintained by Ingmar Runge." %}</p>
		</div>
	</div>

</div>

<script type="text/javascript">

jQuery.fn.dataTableExt.aTypes.unshift(
	function(sData)
	{
		if(sData.match(/^-?\d+(.\d+)?([ %A-Za-z]|$)/))
		{
			return 'numeric-simple';
		}
		return null;
	}
);

var myToFloat = function(s)
{
	return parseFloat(s.replace(/^(-?\d+(.\d+)?)([ %A-Za-z]|$)/, '$1'));
};

jQuery.fn.dataTableExt.oSort['numeric-simple-asc'] = function(a, b) {
	var x = myToFloat(a), y = myToFloat(b);
	return ((x < y) ? -1 : ((x > y) ? 1 : 0));
};

jQuery.fn.dataTableExt.oSort['numeric-simple-desc'] = function(a, b) {
	var x = myToFloat(a), y = myToFloat(b);
	return ((x < y) ? 1 : ((x > y) ? -1 : 0));
};

var dtLang = {
	sProcessing:   "{% trans "Processing..." %}",
	sLengthMenu:   "{% trans "Show _MENU_ entries" %}",
	sZeroRecords:  "{% trans "No matching records found" %}",
	sInfo:         "{% trans "Showing _START_ to _END_ of _TOTAL_ entries" %}",
	sInfoEmpty:    "{% trans "Showing 0 to 0 of 0 entries" %}",
	sInfoFiltered: "{% trans "(filtered from _MAX_ total entries)" %}",
	sInfoPostFix:  "",
	sSearch:       "{% trans "Search:" %}",
	sUrl:          '',
	oPaginate: {
		sFirst:      "{% trans "First" %}",
		sPrevious:   "{% trans "Previous" %}",
		sNext:       "{% trans "Next" %}",
		sLast:       "{% trans "Last" %}"
	}
};

$(document).ready(function() {
	$('#stations').dataTable({
		bJQueryUI: true,
		bAutoWidth: false,
		oLanguage: dtLang,
		/*bStateSave: true,*/
{%if scroll_mode %}
		sScrollY: '400px',
		bPaginate: false,
{% else %}
		aLengthMenu: [[15, 20, 25, 50, 100, -1], [15, 20, 25, 50, 100, "{% trans "All" %}"]],
		iDisplayLength: 15,
		{% if highlight_station_id %}
		iDisplayStart: Math.floor((function() { var c = 0, f = 0; $('#stations > tbody > tr').each(function(d, e) { if($(e).hasClass('highlighted')) f = 1; else if(f == 0) c++; }); return (f ? c : 0); })() / 15) * 15,
		{% endif %}
{% endif %}
		fnDrawCallback: function(oSettings)
		{
			/* Need to redo the counters if sorted */
			if(oSettings.bSorted)
			{
				for(var i = 0, iLen = oSettings.aiDisplay.length; i < iLen; i++)
				{
					$('td:eq(0)', oSettings.aoData[oSettings.aiDisplay[i]].nTr).html(i + 1);
				}
			}
		},
		aoColumnDefs: [
			{ bSortable: false, aTargets: [ 0 ] },
			{ asSorting: [ 'desc', 'asc' ], aTargets: [ 2, 4, 5 ] }
		],
		aaSorting: [[ {{ sort_col_int }}, '{{ sort_col_dir }}' ]]
	});
});
</script>

{% include '_piwik.tpl' %}

</body>
</html>
