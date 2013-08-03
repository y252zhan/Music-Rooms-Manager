<?php

namespace Prooms\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Prooms\RoomsBundle\Entity\Room;
use Prooms\RoomsBundle\Entity\UserRoom;

/**
 * @ORM\Entity()
 */
class Staff extends User
{   
    private $discr = "staff";

    public function getDiscr() {
        return $this->discr;
    }
    
    public function getRoles() {
        return array(
            'ROLE_STAFF'
        );
    }
    
    public function isStudent() {
        return false;
    }
    
    public function isStaff() {
        return true;
    }
}
