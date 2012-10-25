{% extends "base.tpl" %}

{% block body %}
<div data-role="page" id="activity-start">
	<div data-role="header">
		<h1>{% trans "Station Ranking" %}</h1>
		<a data-rel="back" data-icon="back" href="/m/home">{% trans "Back" %}</a>
	</div>
	<div data-role="content">
		<ul data-role="listview">
			{% for station in ranking %}
			<li>
				<h3>{{ station.city }}</h3>
				<p><strong>{{ station.strike_count }} {% trans "strikes" %}, {{ station.strike_ratio }}%</strong></p>

				<p>{{ station.signal_count }} {% trans "signals" %}, {{ station.signal_ratio }}%</p>
				<p class="ui-li-aside"><strong>{{ station.efficiency }}</strong>%<br />{% trans "Efficiency" %}</p>
			</li>
			{% endfor %}
		</ul>
	</div>
	<div data-role="footer">
		<h4>{% laststrikeupdate %}</h4>
	</div>
</div>
{% endblock %}
