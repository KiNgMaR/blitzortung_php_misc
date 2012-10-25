<?php

date_default_timezone_set('Europe/Berlin');

define('SYSTEMDIR', dirname(__FILE__) . '/');

require SYSTEMDIR . 'lib/utils.php';

$id = getifset($_GET, 'id');

if(preg_match('~^[a-f0-9]{8}-[a-f0-9]{8}$~i', $id))
{
	$cache_file_name = SYSTEMDIR . 'cache/map/' . $id;

	if(sendHTTPCacheHeaders($cache_file_name, true))
	{
		header('Content-Type: image/png');
		header('X-Sendfile: ' . $cache_file_name);
	}
}

