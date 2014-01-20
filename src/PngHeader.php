<?php

namespace Spol\Ping;

class PngHeader
{
	public $width;
	public $height;
	public $depth;
	public $colortype;
	public $compression;
	public $filter;
	public $interlace;

	public function __construct($data)
	{
		foreach ($data as $key => $value)
		{
			$this->$key = $value;
		}

		if ($this->compression != 0)
		{
			throw new PngFormatException('Invalid or Unknown compression method.');
		}

		if ($this->filter != 0)
		{
			throw new PngFormatException('Invalid or Unknown filter method.');
		}

		if ($this->interlace != 0 && $this->interlace != 1)
		{
			throw new PngFormatException('Invalid or Unknown interlace method.');
		}

		if (!in_array($this->colortype, array(0, 2, 3, 4, 6)))
		{
			throw new PngFormatException('Image contains an invalid color type.');
		}
	}
}
