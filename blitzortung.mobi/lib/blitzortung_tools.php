<?php

class blitzortung_tools
{
	public static function rectangleCoordsFromWidthHeight($center_lat, $center_lon, $width, $height, &$lat1, &$lon1, &$lat2, &$lon2)
	{
		// 1 deg of lat = about 111 km
		// 1 deg of lon = about cos(lat) * 111 km
		$lat1 = $center_lat - $height / 111;
		$lon1 = $center_lon - $width / abs(cos(deg2rad($center_lat)) * 111);
		$lat2 = $center_lat + $height / 111;
		$lon2 = $center_lon + $width / abs(cos(deg2rad($center_lat)) * 111);
	}

	public static function rectangleCoordsFromRadius($center_lat, $center_lon, $radius, &$lat1, &$lon1, &$lat2, &$lon2)
	{
		self::rectangleCoordsFromWidthHeight($center_lat, $center_lon, $radius, $radius, $lat1, $lon1, $lat2, $lon2);
	}
}
