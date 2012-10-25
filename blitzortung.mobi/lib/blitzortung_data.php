<?php

class blitzortung_data
{
	protected $cache_dir = NULL;
	protected $ch = NULL;
	protected $bo_user = '', $bo_pass = '';
	protected $interface = '';

	protected $buf_participants, $buf_stations;

	protected $stations = array();
	protected $participants = array();


	public function __construct($cache_dir, $interface = '')
	{
		$this->cache_dir = $cache_dir;
		$this->interface = $interface;

		$this->ch = curl_init();
	}

	public function setLogin($user, $pass)
	{
		$this->bo_user = $user;
		$this->bo_pass = $pass;
	}

	public function retrieve()
	{
		curl_setopt($this->ch, CURLOPT_HEADER, 1);
		curl_setopt($this->ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);

		if(!empty($this->interface))
		{
			curl_setopt($this->ch, CURLOPT_INTERFACE, $this->interface);
		}

		//curl_setopt($this->ch, CURLOPT_VERBOSE, 1);

		if(!empty($this->bo_user))
		{
			curl_setopt($this->ch, CURLOPT_USERPWD, $this->bo_user . ':' . $this->bo_pass);
		}

		$this->buf_participants = $this->retrieveFile('participants.txt');
		$this->buf_stations = $this->retrieveFile('stations.txt');

		if(!empty($this->buf_participants) && !empty($this->buf_stations))
		{
			return $this->parse();
		}

		return false;
	}

	protected function retrieveFile($file)
	{
		if(!preg_match('~^[\w-]+\.\w+$~', $file))
		{
			return false;
		}

		$url = 'http://blitzortung.net/Data_1/Protected/' . $file;
		$local_file = $this->cache_dir . '/' . $file;
		$etag_file = $local_file . '.etag';
		$etag = '';

		if(file_exists($local_file) && file_exists($etag_file))
		{
			$etag = @file_get_contents($etag_file);
		}

		curl_setopt($this->ch, CURLOPT_URL, $url);

		if(!empty($etag))
		{
			if(preg_match('~^W/(.+?)$~', $etag, $match))
			{
				$etag = $match[1];
			}
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('If-None-Match: ' . $etag));
		}
		else
		{
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, array());
		}

		$response = curl_exec($this->ch);

		$http_code = (int)curl_getinfo($this->ch, CURLINFO_HTTP_CODE);

		if($http_code == 200)
		{
			$response = preg_split('~\r?\n\r?\n~', $response, 2);
			$etag = '';

			if(preg_match('~^ETag: (.+?)$~m', $response[0], $match))
			{
				$etag = trim($match[1]);
			}

			file_put_contents($local_file, $response[1]);
			file_put_contents($etag_file, $etag);

			return $response[1];
		}
		elseif($http_code == 304)
		{
			return file_get_contents($local_file);
		}
	
		return false;
	}

	protected function _fix_col($s)
	{
		$s = str_replace('\null', '', $s);
		$s = str_replace("\\'", "'", $s);
		$s = str_replace('&nbsp;', ' ', $s);
		return html_entity_decode($s, ENT_COMPAT, 'UTF-8');
	}

	protected function _to_time($s)
	{
		if(preg_match('~^(.+)(\.\d+)$~', $s, $match))
		{
			$t = (string)strtotime($match[1]) . $match[2];
			if($t < 0) $t = 0;
			return $t;
		}

		return (string)strtotime($s);
	}

	protected function parse()
	{
		if(empty($this->buf_participants) || empty($this->buf_stations))
		{
			return false;
		}

		$stations = array();

		echo "parsing...\n";

		foreach(explode("\n", $this->buf_stations) as $line)
		{
			$line = explode(' ', $line);
			if(count($line) < 11)
				continue;

			list($num_id, $idf, $owner, $city, $country, $lat, $lon, $last_signal, $status, $client, $signals) = $line;

			if(empty($idf) || $num_id == 0)
			{
				continue;
			}

			$new = new stdClass();

			$new->num_id = (int)$num_id;
			$new->idf = $idf;
			$new->owner = self::_fix_col($owner);
			$new->city = self::_fix_col($city);
			$new->country = self::_fix_col($country);
			$new->lat = (float)$lat;
			$new->lon = (float)$lon;
			$new->status = $status;
			$new->last_signal = self::_to_time(self::_fix_col($last_signal));
			$new->client = self::_fix_col($client);
			$new->signals = (int)$signals;

			$stations[$idf] = $new;
		}

		echo 'have ' . count($stations) . " stations\n";

		$this->stations = $stations;

		$particips = array();

		foreach(explode("\n", $this->buf_participants) as $line)
		{
			$cols = explode(' ', $line);
			if(count($cols) < 8)
				continue;

			list($date, $time, $lat, $lon, $ampere, , $km, $num_part) = $cols;

			$new = new stdClass();
			$new->stations = array_slice($cols, 8);
			$new->time = self::_to_time($date . ' ' . $time);
			$new->lat = (float)$lat;
			$new->lon = (float)$lon;

			if(count($new->stations) > 0)
			{
				$particips[] = $new;
			}
		}

		$this->participants = $particips;

		echo 'have ' . count($particips) . " participants entries\n";

		return true;
	}

	public function resultStations()
	{
		return $this->stations;
	}

	public function resultStrikes()
	{
		return $this->participants;
	}

	public function __destruct()
	{
		if($this->ch)
		{
			curl_close($this->ch);
		}
	}
}

