<?php

namespace Bolt\Thumbs;

/**
 * Wrapper class to represent GD's native image resources
 *
 **/
class ImageResource
{
    protected $file;

    protected $resource;    
    
    protected $width = 0;
    
    protected $height = 0;
    
    protected $x = 0;
    
    protected $y = 0;
    
    protected $type;
    
    public function __construct($file)
    {
        $this->file = $file;
        
        if( is_string($file) && is_file($file)) {
            $this->createFromFile($file);
        }
        
        if (is_resource($file) && get_resource_type($file) === 'gd' ) {
            $this->setResource($file);
        }
        
    }
    
    public function createFromFile($file)
    {
        if (!list($w, $h) = getimagesize($file)) {
            throw new \InvalidArgumentException("Filename must point to a valid image", 1);  
        }

        $type = strtolower(substr(strrchr($file, '.'), 1));
        if ($type == 'jpeg') {
            $type = 'jpg';
        }
        
        switch($type)
        {
            case 'bmp':
                $img = imagecreatefromwbmp($file);
                break;
            case 'gif':
                $img = imagecreatefromgif($file);
                break;
            case 'jpg':
                $img = imagecreatefromjpeg($file);
                break;
            case 'png':
                $img = imagecreatefrompng($file);
                break;
            default:
                throw new \InvalidArgumentException("Cannot handle this image type", 1); 
        }
        $this->setType($type);
        $this->setResource($img);
        $this->setWidth($w);
        $this->setHeight($h);
    }
    
    public function setResource($resource)
    {
        $type = get_resource_type($resource);
        
        if ($type !== 'gd') {
            throw new \InvalidArgumentException("Argument must be a valid GD image resource", 1);  
        }
        
        $this->resource = $resource;
    }
    
    public function getResource()
    {
        return $this->resource;
    }
    
    public function getFile()
    {
        return $this->file;
    }
    
    public function setWidth($width)
    {
        $this->width = $width;
    }
    
    public function getWidth()
    {
        return $this->width;
    }
    
    public function setHeight($height)
    {
        $this->height = $height;
    }
    
    public function getHeight()
    {
        return $this->height;
    }
    
    
    public function setType($type)
    {
        $this->type = $type;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setX($x)
    {
        $this->x = $x;
    }
    
    public function getX()
    {
        return $this->x;
    }
    
    public function setY($y)
    {
        $this->y = $y;
    }
    
    public function getY()
    {
        return $this->y;
    }


    

} 