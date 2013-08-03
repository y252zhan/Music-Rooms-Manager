<?php

namespace Prooms\RoomsBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Prooms\RoomsBundle\Entity\Room;
use Prooms\SecurityBundle\Entity\User;

class UserRoomRepository extends EntityRepository
{
    public function findByRoom(Room $room) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('ur')
            ->from('ProomsRoomsBundle:UserRoom', 'ur')
            ->where("ur.room = :room")
            ->setParameter('room', $room);
        $userRooms = $qb->getQuery()->getResult();
        return $userRooms;
    }
    
    public function findByUser(User $user) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('ur')
            ->from('ProomsRoomsBundle:UserRoom', 'ur')
            ->where("ur.user = :user")
            ->setParameter('user', $user);
        $userRooms = $qb->getQuery()->getResult();
        return $userRooms;
    }
    
    public function deleteByUserAndRoomAndTerm(User $user, Room $room, $term)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete('ProomsRoomsBundle:UserRoom', 'ur')
            ->where("ur.term = $term AND ur.room = :room AND ur.user = :user")
            ->setParameter('room', $room)
            ->setParameter('user', $user);
        $qb->getQuery()->execute();
    }
    
    public function findByRoomAndTermAndWeekdayAndStartTime(Room $room, $term, $weekday, $startTime) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('ur')
            ->from('ProomsRoomsBundle:UserRoom', 'ur')
            ->where("ur.room = :room AND ur.term=$term AND ur.startTime = $startTime AND ur.dayOfWeek = $weekday")
            ->setParameter('room', $room);
        $userRooms = $qb->getQuery()->getResult();
        return $userRooms;
    }
    
    public function countTermHoursExceptFor(User $user, Room $room, $term) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('ur')
            ->from('ProomsRoomsBundle:UserRoom', 'ur')
            ->where("ur.user = :user")
            ->andWhere("ur.term = :term")
            ->andWhere("ur.room != :room")
            ->setParameters(array(
                'user' => $user,
                'term' => $term,
                'room' => $room));
        $userRooms = $qb->getQuery()->getResult();
        return count($userRooms);
    }
}
