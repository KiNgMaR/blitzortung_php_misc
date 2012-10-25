{% extends "base.tpl" %}

{% block body %}
<div data-role="page">
	<div data-role="header">
		<h1>{% trans area.name %}</h1>
		<a data-rel="back" data-icon="back" href="/m/activity">{% trans "Back" %}</a>
	</div>
	<div data-role="content">

		<div data-role="fieldcontain">
			<form action="/m/activity_map" method="get">
			<input type="hidden" name="area" value="{{ area.code }}" />
			<label for="select-choice-timespan" class="select">{% trans "Time span" %}:</label>
			<select id="select-choice-timespan" name="timespan" data-native-menu="false" onchange="this.form.submit()">
				<option value="h:24"{% if timespan == "h:24" %} selected="selected"{% endif %}>{% trans "Last 24h" %}</option>
				<option value="h:12"{% if timespan == "h:12" %} selected="selected"{% endif %}>{% trans "Last 12h" %}</option>
				<option value="h:2"{% if timespan == "h:2" %} selected="selected"{% endif %}>{% trans "Last 2h" %}</option>
				<option value="h:1"{% if timespan == "h:1" %} selected="selected"{% endif %}>{% trans "Last hour" %}</option>
			</select>
			</form>
		</div>

		<div style="width:{{ area.img_width }}px;height:{{ area.img_height }}px;padding:0;background-image:url('{{ map_image_url }}')">
			<img src="{{ overlay_image_url }}" alt="" />
		</div>
		<p>{{ num_strikes }} {% trans "strikes" %}</p>

	</div>
	<div data-role="footer">
		<h4>{% laststrikeupdate %}</h4>
	</div>
</div>
{% endblock %}
