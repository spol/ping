<?php

namespace Spol\Ping;

class PngReader
{
    const PALETTE = 1;
    const COLOR = 2;
    const ALPHA = 4;

    private $width;
    private $height;
    private $colortype;
    private $palette;
    private $depth;
    private $image;
    private $interlaced;
    private $source;

    public function __construct()
    {
    }

    public function load($file)
    {
        $file = fopen($file, 'rb');
        $header = fread($file, 8);
        $imageDataChunks = array();
        $palette = null;

        if (bin2hex($header) == '89504e470d0a1a0a')
        {
            while($chunk = $this->readPngChunk($file))
            {
                switch ($chunk->type)
                {
                    case "IHDR":
                        $pngHeader = $this->parseHeader($chunk);
                        $this->height = $pngHeader->height;
                        $this->width = $pngHeader->width;
                        $this->depth = $pngHeader->depth;
                        $this->colortype = $pngHeader->colortype;
                        $this->interlaced = $pngHeader->interlaced;
                        unset($pngHeader);
                        break;
                    case "IDAT":
                        $imageDataChunks[] = $chunk;
                        break;
                    case "PLTE":
                        $this->palette = $this->parsePalette($chunk);
                        break;
                    case "bKGD":
                        break;
/*
                    case "tRNS":
                        $trns = $this->parseTransparencyChunk($chunk);
                        break;
*/
                    case "IEND":
                        $ended = true;
                        break;
                    default:
                        break;
                }
            }

            if (!$ended)
            {
                throw new PngFormatException('PNG file did not end correctly.');
            }

            if (empty($imageDataChunks))
            {
                throw new PngFormatException('No image data found.');
            }

            if ($this->colortype & PNGReader::PALETTE)
            {
                return new Image($this->parseImageData($imageDataChunks), null, 8, $this->colortype & PNGReader::COLOR);
            }
            else
            {
                return new Image($this->parseImageData($imageDataChunks), null, $this->depth, $this->colortype & PNGReader::COLOR);
            }
        }
        else
        {
            // TODO: Check for possible header corruption, e.g. added CR/LF bytes.
            throw new PngFormatException('Not a PNG file.');
        }

    }

    private function parseTransparencyChunk($chunk)
    {
        if ($this->colortype & PNGReader::PALETTE)
        {
            $bytes = array_values(unpack('C*', $chunk->data));

            foreach ($bytes as $i => $byte)
            {
                $this->palette->setEntryAlpha($i, $byte);
            }
        }
        elseif ($this->colortype & PNGReader::COLOR)
        {
            return array_values(unpack('n3', $chunk->data));
        }
        else // greyscale
        {
            return current(unpack('n', $chunk->data));
        }
    }

    private function parseHeader($chunk)
    {
        $data = unpack('Nwidth/Nheight/Cdepth/Ccolortype/Ccompression/Cfilter/Cinterlaced', $chunk->data);

        return new PngHeader($data);
    }

    private function parsePalette($chunk)
    {
        if ($chunk->length % 3 != 0)
        {
            throw new Exception('Invalid palette length.');
        }

        return new Palette($chunk->data);
    }

