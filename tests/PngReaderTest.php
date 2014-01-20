<?php

namespace Spol\Ping\Tests;

use Spol\Ping\PngReader;
use Spol\Path\Path;
use GlobIterator;
use FilesystemIterator;

class PngReaderTest extends \PHPUnit_Framework_TestCase
{

    public function testLoad()
    {
        $reader = new PngReader();
        $file = Path::resolve(__DIR__, "../png_test_images/valid/basi0g01.png");
        $image = $reader->load($file);

        $this->assertInstanceOf('Spol\Ping\Image', $image);
    }

    public function testLoadingAllImages()
    {
        $globPath = Path::resolve(__DIR__, "../png_test_images/valid/*.png");

        foreach (new GlobIterator($globPath, FilesystemIterator::CURRENT_AS_PATHNAME) as $imagePath) {
            $reader = new PngReader;
            $image = $reader->load($imagePath);

            $this->assertInstanceOf('Spol\Ping\Image', $image);
        }
    }

    /**
     * @expectedException        Spol\Ping\PngFormatException
     * @expectedExceptionMessage Image contains an invalid color type.
     */
    public function testBrokenImageColorType1()
    {
        $reader = new PngReader();
        $reader->load(Path::resolve(__DIR__, "../png_test_images/broken/xc1n0g08.png"));
    }

    /**
     * @expectedException        Spol\Ping\PngFormatException
     * @expectedExceptionMessage Image contains an invalid color type.
     */
    public function testBrokenImageColorType9()
    {
        $reader = new PngReader();
        $reader->load(Path::resolve(__DIR__, "../png_test_images/broken/xc9n2c08.png"));
    }

    /**
     * @expectedException        Spol\Ping\PngFormatException
     * @expectedExceptionMessage Not a PNG file.
     */
    public function testBrokenImageAddedCR()
    {
        $reader = new PngReader();
        $reader->load(Path::resolve(__DIR__, "../png_test_images/broken/xcrn0g04.png"));
    }

    // /**
    //  * @expectedException        Spol\Ping\PngFormatException
    //  * @expectedExceptionMessage Incorrect IDAT checksum.
    //  */
    // public function testBrokenImageIncorrectChecksum()
    // {
    //     $reader = new PngReader();
    //     $reader->load(Path::resolve(__DIR__, "../png_test_images/broken/xcsn0g01.png"));
    // }

    /**
     * @expectedException        Spol\Ping\PngFormatException
     * @expectedExceptionMessage Invalid color depth for color type.
     */
    public function testBrokenImageBitDepth0()
    {
        $reader = new PngReader();
        $reader->load(Path::resolve(__DIR__, "../png_test_images/broken/xd0n2c08.png"));
    }

    /**
     * @expectedException        Spol\Ping\PngFormatException
     * @expectedExceptionMessage Invalid color depth for color type.
     */
    public function testBrokenImageBitDepth3()
    {
        $reader = new PngReader();
        $reader->load(Path::resolve(__DIR__, "../png_test_images/broken/xd3n2c08.png"));
    }

    /**
     * @expectedException        Spol\Ping\PngFormatException
     * @expectedExceptionMessage Invalid color depth for color type.
     */
    public function testBrokenImageBitDepth99()
    {
        $reader = new PngReader();
        $reader->load(Path::resolve(__DIR__, "../png_test_images/broken/xd9n2c08.png"));
    }

    /**
     * @expectedException        Spol\Ping\PngFormatException
     * @expectedExceptionMessage No image data found.
     */
    public function testBrokenImageMissingIDATChunk()
    {
        $reader = new PngReader();
        $reader->load(Path::resolve(__DIR__, "../png_test_images/broken/xdtn0g01.png"));
    }

    // /**
    //  * @expectedException        Spol\Ping\PngFormatException
    //  * @expectedExceptionMessage No image data found.
    //  */
    // public function testBrokenImageIncorrectIHDRChecksum()
    // {
    //     $reader = new PngReader();
    //     $reader->load(Path::resolve(__DIR__, "../png_test_images/broken/xhdn0g08.png"));
    // }

    /**
     * @expectedException        Spol\Ping\PngFormatException
     * @expectedExceptionMessage Not a PNG file.
     */
    public function testBrokenImageAddedLineFeed()
    {
        $reader = new PngReader();
        $reader->load(Path::resolve(__DIR__, "../png_test_images/broken/xlfn0g04.png"));
    }

    /**
     * @expectedException        Spol\Ping\PngFormatException
     * @expectedExceptionMessage Not a PNG file.
     */
    public function testBrokenImageSignatureByte1()
    {
        $reader = new PngReader();
        $reader->load(Path::resolve(__DIR__, "../png_test_images/broken/xs1n0g01.png"));
    }

    /**
     * @expectedException        Spol\Ping\PngFormatException
     * @expectedExceptionMessage Not a PNG file.
     */
    public function testBrokenImageSignatureByte2()
    {
        $reader = new PngReader();
        $reader->load(Path::resolve(__DIR__, "../png_test_images/broken/xs2n0g01.png"));
    }

    /**
     * @expectedException        Spol\Ping\PngFormatException
     * @expectedExceptionMessage Not a PNG file.
     */
    public function testBrokenImageSignatureByte4()
    {
        $reader = new PngReader();
        $reader->load(Path::resolve(__DIR__, "../png_test_images/broken/xs4n0g01.png"));
    }

    /**
     * @expectedException        Spol\Ping\PngFormatException
     * @expectedExceptionMessage Not a PNG file.
     */
    public function testBrokenImageSignatureByte7()
    {
        $reader = new PngReader();
        $reader->load(Path::resolve(__DIR__, "../png_test_images/broken/xs7n0g01.png"));
    }
}
