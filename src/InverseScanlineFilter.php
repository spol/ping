<?php

namespace Spol\Ping;

class InverseScanlineFilter
{
	public static function sub($scanline, $bytesPerPixel)
	{
//		foreach ($scanline->bytes as $index => $byte)

		for ($index = 0; $index < strlen($scanline); ++$index)
		{
			if ($index - $bytesPerPixel < 0)
			{
				$prev = 0;
			}
			else
			{
				$prev = current(unpack('C', $scanline[(int)($index - $bytesPerPixel)]));
			}

			$scanline[$index] = pack('C', (current(unpack('C', $scanline[$index])) + $prev) & 0xFF);
		}

		return $scanline;
	}

	public static function up($scanline, $previousScanline)
	{
		if ($previousScanline == null)
		{
			// no previous scanline
			return $scanline;
		}
//		foreach ($scanline->bytes as $index => $byte)
		for ($index = 0; $index < strlen($scanline); ++$index)
		{
			$scanline[$index] = pack('C', (current(unpack('C', $scanline[$index])) + current(unpack('C', $previousScanline[$index]))) & 0xFF);
		}
		return $scanline;
	}

	public static function Average($scanline, $previousScanline, $bytesPerPixel)
	{
		//foreach ($scanline->bytes as $index => $byte)

		for ($index = 0; $index < strlen($scanline); ++$index)
		{
			if ($previousScanline == null)
			{
				$upByte = 0;
			}
			else
			{
				$upByte = current(unpack('C', $previousScanline[$index]));
			}

			if ($index - $bytesPerPixel < 0)
			{
				$leftByte = 0;
			}
			else
			{
				$leftByte = current(unpack('C', $scanline[(int)($index-$bytesPerPixel)]));
			}

			$scanline[$index] = pack('C', (current(unpack('C', $scanline[$index])) + floor(($upByte + $leftByte)/2)) & 0xFF);
		}
		return $scanline;
	}

	public static function Paeth($scanline, $previousScanline, $bytesPerPixel)
	{
//		foreach ($scanline->bytes as $index => $byte)

		for ($index = 0; $index < strlen($scanline); ++$index)
		{
			if ($previousScanline == null)
			{
				$up = 0;
				$upLeft = 0;
			}
			else
			{
				$up = current(unpack('C', $previousScanline[$index]));
				if ($index - $bytesPerPixel < 0)
				{
					$upLeft = 0;
				}
				else
				{
					$upLeft = current(unpack('C', $previousScanline[(int)($index - $bytesPerPixel)]));
				}
			}

			if ($index - $bytesPerPixel < 0)
			{
				$left = 0;
			}
			else
			{
				$left = current(unpack('C', $scanline[(int)($index - $bytesPerPixel)]));
			}

			$scanline[$index] = pack('C', (current(unpack('C', $scanline[$index])) + ScanlineFilter::PaethPredictor($left, $up, $upLeft)) & 0xFF);
		}
		return $scanline;
	}
}