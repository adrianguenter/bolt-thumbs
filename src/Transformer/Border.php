<?php
namespace Bolt\Thumbs\Transformer;

use Symfony\Component\HttpFoundation\File\File;
use Bolt\Thumbs\ImageResource;

class Border implements TransformerInterface
{
    
    public $targetWidth;
    public $targetHeight;
    
    public $canvas  = array(255, 255, 255);
    
    public function __construct($targetWidth, $targetHeight) 
    {
        $this->targetWidth = $targetWidth;
        $this->targetHeight = $targetHeight;
    }


    public function getName()
    {
        return "border";
    }
    
    public function transform(ImageResource $image)
    {
        if (count($this->canvas) == 3) {
            $canvas = imagecolorallocate($image->getResource(), $this->canvas[0], $this->canvas[1], $this->canvas[2]);
            imagefill($image->getResource(), 0, 0, $canvas);
        }

        $tmpheight = $image->getHeight() * ($this->targetWidth / $image->getWidth());
        if ($tmpheight > $this->targetHeight) {
            $width = $image->getWidth() * ($this->targetHeight / $image->getHeight());
            $image->setX( round(($this->targetWidth - $image->getWidth()) / 2) );
        } else {
            $height = $tmpheight;
            $image->setY(round(($this->targetHeight - $image->getHeight()) / 2));
        }
        
    }
    
    public function transformOutput(ImageResource $image, ImageResource $original) {
        
    }




}
