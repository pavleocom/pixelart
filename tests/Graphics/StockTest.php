<?php
declare(strict_types=1);

namespace PixelArt\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use PixelArt\Factory\ImageFactory;
use PixelArt\Graphics\Stock;

class StockTest extends TestCase
{
    private $stockFile;
    private $inputDir;
    private $outputDir;

    protected function setUp(): void
    {
       $this->stockFile = dirname(__FILE__) . '/../images/stock/stock.csv';
       $this->inputDir = dirname(__FILE__) . '/../images/input';
       $this->outputDir = dirname(__FILE__) . '/../images/output';
    }

    public function testGetBestMatchFindCorrectMatchFromLoadedStock()
    {
        $imageFactory = new ImageFactory();
        $stock = new Stock($imageFactory);
        $stock->load($this->stockFile);
        $image = $stock->getBestMatch([180, 160, 150]);
        $rgb = $image->getMeanRGB();
        $this->assertEquals(186, $rgb[0]);
        $this->assertEquals(162, $rgb[1]);
        $this->assertEquals(158, $rgb[2]);
    }

    public function testGetBestMatchFindsCorrectMatchFromNewlyCreatedStock()
    {
        $imageFactory = new ImageFactory();
        $stock = new Stock($imageFactory);
        $newStockFile = $stock->createStock($this->inputDir, $this->outputDir);
        $stock->load($newStockFile);
        $image = $stock->getBestMatch([180, 160, 150]);
        $rgb = $image->getMeanRGB();
        $this->assertEquals(186, $rgb[0]);
        $this->assertEquals(162, $rgb[1]);
        $this->assertEquals(158, $rgb[2]);

        $this->outputDirCleanUp();

    }

    private function outputDirCleanUp()
    {
        try {
            unlink($this->outputDir . '/1.jpg');
            unlink($this->outputDir . '/2.jpg');
            unlink($this->outputDir . '/3.jpg');
            unlink($this->outputDir . '/4.jpg');
            unlink($this->outputDir . '/5.jpg');
            unlink($this->outputDir . '/6.jpg');
            unlink($this->outputDir . '/stock.csv');
        } catch (Exception $e)
        {
            fwrite(STDERR, 'Could not delete files in the output dir.');
        }
    }

}