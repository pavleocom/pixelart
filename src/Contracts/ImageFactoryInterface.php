<?php

namespace PixelArt\Contracts;

use PixelArt\Contracts\ImageInterface;

interface ImageFactoryInterface
{
    public function create(string $file): ImageInterface;
    public function setStockImageSize(int $size);
}
