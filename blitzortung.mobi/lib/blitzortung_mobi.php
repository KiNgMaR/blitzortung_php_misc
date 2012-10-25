<?php

namespace bo_mobi;

class lightning_activity
{
	static $levels = array(
		'critical' =>
			/* num of strikes => range in km */
			array(5 => 10, 10 => 20, 100 => 50),
		'veryhigh' =>
			array(5 => 20, 15 => 40, 50 => 50),
		'high' =>
			array(10 => 40, 75 => 150),
		'medium' =>
			array(10 => 50, 15 => 100),
		'some' =>
			array(1 => 50, 5 => 100),
		'low' =>
			array(1 => 200),
		'none' =>
			array(0 => 200)
	);

	public static function calcActivity($lat, $lon, $dbh)
	{
		foreach(self::$levels as $lvl_name => $level)
		{
			foreach($level as $min_strikes => $radius)
			{
				$query = new \blitzortung_query($dbh);
				$query->setCenter($lat, $lon);
				$query->setRadius($radius);
				$query->setTimeSpan(gmtime() - 3600 * 2, gmtime());

				$strikes_actual = $query->queryNumber();

				if($strikes_actual >= $min_strikes)
				{
					$lvl_info = new \stdClass();
					$lvl_info->name = $lvl_name;
					$lvl_info->strikes = $strikes_actual;
					$lvl_info->radius = $radius;
					return $lvl_info;
				}
			}
		}

		assert(false);
	}
}

require_once SYSTEMDIR . 'lib/blitzortung_query.php';
