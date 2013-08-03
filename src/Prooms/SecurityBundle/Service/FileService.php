<?php

namespace Prooms\SecurityBundle\Service;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileService
{
    private $name;
    
    /**
     * @Assert\File(maxSize="600000")
     */
    private $file;
    
    public function getAbsolutePath()
    {
        return null === $this->name
            ? null
            : $this->getUploadRootDir().'/'.$this->name;
    }

    public function getWebPath()
    {
        return null === $this->name
            ? null
            : $this->getUploadDir().'/'.$this->name;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'files';
    }
    
    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    public function preUpload()
    {
        if (null !== $this->getFile()) {
            // generate a unique name
            $filename = $this->getFile()->getClientOriginalName();
            $this->name = $filename;//.'.'.$this->getFile()->guessExtension();
        }
    }

    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFile()->move($this->getUploadRootDir(), $this->name);

        $this->file = null;
    }

    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }
    
    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }
}
