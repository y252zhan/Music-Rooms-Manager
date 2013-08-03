<?php
namespace Prooms\RoomsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rooms_unavailable_hours")
 * @ORM\Entity(repositoryClass="Prooms\RoomsBundle\Entity\RoomUnavailableHoursRepository")
 */
class RoomUnavailableHours
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Room", inversedBy="unavailableHours")
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
