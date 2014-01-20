<?php

namespace Spol\Ping\Tests;

use Spol\Ping\Palette;
use Spol\Path\Path;

class PaletteTest extends \PHPUnit_Framework_TestCase
{
    public function testPalette()
    {
        $file = Path::resolve(__DIR__, "../png_test_data/paletteSample.dat");

        $paletteData = file_get_contents($file);

        $palette = new Palette($paletteData);

        $this->assertEquals(256, $palette->getEntryCount());

        $this->assertEquals(0x22, $palette->getEntry(0)->red);
        $this->assertEquals(0xED, $palette->getEntry(33)->green);
        $this->assertEquals(0x01, $palette->getEntry(65)->blue);
        $this->assertNull($palette->getEntry(0)->alpha);

        $palette->setEntryAlpha(0, 0x2F);
        $this->assertEquals(0x2F, $palette->getEntry(0)->alpha);
    }
}