<?php

class blitzortung_badge
{
	protected $station;
	protected $lang;

	protected $tpl_file;
	protected $font_file;
	protected $font_file_light;
	protected $font_file_bold;

	protected $imghandle = NULL;

	public function __construct(array $station, $lang = 'de')
	{
		$this->tpl_file = SYSTEMDIR . 'res/badge-tpl.png';
		$this->font_file = SYSTEMDIR . 'res/SegoeWP.ttf';
		$this->font_file_light = SYSTEMDIR . 'res/SegoeWP-Light.ttf';
		$this->font_file_bold = SYSTEMDIR . 'res/SegoeWP-Semibold.ttf';

		$this->station = $station;
		$this->lang = $lang;
	}

	public function make()
	{
		$this->imghandle = imagecreatefrompng($this->tpl_file);

		$clr_title = imagecolorallocate($this->imghandle, 0, 0, 0);
		$clr_on = imagecolorallocate($this->imghandle, 0, 200, 0);
		$clr_off = imagecolorallocate($this->imghandle, 200, 0, 0);
		$clr_credits = imagecolorallocate($this->imghandle, 255, 255, 255);

		// step 1: Station name
		$title = 'Station ' . $this->station['city'];
		$padding_x = 10;
		$title_pos_y = 20;

		$cut = 3;
		do
		{
			$real_title = $title;
			$dims = imagettfbbox(11, 0, $this->font_file_bold, $title);
			$cut++;
			$title = substr($title, 0, -$cut) . '...';
		} while($dims[2] - $dims[0] > imagesx($this->imghandle) - $padding_x * 2);
		$title = $real_title;

		imagettftext($this->imghandle, 11, 0, $padding_x, $title_pos_y, $clr_title, $this->font_file_bold, $title);

		// step 2: online/active?
		$is_online = ($this->station['last_signal'] > gmtime() - 86400 / 2);

		if(!$is_online)
		{
			$off_text = '- Offline -';
			$dims = imagettfbbox(10, 0, $this->font_file_bold, $off_text);
			imagettftext($this->imghandle, 10, 0,
				(imagesx($this->imghandle) - ($dims[4] - $dims[6])) / 2,
				$title_pos_y * 2.2, $clr_off, $this->font_file_bold, $off_text);
			return true;
		}

		// step 3: description
		$descr_line1 = sprintf($this->lang == 'de' ?
			'%d Blitz-Ortungen in den letzten 24h' :
			'%d detected strikes in the last 24h', $this->station['strikes_24h']);
		imagettftext($this->imghandle, 10, 0, $padding_x, $title_pos_y * 2 - 2, $clr_title, $this->font_file, $descr_line1);

		$descr_line2 = sprintf($this->lang == 'de' ?
			'Quote (1h): %.1f%% (%d Blitze / %d Signale)' :
			'Ratio (1h): %.1f%% (%d strikes / %d signals)',
			($this->station['signals'] > 0 ? $this->station['strikes_1h'] / $this->station['signals'] * 100 : 0), $this->station['strikes_1h'], $this->station['signals']);
		imagettftext($this->imghandle, 10, 0, $padding_x, $title_pos_y * 2 + 13, $clr_title, $this->font_file, $descr_line2);

		// "blitzortung.org"
		$credit_text = 'blitzortung.org';
		$dims = imagettfbbox(7, 0, $this->font_file, $credit_text);
		imagettftext($this->imghandle, 7, 0, imagesx($this->imghandle) - 3 -
			($dims[4] - $dims[6]), imagesy($this->imghandle) - 4, $clr_credits,
			$this->font_file, $credit_text);

		return true;
	}

	public function output($filename = NULL)
	{
		if(!$this->imghandle)
		{
			return false;
		}

		if(is_null($filename))
		{
			header('Content-Type: image/png');
			imagepng($this->imghandle);
		}
		else
		{
			imagepng($this->imghandle, $filename);
		}

		return true;
	}
}
