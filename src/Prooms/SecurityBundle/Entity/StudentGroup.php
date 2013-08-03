<?php

namespace Prooms\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Prooms\RoomsBundle\Entity\Room;
use Prooms\RoomsBundle\Entity\UserRoom;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="student_groups")
 * @ORM\Entity(repositoryClass="Prooms\SecurityBundle\Entity\StudentGroupRepository")
 * @UniqueEntity(
 *     fields={"year", "degree", "major", "instrument", "gpaFloor"},
 *     message="Duplicate student group exist in your uploaded csv file."
 * )
 */
class StudentGroup
{   
    /**
     * @ORM\Column(type="integer") 
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\Column(type="integer") 
     */
    private $year;
    
    /**
     * @ORM\Column(type="string", length=30) 
     */
    private $degree;
    
    /**
     * @ORM\Column(type="string", length=30) 
     */
    private $major;
    
    /**
     * @ORM\Column(type="string", length=30) 
     */
    private $instrument;
    
    /**
     * @ORM\Column(name="gpa_floor", type="integer") 
     */
    private $gpaFloor;
    
    /**
     * @ORM\Column(name="max_hours", type="integer") 
     */
    private $maxHours;
    
    /**
     * @ORM\Column(name="opening_datetime", type="datetime") 
     */
    private $openingDatetime;
    
    public static function getAllDegrees() {
        return array(
            'bmvs' => 'BMVS',
            'mmus' => 'MMUS',
            'dma' => 'DMA',
            'dpst' => 'DPST',
            'dmps' => 'DMPS'
        );
    }
    
    public static function getAllMajors() {
        return array(
            'gmus'=>'GMUS',
            'gsem'=>'GSEM',
            'gssm'=>'GSSM',
            'voic'=>'VOIC',
            'muss'=>'MUSS',
            'guit'=>'GUIT',
            'comp'=>'COMP',
            'orin'=>'ORIN',
            'pian'=>'PIAN',
            'oper'=>'OPER',
            'harp'=>'HARP',
            'opgn'=>'OPGN',
            'chor'=>'CHOR',
            'oper'=>'OPER',
        ) ;
    }

    public function getId() {
        return $this->id;
    }
    
    public function getDegree() {
        return $this->degree;
    }
    
    public function setDegree($degree) {
        $this->degree = $degree;
    }
    
    public function getMajor() {
        return $this->major;
    }
    
    public function setMajor($major = NULL) {
        $this->major = $major;
    }
    
    public function getYear() {
        return $this->year;
    }
    
    public function setYear($year = NULL) {
        $this->year = $year;
    }
    
    public function setInstrument($instrument) {
        $this->instrument = $instrument;
    }
    
    public function getInstrument() {
        return $this->instrument;
    }
    
    public function getGpaFloor() {
        return $this->gpaFloor;
    }
    
    public function setGpaFloor($gpa) {
        $this->gpaFloor = $gpa;
    }
    
    public function getMaxHours() {
        return $this->maxHours;
    }
    
    public function setMaxHours($maxHours) {
        $this->maxHours = $maxHours;
    }
    
    public function getOpeningDatetime() {
        return $this->openingDatetime;
    }
    
    public function setOpeningDatetime($dateTime) {
        $this->openingDatetime = $dateTime;
    }
    
    public function isDefault() {
        return ! ($this->degree || $this->major || $this->year || $this->instrument || $this->gpaFloor);
    }
    
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('gpaFloor', new Assert\Max(array(
            'limit'   => '100',
            'message' => 'GPA floor must be 100(%) or smaller.',
        )));
        $metadata->addPropertyConstraint('gpaFloor', new Assert\Min(array(
            'limit'   => '0',
            'message' => 'GPA floor must be 0(%) or higher.',
        )));
        $metadata->addPropertyConstraint('maxHours', new Assert\Min(array(
            'limit'   => '0',
            'message' => 'GPA floor must be 0 or higher.',
        )));
    }
    
}
