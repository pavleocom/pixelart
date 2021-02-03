<?php

namespace PixelArt\Factory;

use PixelArt\Contracts\ImageFactoryInterface;
use PixelArt\Contracts\ImageInterface;
use PixelArt\Graphics\Image;

class ImageFactory implements ImageFactoryInterface
{
    private $stockImageSize = 50;

    public function create(string $file): ImageInterface
    {
        return new Image($file, $this->stockImageSize);
    }

    public function setStockImageSize(int $size)
    {
        $this->stockImageSize = $size;
        return $this;
    }
}