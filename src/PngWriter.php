<?php

namespace Spol\Ping;

define('MOD_ADLER', 65521);

class PngWriter
{
	private $depth = null;
	private $colortype = 6;
	private $interlaced = false;

	public function __construct()
	{
	}

	public function setDepth($depth)
	{
		$this->depth = $depth;
	}

	private function getDepth($image)
	{
		return $this->depth ? $this->depth : $image->getDepth();
	}

	private function setColortype($type)
	{
		throw new \Exception('Not supported yet.');
	}

	private function setInterlace($interlaced)
	{
		throw new \Exception('Not supported yet.');
	}

	public function save(Image $image, $path)
	{
		$file = fopen($path, 'wb');
		$sig = pack('H*', '89504e470d0a1a0a');
		fwrite($file, $sig);

		$this->writeHeader($file, $image);
		$this->writeImageData($file, $image);
		$this->writeEnd($file);
		fclose($file);
	}

	private function writeHeader($file, $image)
	{
		fwrite($file, pack('N', 13));

		$colortype = $image->isGreyscale() ? 0 : 2;
//		$colortype += $image->hasAlpha() ? 4 : 0;

		$headerdata = pack('A4NNCCCCC',
			'IHDR',
			$image->getWidth(),
			$image->getHeight(),
			$this->getDepth($image),
			$colortype,					// colortype
			0,							// compression method
			0,							// filter method
			$this->interlaced ? 1 : 0);	// interlace method (1 for adam7 interlacing

		fwrite($file, $headerdata);
		fwrite($file, hash('crc32b', $headerdata, true));
	}

	private function writeImageData($file, $image)
	{
		if ($image->isGreyscale())
		{
			$this->writeGreyscaleImageData($file, $image);
		}
		else
		{
			$this->writeTruecolorImageData($file, $image);
		}
	}

	private function writeTruecolorImageData($file, $image)
	{
		if ($this->getDepth($image) == 16)
		{
			$scanlines = '';
			for ($y = 0; $y < $image->getHeight(); ++$y)
			{
				$scanline = '';
				$scanline .= pack('C', 0);
				for ($x = 0; $x < $image->getWidth(); ++$x)
				if ($image->hasAlphaChannel())
				{
					$scanline .= pack('nnnn', $image->getPixel($x, $y)->getRed(), $image->getPixel($x, $y)->getGreen(), $image->getPixel($x, $y)->getBlue(), $image->getPixel($x, $y)->getAlpha());
				}
				else
				{
					$pixel = pack('nnn', $image->getPixel($x, $y)->getRed(), $image->getPixel($x, $y)->getGreen(), $image->getPixel($x, $y)->getBlue());
					$scanline .= $pixel;
				}
				$scanlines.= $scanline;
			}
		}
		elseif ($this->getDepth($image) == 8)
		{
			$scanlines = '';
			for ($y = 0; $y < $image->getHeight(); ++$y)
			{
				$scanlines .= pack('C', 0);
				for ($x = 0; $x < $image->getWidth(); ++$x)
				{
					if ($image->hasAlphaChannel())
					{
						$scanlines .= pack('CCCC', $image->getPixel($x, $y)->getRed(), $image->getPixel($x, $y)->getGreen(), $image->getPixel($x, $y)->getBlue(), $image->getPixel($x, $y)->getAlpha());
					}
					else
					{
						$scanlines .= pack('CCC', $image->getPixel($x, $y)->getRed(), $image->getPixel($x, $y)->getGreen(), $image->getPixel($x, $y)->getBlue());
					}
				}
			}
		}

		else
		{
			var_dump($this->getDepth($image));
			throw new \Exception('Not supported');
		}

		$data = gzcompress($scanlines);
		$length = strlen($data);
		$data = pack('A4', 'IDAT') . $data;

		fwrite($file, pack('N', $length));
		fwrite($file, $data);
		fwrite($file, hash('crc32b', $data, true));
	}

	private function writeGreyscaleImageData($file, $image)
	{
		if ($this->getDepth($image) == 16)
		{
			$scanlines = '';
			for ($y = 0; $y < $image->getHeight(); ++$y)
			{
				$scanlines .= pack('C', 0);
				for ($x = 0; $x < $image->getWidth(); ++$x)
				{
					if ($image->hasAlphaChannel())
					{
						$scanlines .= pack('nn', $image->getPixel($x, $y)->getRed(), $image->getPixel($x, $y)->getAlpha());
					}
					else
					{
						$scanlines .= pack('n', $image->getPixel($x, $y)->getRed());
					}
				}
			}
		}
		elseif ($this->getDepth($image) == 8)
		{
			$scanlines = '';
			for ($y = 0; $y < $image->getHeight(); ++$y)
			{
				$scanlines .= pack('C', 0);
				for ($x = 0; $x < $image->getWidth(); ++$x)
				{
					if ($image->hasAlphaChannel())
					{
						$scanlines .= pack('CC', $image->getPixel($x, $y)->getRed(), $image->getPixel($x, $y)->getAlpha());
					}
					else
					{
						$scanlines .= pack('C', $image->getPixel($x, $y)->getRed());
					}
				}
			}
		}
		elseif ($this->getDepth($image) == 1 || $this->getDepth($image) == 2 || $this->getDepth($image) == 4)
		{
			$scanlines = '';

			$pixelsPerByte = 8 / $this->getDepth($image);

			for ($y = 0; $y < $image->getHeight(); ++$y)
			{
				$scanlines .= pack('C', 0);
				for ($x = 0; $x < $image->getWidth(); $x+=$pixelsPerByte)
				{
					$byte = 0;
					for ($byteOffset = 0; $byteOffset < $pixelsPerByte; $byteOffset++)
					{
						if ($x+$byteOffset < $image->getWidth())
						{
							$byte = $byte | $image->getPixel($x+$byteOffset, $y)->getRed() << (8 - ($this->getDepth($image) * (1 + $byteOffset)));
						}
					}

					$byte = pack('C', $byte);
					$scanlines .= $byte;
				}
			}
		}
		else
		{
			throw new PngFormatException('The Png Format does not support this color depth.');
		}

		$data = gzcompress($scanlines);
		$length = strlen($data);
		$data = pack('A4', 'IDAT') . $data;

		fwrite($file, pack('N', $length));
		fwrite($file, $data);
		fwrite($file, hash('crc32b', $data, true));
	}

	private function writeEnd($file)
	{
		fwrite($file, pack('N', 0));

		fwrite($file,'IEND');
		fwrite($file, pack('H*', 'AE426082'));
	}


}