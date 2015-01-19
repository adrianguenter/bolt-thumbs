<?php
namespace Bolt\Thumbs;

use Symfony\Component\HttpFoundation\File\File;

class ThumbnailCreator implements ResizeInterface
{
    public $source;
    public $defaultSource;
    public $errorSource;
    public $allowUpscale = false;
    public $quality = 80;
    public $canvas  = array(255, 255, 255);
    public $transformers = array(
        'input'=>array(),
        'output' => array()
    );

    public $targetWidth;
    public $targetHeight;

    public function provides()
    {
        return array(
            'c' => 'crop',
            'r' => 'resize',
            'b' => 'border',
            'f' => 'fit'
        );

    }

    public function setSource(File $source)
    {
        $this->source = $source;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setDefaultSource(File $source)
    {
        $this->defaultSource = $source;
    }

    public function setErrorSource(File $source)
    {
        $this->errorSource = $source;
    }

    /**
     *  This method performs the basic sanity checks before allowing the operation to continue.
     *  If there are any problems with the request it can also reset the source to be one of the
     *  configured fallback images.
     **/
    public function verify($parameters = array())
    {
        if (!$this->source->isReadable() && $this->defaultSource) {
            $this->source = $this->defaultSource;
        }

        // Get the original dimensions of the image
        $imageMetrics = @getimagesize($this->source->getRealPath());

        if (!$imageMetrics) {
            $this->source = $this->errorSource;
            $imageMetrics = @getimagesize($this->source->getRealPath());
            if (!$imageMetrics) {
                throw new \RuntimeException(
                    'There was an error with the thumbnail image requested and additionally the fallback image could not be displayed.',
                    1
                );
            }
        }
        $this->originalWidth = $imageMetrics[0];
        $this->originalHeight = $imageMetrics[1];

        // Set target dimensions to sanitized values
        if (isset($parameters['width']) && preg_match('%^\d+$%', $parameters['width'])) {
            $this->targetWidth = $parameters['width'];
        } else {
            $this->targetWidth = $this->originalWidth;
        }

        if (isset($parameters['height']) && preg_match('%^\d+$%', $parameters['height'])) {
            $this->targetHeight = $parameters['height'];
        } else {
            $this->targetHeight = $this->originalHeight;
        }

        // Autoscaling
        if ($this->targetWidth == 0 and $this->targetHeight == 0) {
            $this->targetWidth = $this->originalWidth;
            $this->targetHeight = $this->originalHeight;
        } elseif ($this->targetWidth == 0) {
            $this->targetWidth = round($this->targetHeight * $this->originalWidth / $this->originalHeight);
        } elseif ($this->targetHeight == 0) {
            $this->targetHeight = round($this->targetWidth * $this->originalHeight / $this->originalWidth);
        }

        // Check for upscale
        if (!$this->allowUpscale) {
            if ($this->targetWidth > $this->originalWidth) {
                $this->targetWidth = $this->originalWidth;
            }
            if ($this->targetHeight > $this->originalHeight) {
                $this->targetHeight = $this->originalHeight;
            }
        }
    }

    public function resize($parameters = array())
    {
        $this->verify($parameters);
        $data = $this->doResize($this->source->getRealPath(), $this->targetWidth, $this->targetHeight, false);
        if (false !== $data) {
            return $data;
        }
    }

    public function crop($parameters = array())
    {
        $this->verify($parameters);
        $data = $this->doResize($this->source->getRealPath(), $this->targetWidth, $this->targetHeight, true);
        if (false !== $data) {
            return $data;
        }

    }

    public function border($parameters = array())
    {
        $this->verify($parameters);
        $data = $this->doResize($this->source->getRealPath(), $this->targetWidth, $this->targetHeight, false, false, true);
        if (false !== $data) {
            return $data;
        }
    }

    public function fit($parameters = array())
    {
        $this->verify($parameters);
        $data = $this->doResize($this->source->getRealPath(), $this->targetWidth, $this->targetHeight, false, true);
        if (false !== $data) {
            return $data;
        }
    }
    
    /**
     * undocumented function
     *
     * @return void
     * @author 
     **/
    public function registerTransformer(Transformer\TransformerInterface $transformer, $type = 'input')
    {
        $this->transformers[$type][$transformer->getName()] = $transformer;   
    }
    

    /**
     * Main resizing function.
     *
     * Since the resizing functionality is almost identical across all actions they all delegate here.
     * Main difference is in plotting the output dimensions where the ratios and position differ slightly.
     *
     * @return $imageData
     **/
    protected function doResize($src, $width, $height, $crop = false, $fit = false, $border = false)
    {
        $image = new ImageResource($src);
        
        if ($crop) {
            $this->registerTransformer(new Transformer\Crop($width, $height));
        } elseif (!$border && !$fit) {
            $this->registerTransformer(new Transformer\Resize($width, $height));
        
        }
        
        foreach($this->transformers['input'] as $transformer) {
            $transformer->transform($image);
        }
        

        $new = new ImageResource(imagecreatetruecolor($width, $height));
        
        if ($border) {
            $this->registerTransformer(new Transformer\Border($width, $height), 'output');
        }
        
        foreach($this->transformers['output'] as $transformer) {
            $transformer->transformOutput($new, $image); 
        }


        return $this->getOutput($new->getResource(), $image->getType());
    }

    /**
     * undocumented function
     *
     * @param $imageContent an image resource
     * @param $type one of bmp|gif|jpg|png
     * @return $imageData | false
     **/
    protected function getOutput($imageContent, $type)
    {
        // This block captures the image data, since these image commands echo out the data
        // we wrap the operation in output buffering to capture the data as a string.
        ob_start();
        switch($type) {
            case 'bmp':
                imagewbmp($imageContent);
                break;
            case 'gif':
                imagegif($imageContent);
                break;
            case 'jpg':
                imagejpeg($imageContent, null, $this->quality);
                break;
            case 'png':
                imagepng($imageContent);
                break;
        }
        $imageData = ob_get_contents();
        ob_end_clean();

        if ($imageData) {
            return $imageData;
        }

        return false;
    }

}