    private function parseInterlacedImageData($data, $length, $pixelSize)
    {
        $fullFrameHorizontalCount = floor($this->width / 8);
        $fullFrameVerticalCount = floor($this->height / 8);

        // First Pass Frame
        $frames[0]['width'] = $fullFrameHorizontalCount;
        $frames[0]['height'] = $fullFrameVerticalCount;
        if (($this->width % 8) >= 1)
        {
            $frames[0]['width']++;
        }
        if (($this->height % 8) >= 1)
        {
            $frames[0]['height']++;
        }

        // Second Pass Frame
        $frames[1]['width'] = $fullFrameHorizontalCount;
        $frames[1]['height'] = $fullFrameVerticalCount;
        if (($this->width % 8) >= 5)
        {
            $frames[1]['width']++;
        }

        if (($this->height % 8) >= 1)
        {
            $frames[1]['height']++;
        }

        // Third Pass Frame
        $frames[2]['width'] = $fullFrameHorizontalCount * 2;
        $frames[2]['height'] = $fullFrameVerticalCount;
        if (($this->width % 8) >= 5)
        {
            $frames[2]['width']+=2;
        }
        elseif (($this->width % 8) >= 1)
        {
            $frames[2]['width']++;
        }

        if (($this->height % 8) >= 5)
        {
            $frames[2]['height']++;
        }

        // Fourth Pass Frame
        $frames[3]['width'] = $fullFrameHorizontalCount * 2;
        $frames[3]['height'] = $fullFrameVerticalCount * 2;
        if (($this->width % 8) >= 7)
        {
            $frames[3]['width']+=2;
        }
        elseif (($this->width % 8) >= 3)
        {
            $frames[3]['width']++;
        }

        if (($this->height % 8) >= 5)
        {
            $frames[3]['height']+=2;
        }
        elseif (($this->width % 8) >= 1)
        {
            $frames[3]['height']++;
        }

        // Fifth Pass Frame
        $frames[4]['width'] = $fullFrameHorizontalCount * 4;
        $frames[4]['height'] = $fullFrameVerticalCount * 2;
        if (($this->width % 8) >= 7)
        {
            $frames[4]['width']+=4;
        }
        elseif (($this->width % 8) >= 5)
        {
            $frames[4]['width']+=3;
        }
        elseif (($this->width % 8) >= 3)
        {
            $frames[4]['width']+=2;
        }
        elseif (($this->width % 8) >= 1)
        {
            $frames[4]['width']+=1;
        }

        if (($this->height % 8) >= 7)
        {
            $frames[4]['height']+=2;
        }
        elseif (($this->height % 8) >= 3)
        {
            $frames[4]['height']++;
        }

        // Sixth Pass Frame
        $frames[5]['width'] = $fullFrameHorizontalCount * 4;
        $frames[5]['height'] = $fullFrameVerticalCount * 4;
        if (($this->width % 8) >= 6)
        {
            $frames[5]['width']+=3;
        }
        elseif (($this->width % 8) >= 4)
        {
            $frames[5]['width']+=2;
        }
        elseif (($this->width % 8) >= 2)
        {
            $frames[5]['width']+=1;
        }

        if (($this->height % 8) >= 7)
        {
            $frames[5]['height']+=4;
        }
        elseif (($this->height % 8) >= 5)
        {
            $frames[5]['height']+=3;
        }
        elseif (($this->height % 8) >= 3)
        {
            $frames[5]['height']+=2;
        }
        elseif (($this->height % 8) >= 1)
        {
            $frames[5]['height']+=1;
        }

        // Seventh Pass Frame
        $frames[6]['width'] = $fullFrameHorizontalCount * 8;
        $frames[6]['height'] = $fullFrameVerticalCount * 4;

        $frames[6]['width'] += $this->width % 8;

        if (($this->height % 8) >= 6)
        {
            $frames[6]['height']+=3;
        }
        elseif (($this->height % 8) >= 4)
        {
            $frames[6]['height']+=2;
        }
        elseif (($this->height % 8) >= 2)
        {
            $frames[6]['height']+=1;
        }

        // Frame 1
        $frames[0]['offset'] = 0;

        for ($i = 0; $i < 7; $i++)
        {
            $pixelCount = $frames[$i]['width'] * $frames[$i]['height'];

            if ($pixelCount > 0)
            {
                $frames[$i]['length'] = (ceil(($frames[$i]['width'] * $pixelSize)/8) + 1) * $frames[$i]['height'];
                $frames[$i+1]['offset'] = $frames[$i]['offset'] + $frames[$i]['length'];

                $frames[$i]['data'] = substr($data, $frames[$i]['offset'], $frames[$i]['length']);
                $frames[$i]['pixels'] = $this->parseProgressiveImageData($frames[$i]['data'], $frames[$i]['length'], $pixelSize, $frames[$i]['width'], $frames[$i]['height']);
            }
            else
            {
                $frames[$i+1]['offset'] = $frames[$i]['offset'];
                $frames[$i]['pixels'] = array();
            }
        }

        $pixels = array();

        for ($x = 0; $x < $this->width; ++$x)
        {
            for ($y = 0; $y < $this->height; ++$y)
            {
                if ($y % 8 == 0 && $x % 8 == 0)
                {
                    $pixels[$y][$x] = $frames[0]['pixels'][$y/8][$x/8];
                }
                elseif ($y % 8 == 0 && $x % 8 == 4)
                {
                    $pixels[$y][$x] = $frames[1]['pixels'][$y/8][($x-4)/8];
                }
                elseif ($y % 8 == 4 && $x % 4 == 0)
                {
                    $pixels[$y][$x] = $frames[2]['pixels'][($y-4)/8][$x/4];
                }
                elseif ($y % 4 == 0 && $x % 4 == 2)
                {
                    $pixels[$y][$x] = $frames[3]['pixels'][$y/4][($x-2)/4];
                }
                elseif ($y % 4 == 2 && $x % 2 == 0)
                {
                    $pixels[$y][$x] = $frames[4]['pixels'][($y-2)/4][$x/2];
                }
                elseif ($y % 2 == 0 && $x % 2 == 1)
                {
                    $pixels[$y][$x] = $frames[5]['pixels'][$y/2][($x-1)/2];
                }
                elseif ($y % 2 == 1)
                {
                    $pixels[$y][$x] = $frames[6]['pixels'][$y/2][$x];
                }
                else
                {
                    throw new \Exception('This shouldn\'t happen: ' . $x . ', ' . $y);
                }
            }
        }
        return $pixels;
    }

