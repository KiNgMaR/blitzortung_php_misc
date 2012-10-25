<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>{{ station.city }}</title>
	<style type="text/css">
	* { margin: 0; padding: 0; font-family: Arial, sans-serif; }
	th { background-color: #ddd; }
	td, th { padding: 5px; text-align: center; }
	img, table { margin: 0 0.5em 0.5em 0.5em; }
	</style>
</head>
<body>

<img src="{{ chart_url }}" alt="" />

<table>
	<tr><th>Day</th><th>Total strikes</th><th>Station strikes</th><th>Ratio</th></tr>
{% for day in data %}
	<tr><td>{{ day.day_friendly }}</td><td>{{ day.strikes }}</td><td>{{ day.station_strikes }}</td><td>{{ day.ratio }} %</td></tr>
{% endfor %}
</table>

{% include '_piwik.tpl' %}

</body>
</html>
