<?php

namespace bo_mobi;

class view_activity_map extends \cms\page_view
{
	protected static $areas = array(
		'de' => array(
			'center_lat' => 51.0,
			'center_lon' => 10.5,
			'name' => 'Germany',
			'zoom' => 5,
			'img_width' => 290,
			'img_height' => 400
		),
		
	);

	public function __construct()
	{
		$this->needs_db = true;
		parent::__construct('activity_map');
	}

	public function render()
	{
		$area_code = strtolower(getifset($_GET, 'area'));

		if(!isset(self::$areas[$area_code]))
		{
			return 404;
		}

		$area = self::$areas[$area_code];
		$area['code'] = $area_code;

		$timespan = getifset($_GET, 'timespan', 'h:24');
		$timespan_hours = 0;

		if(!preg_match('~^h:(\d+)$~', $timespan, $match) || $match[1] < 1 || $match[1] > 24)
		{
			return 404;
		}
		else
		{
			$timespan_hours = (int)$match[1];
		}

		$map_id = NULL;
		$data = array();

		$bo_query = new \blitzortung_query($this->dbh);
		$bo_query->setCenter($area['center_lat'], $area['center_lon']);
		$bo_query->setRectangleForGMaps($area['img_width'], $area['img_height'], $area['zoom']);
		$bo_query->setTimeSpan(gmtime() - $timespan_hours * 3600, gmtime());

		if($bo_query->queryData($data))
		{
			$bo_overlay = new \blitzortung_overlay($area['center_lat'], $area['center_lon'],
				$area['zoom'], $area['img_width'], $area['img_height']);

			$bo_overlay->addStrikes($data);

			$strike_hash_data = '';
			foreach($data as $strike) { $strike_hash_data .= $strike['latitude'] . '|' . $strike['longitude'] . "\n"; }

			$tmp_map_id = sprintf('%08x-%08x', crc32(json_encode($area)), crc32($strike_hash_data));

			if($bo_overlay->saveToFile(SYSTEMDIR . 'cache/map/' . $tmp_map_id))
			{
				$overlay_image_id = $tmp_map_id;
			}
		}

		if(empty($overlay_image_id))
		{
			return 404;
		}

		$num_strikes = count($data);
		$map_image_url = $bo_overlay->getGoogleStaticMapUrl();
		$overlay_image_url = '/imageproxy.php?id=' . $overlay_image_id;

		$vars = compact('area', 'num_strikes', 'map_image_url', 'overlay_image_url', 'timespan');

		\Haanga::Load('activity_map.tpl', $vars);
	}
}

require_once SYSTEMDIR . 'lib/blitzortung_query.php';
require_once SYSTEMDIR . 'lib/blitzortung_overlay.php';
