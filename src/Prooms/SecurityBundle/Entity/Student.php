<?php

namespace Prooms\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Prooms\RoomsBundle\Entity\Room;
use Prooms\RoomsBundle\Entity\UserRoom;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 */
class Student extends User
{ 
    public static $degrees = array(
            'B' => 'B',
            'BMVS'=>'BMUS',
            'MMUS'=>'MMUS',
            'DMA'=>'DMA',
            'DPST'=>'DPST',
            'DMPS'=>'DMPS'
    );
    
    public static $majors = array(
        'GMUS'=>'GMUS',
        'GSEM'=>'GSEM',
        'GSSM'=>'GSSM',
        'VOIC'=>'VOIC',
        'MUSS'=>'MUSS',
        'GUIT'=>'GUIT',
        'COMP'=>'COMP',
        'ORIN'=>'ORIN',
        'PIAN'=>'PIAN',
        'OPER'=>'OPER',
        'HARP'=>'HARP',
        'OPGN'=>'OPGN',
        'CHOR'=>'CHOR',
        'OPER'=>'OPER',
        'DMPS'=>'DMPS',
        'DPST'=>'DPST'
    );
    
    public static $instruments = array(
        'Bass trombone'=>'Bass trombone',
        'Bassoon'=>'Bassoon',
        'Cello'=>'Cello',
        'Clarinet'=>'Clarinet',
        'Comp'=>'Comp',
        'Dbl Bass'=>'Dbl Bass',
        'Ethno'=>'Ethno',
        'Euphonium'=>'Euphonium',
        'Flute'=>'Flute',
        'Guitar'=>'Guitar',
        'Harp'=>'Harp',
        'Harpsichord'=>'Harpsichord',
        'Horn'=>'Horn',
        'Musc'=>'Musc',
        'Oboe'=>'Oboe',
        'Orga'=>'Orga',
        'Percussion'=>'Percussion',
        'Piano'=>'Piano',
        'Saxophone'=>'Saxophone',
        'theory'=>'theory',
        'Trombone'=>'Trombone',
        'Trumpet'=>'Trumpet',
        'Tuba'=>'Tuba',
        'Viola'=>'Viola',
        'Violin'=>'Violin',
        'Voice'=>'Voice'
    );
    
    /**
     * @ORM\Column(type="integer") 
     */
    private $year;
    
    /**
     * @ORM\Column(type="integer") 
     */
    private $gpa;
    
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
     * @ORM\Column(name="con_instrument", type="string", length=30) 
     */
    private $conInstrument;
    
    /**
     * @ORM\Column(type="string", length=10) 
     */
    private $phone;
    
    /**
     * @ORM\Column(type="string", length=10) 
     */
    private $cell;
    
    /**
     * @ORM\Column(type="string", length=50) 
     */
    private $email;
    
    /**
     * @ORM\Column(name="address_1", type="string", length=50) 
     */
    private $address1;
    
    /**
     * @ORM\Column(name="address_2", type="string", length=50) 
     */
    private $address2;
    
    /**
     * @ORM\Column(type="string", length=15) 
     */
    private $city;
    
    /**
     * @ORM\Column(name="postal_code", type="string", length=15) 
     */
    private $postalCode;
    
    /**
     * @ORM\Column(type="string", length=30) 
     */
    private $country;
    
    /**
     * @ORM\Column(type="boolean")  
     */
    private $status = 0;
    
    /**
     * @ORM\Column(name="last_midified_by_student", type="datetime") 
     */
    private $lastModifiedByStudent;
    
    /**
     * @ORM\Column(name="last_reviewed_by_staff", type="datetime") 
     */
    private $lastReviewedByStaff;
    
    /**
     * @ORM\Column(name="permission_to_publish_personal_info", type="boolean") 
     */
    private $permissionToPublishPersonalInfo = false;
    
    private $discr = "student";
    
    /**
     * 
     * @ORM\OneToMany(targetEntity="Prooms\RoomsBundle\Entity\UserRoom", mappedBy="user",
     *     cascade={"persist", "remove"})
     */
    private $userRooms;
    
