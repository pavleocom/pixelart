<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use PixelArt\Factory\PixelArtFactory;

/**
 * Directory containing original images that will be used to create stock. 
 * @var string $dir
 */
$dir = '/var/www/localhost/public_html/pixelart/example/images'; 

/**
 * The image that will be turned into photo mosaic. 200-300 x 200-300 pixels max for best results
 * @var string $imagePath
 */
$imagePath = '/var/www/localhost/public_html/pixelart/example/rock.jpg';

$settings = [
    'image' => $imagePath,
    'inputDir' => $dir
];

$pixelArt = PixelArtFactory::create($settings);

$schema = $pixelArt->buildSchema();
$outputDir = __DIR__;
$path = $pixelArt->buildPhotoMosaic($schema, $outputDir);

echo 'Your photo mosaic was saved here: ' . $path;