    private function parseImageData($chunks)
    {
        $data = '';
        $length = 0;
        foreach ($chunks as $chunk)
        {
            $data .= $chunk->data;
            $length += $chunk->length;
        }
        unset($chunks);

        $data = gzinflate(substr($data,2,$length - 6)); // 2 bytes on the front and 4 at the end
        if ($data == false)
        {
            throw new PngFormatException('Error decompressing image data.');
        }

        $pixelSize = $this->depth;

        if ($this->colortype & PNGReader::COLOR && !($this->colortype & PNGReader::PALETTE))
        {
            $pixelSize *= 3;
        }
        if ($this->colortype & PNGReader::ALPHA)
        {
            $pixelSize += $this->depth;
        }

        if ($this->interlaced)
        {
            return $this->parseInterlacedImageData($data, $length, $pixelSize);
        }
        else
        {
            return $this->parseProgressiveImageData($data, $length, $pixelSize);
        }
    }

    private function parseProgressiveImageData($data, $length, $pixelSize, $width=null, $height=null)
    {
        if ($width == null) $width = $this->width;
        if ($height == null) $height = $this->height;

        $bytesPerPixel = ceil($pixelSize/8);

        $scanlineByteLength = ceil(($pixelSize * $width) / 8) + 1; // + 1 byte for filter type.

        $rows = array();

/*      $previous = null; */

        $scanlines = str_split($data, $scanlineByteLength);
        unset($data);

        for ($line = 0; $line < $height; ++$line)
        {
            // var_dump(memory_get_usage());
//          $scanline = new Scanline(substr($data, $line*$scanlineByteLength, $scanlineByteLength));

/*
            $scanline = new Scanline(substr($data, 0, $scanlineByteLength));
            $data = substr($data, $scanlineByteLength);
*/
            // echo(current(unpack('C', $scanlines[$line][0])));
            switch (current(unpack('C', $scanlines[$line][0])))
            {
                case 0:
                    $scanlines[$line] = substr($scanlines[$line], 1);
                    break;
                case 1: // Sub
                    $scanlines[$line] = substr($scanlines[$line], 1);
                    $scanlines[$line] = InverseScanlineFilter::sub($scanlines[$line], $bytesPerPixel);
                    break;
                case 2: // Up
                    $scanlines[$line] = substr($scanlines[$line], 1);
                    $scanlines[$line] = InverseScanlineFilter::up($scanlines[$line], $line > 0 ? $scanlines[$line-1] : 0);
                    break;
                case 3: // Average
                    $scanlines[$line] = substr($scanlines[$line], 1);
                    $scanlines[$line] = InverseScanlineFilter::average($scanlines[$line], $line > 0 ? $scanlines[$line-1] : 0, $bytesPerPixel);
                    break;
                case 4: // Paeth
                    $scanlines[$line] = substr($scanlines[$line], 1);
                    InverseScanlineFilter::paeth($scanlines[$line], $line > 0 ? $scanlines[$line-1] : 0, $bytesPerPixel);
                    break;
            }

            if ($this->colortype & PNGReader::PALETTE)
            {
                // palette based image
                $rows[] = $this->parsePaletteScanline($scanlines[$line], $width);
            }
            elseif ($this->colortype & PNGReader::COLOR)
            {
                // color image
                $rows[] = $this->parseColorScanline($scanlines[$line], $width);
            }
            else
            {
                // greyscale
                $rows[] = $this->parseGreyscaleScanline($scanlines[$line], $width);
            }

            unset($scanlines[$line-1]);

            // store scanline for next iteration.
//          $previous = $scanline;
        }

        return $rows;
    }

    public function parsePaletteScanline($scanline, $width)
    {
        $cursor = 0;
        $pixels = array();

        for ($i = 0; $i < $width; ++$i)
        {
            if ($this->depth == 8)
            {
                if ($this->palette->getEntry(current(unpack('C', $scanline[$cursor]))) == null)
                {
                    throw new \Exception('Index not found in palette: ' . current(unpack('C', $scanline[$cursor])));
                }
                $pixel = $this->palette->getEntry(current(unpack('C', $scanline[$cursor])));
                $cursor++;

                $pixels[] = $pixel;
            }
            else if ($this->depth == 4)
            {
                $value = current(unpack('C', $scanline[(int)floor($cursor/2)]));

                $offset = (1 - ($cursor % 2)) * 4;
                $value = ($value >> $offset) & 0x0F;

                $pixels[] = $this->palette->getEntry($value);
                $cursor++;
            }
            elseif ($this->depth == 2)
            {
                $value = current(unpack('C', $scanline[(int)floor($cursor/4)]));
                $masks = array(0xC0, 0x30, 0x0C, 0x03);

                $offset = (3 - ($cursor % 4)) * 2;

                $value = ($value >> $offset) & 0x03;

                $pixels[] = $this->palette->getEntry($value);
                $cursor++;
            }
            elseif ($this->depth == 1)
            {
                $value = current(unpack('C', $scanline[(int)floor($cursor/8)]));

                $offset = (7 - ($cursor % 8));

                $value = ($value >> $offset) & 0x01;

                $pixels[] = $this->palette->getEntry($value);
                $cursor++;
            }
            else
            {
                throw new \Exception('Palette depth is invalid.');
            }
        }
        return $pixels;
    }

