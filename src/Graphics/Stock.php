<?php

namespace PixelArt\Graphics;

use Exception;
use DirectoryIterator;
use PixelArt\Contracts\ImageFactoryInterface;

class Stock
{
    /**
     * Mimes of files that can be used for creating stock images.
     * @var array $supportedMimeTypes
     */
    private $supportedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'];

    /**
     * Array of stock images (ImageInterface)
     * @var array $stockImages
     */
    private $stockImages = [];

    /**
     * Image factory
     * @var ImageFactoryInterface $imageFactory
     */
    private $imageFactory;

    public function __construct(ImageFactoryInterface $imageFactory)
    {
        $this->imageFactory = $imageFactory;
    }

    /**
     * Sets stock image size (pixels). Stock images are always square.
     * @param int $size
     */
    public function setStockImageSize(int $size)
    {
        $this->imageFactory->setStockImageSize($size);
        return $this;
    }

    /**
     * Loads stock from CSV file.
     * @param string $stockFile - fullpath
     * @return self $this
     */
    public function load(string $stockFile)
    {
        if (is_readable($stockFile)) {

            $dir = dirname($stockFile);
            $handle = fopen($stockFile, 'r');

            while ( ($row = fgetcsv($handle)) !== false ) {
                $stockImagePath = $row[0];
                $stockImageMeanRGB = [$row[1], $row[2], $row[3]];

                $stockImage = $this->imageFactory->create( $dir . DIRECTORY_SEPARATOR . $stockImagePath );
                $stockImage->setMeanRGB($stockImageMeanRGB);

                $this->stockImages[] = $stockImage;
            }

            fclose($handle);
        }

        return $this;
    }

    /**
     * Creates resized stock images from original images.
     * @param string $inputDir - directory with original images 
     * @param string $outputDir (optional
     * @return string $stockFile - CSV file contain paths and mean RGBs of stock images
     */
    public function createStock(string $inputDir, string $outputDir = null)
    {
        if ($outputDir === null) {
            $outputDir = dirname($inputDir) . DIRECTORY_SEPARATOR . 'stock_' . time();
        }

        if (!file_exists($outputDir)) {
            if ( !mkdir($outputDir, 0755, true) ) {
                throw new Exception('Unable to create directory for new stock.');
            }
        }

        $stockFile = $outputDir . DIRECTORY_SEPARATOR . 'stock.csv';

        foreach (new DirectoryIterator($inputDir) as $file) {

            if ( $file->isFile() && $file->isReadable() ) {

                $fileMimeType = mime_content_type( $file->getPathname() );

                if (in_array($fileMimeType, $this->supportedMimeTypes)) {
                    $image = $this->imageFactory->create( $file->getPathname() );
                    $stockImagePath = $image->createStockImage($outputDir);
                    $stockImageMeanRGB = $this->imageFactory->create($stockImagePath)->getMeanRGB();
                    $pathAndRGB = [ basename($stockImagePath),$stockImageMeanRGB[0],
                                    $stockImageMeanRGB[1], $stockImageMeanRGB[2] ];

                    $this->writeToCsv($stockFile, $pathAndRGB);
                }  
            }
        }

        return $stockFile;
    }

    /**
     * Writes array to CSV file.
     * @param string $file - path to the csv file.
     * @param array $fields
     */
    public function writeToCsv($file, $fields)
    {
        $fp = fopen($file, 'a+');
        fputcsv($fp, $fields);
        fclose($fp);

        chmod($file, 0775);
    }

    /**
     * Finds and returns the best matching RGB out of stock images.
     * @param array $meanRGB [<int>R, <int>G, <int>B]
     * @return array [<int>R, <int>G, <int>B]
     */
    public function getBestMatch(array $meanRGB)
    {
        if (empty($this->stockImages)) {
            throw new Exception('There are no images in this stock.');
        }

        $matches = [];

        foreach ($this->stockImages as $image) {

            $stockImageMeanRGB = $image->getMeanRGB();
            
            $redDifference = abs($stockImageMeanRGB[0] - $meanRGB[0]);
            $greenDifference = abs($stockImageMeanRGB[1] - $meanRGB[1]);
            $blueDifference = abs($stockImageMeanRGB[2] - $meanRGB[2]);

            $totalDifference = $redDifference + $greenDifference + $blueDifference;

            $matches[$totalDifference] = $image;
        }

        ksort($matches);

        $key = array_key_first($matches);

        return $matches[$key];
    }
}