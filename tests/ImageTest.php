<?php

namespace Spol\Ping\Tests;

use Spol\Ping;
use Spol\Ping\PngReader;
use Spol\Path\Path;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateBlankImage()
    {
        $image = new Ping\Image(10, 10);
    }

    public function testGetPixel()
    {
        $image = new Ping\Image(10, 10);

        $pixel = $image->getPixel(0,0);

        $this->assertInstanceOf('Spol\Ping\Color', $pixel);

        $this->assertEquals(0xFF, $pixel->red);
        $this->assertEquals(0xFF, $pixel->green);
        $this->assertEquals(0xFF, $pixel->blue);
        $this->assertEquals(0xFF, $pixel->alpha);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Pixel coordinates are outside of image canvas.
     */
    public function testGetPixelOvershoot()
    {
        $image = new Ping\Image(10, 10);

        $pixel = $image->getPixel(11,11);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Pixel coordinates are outside of image canvas.
     */
    public function testGetPixelUndershoot()
    {
        $image = new Ping\Image(10, 10);

        $pixel = $image->getPixel(-1,-1);
    }

    public function testChangeDepth()
    {
        $image = new Ping\Image(10, 10);

        $this->assertEquals(8, $image->getDepth());

        $image->changeDepth(4);

        $this->assertEquals(4, $image->getDepth());
        $this->assertEquals(0xF, $image->getPixel(0,0)->red);

        $image->changeDepth(8);

        $this->assertEquals(8, $image->getDepth());
        $this->assertEquals(0xFF, $image->getPixel(0,0)->red);
    }
}
