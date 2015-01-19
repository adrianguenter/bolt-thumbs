<?php
namespace Bolt\Thumbs\Transformer;

use Symfony\Component\HttpFoundation\File\File;
use Bolt\Thumbs\ImageResource;

class Exif implements TransformerInterface
{


    public function getName()
    {
        return "exif";
    }
    
    public function transform(ImageResource $image)
    {

         // Handle exif orientation
        if ($image->getType() === 'jpg' && function_exists('exif_read_data')) {
            $exif = exif_read_data($image->getFile());
        } else {
            $exif = false;
        }
        $modes = array(2 => 'H-', 3 => '-T', 4 => 'V-', 5 => 'VL', 6 => '-L', 7 => 'HL', 8 => '-R');
        $orientation = isset($exif['Orientation']) ? $exif['Orientation'] : 0;
        if (isset($modes[$orientation])) {
            $mode = $modes[$orientation];
            $img = $image->getResource();
            $img = $this->imageFlipRotate($img, $mode[0], $mode[1]);
            $image->setWidth(imagesx($img));
            $image->setHeight(imagesy($img));
            $image->setResource($img);
        }
    }
    
    public function transformOutput(ImageResource $image, ImageResource $original) {
        
    }


    /**
     * Image flip and rotate
     *
     * Based on http://stackoverflow.com/a/10001884/1136593
     * Thanks Jon Grant
     *
     * @param $img (image to flip and/or rotate)
     * @param $mode ('V' = vertical, 'H' = horizontal, 'HV' = both)
     * @param $angle ('L' = -90°, 'R' = +90°, 'T' = 180°)
     *
     */
    public function imageFlipRotate($img, $mode, $angle)
    {
        // Flip the image
        if ($mode === 'V' || $mode === 'H' || $mode === 'HV') {
            $width = imagesx($img);
            $height = imagesy($img);

            $srcX = 0;
            $srcY = 0;
            $srcWidth = $width;
            $srcHeight = $height;

            switch ($mode) {
                case 'V': // Vertical
                    $srcY = $height - 1;
                    $srcHeight = -$height;
                    break;
                case 'H': // Horizontal
                    $srcX = $width - 1;
                    $srcWidth = -$width;
                    break;
                case 'HV': // Both
                    $srcX = $width - 1;
                    $srcY = $height - 1;
                    $srcWidth = -$width;
                    $srcHeight = -$height;
                    break;
            }

            $imgdest = imagecreatetruecolor($width, $height);

            if (imagecopyresampled($imgdest, $img, 0, 0, $srcX, $srcY, $width, $height, $srcWidth, $srcHeight)) {
                $img = $imgdest;
            }
        }

        // Rotate the image
        if ($angle === 'L' || $angle === 'R' || $angle === 'T') {
            $rotate = array('L' => 270, 'R' => 90, 'T' => 180);
            $img = imagerotate($img, $rotate[$angle], 0);
        }

        return $img;
    }
    

}
