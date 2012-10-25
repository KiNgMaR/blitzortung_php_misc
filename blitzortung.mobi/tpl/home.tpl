{% extends "base.tpl" %}

{% block body %}
<div data-role="page">
	<div data-role="header">
		<h1>blitzortung.mobi</h1>
	</div>
	<div data-role="content">
		<h3 style="color:red;text-align:center;">Work in Progress!</h3>
		<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
			<li data-role="list-divider">{% trans "Tools" %}</li>
			<li><a data-ajax="false" href="/m/activity">{% trans "Lightning Activity" %}</a></li>
			<li><a href="#">{% trans "Project Statistics" %}</a></li>
		</ul>

		<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
			<li data-role="list-divider">{% trans "For Station Owners" %}</li>
			<li><a href="/m/station_ranking">{% trans "Station Ranking" %}</a></li>
		</ul>

		<h4>{% trans "Language" %}</h4>
		<div data-role="controlgroup" data-type="horizontal">
			<a href="/?lang=de" data-role="button" data-ajax="false"><img src="http://static.irsoft.de/flags/de.png" alt="" /> Deutsch</a>
			<a href="/?lang=en" data-role="button" data-ajax="false"><img src="http://static.irsoft.de/flags/gb.png" alt="" /> English</a>
		</div>

		<h4>{% trans "About" %}</h4>
		<p>Data collected by <a href="http://www.blitzortung.org/">blitzortung.org</a> station owners.</p>
		<p>blitzortung.mobi by Ingmar Runge 2011.</p>
	</div>
	<div data-role="footer">
		<h4>{% laststrikeupdate %}</h4>
	</div>
</div>
{% endblock %}

