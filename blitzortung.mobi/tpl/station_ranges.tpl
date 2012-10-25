<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>{{ station.city }}</title>
	<style type="text/css">
	* { margin: 0; padding: 0; font-family: Arial, sans-serif; }
	th { background-color: #ddd; }
	td, th { padding: 5px; text-align: center; }
	div, table { margin: 0 0.5em 0.5em 0.5em; }
	</style>
	<title>{{ station.city }} - TOA Range Chart</title>
</head>
<body>

<div style="width:{{ IMG_WIDTH }}px;height:{{ IMG_HEIGHT }}px;padding:0;background-image:url('{{ map_image_url }}')">
	<img src="{{ overlay_image_url }}" alt="" />
</div>

<table>
	<tr><th>From</th><th>To</th><th>Total strikes</th><th>Station strikes</th><th>Ratio</th></tr>
{% for r in range_finals %}
	<tr><td>{{ r.from }} km</td><td>{{ r.to }} km</td><td>{{ r.total }}</td><td>{{ r.station }}</td><td>{{ r.percentage }} %</td></tr>
{% endfor %}
</table>

{% include '_piwik.tpl' %}

</body>
</html>
