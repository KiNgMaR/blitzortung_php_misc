<?php

namespace bo_mobi;

class view_station_ranges extends \cms\page_view
{
	public function __construct()
	{
		$this->needs_db = true;
		parent::__construct('station_ranges');
	}

	public function render()
	{
		$station_id = (int)getifset($_GET, 'station_id');
		$user_max_radius = (int)getifset($_GET, 'max_radius');

		$maptypes = array('satellite', 'roadmap', 'terrain', 'hybrid', 'terrain');
		$map_type = getifset($_GET, 'maptype');

		if(!in_array($map_type, $maptypes)) $map_type = $maptypes[0];

		$station = $this->dbh->query('SELECT idf, city, latitude, longitude
			FROM stations WHERE my_id = ' . $station_id);
		$station = $station->fetch(\PDO::FETCH_ASSOC);

		if(!is_array($station))
		{
			return 404;
		}

		$HOURS_BACK = 24;
		$IMG_WIDTH = 640;
		$IMG_HEIGHT = 640;

		require_once SYSTEMDIR . 'lib/blitzortung_query.php';

		$bo_query = new \blitzortung_query($this->dbh);
		$bo_query->setStationId($station_id);
		$bo_query->setCenter($station['latitude'], $station['longitude']);
		$bo_query->setTimeSpan(gmtime() - $HOURS_BACK * 3600, ceil(gmtime() / 60) * 60);

		$strikes = array();
		$bo_query->queryData($strikes);

		$max_radius = 0;
		$min_radius = 1000000;

		foreach($strikes as $strike)
		{
			$strike['distance'] = (float)$strike['distance'];

			if($strike['distance'] < $min_radius) $min_radius = $strike['distance'];
			if($strike['distance'] > $max_radius) $max_radius = $strike['distance'];
		}

		require_once SYSTEMDIR . 'lib/blitzortung_overlay.php';

		$bo_overlay = new \blitzortung_overlay($station['latitude'], $station['longitude'], 2, $IMG_WIDTH, $IMG_HEIGHT);

		if($user_max_radius > 10000)
			$user_max_radius = 10000;

		$eff_max_radius = ($user_max_radius < 1 ? $max_radius : $user_max_radius);

		$range_finals = array();

		if($max_radius > 0)
		{
			$step = ($eff_max_radius < 100 ? 10 : 100);

			$rounded_max_km = ceil($eff_max_radius / $step) * $step;
			$bo_overlay->calcZoomFromRadius($rounded_max_km);

			$min_range_height = 30; // in px

			$ranges = array();

			$prev_xy = 0;
			for($r = $step; $r <= $rounded_max_km; $r += $step)
			{
				$xy = $bo_overlay->calcImageRadius($r);

				if($xy - $prev_xy >= $min_range_height)
				{
					$ranges[$r] = $xy - $prev_xy;
					$prev_xy = $xy;
				}
			}

			$bo_query->setStationId(0);

			$prev_range_strikes = 0;
			$prev_range_km = 0;
			foreach($ranges as $rkm => $xy)
			{
				$bo_overlay->addRadiusCircle($rkm, false);
				$rpx = $bo_overlay->calcImageRadius($rkm);

				$font = 3;
				$text = "$rkm km";

				$bo_overlay->addText($font, ($IMG_WIDTH - imagefontwidth($font) * strlen($text)) / 2,
					$IMG_HEIGHT / 2 - $rpx + ($xy - imagefontheight($font)) / 2,
					$text);

				$bo_query->setRadius($rkm);
				$radius_strikes = $bo_query->queryNumber();
				$strikes_in_section = $radius_strikes - $prev_range_strikes;
				$prev_range_strikes = $radius_strikes;

				$station_strikes_in_section = 0;
				foreach($strikes as $strike)
				{
					if($strike['distance'] >= $prev_range_km && $strike['distance'] < $rkm)
					{
						$station_strikes_in_section++;
					}
				}

				$text = "$station_strikes_in_section/$strikes_in_section";
				if($strikes_in_section > 0) $text .= ' ' . round($station_strikes_in_section / $strikes_in_section * 100, 1) . '%';

				$bo_overlay->addText($font, ($IMG_WIDTH - imagefontwidth($font) * strlen($text)) / 2,
					$IMG_HEIGHT / 2 + $rpx - imagefontheight($font) - ($xy - imagefontheight($font)) / 2,
					$text);

				$range_finals[] = array('from' => $prev_range_km, 'to' => $rkm, 'total' => $strikes_in_section, 'station' => $station_strikes_in_section, 'percentage' => round($station_strikes_in_section / $strikes_in_section * 100, 1));

				$prev_range_km = $rkm;
			}
		}

		// abuse to mark station:
		$fake_strikes = array($station);
		$bo_overlay->addStrikes($fake_strikes);

		$overlay_image_id = sprintf('%08x-%08x', crc32("stat$station_id:ion_rangEz$user_max_radius"), ceil(gmtime() / 60) * 47);

		$bo_overlay->saveToFile(SYSTEMDIR . 'cache/map/' . $overlay_image_id);

		$map_image_url = $bo_overlay->getGoogleStaticMapUrl($map_type);
		$overlay_image_url = '/imageproxy.php?id=' . $overlay_image_id;

		$vars = compact('station', 'map_image_url', 'overlay_image_url', 'IMG_WIDTH', 'IMG_HEIGHT', 'range_finals');

		\Haanga::Load('station_ranges.tpl', $vars);
	}
}
