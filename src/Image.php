<?php

namespace Spol\Ping;

class Image
{
	private $pixels;
	private $depth;
	private $hasAlpha = false;
	private $isGreyscale = false;

	public function __construct($pixelData, $height=null, $depth=8, $color=true)
	{
		$this->depth = $depth;

		$this->isGreyscale = !$color;

		// Does $pixelData actually contain a width?
		if ($pixelData !== null && is_numeric($pixelData) && $height != null && is_numeric($height))
		{
			$this->createEmptyCanvas($pixelData, $height);
		}

		else if ($pixelData !== null)
		{
			$this->pixels = $pixelData;
		}
	}

	public function createEmptyCanvas($width, $height)
	{
		$this->pixels = array();

		for ($y = 0; $y < $height; $y++) {
			$row = array();
			for ($x = 0; $x < $height; $x++) {
				$row[] = new Color(0xFFFFFFFF); // default to a white background;
			}
			$this->pixels[] = $row;
		}
	}

	public function getPixel($x, $y)
	{
		if ($x > $this->getWidth() || $y > $this->getHeight() || $x < 0 || $y < 0)
		{
			throw new ImageOperationException('Pixel coordinates are outside of image canvas.');
		}
		else
		{
			return $this->pixels[$y][$x];
		}
	}

	public function setPixel($x, $y, Color $color)
	{
		if ($x > $this->getWidth() || $y > $this->getHeight())
		{
			throw new ImageOperationException('Pixel coordinates are outside of image canvas.');
		}
		else
		{
			$old = $this->pixels[$x][$y];
			$this->pixels[$x][$y] = $color;
			return $old;
		}
	}

	public function getWidth()
	{
		return count($this->pixels[0]);
	}

	public function getHeight()
	{
		return count($this->pixels);
	}

	public function changeCanvasWidth()
	{
	}

	public function changeCanvasHeight()
	{
	}

	public function getDepth()
	{
		return $this->depth;
	}

	public function changeDepth($newDepth)
	{
		if ($newDepth != $this->depth)
		{
			for ($x = 0; $x < $this->getWidth(); ++$x)
			{
				for ($y = 0; $y < $this->getHeight(); ++$y)
				{
					$this->pixels[$y][$x]->red   = (int)$this->scale($this->pixels[$y][$x]->red, $this->depth, $newDepth);
					$this->pixels[$y][$x]->green = (int)$this->scale($this->pixels[$y][$x]->green, $this->depth, $newDepth);
					$this->pixels[$y][$x]->blue  = (int)$this->scale($this->pixels[$y][$x]->blue, $this->depth, $newDepth);
					$this->pixels[$y][$x]->alpha = (int)$this->scale($this->pixels[$y][$x]->alpha, $this->depth, $newDepth);
				}
			}
			$this->depth = $newDepth;
		}
	}

	private function scale($value, $from, $to)
	{
		if ($from == $to)
		{
			return $value;
		}

		if ($from > $to)
		{
			$maxin = pow(2, $from)-1;
			$maxout = pow(2, $to) - 1;
			return round($value * $maxout / $maxin);
		}

		else // $from < $to
		{
			$maxin = pow(2, $from)-1;
			$maxout = pow(2, $to) - 1;
			return round($value * $maxout / $maxin);
		}
	}

	public function hasAlphaChannel()
	{
		return $this->hasAlpha;
	}

	public function isGreyscale()
	{
		return $this->isGreyscale;
	}

	// operations
	public function Crop($x, $y, $width, $height)
	{
		$x2 = $x + $width;
		$y2 = $y + $height;

		if ($x < 0 || $y < 0 || $x2 <= $x || $y2 <= $y || $x2 >= $this->getWidth() || $y2 >= $this->getHeight())
		{
			throw new ImageOperationException('Invalid crop coordinates or dimensions given');
		}

		$this->pixels = array_slice($this->pixels, $y, $height);

		foreach ($this->pixels as $key => $row)
		{
			$this->pixels[$key] = array_slice($row, $x, $width);
		}
	}
}