<?php

namespace Prooms\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Prooms\RoomsBundle\Entity\Room;
use Prooms\RoomsBundle\Entity\UserRoom;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"student" = "Student", "staff" = "Staff"})
 * @UniqueEntity(
 *      fields={"id"},
 *      message="Duplicate student id used."
 * )
 */
abstract class User implements UserInterface, \Serializable
{
     /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    private $id;
    
    /**
     * @ORM\Column(type="string", length=20) 
     */
    private $firstname;
    
    /**
     * @ORM\Column(type="string", length=20) 
     */
    private $lastname;
    
    /**
     * @ORM\Column(type="string", length=25, unique=true) 
     */
    private $username;
    
    /**
     * @ORM\Column(type="string", length=32) 
     */
    private $salt;
    
    /**
     * @ORM\Column(type="string", length=40) 
     */
    private $password;
    
    /**
     * @ORM\Column(type="string", length=10) 
     */
    private $title;
    
//    /**
//     * @ORM\OneToMany(targetEntity="Prooms\RoomsBundle\Entity\UserRoom", mappedBy="user",
//     *     cascade={"persist", "remove"})
//     */
//    private $userRooms;
    
    public function __construct() {
        $this->salt = md5(uniqid(null, true));
//        $this->userRooms = new ArrayCollection();
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
    }
    
    public function getTitle() {
        return $this->title;
    }
    
    public function setTitle($title) {
        $this->title = $title;
    }
    
    public function getFirstName() {
        return $this->firstname;
    }
    
    public function setFirstName($firstname) {
        $this->firstname = $firstname;
    }
    
    public function getLastName() {
        return $this->lastname;
    }
    
    public function setLastName($lastname) {
        $this->lastname = $lastname;
    }
    
    public function getUsername() {
        return $this->username;
    }
    
    public function setUsername($username) {
        $this->username = $username;
    }
    
    /**
     * @inheritDoc 
     */
    public function getSalt() {
        return $this->salt;
    }
    
    public function setPassword($password) {
        $this->password = $password;
    }
    
    /**
     * @inheritDoc 
     */
    public function getPassword() {
        return $this->password;
    }

    public function serialize() {
        return serialize(array(
            $this->id,
        ));
    }
    
    /**
     * @see \Serializable::unserialize() 
     */
    public function unserialize($serialized) {
        list (
            $this->id,
        ) = unserialize($serialized);
    }

    public function eraseCredentials() {
        
    }
    
    abstract function isStudent();
    
    abstract function isStaff();
    
    abstract function getRoles();
    
}