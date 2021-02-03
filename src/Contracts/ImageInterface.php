<?php

namespace PixelArt\Contracts;

interface ImageInterface
{
    public function createStockImage(string $outputDir): string;
    public function setMeanRGB(array $meanRGB);
    public function getMeanRGB(): array;
}