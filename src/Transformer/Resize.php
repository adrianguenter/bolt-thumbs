<?php
namespace Bolt\Thumbs\Transformer;

use Symfony\Component\HttpFoundation\File\File;
use Bolt\Thumbs\ImageResource;

class Resize implements TransformerInterface
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
        return "resize";
    }
    
    public function transform(ImageResource $image)
    {
        $ratio = min($this->targetWidth / $image->getWidth(), $this->targetHeight / $image->getHeight());
        $image->setWidth($image->getWidth() * $ratio);
        $image->setHeight($image->getHeight() * $ratio);
        
    }
    
    public function transformOutput(ImageResource $image, ImageResource $original) {
        
    }



}
