<?php

function getifset(array &$arr, $key, $default = false)
{
	return (isset($arr[$key]) ? $arr[$key] : $default);
}

/**
 * @return false if not cached or modified, true otherwise.
 * @param bool check_request set this to true if you want to check the client's request headers and "return" 304 if it makes sense. will only output the cache response headers otherwise.
 **/     
function sendHTTPCacheHeaders($cache_file_name, $check_request = false)
{
	$mtime = @filemtime($cache_file_name);

	if($mtime > 0)
	{
		$gmt_mtime = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
		$etag = sprintf('%08x-%08x', crc32($cache_file_name), $mtime);

		header('ETag: "' . $etag . '"');
		header('Last-Modified: ' . $gmt_mtime);
		header('Cache-Control: private');
		// we don't send an "Expires:" header to make clients/browsers use if-modified-since and/or if-none-match

		if($check_request)
		{
			if(isset($_SERVER['HTTP_IF_NONE_MATCH']) && !empty($_SERVER['HTTP_IF_NONE_MATCH']))
			{
				$tmp = explode(';', $_SERVER['HTTP_IF_NONE_MATCH']); // IE fix!
				if(!empty($tmp[0]) && strtotime($tmp[0]) == strtotime($gmt_mtime))
				{
					header('HTTP/1.1 304 Not Modified');
					return false;
				}
			}

			if(isset($_SERVER['HTTP_IF_NONE_MATCH']))
			{
				if(str_replace(array('\"', '"'), '', $_SERVER['HTTP_IF_NONE_MATCH']) == $etag)
				{
					header('HTTP/1.1 304 Not Modified');
					return false;
				}
			}
		}
	}

	return true;
}


function gmtime()
{
	return time() - ((int)substr(date('O'), 0, 3) * 60 * 60);
}
