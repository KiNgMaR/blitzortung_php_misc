<?php

date_default_timezone_set('Europe/Berlin');

define('SYSTEMDIR', realpath(dirname(__FILE__) . '/../') . '/');

function blitzortung_tidy_cache()
{
	foreach(glob(SYSTEMDIR . 'cache/map/*') as $cache_file)
	{
		if(@filemtime($cache_file) < time() - 86400)
		{
			@unlink($cache_file);
		}
	}
}

blitzortung_tidy_cache();
