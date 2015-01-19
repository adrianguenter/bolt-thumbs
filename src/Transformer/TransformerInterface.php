<?php
namespace Bolt\Thumbs\Transformer;

use Bolt\Thumbs\ImageResource;


/**
 * Transformer Interface, allows pre-processing on thumbnails before resize occurs.
 *
 * @author 
 **/

interface TransformerInterface
{

    /**
     * Returns the name of the transformer
     *
     * @return string
     **/
    public function getName();
    
     /**
     * Receives an image resource by reference, handles any transformation
     *
     * @return void
     **/
    public function transform(ImageResource $image);
    
     /**
     * Receives both the original and new output, handles any transformation before output.
     *
     * @return void
     **/
    public function transformOutput(ImageResource $image, ImageResource $original);

}