    public function __construct() {
        parent::__CONSTRUCT();
        $this->userRooms = new ArrayCollection();
        $this->setLastModifiedByStudent();
    }
    
    public function setGpa($gpa) {
        $this->gpa = $gpa;
    }
    
    public function getGpa() {
        return $this->gpa;
    }
    
    public function setDegree($degree) {
        $this->degree = $degree;
    }
    
    public function getDegree() {
        return $this->degree;
    }
    
    public function setMajor($major) {
        $this->major = $major;
    }
    
    public function getMajor() {
        return $this->major;
    }
    
    public function setYear($year) {
        $this->year = $year;
    }
    
    public function getYear() {
        return $this->year;
    }
    
    public function setInstrument($instrument) {
        $this->instrument = $instrument;
    }
    
    public function getInstrument() {
        return $this->instrument;
    }
    
    public function setConInstrument($conInstrument) {
        $this->conInstrument = $conInstrument;
    }
    
    public function getConInstrument() {
        return $this->conInstrument;
    }
    
    public function setPhone($phone) {
        $this->phone = $phone;
    }
    
    public function getPhone() {
        return $this->phone;
    }
    
    public function setCell($cell) {
        $this->cell = $cell;
    }
    
    public function getCell() {
        return $this->cell;
    }
    
    public function setEmail($email) {
        $this->email = $email;
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function setAddress1($address1) {
        $this->address1 = $address1;
    }
    
    public function getAddress1() {
        return $this->address1;
    }
    
    public function setAddress2($address2) {
        $this->address2 = $address2;
    }
    
    public function getAddress2() {
        return $this->address2;
    }
    
    public function setCity($city)
    {
        $this->city = $city;
    }
    public function getCity(){
        return $this->city;
    }
    
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }
    
    public function getPostalCode(){
        return $this->postalCode;
    }
    
    public function setCountry($country) {
        $this->country = $country;
    }
    
    public function getCountry() {
        return $this->country;
    }
    
    //status = 0 if student has not confirmed his personal information
    //status = 1 otherwise
    public function setStatus($status) {
        $this->status = $status;
    }
    
    public function getStatus () {
        return $this->status;
    }
    
    //update last modified by student to current
    public function setLastModifiedByStudent()
    {
        $this->lastModifiedByStudent = new \DateTime();
    }
    
    public function getLastModifiedByStudent(){
        return $this->lastModifiedByStudent;
    }
    
    //update last reviewed by staff to current
    public function setLastReviewedByStaff()
    {
        $this->lastReviewedByStaff = new \DateTime();
    }
    
    public function getLastReviewedByStaff(){
        return $this->lastReviewedByStaff;
    }
    
    public function setPermissionToPublishPersonalInfo($permission) {
        $this->permissionToPublishPersonalInfo = $permission;
    }
    
    public function getPermissionToPublishPersonalInfo() {
        return $this->permissionToPublishPersonalInfo;
    }
    
    public function getDiscr() {
        return $this->discr;
    }

    public function getRoles() {
        return array(
            'ROLE_STUDENT'
        );
    }
    
    public function getUserRooms() {
        if( ! $this->userRooms ) {
            $this->userRooms = new ArrayCollection();
        } 
        return $this->userRooms->toArray();
    }
    
    public function addUserRoom(UserRoom $userRoom) {
        if( ! $this->userRooms ) {
            $this->userRooms = new ArrayCollection();
        } 
        $this->userRooms->add($userRoom);
    }
    
    public function isStudent() {
        return true;
    }
    
    public function isStaff() {
        return false;
    }
    
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('gpa', new Assert\Max(array(
            'limit'   => '100',
            'message' => 'GPA must be 100(%) or smaller.',
        )));
        $metadata->addPropertyConstraint('gpa', new Assert\Min(array(
            'limit'   => '0',
            'message' => 'GPA must be 0(%) or higher.',
        )));
    }
}