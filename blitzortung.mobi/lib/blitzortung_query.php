<?php

class blitzortung_query
{
	protected $dbh = NULL;
	protected $center_lat = NULL, $center_lon = NULL;
	protected $radius = NULL;
	protected $station_id = 0;
	protected $rect_lat1 = NULL, $rect_lon1 = NULL;
	protected $rect_lat2 = NULL, $rect_lon2 = NULL;
	protected $time_start = 0, $time_end = 0;

	public function __construct($dbh)
	{
		$this->dbh = $dbh;
	}

	/**
	 *
	 **/
	public function setCenter($lat, $lon)
	{
		$this->center_lat = (float)$lat;
		$this->center_lon = (float)$lon;
	}

	/**
	 * radius in km
	 **/
	public function setRadius($radius)
	{
		if(is_null($this->center_lat) || is_null($this->center_lon))
		{
			return false;
		}

		$this->radius = (float)$radius;

		blitzortung_tools::rectangleCoordsFromRadius($this->center_lat, $this->center_lon,
			$this->radius, $this->rect_lat1, $this->rect_lon1, $this->rect_lat2, $this->rect_lon2);

		return $this->radius;
	}

	/**
	 *
	 **/
	public function setRectangleAbsolute($lat1, $lon1, $lat2, $lon2)
	{
		$this->radius = NULL;

		$this->rect_lat1 = (float)$lat1;
		$this->rect_lon1 = (float)$lon1;
		$this->rect_lat2 = (float)$lat2;
		$this->rect_lon2 = (float)$lon2;

		return true;
	}

	/**
	 * width & height in km
	 **/
	public function setRectangleFromCenter($width, $height)
	{
		$this->radius = NULL;

		blitzortung_tools::rectangleCoordsFromWidthHeight($this->center_lat, $this->center_lon,
			$width, $height, $this->rect_lat1, $this->rect_lon1, $this->rect_lat2, $this->rect_lon2);

		return true;
	}

	public function setRectangleForGMaps($img_width, $img_height, $zoom)
	{
		$this->radius = NULL;

		$this->rect_lat1 = \Google_Maps::adjustLatByPixels($this->center_lat, -$img_height / 2, $zoom);
		$this->rect_lon1 = \Google_Maps::adjustLonByPixels($this->center_lon, -$img_width / 2, $zoom);
		$this->rect_lat2 = \Google_Maps::adjustLatByPixels($this->center_lat, $img_height / 2, $zoom);
		$this->rect_lon2 = \Google_Maps::adjustLonByPixels($this->center_lon, $img_width / 2, $zoom);

		return true;
	}

	public function setStationId($station_id)
	{
		$this->station_id = (int)$station_id;
	}

	/**
	 * start & end as UTC unix timestamps
	 **/
	public function setTimeSpan($start, $end)
	{
		$this->time_start = (int)$start;
		$this->time_end = (int)$end;
	}

	protected function prepareSql($sql)
	{
		$sql = str_replace('@QDISTANCE@', '(6378.137 * 2 * ASIN(SQRT(POWER(SIN((:lat - latitude) * PI() / 180 / 2), 2) + '. 
			'COS(:lat * PI() / 180) * COS(latitude * PI() / 180) * POWER(SIN((:lon - longitude) * PI() / 180 / 2), 2)))) AS distance',
			 $sql);

		$sql = str_replace('@RECTWHERE@', '(latitude BETWEEN :lat1 AND :lat2 AND longitude BETWEEN :lon1 AND :lon2)', $sql, $rect_replaced);

		$time_param = false;
		if($this->time_start > 0 && $this->time_end > 0)
		{
			$sql = str_replace('@TIMEWHERE@', '(time_actual BETWEEN :tm1 AND :tm2)', $sql, $replaced);

			$time_param = ($replaced > 0);
		}
		else
		{
			$sql = str_replace('@TIMEWHERE@', '1', $sql);
		}

		$stmt = $this->dbh->prepare($sql);

		$stmt->bindValue(':lat', $this->center_lat);
		$stmt->bindValue(':lon', $this->center_lon);

		if($rect_replaced > 0)
		{
			$stmt->bindValue(':lat1', min($this->rect_lat1, $this->rect_lat2));
			$stmt->bindValue(':lon1', min($this->rect_lon1, $this->rect_lon2));
			$stmt->bindValue(':lat2', max($this->rect_lat1, $this->rect_lat2));
			$stmt->bindValue(':lon2', max($this->rect_lon1, $this->rect_lon2));
		}

		if($time_param)
		{
			$stmt->bindValue(':tm1', min($this->time_start, $this->time_end));
			$stmt->bindValue(':tm2', max($this->time_start, $this->time_end));
		}

		return $stmt;
	}

	public function queryNumber()
	{
		$stmt = NULL;

		// :TODO: support queries for station_id
		if(is_null($this->radius))
		{
			$stmt = $this->prepareSql('SELECT COUNT(*) FROM ' .
				'(SELECT latitude FROM strikes WHERE @RECTWHERE@ AND @TIMEWHERE@) ddd');
		}
		else
		{
			$stmt = $this->prepareSql('SELECT COUNT(*) FROM ' .
				'(SELECT @QDISTANCE@ FROM strikes WHERE @RECTWHERE@ AND @TIMEWHERE@ HAVING distance <= :radius) ddd');

			$stmt->bindValue(':radius', $this->radius);
		}

		$result = false;

		if($stmt->execute())
		{
			$result = (int)$stmt->fetchColumn();
		}

		unset($stmt);

		return $result;
	}

	public function queryData(array& $data)
	{
		$stmt = NULL;

		if($this->station_id < 1)
		{
			$sql = 'SELECT latitude, longitude, @QDISTANCE@ FROM strikes WHERE @RECTWHERE@ AND @TIMEWHERE@';
		}
		else
		{
			$sql = 'SELECT latitude, longitude, @QDISTANCE@ FROM strike_stations ss, strikes s
				WHERE s.my_id = ss.strike_id AND ss.station_id = :station_id AND @TIMEWHERE@'; /* no support for queries with radius/rect + station_id yet */
		}

		if(!is_null($this->radius))
		{
			$sql .= 'HAVING distance <= :radius';

			$stmt = $this->prepareSql($sql);
			$stmt->bindValue(':radius', $this->radius);
		}
		else
		{
			$stmt = $this->prepareSql($sql);
		}

		if($this->station_id > 0)
		{
			$stmt->bindValue(':station_id', $this->station_id);
		}

		$result = false;

		if($stmt->execute())
		{
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$result = count($data);
		}

		unset($stmt);

		return $result;
	}
}

require_once SYSTEMDIR . 'lib/blitzortung_tools.php';
require_once SYSTEMDIR . 'lib/Google_Maps.php';
