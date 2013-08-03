<?php

namespace Prooms\RoomsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Prooms\SecurityBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="rooms")
 * @UniqueEntity(
 *      fields={"id"},
 *      message="Duplicate room id used."
 * )
 */
class Room
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="piano_type", type="string", length=30)
     */
    private $pianoType;
    
    /**
     * @ORM\Column(name="piano_detail", type="string", length=50)
     */
    private $pianoDetail;
    
    /**
     * @ORM\Column(name="max_people_allowed", type="integer")
     */
    private $maxPeopleAllowed;
    
    
    /**
     * @ORM\Column(type="string", length=100)
     */
    private $description;
    
    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $image;
    
    /**
     * @Assert\File(maxSize="600000")
     */
    private $file;
    
    private $temp;
    
    /**
     * @ORM\OneToMany(targetEntity="RoomUnavailableHours", mappedBy="room",
     *     cascade={"persist", "remove"})
     */
    private $unavailableHours;
    
    /**
     * @ORM\OneToMany(targetEntity="RoomConstraint", mappedBy="room",
     *     cascade={"persist", "remove"})
     */
    private $constraints;
    
    /**
     * @ORM\OneToMany(targetEntity="UserRoom", mappedBy="room",
     *     cascade={"persist", "remove"})
     */
    private $userRooms;
    
    public function __construct() {
        $this->unavailableHours = new ArrayCollection();
        $this->userRooms = new ArrayCollection();
        $this->constraints = new ArrayCollection;
    }
    
    public function setId($id) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }
    
    public function setPianoType($type = Null) {
        return $this->pianoType = $type;
    }
    
    public function getPianoType() {
        return $this->pianoType;
    } 
    
    public function getPianoDetail() {
        return $this->pianoDetail;
    }
    
    public function setPianoDetail($pianoDetail = NULL) {
        $this->pianoDetail = $pianoDetail;
    }
    
    public function getMaxPeopleAllowed() {
        return $this->maxPeopleAllowed;
    }
    
    public function setMaxPeopleAllowed($maxPeopleAllowed) {
        $this->maxPeopleAllowed = $maxPeopleAllowed;
    }
    
    public function setDescription($description) {
        $this->description = $description;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function getAbsolutePath()
    {
        return null === $this->image
            ? null
            : $this->getUploadRootDir().'/'.$this->image;
    }

    public function getWebPath()
    {
        return null === $this->image
            ? null
            : $this->getUploadDir().'/'.$this->image;
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
        return 'images';
    }
    
    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        // check if we have an old image path
        if (isset($this->image)) {
            // store the old name to delete after the update
            $this->temp = $this->image;
            $this->image = null;
        } else {
            $this->image = 'initial';
        }
    }

    public function preUpload()
    {
        if (null !== $this->getFile()) {
            // generate a unique name
            $filename = $this->getId() . "_" . $this->getFile()->getClientOriginalName();
            $this->image = $filename;//.'.'.$this->getFile()->guessExtension();
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
        $this->getFile()->move($this->getUploadRootDir(), $this->image);

        // check if we have an old image
        if (isset($this->temp)) {
            // delete the old image
            unlink($this->getUploadRootDir().'/'.$this->temp);
            // clear the temp image path
            $this->temp = null;
        }
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
    
    public function getUnavailableHours() {
        return $this->unavailableHours->toArray();
    }
    
    public function addUnavailableHours(RoomUnavailableHours $unavailableHours) {
        $this->unavailableHours->add($unavailableHours);
    }
    
    public function getUserRooms() {
        return $this->userRooms->toArray();
    }
    
    public function addUserRoom(UserRoom $userRoom) {
        $this->userRooms->add($userRoom);
    }
    
    public function getConstraints() {
        return $this->constraints->toArray();
    }
    
    public function addConstraint(RoomConstraint $constraint) {
        $this->constraints->add($constraint);
    }
}