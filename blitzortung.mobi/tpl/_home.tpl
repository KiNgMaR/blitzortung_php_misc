
{% if render_full_page %}{% include "head_xml.tpl" %}{% endif %}
{% if render_full_page %}{% include "head_body.tpl" %}{% endif %}

<div data-role="page">
	<div data-role="header">
		<h1>blitzortung.mobi</h1>
	</div>
	<div data-role="content">
		<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
			<li data-role="list-divider">{% trans "Tools" %}</li>
			<li><a href="/m/activity">{% trans "Lightning Activity" %}</a></li>
			<li><a href="#">{% trans "Project Statistics" %}</a></li>
		</ul>

		<ul data-role="listview" data-inset="true" data-theme="c" data-dividertheme="b">
			<li data-role="list-divider">{% trans "For Station Owners" %}</li>
			<li><a href="#">{% trans "Station Ranking" %}</a></li>
			<li><a href="#">{% trans "Strike List" %}</a></li>
		</ul>

		<h4>{% trans "About" %}</h4>
		<p>Data collected by <a href="http://www.blitzortung.org/">blitzortung.org</a> station owners.</p>
		<p>blitzortung.mobi by Ingmar Runge 2011.</p>
	</div>
	<div data-role="footer">
		<h4>04 April 12:13:14</h4>
	</div>
</div>

{% if render_full_page %}{% include "footer.tpl" %}{% endif %}

