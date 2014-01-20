<?php

namespace Spol\Ping;

class ScanlineFilter
{
	public static function PaethPredictor($left, $up, $upLeft)
	{
		$p = $left + $up - $upLeft;
		$pa = abs($p - $left);
		$pb = abs($p - $up);
		$pc = abs($p - $upLeft);

		if (($pa <= $pb) && ($pa <= $pc)) return $left;
		if ($pb <= $pc) return $up;
		return $upLeft;
	}
}