# PixelArt

Create beautiful photo mosaics out of your own pictures.

![PixelArt Demo](example/the-rock-result.jpg)

## Usage

```php
require_once '/vendor/autoload.php';

use PixelArt\Factory\ImageFactory;
use PixelArt\Factory\PixelArtFactory;
use PixelArt\Graphics\Stock;
use PixelArt\PixelArt;

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

$imageFactory = new ImageFactory();
$stock = new Stock($imageFactory);

$stockFile = $stock->createStock($dir); //can be skipped if existing stock used
$stock->setStockImageSize(50)->load($stockFile); // can load stock from previous runs

$pixelArt = new PixelArt($stock, $imageFactory->create($imagePath));

$schema = $pixelArt->buildSchema();

$outputDir = __DIR__;
$path = $pixelArt->buildPhotoMosaic($schema, $outputDir);

echo 'Your photo mosaic was saved here: ' . $path;
```

### With Factory
```php
require_once '/vendor/autoload.php';

use PixelArt\Factory\PixelArtFactory;

$dir = '/var/www/localhost/public_html/pixelart/example/images';
$imagePath = '/var/www/localhost/public_html/pixelart/example/rock.jpg';

$settings = [
    'image' => $imagePath,
    'inputDir' => $dir
];

$pixelArt = PixelArtFactory::create($settings);

$schema = $pixelArt->buildSchema();
$path = $pixelArt->buildPhotoMosaic($schema, __DIR__);

echo 'Your photo mosaic was saved here: ' . $path;
```
