{% extends "base.tpl" %}

{% block scripts %}
<script src="/js/yqlgeo.js" type="text/javascript"></script>
<script src="/js/view_activity.js" type="text/javascript"></script>
{% endblock %}

{% block body %}
<div data-role="page" id="activity-start">
	<div data-role="header">
		<h1>{% trans "Lightning Activity" %}</h1>
		<a data-rel="back" data-icon="back" href="/m/home">{% trans "Back" %}</a>
	</div>
	<div data-role="content">
		<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
			<li data-role="list-divider">{% trans "Choose Location" %}</li>
			<li><a href="javascript:onCurrentLocationButtonClick()">{% trans "My current location" %}</a></li>
			<li><a href="#activity-location-query">{% trans "Other location" %}</a></li>
		</ul>
		<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
			<li data-role="list-divider">{% trans "Maps" %}</li>
			<!--<li><img src="http://static.irsoft.de/flags/europeanunion.png" alt="" class="ui-li-icon"><a href="/m/activity_map?area=eu">{% trans "Europe" %}</a></li>//-->
			<li><img src="http://static.irsoft.de/flags/de.png" alt="" class="ui-li-icon"><a href="/m/activity_map?area=de">{% trans "Germany" %}</a></li>
		</ul>
	</div>
	<div data-role="footer">
		<h4>{% laststrikeupdate %}</h4>
	</div>
</div>

<div data-role="page" id="activity-location-query">
	<div data-role="header">
		<h1>{% trans "Lightning Activity" %}</h1>
		<a data-rel="back" data-icon="back" href="#activity-start">{% trans "Back" %}</a>
	</div>
	<div data-role="content">
		<fieldset>
		<div data-role="fieldcontain">
			<label for="search">{% trans "City and country:" %}</label>
			<input type="search" name="location" id="location-input" value="" />
		</div>
		<button id="location-submit">{% trans "Submit" %}</button>
	</div>
	<div data-role="footer">
		<h4>{% laststrikeupdate %}</h4>
	</div>
</div>
{% endblock %}
