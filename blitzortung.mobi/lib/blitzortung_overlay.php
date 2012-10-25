<?php

class blitzortung_overlay
{
	protected $center_lat, $center_lon;
	protected $img_width, $img_height;
	protected $zoom; // 0 to 21
	protected $imghandle;

	public function __construct($center_lat, $center_lon, $zoom, $img_width, $img_height)
	{
		$this->center_lat = (float)$center_lat;
		$this->center_lon = (float)$center_lon;
		$this->img_width = (int)$img_width;
		$this->img_height = (int)$img_height;
		$this->zoom = (int)$zoom;

		$this->imghandle = imagecreatetruecolor($this->img_width, $this->img_height);
		imagesavealpha($this->imghandle, true);

		// establish transparent background:
		$transp_clr = imagecolorallocatealpha($this->imghandle, 255, 0, 255, 127);
		imagefill($this->imghandle, 0, 0, $transp_clr);
	}

	public function getZoom()
	{
		return $this->zoom;
	}

	public function getGoogleStaticMapUrl($maptype = 'terrain')
	{
		return 'http://maps.google.com/maps/api/staticmap?' . http_build_query(array(
			'center' => $this->center_lat . ',' . $this->center_lon,
			'size' => $this->img_width . 'x' . $this->img_height,
			'zoom' => $this->zoom,
			'maptype' => $maptype,
			'sensor' => 'false'
		), '', '&');
	}

	/**
	 * radius in km
	 **/
	public function addRadiusCircle($radius, $filled = true)
	{
		$img_center_x = floor(imagesx($this->imghandle) / 2);
		$img_center_y = floor(imagesy($this->imghandle) / 2);

		$pixel_radius = $this->calcImageRadius($radius);

		if($filled)
		{
			imagesmootharc($this->imghandle, $img_center_x, $img_center_y,
				$pixel_radius * 2, $pixel_radius * 2, array(255, 0, 0, 100), deg2rad(0), deg2rad(360));
		}
		else
		{
			$clr = imagecolorallocate($this->imghandle, 255, 0, 0);

			imagearc($this->imghandle, $img_center_x, $img_center_y,
				$pixel_radius * 2, $pixel_radius * 2, 0, 360, $clr);
		}
	}

	/**
	 * converts km $radius to image pixels, based on center_lat etc.
	 **/
	public function calcImageRadius($radius)
	{
		$world_center_x = Google_Maps::LonToX($this->center_lon);
		$world_center_y = Google_Maps::LatToY($this->center_lat);

		blitzortung_tools::rectangleCoordsFromRadius($this->center_lat, $this->center_lon, $radius,
			/* byref: */ $lat1, $lon1, $lat2, $lon2);

		$world_lat1_y = Google_Maps::LatToY($lat1);
		$world_lon1_x = Google_Maps::LonToX($lon1);

		$delta_x = abs($world_center_x - $world_lon1_x) >> (21 - $this->zoom);
		$delta_y = abs($world_center_y - $world_lat1_y) >> (21 - $this->zoom);

		$pixel_radius = ceil($delta_x / 2 + $delta_y / 2);
		return $pixel_radius;
	}

	public function calcZoomFromRadius($radius)
	{
		$orig_zoom = $this->zoom;
		$zoom = $this->zoom - 1;

		do
		{
			$zoom++;
			if($zoom > 21)
			{
				$zoom = $orig_zoom + 1;
				break;
			}

			$this->zoom = $zoom;
			$pixel_radius = $this->calcImageRadius($radius);
		} while($pixel_radius * 2 < $this->img_width && $pixel_radius * 2 < $this->img_height);

		$this->zoom = $zoom - 1;
	}

	public function addStrikes(array $strikes)
	{
		$co_left = Google_Maps::adjustLonByPixels($this->center_lon, -$this->img_width / 2, $this->zoom);
		$co_top = Google_Maps::adjustLatByPixels($this->center_lat, -$this->img_height / 2, $this->zoom);

		$world_lat1_y = Google_Maps::LatToY($co_top);
		$world_lon1_x = Google_Maps::LonToX($co_left);

		$clr = imagecolorallocatealpha($this->imghandle, 255, 0, 0, 20);

		foreach($strikes as $strike)
		{
			$strike_y = Google_Maps::LatToY($strike['latitude']);
			$strike_x = Google_Maps::LonToX($strike['longitude']);

			$delta_x = abs($strike_x - $world_lon1_x) >> (21 - $this->zoom);
			$delta_y = abs($strike_y - $world_lat1_y) >> (21 - $this->zoom);

			imagefilledellipse($this->imghandle, $delta_x, $delta_y, 4, 4, $clr);
		}
	}

	public function addText($font, $x, $y, $text)
	{
		$clr_back = imagecolorallocatealpha($this->imghandle, 0, 0, 0, 50);
		imagefilledrectangle($this->imghandle, $x - 2, $y, $x + imagefontwidth($font) * strlen($text) + 2, $y + imagefontheight($font), $clr_back);
		$clr = imagecolorallocate($this->imghandle, 255, 255, 255);
		imagestring($this->imghandle, $font, $x, $y, $text, $clr);
	}

	/**
	 * Save a png to $filename.
	 **/
	public function saveToFile($filename)
	{
		return (bool)@imagepng($this->imghandle, $filename);
	}

	public function __destruct()
	{
		@imagedestroy($this->imghandle);
	}
}

require_once SYSTEMDIR . 'lib/blitzortung_tools.php';
require_once SYSTEMDIR . 'lib/Google_Maps.php';
require_once SYSTEMDIR . 'lib/imageSmoothArc.php';
