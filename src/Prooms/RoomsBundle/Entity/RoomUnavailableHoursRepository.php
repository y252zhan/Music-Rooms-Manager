<?php

namespace Prooms\RoomsBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Prooms\RoomsBundle\Entity\Room;

class RoomUnavailableHoursRepository extends EntityRepository
{
    //delete all room unavailable hours with term = $term
    public function deleteTermHours(Room $room, $term)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete('ProomsRoomsBundle:RoomUnavailableHours', 'h')
            ->where("h.term = $term AND h.room = :room")
            ->setParameter('room', $room);
        $qb->getQuery()->execute();
    }
}