<?php

namespace bo_mobi;

class view_activity_result extends \cms\page_view
{
	public function __construct()
	{
		$this->needs_db = true;
		parent::__construct('activity_result');
	}

	public function render()
	{
		$lat = (float)getifset($_GET, 'lat');
		$lon = (float)getifset($_GET, 'lon');

		if($lat === 0 || $lon === 0)
		{
			return 404;
		}

		$activity_level = lightning_activity::calcActivity($lat, $lon, $this->dbh);
		$activity_level->friendly_name = _('activity:' . $activity_level->name);

		$overlay_image_url = $overlay_map_url = NULL;
		if($activity_level->strikes > 0)
		{
			$img_width = 290;
			$img_height = 400;
			require_once SYSTEMDIR . 'lib/blitzortung_overlay.php';
			$overlay = new \blitzortung_overlay($lat, $lon, 0 /* min zoom */, $img_width, $img_height);
			$overlay->calcZoomFromRadius($activity_level->radius);
			$overlay->addRadiusCircle($activity_level->radius);

			$query = new \blitzortung_query($this->dbh);
			$query->setCenter($lat, $lon);
			$query->setRectangleForGMaps($img_width, $img_height, $overlay->getZoom());
			$query->setTimeSpan(gmtime() - 3600 * 2, gmtime());

			$data = array();
			if($query->queryData($data))
			{
				$overlay->addStrikes($data);

				$overlay_image_id = sprintf('%08x-%08x', crc32("custom$lat|$lon"), gmtime());
				$overlay->saveToFile(SYSTEMDIR . 'cache/map/' . $overlay_image_id);

				$overlay_image_url = '/imageproxy.php?id=' . $overlay_image_id;
				$overlay_map_url = $overlay->getGoogleStaticMapUrl();
			}
		}

		$vars = compact('activity_level', 'lat', 'lon',
			'overlay_image_url', 'overlay_map_url', 'img_width', 'img_height');

		\Haanga::Load('activity_result.tpl', $vars);
	}
}

require_once SYSTEMDIR . 'lib/blitzortung_query.php';
require_once SYSTEMDIR . 'lib/blitzortung_mobi.php';
