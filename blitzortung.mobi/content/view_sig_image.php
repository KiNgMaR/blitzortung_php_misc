<?php

namespace bo_mobi;

class view_sig_image extends \cms\page_view
{
	public function __construct()
	{
		$this->needs_db = true;
		$this->text_output = false;
		parent::__construct('sig_image');
	}

	public function render()
	{
		$station_id = (int)getifset($_GET, 'station_id');
		$lang = getifset($_GET, 'lang');
		if($lang != 'de' && $lang != 'en')
		{
			$lang = 'en';
		}

		$cache_file_name = SYSTEMDIR . 'cache/map/' . sprintf('%04d_%08X_%s', $station_id, time() / 60, $lang);

		if(!sendHTTPCacheHeaders($cache_file_name, true))
		{
			header('Content-Type: image/png');
			header('X-Sendfile: ' . $cache_file_name);
		}
		else
		{
			$tmp = $this->dbh->prepare('SELECT my_id, city, signals, last_signal, signals,
				(SELECT COUNT(*) FROM strike_stations WHERE station_id = stations.my_id AND strike_time >= :onehback) AS strikes_1h,
				(SELECT COUNT(*) FROM strike_stations WHERE station_id = stations.my_id AND strike_time >= :twenty4hback) AS strikes_24h
				FROM stations WHERE my_id = :id');
			$tmp->bindValue(':onehback', gmtime() - 3600);
			$tmp->bindValue(':twenty4hback', gmtime() - 86400);
			$tmp->bindValue(':id', $station_id);

			if($tmp->execute() && ($station = $tmp->fetch(\PDO::FETCH_ASSOC)))
			{
				$badge = new \blitzortung_badge($station, $lang);
				if($badge->make())
				{
					$badge->output($cache_file_name);
					sendHTTPCacheHeaders($cache_file_name);
				}
			}

			if(file_exists($cache_file_name))
			{
				header('Content-Type: image/png');
				header('X-Sendfile: ' . $cache_file_name);
			}
			else
			{
				header('HTTP/1.0 404 Not Found');
			}
		}
	}
}

require SYSTEMDIR . 'lib/blitzortung_query.php';
require SYSTEMDIR . 'lib/blitzortung_badge.php';
