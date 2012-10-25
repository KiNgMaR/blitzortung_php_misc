<?php

namespace bo_mobi;

class view_particip_chart extends \cms\page_view
{
	public function __construct()
	{
		$this->needs_db = true;
		parent::__construct('particip_chart');
	}

	public function render()
	{
		$station_id = (int)getifset($_GET, 'station_id');

		$tmp = $this->dbh->prepare('SELECT my_id, city, last_signal FROM stations WHERE my_id = :id');
		$tmp->bindValue(':id', $station_id);

		if(!$tmp->execute() || !($station = $tmp->fetch(\PDO::FETCH_ASSOC)))
		{
			return 404;
		}

		$tmp = NULL;

		$strikes_max = 0;
		$data = array();
		for($day = 0; $day < 7; $day++)
		{
			$tm = gmtime() - $day * 86400;
			$midnight = mktime(0, 0, 0, gmdate('n', $tm), gmdate('j', $tm), gmdate('Y', $tm));

			$tmp = $this->dbh->prepare('SELECT
				(SELECT COUNT(*) FROM strike_stations WHERE station_id = :station_id AND strike_time BETWEEN :timelow AND :timehigh) AS station_strikes,
				(SELECT COUNT(*) FROM strikes WHERE time_actual BETWEEN :timelow AND :timehigh) AS total_strikes');
			$tmp->bindValue(':station_id', $station['my_id']);
			$tmp->bindValue(':timelow', $midnight);
			$tmp->bindValue(':timehigh', $midnight + 86400);

			$info = new \stdClass();
			$info->midnight = $midnight;
			$info->day = date('Y-m-d', $midnight);
			$info->day_friendly = date('M d', $midnight);
			$info->strikes = 0;
			$info->station_strikes = 0;

			if($tmp->execute() && ($day_data = $tmp->fetch(\PDO::FETCH_ASSOC)))
			{
				$info->strikes = (int)$day_data['total_strikes'];
				$info->station_strikes = (int)$day_data['station_strikes'];

				if($info->strikes > $strikes_max) $strikes_max = $info->strikes;

				$info->ratio = ($info->strikes > 0 ? round($info->station_strikes / $info->strikes * 100, 1) : 0);
			}

			array_unshift($data, $info);
		}

		unset($info);

		$chd = array('station' => array(), 'without' => array(), 'relative' => array());
		$chxl = array();

		foreach($data as $day)
		{
			$chd['station'][] = $day->station_strikes;
			$chd['without'][] = $day->strikes - $day->station_strikes;
			$chd['relative'][] = $day->ratio;

			$chxl[] = $day->day_friendly;
		}

		$scale_add_factor = floor(0.02 * $strikes_max);
		$scale_max = ceil($strikes_max / $scale_add_factor) * $scale_add_factor + $scale_add_factor;

		$chart_url = 'http://chart.googleapis.com/chart?';

		$params = array(
			'cht' => 'bvs',
			'chtt' => 'Station ' . $station['city'],
			'chs' => '500x200',
			'chbh' => '33,12',
			'chd' => 't2:' . join(',', $chd['station']) . '|' . join(',', $chd['without']) . '|' . join(',', $chd['relative']),
			'chco' => '4169E1,87CEEB',
			'chm' => 'D,DC143C,2,0,3',
			'chds' => '0,' . $scale_max . ',0,' . $scale_max . ',0,100',
			'chxt' => 'x',
			'chxl' => '0:|' . join('|', $chxl),
			'chdl' => 'Strikes located by station|Other strikes',
			'chts' => '000000,15'
		);

		$chart_url .= http_build_query($params, '', '&');

		$vars = compact('data', 'chart_url', 'station');

		\Haanga::Load('particip_chart.tpl', $vars);
	}
}

require SYSTEMDIR . 'lib/blitzortung_query.php';
