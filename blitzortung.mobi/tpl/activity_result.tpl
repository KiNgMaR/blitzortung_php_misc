{% extends "base.tpl" %}

{% block scripts %}
<style type="text/css">
/*.lightning-activity-low li {
	background-image: -moz-linear-gradient(top,#C77B7C,#D7AFB0) !important;
	background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0,#C77B7C),color-stop(1,#D7AFB0)) !important;
}

	background-image: -moz-linear-gradient(top,#C6F5C1,#8BEF80) !important;
	background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0,#C6F5C1),color-stop(1,#8BEF80)) !important;
}*/
</style>
{% endblock %}

{% block body %}
<div data-role="page">
	<div data-role="header">
		<h1>{% trans "Lightning Activity" %}</h1>
		<a data-rel="back" data-icon="back" href="/m/activity">{% trans "Back" %}</a>
	</div>
	<div data-role="content">
		<ul data-role="listview" data-inset="true" class="lightning-activity lightning-activity-{{ activity_level.name }}">
			<li>
				<img src="http://maps.google.com/maps/api/staticmap?center={{ lat }},{{ lon }}&amp;maptype=terrain&amp;size=100x100&amp;zoom=6&amp;sensor=false&amp;markers=color:red%7C{{ lat }},{{ lon }}" alt="Location map" />
				<h3>{{ activity_level.friendly_name }}</h3>
				<p><strong>{{ activity_level.strikes }} {% trans "strike(s) in" %} {{ activity_level.radius }} km</strong></p>
			</li>
		</ul>
		{% if activity_level.strikes > 0 %}
		<div style="width:{{ img_width }}px;height:{{ img_height }}px;padding:0;background-image:url('{{ overlay_map_url }}')">
			<img src="{{ overlay_image_url }}" alt="" />
		</div>
		{% endif %}
		<p>{% trans "Based on data of the last two hours." %}</p>
	</div>
	<div data-role="footer">
		<h4>{% laststrikeupdate %}</h4>
	</div>
</div>
{% endblock %}

