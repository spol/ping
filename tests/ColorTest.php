<?php

namespace Spol\Ping\Tests;

use Spol\Ping\Color;

class ColorTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor1()
    {
        $color = new Color(0xFFEEDDCC);

        $this->assertEquals(0xFF, $color->red);
        $this->assertEquals(0xEE, $color->green);
        $this->assertEquals(0xDD, $color->blue);
        $this->assertEquals(0xCC, $color->alpha);

        $color = new Color(0xFFEEDD, false);

        $this->assertEquals(0xFF, $color->red);
        $this->assertEquals(0xEE, $color->green);
        $this->assertEquals(0xDD, $color->blue);
        $this->assertNull($color->alpha);
    }

    public function testSetColor()
    {
        $color = new Color;
        $color->setColor(0xFF, 0xEE, 0xDD, 0xCC);

        $this->assertEquals(0xFF, $color->red);
        $this->assertEquals(0xEE, $color->green);
        $this->assertEquals(0xDD, $color->blue);
        $this->assertEquals(0xCC, $color->alpha);
    }

    public function testSetGreyscale()
    {
        $color = new Color;
        $color->setGreyscale(0xFF, 0xCC);

        $this->assertEquals(0xFF, $color->red);
        $this->assertEquals(0xFF, $color->green);
        $this->assertEquals(0xFF, $color->blue);
        $this->assertEquals(0xCC, $color->alpha);
    }

    public function testGetters()
    {
        $color = new Color(0xFFEEDDCC);

        $this->assertEquals(0xFF, $color->getRed());
        $this->assertEquals(0xEE, $color->getGreen());
        $this->assertEquals(0xDD, $color->getBlue());
        $this->assertEquals(0xCC, $color->getAlpha());

        $this->assertEquals(0xFF, $color->getRed(8));
        $this->assertEquals(0xEE, $color->getGreen(8));
        $this->assertEquals(0xDD, $color->getBlue(8));
        $this->assertEquals(0xCC, $color->getAlpha(8));

        $this->assertEquals(0xF, $color->getRed(4));
        $this->assertEquals(0xE, $color->getGreen(4));
        $this->assertEquals(0xD, $color->getBlue(4));
        $this->assertEquals(0xC, $color->getAlpha(4));

        $this->assertEquals(0xFFFF, $color->getRed(16));
        $this->assertEquals(0xEEEE, $color->getGreen(16));
        $this->assertEquals(0xDDDD, $color->getBlue(16));
        $this->assertEquals(0xCCCC, $color->getAlpha(16));
    }
}