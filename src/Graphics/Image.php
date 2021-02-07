<?php

namespace PixelArt\Graphics;

use Exception;
use PixelArt\Contracts\ImageInterface;

class Image implements ImageInterface
{
    /**
     * Filepath of the image
     * @var string $file
     */
    private $file;

    /**
     * Stock image size in pixels (square)
     * @var int $stockImageSize
     */
    private $stockImageSize;

    /**
     * Mean RGB of the image
     * @var array [<int>R, <int>G, <int>B]
     */
    private $meanRGB = null;

    /**
     * Image resource
     * @var resource
     */
    private $imageResource = null;

    public function __construct(string $file, int $stockImageSize = 50)
    {
        $this->file = $file;
        $this->stockImageSize = $stockImageSize;

        if (!is_readable($this->file)) {
            throw new Exception('File not found or is not accessible.');
        }

        $this->setImageSize();
    }

    /**
     * Returns mean RGB of the image
     * @return array $meanRGB [<int>R, <int>G, <int>B]
     */
    public function getMeanRGB(): array
    {
        if ($this->meanRGB === null) {
            $this->meanRGB = $this->calculateMeanRGB();
        }

        return $this->meanRGB;
    }

    /**
     * Sets mean RGB of the image
     * @param array $meanRGB [<int>R, <int>G, <int>B]
     * @return self $this
     */
    public function setMeanRGB(array $meanRGB)
    {
        $this->meanRGB = $meanRGB;
        return $this;
    }

    /**
     * Calculates mean RGB of the entire image
     * @return array [<int>R, <int>G, <int>B]
     */
    private function calculateMeanRGB()
    {
        $rgbs = $this->getRGBs();
        $reds = 0;
        $greens = 0;
        $blues = 0;
        $pixels = count($rgbs);
        
        foreach ($rgbs as $rgb) {
            $reds +=$rgb[0];
            $greens += $rgb[1];
            $blues += $rgb[2];
        }

        $meanRed = (int) ($reds / $pixels);
        $meanGreen = (int) ($greens / $pixels);
        $meanBlue = (int) ($blues / $pixels);

        return [$meanRed, $meanGreen, $meanBlue];

    }

    /**
     * Returns array where pixel position is key and value is RGB array, e.g. [ '1,1' => [255, 44, 65], ... ]
     * @return array (RGB for each pixel)
     */
    public function getRGBs()
    {
        $RGBs = [];
        $width = $this->getWidth();
        $height = $this->getHeight();
        $imageResource = $this->getImageResource();

        for ($y = 0; $y < $height; $y++) {

            for ($x = 0; $x < $width; $x++) {

                $pixelKey = $x . ',' . $y;
                $index = imagecolorat($imageResource, $x, $y);
                $colours = imagecolorsforindex($imageResource, $index);
                $RGBs[$pixelKey] = [$colours['red'], $colours['green'], $colours['blue']];

            }

        }

        imagedestroy($imageResource);
        $this->imageResource = null;
        return $RGBs;
    }

    /**
     * Creates a stock image. Crops and resizes the original image resource.
     * @param string $outputDir
     * @return string $stockImagePath
     */
    public function createStockImage(string $outputDir): string
    {
        $imageResource = $this->getImageResource();

        $size = min($this->getWidth(), $this->getHeight());

        $center_x = ceil(($this->getWidth() / 2));
        $center_y = ceil(($this->getHeight() / 2));

        $start_x = $center_x - (floor($size / 2));
        $start_y = $center_y - (floor($size / 2));

        $croppedImageResource = imagecrop($imageResource, [
            'x' => $start_x,
            'y' => $start_y,
            'width' => $size,
            'height' => $size
        ]);

        $stockImagePath = $outputDir . DIRECTORY_SEPARATOR . basename($this->file);
        $stockImageResource  = imagecreatetruecolor($this->stockImageSize, $this->stockImageSize);

        imagecopyresampled($stockImageResource, $croppedImageResource, 0, 0, 0, 0, $this->stockImageSize, $this->stockImageSize, $size, $size);

        imagejpeg($stockImageResource, $stockImagePath);
        chmod($stockImagePath, 0775);

        imagedestroy($croppedImageResource);
        imagedestroy($stockImageResource);
        imagedestroy($imageResource);
        $this->imageResource = null;

        return $stockImagePath;
    }

    /**
     * Returns width of the image
     * @return int $this->width
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Returns height of the image
     * @return int $this->height
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Sets width and height from image resourse
     * @param string $this->file
     */
    private function setImageSize()
    {
        list($this->width, $this->height) = getimagesize($this->file);
    }

    /**
     * Returns image resource
     * @return resource $this->imageResource
     */
    public function getImageResource()
    {
        if ($this->imageResource === null) {

            $mime = mime_content_type($this->file);

            if ($mime === 'image/jpeg') {
                $this->imageResource = imagecreatefromjpeg($this->file);
            } else if ($mime === 'image/png') {
                $this->imageResource = imagecreatefrompng($this->file);
            } else if ($mime === 'image/gif') {
                $this->imageResource = imagecreatefromgif($this->file);
            } else if ($mime === 'image/bmp') {
                $this->imageResource = imagecreatefrombmp($this->file);
            } else if ($mime === 'image/webp') {
                $this->imageResource = imagecreatefromwebp($this->file);
            } else {
                throw new Exception('Unsupported image: ' . $this->file);
            }
        }

        return $this->imageResource;
 
    }

    /**
     * Builds photo mosaic
     * @param array $schema - array containing pixels and image objects: ['0,0' => ImageInterface, ...]
     */
    public static function createFromSchema(array $schema, string $outputDir)
    {
        $firstKey = array_key_first($schema);
        $stockImageSize = imagesx($schema[$firstKey]->getImageResource());

        $canvasWidth = self::getWidthFromSchema($schema) * $stockImageSize;
        $canvasHeight = self::getHeightFromSchema($schema) * $stockImageSize;

        $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);

        foreach ($schema as $pixel => $image) {
            $parts = explode(',', $pixel);
            $x = (intval($parts[0])) * $stockImageSize;
            $y = (intval($parts[1])) * $stockImageSize;

            imagecopy($canvas, $image->getImageResource(), $x, $y, 0, 0, $stockImageSize, $stockImageSize);
        }

        $filepath = rtrim($outputDir, '\\/') . DIRECTORY_SEPARATOR . 'pixelart_' . time() . '.jpg';
        imagejpeg($canvas, $filepath);

        return $filepath;
    }

    /**
     * Returns width determined from schema array.
     * @param array $schema
     * @return int $width
     */
    private static function getWidthFromSchema($schema)
    {
        $width = 0;

        foreach ($schema as $pixel => $rgb)
        {
            $parts = explode(',', $pixel);

            $pixelXPosition = (int) $parts[0];

            if ( $width <= $pixelXPosition) {
                $width = $pixelXPosition;
            }

        }

        return $width + 1;
    }

    /**
     * Return height determined from schema array.
     * @param array $schema
     * @return int $height
     */
    private static function getHeightFromSchema($schema)
    {
        $height = 0;

        foreach ($schema as $pixel => $rgb)
        {
            $parts = explode(',', $pixel);

            $pixelYPosition = (int) $parts[1];

            if ( $height <= $pixelYPosition) {
                $height = $pixelYPosition;
            }

            if ($pixelYPosition < $height) {
                return $height + 1;
            }
        }

        return $height + 1;
    }
}
