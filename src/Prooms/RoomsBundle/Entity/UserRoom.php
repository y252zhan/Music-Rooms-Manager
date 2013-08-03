<?php

namespace Prooms\RoomsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Prooms\SecurityBundle\Entity\User;

/**
 * @ORM\Entity
 * @ORM\Table(name="users_rooms")
 * @ORM\Entity(repositoryClass="Prooms\RoomsBundle\Entity\UserRoomRepository")
 */
class UserRoom
{
    /**
     * @ORM\ManyToOne(targetEntity="Prooms\SecurityBundle\Entity\Student", inversedBy="userRooms")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Room", inversedBy="userRooms")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     */
    private $room;
    
    /**
     * @ORM\Id
     * @ORM\Column(name="day_of_week", type="integer")
     */
    private $dayOfWeek;
    
    /**
     * @ORM\Id
     * @ORM\Column(name="start_time", type="integer")
     */
    private $startTime;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $term;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $duration;
    
    public function getUser() {
        return $this->user;
    }
    
    public function setUser(User $user) {
        $this->user = $user;
    }
    
    public function getRoom() {
        return $this->room;
    }

    public function setRoom(Room $room) {
        $this->room = $room;
    }
    
    public function getDayOfWeek() {
        return $this->dayOfWeek;
    }
    
    public function setDayOfWeek($dayOdWeek) {
        $this->dayOfWeek = $dayOdWeek;
    }
    
    public function getStartTime() {
        return $this->startTime;
    }
    
    public function setStartTime($startTime) {
        $this->startTime = $startTime;
    }
    
    public function getTerm() {
        return $this->term;
    }
    
    public function setTerm($term) {
        $this->term = $term;
    }
    
    public function getDuration() {
        return $this->duration;
    }
    
    public function setDuration($duration) {
        $this->duration = $duration;
    }
}
