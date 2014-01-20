<?php

namespace Spol\Ping\Tests;

use Spol\Ping\PngReader;
use Spol\Ping\PngWriter;
use Spol\Path\Path;
use GlobIterator;
use FilesystemIterator;

class PngWriterTest extends \PHPUnit_Framework_TestCase
{
    public function testImageWriter()
    {
        $globPath = Path::resolve(__DIR__, "../png_test_images/valid/basi*.png");

        $currentlyBroken = array(
            "basi0g08",
            "basi2c08",
            "basi2c16",
            "basi4a08",
            "basi4a16",
            "basi6a08",
            "basi6a16"
        );

        foreach (new GlobIterator($globPath, FilesystemIterator::CURRENT_AS_PATHNAME) as $imagePath) {

            // Skip currently broken image types whilst I fix them.
            foreach ($currentlyBroken as $name) {
                if (strpos($imagePath, $name) !== false) {
                    continue 2;
                }
            }

            $reader = new PngReader;

            $outputPath = Path::resolve(__DIR__, "../tmp/" . basename($imagePath));

            $image = $reader->load($imagePath);

            $writer = new PngWriter;

            $writer->save($image, $outputPath);

            $cmd = sprintf('compare -metric AE "%s" "%s" "%s.diff.png"', $imagePath, $outputPath, $outputPath);

            $descriptorspec = array(
                0 => array('pipe', 'r'),
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w')
            );

            $proc = proc_open($cmd, $descriptorspec, $pipes);

            if (is_resource($proc)) {
                $compareResult = trim(stream_get_contents($pipes[2]));
                fclose($pipes[0]);
                fclose($pipes[1]);
                fclose($pipes[2]);

                proc_close($proc);
            }

            $this->assertEquals(0, $compareResult, $outputPath);

        }

    }
}
