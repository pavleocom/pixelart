<?php

namespace PixelArt\Factory;

use PixelArt\PixelArt;
use PixelArt\Graphics\Stock;

class PixelArtFactory
{
    public static function create(array $settings)
    {
        $imageFactory = new ImageFactory();
        $stock = new Stock($imageFactory);
        $stockFile = $stock->setStockImageSize(50)->createStock($settings['inputDir']);
        $stock->load($stockFile);
        return new PixelArt($stock, $imageFactory->create($settings['image']));
    }

}