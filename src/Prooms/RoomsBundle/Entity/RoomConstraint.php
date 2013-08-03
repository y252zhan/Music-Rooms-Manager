<?php
namespace Prooms\RoomsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rooms_constraints")
 */
class RoomConstraint
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Room", inversedBy="constraints")
     * @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     */
    private $room;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=30)
     */
    private $instrument;
    
    public function getRoom() {
        return $this->room;
    }

    public function setRoom(Room $room) {
        $this->room = $room;
    }
    
    public function getInstrument() {
        return $this->instrument;
    }
    
    public function setInstrument($instrument) {
        $this->instrument = $instrument;
    }
}
