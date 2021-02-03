<?php

namespace PixelArt;

use PixelArt\Graphics\Image;
use PixelArt\Graphics\Stock;
use PixelArt\Contracts\ImageInterface;

class PixelArt
{
    /**
     * Stock of processed images that will be used to create photo mosaic.
     * @var Stock $stock
     */
    private $stock;

    /**
     * The base image that will be turned into a photo mosaic.
     * @var Image $image
     */
    private $image;

    public function __construct(Stock $stock, ImageInterface $image)
    {
        $this->stock = $stock;
        $this->image = $image;
    }

    /**
     * Builds schema ['x,y' => ImageInterface, ...]
     * @return array $schema
     */
    public function buildSchema()
    {
        $schema = [];
        $pixelsRGBs = $this->image->getRGBs();

        foreach ($pixelsRGBs as $pixelKey => $pixelRGB) {
            $matchedImage = $this->stock->getBestMatch($pixelRGB);
            $schema[$pixelKey] = $matchedImage;
        }

        return $schema;
    }

    /**
     * Builds photo mosaic
     * @param array $schema
     * @param string $outputDir
     * @return string path of the newly built photo mosaic
     */
    public function buildPhotoMosaic(array $schema, string $outputDir)
    {
        return $this->image->createFromSchema($schema, $outputDir);
    }
}