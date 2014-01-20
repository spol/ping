<?php

namespace Spol\Ping;

class Color
{
	public $red;
	public $green;
	public $blue;
	public $alpha;
	private $greyscale = false;
	public $depth = 8;

	public function __construct($rgb=null, $alpha=true)
	{
		if ($rgb == null) return;

		if ($alpha)
		{
			$this->red = ($rgb >> 24) & 0xFF;
			$this->green = ($rgb >> 16) & 0xFF;
			$this->blue = ($rgb >> 8) & 0xFF;
			$this->alpha = $rgb & 0xFF;
		}
		else
		{
			$this->red = ($rgb >> 16) & 0xFF;
			$this->green = ($rgb >> 8) & 0xFF;
			$this->blue = $rgb & 0xFF;
		}
	}

	public function getRed($depth=null)
	{
		return $this->getColor('red', $depth);
	}

	public function getGreen($depth=null)
	{
		return $this->getColor('green', $depth);
	}

	public function getBlue($depth=null)
	{
		return $this->getColor('blue', $depth);
	}

	public function getAlpha($depth=null)
	{
		return $this->getColor('alpha', $depth);
	}

	private function getColor($field, $depth=null)
	{
		if ($depth == null || $this->depth == $depth)
		{
			return $this->$field;
		}
		else
		{
			return $this->scale($this->$field, $this->depth, $depth);
		}
	}

	public function setColor($red, $green, $blue, $alpha=null)
	{
		$this->greyscale = false;

		$this->red = $red;
		$this->blue = $blue;
		$this->green = $green;
		$this->alpha = $alpha;
	}

	public function setGreyscale($level, $alpha=null)
	{
		$this->greyscale = true;
		$this->red = $level;
		$this->green = $level;
		$this->blue = $level;
		$this->alpha = $alpha;
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
}