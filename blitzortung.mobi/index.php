<?php

date_default_timezone_set('Europe/Berlin');

define('SYSTEMDIR', dirname(__FILE__) . '/');

require SYSTEMDIR . 'config.php';
require SYSTEMDIR . 'lib/utils.php';
require SYSTEMDIR . 'lib/Haanga.php';
require SYSTEMDIR . 'cms/view.php';

/** locale/translation stuff **/

$_locale_id = getifset($_GET, 'lang');
if(empty($_locale_id))
{
	$_locale_id = getifset($_COOKIE, 'language');
}

if(!in_array($_locale_id, array('de', 'en')))
{
	$_locale_id = 'de';
}

if($_locale_id === getifset($_GET, 'lang'))
{
	setcookie('language', $_locale_id, time() + 86400 * 30);
}

require SYSTEMDIR . 'lib/translation.php';

sys_init_locale();

/** template engine setup **/

Haanga::configure(array(
	'template_dir' => SYSTEMDIR . 'tpl/',
	'cache_dir' => SYSTEMDIR . 'cache/tpl/',
));


/** main method **/

function main()
{
	$view_key = getifset($_GET, 'view');
	$view_instance = NULL; /* derived from cms\page_view */

	switch($view_key)
	{
		case 'home':
		case '':
		case false:
			require SYSTEMDIR . 'content/view_home.php';
			$view_instance = new bo_mobi\view_home();
		break;

		case 'activity':
			require SYSTEMDIR . 'content/view_activity.php';
			$view_instance = new bo_mobi\view_activity();
		break;

		case 'activity_result':
			require SYSTEMDIR . 'content/view_activity_result.php';
			$view_instance = new bo_mobi\view_activity_result();
		break;

		case 'activity_map':
			require SYSTEMDIR . 'content/view_activity_map.php';
			$view_instance = new bo_mobi\view_activity_map();
		break;

		case 'sig_image':
			require SYSTEMDIR . 'content/view_sig_image.php';
			$view_instance = new bo_mobi\view_sig_image();
		break;

		case 'particip_chart':
			require SYSTEMDIR . 'content/view_particip_chart.php';
			$view_instance = new bo_mobi\view_particip_chart();
		break;

		case 'station_ranking':
			require SYSTEMDIR . 'content/view_station_ranking.php';
			$view_instance = new bo_mobi\view_station_ranking();
		break;

		case 'station_ranges':
			require SYSTEMDIR . 'content/view_station_ranges.php';
			$view_instance = new bo_mobi\view_station_ranges();
		break;

		default:
			header('HTTP/1.0 404 Not Found');
	}

	$dbh = NULL;

	if($view_instance)
	{
		if($view_instance->needsDb())
		{
			$dbh = new PDO('mysql:host=127.0.0.1;dbname=' . Config::db_name, Config::db_user, Config::db_pass);
			$dbh->exec('SET CHARACTER SET utf8');

			$view_instance->assignDbHandle($dbh);
		}

		if($view_instance->doesTextOutput())
		{
			$USER_AGENT = getifset($_SERVER, 'HTTP_USER_AGENT', '');
			$IS_IE_UA = (strpos($USER_AGENT, 'MSIE ') !== false && strpos($USER_AGENT, 'Opera') === false);

			if(!headers_sent() && !$IS_IE_UA)
			{
				ob_start('ob_gzhandler');
			}
		}

		$view_instance->render();
	}
}

try
{
	main();
}
catch(Exception $ex)
{
	die('<h1>Unhandled Exception</h1><pre>' . htmlspecialchars($ex->getMessage()) . '</pre>');
}

