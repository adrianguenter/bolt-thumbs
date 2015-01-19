<?php
namespace Bolt\Thumbs\Transformer;

use Symfony\Component\HttpFoundation\File\File;
use Bolt\Thumbs\ImageResource;

class Crop implements TransformerInterface
{
    
    public $targetWidth;
    public $targetHeight;
    
    public function __construct($targetWidth, $targetHeight) 
    {
        $this->targetWidth = $targetWidth;
        $this->targetHeight = $targetHeight;
    }


    public function getName()
    {
        return "crop";
    }
    
    public function transform(ImageResource $image)
    {
        $ratio = max($this->targetWidth / $image->getWidth(), $this->targetHeight / $image->getHeight());


        $xratio = $image->getWidth() / $this->targetWidth;
        $yratio = $image->getHeight() / $this->targetHeight;

        // calculate x or y coordinate and width or height of source
        if ($xratio > $yratio) {
            $image->setX ( round(($image->getWidth() - ($image->getWidth() / $xratio * $yratio)) / 2) );
            $image->setWidth( round($image->getWidth() / $xratio * $yratio) );

        } elseif ($yratio > $xratio) {
            $image->setY( round(($image->getHeight() - ($image->getHeight() / $yratio * $xratio)) / 2) );
            $image->setHeight( round($image->getHeight() / $yratio * $xratio) );
        }
        
    }
    
    public function transformOutput(ImageResource $image, ImageResource $original) {
        
    }



}
