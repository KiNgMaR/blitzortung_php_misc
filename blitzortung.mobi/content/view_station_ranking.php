<?php

namespace bo_mobi;

class view_station_ranking extends \cms\page_view
{
	public function __construct()
	{
		$this->needs_db = true;
		parent::__construct('station_ranking');
	}

	public function render()
	{
		global $_locale_id;

		$sort_options = array('city' => 1, 'strike_count' => 2, 'signal_count' => 3, 'signal_ratio' => 4, 'efficiency' => 5);
		$sort_dirs = array('city' => 'asc', 'strike_count' => 'desc', 'signal_count' => 'asc', 'signal_ratio' => 'desc', 'efficiency' => 'desc');

		$sort = getifset($_GET, 'sort', '');
		$sort_dir = getifset($_GET, 'sort_dir', '');

		if(!isset($sort_options[$sort]))
		{
			$sort = 'efficiency';
		}

		if($sort_dir !== 'asc' && $sort_dir !== 'desc')
		{
			$sort_dir = $sort_dirs[$sort];
		}

		$ranking = $this->dbh->query('SELECT s.my_id, strike_count, ROUND(strike_ratio, 1) AS strike_ratio, signal_count,
			ROUND(efficiency, 1) AS efficiency, city, country, IF(strike_count > signal_count, 100.0, ROUND(strike_count / signal_count * 100, 1)) AS signal_ratio,
			(SELECT code FROM country_names cn WHERE cn.name = s.country) AS country_code
			FROM station_ranking sr, stations s WHERE s.my_id = sr.station_id ORDER BY ' . $sort . ' ' . $sort_dir);
		$ranking = $ranking->fetchAll(\PDO::FETCH_ASSOC);
		$has_strikes = (count($ranking) > 0);

		if(!isset($_GET['_desktop_mode']))
		{
			$vars = compact('ranking', 'has_strikes');

			\Haanga::Load('station_ranking.tpl', $vars);
		}
		else
		{
			// read options/flags:
			$highlight_station_id = (int)getifset($_GET, 'station_id');
			$scroll_mode = (bool)(int)getifset($_GET, 'scroll');

			// set up mini map image:
			$IMG_WIDTH = $IMG_HEIGHT = 280;
			$MAP_ZOOM = 3;
			$MAP_LAT = 48.136944;
			$MAP_LON = 11.575278;

			require_once SYSTEMDIR . 'lib/blitzortung_query.php';
			require_once SYSTEMDIR . 'lib/blitzortung_overlay.php';

			$data = array();
			$bo_query = new \blitzortung_query($this->dbh);
			$bo_query->setCenter($MAP_LAT, $MAP_LON);
			$bo_query->setRectangleForGMaps($IMG_WIDTH, $IMG_HEIGHT, $MAP_ZOOM);
			$bo_query->setTimeSpan(gmtime() - 1 * 3600, gmtime());

			$overlay_image_id = '';
			$bo_overlay = NULL;
			if($bo_query->queryData($data))
			{
				$bo_overlay = new \blitzortung_overlay($MAP_LAT, $MAP_LON, $MAP_ZOOM, $IMG_WIDTH, $IMG_HEIGHT);

				$bo_overlay->addStrikes($data);

				$overlay_image_id = sprintf('%08x-%08x', crc32('station_ranking1'), crc32(gmtime()));

				$bo_overlay->saveToFile(SYSTEMDIR . 'cache/map/' . $overlay_image_id);
			}

			if($bo_overlay) $map_image_url = $bo_overlay->getGoogleStaticMapUrl('satellite');
			if($overlay_image_id) $overlay_image_url = '/imageproxy.php?id=' . $overlay_image_id;

			// project stats:
			$tmp = $this->dbh->query('SELECT
				(SELECT COUNT(*) FROM strikes WHERE time_actual >= ' . (gmtime() - 3600) . ') AS strikes_1h,
				(SELECT MAX(num_stations) FROM strikes WHERE time_actual >= ' . (gmtime() - 3600) . ') AS max_stations,
				(SELECT ROUND(AVG(num_stations), 1) FROM strikes WHERE time_actual >= ' . (gmtime() - 3600) . ') AS avg_stations,
				(SELECT COUNT(*) FROM station_ranking) AS station_count');
			$pstats = $tmp->fetch(\PDO::FETCH_ASSOC);
			$tmp = NULL;

			// station badge:
			$station_badge_url = '';
			if($highlight_station_id)
			{
				$tmp = $this->dbh->query('SELECT my_id FROM stations WHERE my_id = ' . ((int)$highlight_station_id));
				if($tmp->fetchColumn(0) == $highlight_station_id)
				{
					$station_badge_url = '/p/badge/' . $highlight_station_id . '-' . $_locale_id . '.png';
				}
				$tmp = NULL;
			}

			$sort_col_int = $sort_options[$sort];
			$sort_col_dir = $sort_dir;
			// pass down vars:
			$vars = compact('ranking', 'highlight_station_id', 'map_image_url', 'overlay_image_url', 'sort_col_int', 'sort_col_dir',
				'country_codes', 'IMG_WIDTH', 'IMG_HEIGHT', 'scroll_mode', 'has_strikes', 'pstats', 'station_badge_url');

			\Haanga::Load('station_ranking_desktop.tpl', $vars);
		}
	}
}