    private function parseColorScanline($scanline, $width)
    {
        $alpha = !!($this->colortype & PNGReader::ALPHA);

        // depth will be 8 or 16.
        $cursor = 0;
        $pixels = array();
        for ($i = 0; $i < $width; ++$i)
        {
            if ($this->depth == 8)
            {
                $pixel = new Color();
                $pixel->red = current(unpack('C', $scanline[$cursor++]));
                $pixel->green = current(unpack('C', $scanline[$cursor++]));
                $pixel->blue = current(unpack('C', $scanline[$cursor++]));

                if ($alpha)
                {
                    $pixel->alpha = current(unpack('C', $scanline[$cursor++]));
                }
                else
                {
                    $pixel->alpha = null;
                }
            }
            elseif ($this->depth == 16)
            {
                $pixel = new Color();
                $pixel->red = current(unpack('n', substr($scanline, $cursor, 2)));
                $cursor += 2;
                $pixel->green = current(unpack('n', substr($scanline, $cursor, 2)));
                $cursor += 2;
                $pixel->blue = current(unpack('n', substr($scanline, $cursor, 2)));
                $cursor += 2;

                if ($alpha)
                {
                    $pixel->alpha = current(unpack('n', substr($scanline, $cursor, 2)));
                    $cursor += 2;
                }
                else
                {
                    $pixel->alpha = null;
                }
            }
            else
            {
                throw new PngFormatException("Invalid color depth for color type.");
            }

            $pixels[] = $pixel;
        }
        return $pixels;
    }

    private function parseGreyscaleScanline(&$scanline, $width)
    {
        $alpha = $this->colortype & PNGReader::ALPHA;
        // without alpha, depth is 1,2,4,8,16
        // with its 8,16

        $cursor = 0;
        $pixels = array();
        for ($i = 0; $i < $width; ++$i)
        {
            $pixel = new Color();
            if ($this->depth == 8)
            {
                $pixel = new Color();

                if ($alpha)
                {
                    $pixel->setGreyscale(current(unpack('C', $scanline[$cursor])), current(unpack('C', $scanline[$cursor+1])));
                    $cursor+=2;
                }
                else
                {
                    $pixel->setGreyscale(current(unpack('C', $scanline[$cursor])));
                    $cursor++;
                }
            }
            elseif ($this->depth == 16)
            {
                $pixel = new Color();

                if ($alpha)
                {
                    $pixel->setGreyscale(current(unpack('n', substr($scanline, $cursor, 2))), current(unpack('n', substr($scanline, $cursor+2, 2))));
                    $cursor += 4;
                }
                else
                {
                    $pixel->setGreyscale(current(unpack('n', substr($scanline, $cursor, 2))));
                    $cursor += 2;
                }
            }
            elseif ($this->depth == 4 || $this->depth == 2 || $this->depth == 1)
            {
                $value = current(unpack('C', $scanline[(int)floor($cursor/(8/$this->depth))]));
                $offset = (((8/$this->depth)-1) - ($cursor % (8 / $this->depth))) * $this->depth;
                $value = ($value >> $offset) & (pow(2, $this->depth) - 1);

                $pixel = new Color();
                $pixel->setGreyscale($value, pow(2, $this->depth) - 1);

                $cursor++;

            }
            else
            {
                throw new \Exception('Unsupported bit depth.');
            }
            $pixels[] = $pixel;
        }
        return $pixels;
    }

    private function readPngChunk($file)
    {
        $length = fread($file, 4);
        if (!$length) return false;
        $length = unpack('Nlength', $length);
        $chunk = new PngChunk();
        $chunk->length = $length['length'];
        $chunk->type = fread($file, 4);
        if ($chunk->length > 0)
        {
            $chunk->data = fread($file, $chunk->length);
        }
        else
        {
            $chunk->data = '';
        }
        $chunk->crc = fread($file, 4);

        return $chunk;
    }
}