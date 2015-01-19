<?php
namespace Bolt\Thumbs\Transformer;

use Symfony\Component\HttpFoundation\File\File;
use Bolt\Thumbs\ImageResource;

class Resample implements OutputTransformerInterface
{

    public function getName()
    {
        return "resample";
    }
    
    public function transform(ImageResource $image) 
    {
        
    }
    
    public function transformOutput(ImageResource $image, ImageResource $original)
    {
        
        imagecolortransparent($image->getResource(), imagecolorallocatealpha($image->getResource(), 0, 0, 0, 127));
        imagealphablending($image->getResource(), false);
        imagesavealpha($image->getResource(), true);

        imagecopyresampled(
            $image->getResource(), 
            $original->getResource(), 
            $image->getX(), 
            $image->getY(), 
            $original->getX(), 
            $original->getY(), 
            $image->getWidth(), 
            $image->getHeight(),
            $original->getWidth(), 
            $original->getHeight()
            
        );
        
    }



}
