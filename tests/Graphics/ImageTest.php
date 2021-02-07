<?php
declare(strict_types=1);

namespace PixelArt\Tests;

use Exception;
use \PixelArt\Graphics\Image;
use PHPUnit\Framework\TestCase;
use PixelArt\Factory\ImageFactory;

class ImageTest extends TestCase
{
    private $image;
    private $imageFactory;

    protected function setUp(): void
    {
        $imageFile = dirname(__FILE__) . '/../images/test.png';
        $this->imageFactory = new ImageFactory();
        $this->image = $this->imageFactory->create($imageFile);
    }

    public function testGetMeanRGBReturnCorrectValue()
    {
        $meanRGB = $this->image->getMeanRGB();
        $this->assertEquals(91, $meanRGB[0]);
        $this->assertEquals(68, $meanRGB[1]);
        $this->assertEquals(57, $meanRGB[2]);

    }

    public function testGetRGBsScansAllPixels()
    {
        $pixels = $this->image->getRGBs();
        $this->assertEquals(61000, count($pixels));

    }

    public function testGetRGBsCorrectlyDeterminesColous()
    {
        $pixels = $this->image->getRGBs();

        $pixel_0_0 = $pixels['0,0'];
        $this->assertEquals(179, $pixel_0_0[0]);
        $this->assertEquals(27, $pixel_0_0[1]);
        $this->assertEquals(2, $pixel_0_0[2]);

        $pixel_135_150 = $pixels['135,150'];
        $this->assertEquals(2, $pixel_135_150[0]);
        $this->assertEquals(106, $pixel_135_150[1]);
        $this->assertEquals(45, $pixel_135_150[2]);

        $pixel_304_199 = $pixels['304,199'];
        $this->assertEquals(0, $pixel_304_199[0]);
        $this->assertEquals(0, $pixel_304_199[1]);
        $this->assertEquals(0, $pixel_304_199[2]);

    }

    public function testCreateFromSchemaAssemblesImageCorrectly()
    {
        $image[0] = $this->imageFactory->create( dirname(__FILE__) . '/../images/stock/1.jpg' );
        $image[1] = $this->imageFactory->create( dirname(__FILE__) . '/../images/stock/2.jpg' );
        $image[2] = $this->imageFactory->create( dirname(__FILE__) . '/../images/stock/3.jpg' );
        $image[3] = $this->imageFactory->create( dirname(__FILE__) . '/../images/stock/4.jpg' );
        $image[4] = $this->imageFactory->create( dirname(__FILE__) . '/../images/stock/5.jpg' );
        $image[5] = $this->imageFactory->create( dirname(__FILE__) . '/../images/stock/6.jpg' );

        $schema = [];

        for ( $x = 0; $x < 6; $x++) {

            for ( $y = 0; $y < 6; $y++ ) {

                $schema[$x.','.$y] = $image[$y];

            }

        }

        $mosaicPath = Image::createFromSchema($schema, dirname(__FILE__) . '/../images/output');

        $this->assertFileExists($mosaicPath);

        $mosaicImage = $this->imageFactory->create($mosaicPath);

        $meanRGB = $mosaicImage->getMeanRGB();
        $this->assertEquals(158, $meanRGB[0]);
        $this->assertEquals(151, $meanRGB[1]);
        $this->assertEquals(159, $meanRGB[2]);

        $mosaicPixels = $mosaicImage->getRGBs();

        $pixel_0_0 = $mosaicPixels['0,0'];
        $this->assertEquals(154, $pixel_0_0[0]);
        $this->assertEquals(180, $pixel_0_0[1]);
        $this->assertEquals(207, $pixel_0_0[2]);

        $pixel_75_75 = $mosaicPixels['75,75'];
        $this->assertEquals(187, $pixel_75_75[0]);
        $this->assertEquals(187, $pixel_75_75[1]);
        $this->assertEquals(187, $pixel_75_75[2]);

        $pixel_125_125 = $mosaicPixels['125,125'];
        $this->assertEquals(183, $pixel_125_125[0]);
        $this->assertEquals(188, $pixel_125_125[1]);
        $this->assertEquals(210, $pixel_125_125[2]);

        $pixel_175_175 = $mosaicPixels['175,175'];
        $this->assertEquals(186, $pixel_175_175[0]);
        $this->assertEquals(162, $pixel_175_175[1]);
        $this->assertEquals(158, $pixel_175_175[2]);

        $pixel_225_225 = $mosaicPixels['225,225'];
        $this->assertEquals(157, $pixel_225_225[0]);
        $this->assertEquals(148, $pixel_225_225[1]);
        $this->assertEquals(139, $pixel_225_225[2]);

        $pixel_275_275 = $mosaicPixels['275,275'];
        $this->assertEquals(83, $pixel_275_275[0]);
        $this->assertEquals(46, $pixel_275_275[1]);
        $this->assertEquals(53, $pixel_275_275[2]);


        try {
            unlink($mosaicPath);
        } catch (Exception $e) {
            fwrite(STDERR, 'Unable to delete images.');
        }

    }


}