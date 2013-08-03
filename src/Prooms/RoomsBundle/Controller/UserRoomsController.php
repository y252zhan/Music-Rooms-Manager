<?php

namespace Prooms\RoomsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Prooms\RoomsBundle\Entity\Room;
use Prooms\RoomsBundle\Entity\RoomUnavailableHours;
use Prooms\RoomsBundle\Entity\UserRoom;
use Prooms\SecurityBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;

class UserRoomsController extends Controller
{
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function viewAction($id) {
        $room = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:Room')
            ->find($id);
        
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('ProomsRoomsBundle:UserRoom');
        
        $bookedHours = $repo->findByRoom($room);
        
        return $this->render('ProomsRoomsBundle:Schedules:view.html.twig', array(
            'bookedHours' => $bookedHours,
            'room' => $room,
        ));
    }
    
    /**
     * @Secure(roles="ROLE_STAFF, ROLE_STUDENT")
     */
    public function browserPrintRoomTimetableAction($id) {
        $room = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:Room')
            ->find($id);
        
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('ProomsRoomsBundle:UserRoom');
        
        $bookedHours = $repo->findByRoom($room);
        
        return $this->render('ProomsRoomsBundle:Schedules:printView.html.twig', array(
            'bookedHours' => $bookedHours,
            'room' => $room,
        ));
    }
   
    /**
     * @Secure(roles="ROLE_STUDENT")
     */
    public function userScheduleAction() {
        $user = $this->get('security.context')->getToken()->getUser();
        
        //if it is user has not confirmed his personal information yet,
        //redurect to student personal information page
        if ( $user->getStatus()==0 ) {
            $url = $this->generateUrl('prooms_students_edit', array(
                'id' =>$user->getId()
            ));
            return $this->redirect($url);
        }
        
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('ProomsRoomsBundle:UserRoom');
        $userHours = $repo->findByUser($user);
        
        return $this->render('ProomsRoomsBundle:Schedules:user.html.twig', array(
            'userHours' => $userHours,
        ));
    }
    
    /**
     * @Secure(roles="ROLE_STUDENT")
     */
    public function browserPrintUserTimetableAction() {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('ProomsRoomsBundle:UserRoom');
        $userHours = $repo->findByUser($user);
        
        return $this->render('ProomsRoomsBundle:Schedules:printUser.html.twig', array(
            'userHours' => $userHours,
        ));
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function removeAllAction() {
        $em = $this->getDoctrine()->getManager();
        $userRooms = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:UserRoom')
            ->findAll();
        foreach($userRooms as $userRoom) {
            $em->remove($userRoom);
        }
        $em->flush();
        
        $this->get('session')->getFlashBag()->add(
            'notice',
            "All students have been removed from practice rooms."
        );
        $url = $this->generateUrl('prooms_system_status_view');
        return $this->redirect($url);
    }
    
    /**
     * @Secure(roles="ROLE_STUDENT")
     */
    public function editAction($id, $term)
    {
        $room = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:Room')
            ->find($id);
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('ProomsRoomsBundle:UserRoom');
        
        $bookedHours = $repo->findByRoom($room);
        $request = $this->getRequest();
        if($request->isMethod('POST')) {
            $url = $this->generateUrl(
                'prooms_rooms_show',
                array('id' => $id)
            );
            
            $em = $this->getDoctrine()->getManager();
            $groupRepo = $em->getRepository('ProomsSecurityBundle:StudentGroup');
            $group = $groupRepo->findGroupByStudent($user);
            $currentDatetime = new \DateTime();
            if ($group && $currentDatetime < $group->getOpeningDatetime()) {
                $openingDatetime = $group->getOpeningDatetime()->format('Y-m-d H:i:s');
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "Your registration opening date and time is $openingDatetime."
                );
                return $this->redirect($url);
            }
            
            $hours = $this->getHoursFromRequest($request);
            
            if( $group && $group->getMaxHours() < $this->countTotalHours($user, $room, $term, $hours) ) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    "You have exceeded max hours you can register."
                );
                return $this->redirect($url);
            }
            
            $repo->deleteByUserAndRoomAndTerm($user, $room, $term);
            
            $message = $this->addHoursToUser($hours, $room, $term);
            $em->persist($user);
            $em->flush();



            if(! $message['roomConstraintOkay'] ) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'You can not book this practice room. For more information, please check room constraints.'
                );
            }
            else if ( $message['hasConflict'] ) {
                $this->get('session')->getFlashBag()->add(
                    'error',
                    'There was a conflict.'
                );
            }
            else {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Your changes were saved successfully.'
                );
            }

            return $this->redirect($url);
        }
        
        return $this->render('ProomsRoomsBundle:Schedules:edit.html.twig', array(
            'bookedHours' => $bookedHours,
            'room' => $room,
            'term' => $term,
        ));
            
    }
    
    private function countTotalHours($user, Room $room, $term, $hours) {
        $count = 0;
        foreach($hours as $day) {
            foreach($day as $hour) {
                $count++;
            }
        }
        
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('ProomsRoomsBundle:UserRoom');
        $count += $repo->countTermHoursExceptFor($user, $room, $term);
        return $count;
    }
    
    private function getHoursFromRequest($request){
        $newUnavailableHours = array();
        for ($i = 1; $i <= 7; $i++) {
            if($request->get("weekday_$i") != "") 
                $newUnavailableHours[$i] = explode(',', $request->get("weekday_$i"));
        }
        return $newUnavailableHours;
    }
    
    private function addHoursToUser(array $hours, $room, $term) {
        $message = array(
            'hasConflict' => false,
            'roomConstraintOkay' => true
        );
        $user = $this->get('security.context')->getToken()->getUser();
        if( ! $this->roomConstraintsOkay($user, $room) ) $message['roomConstraintOkay'] = false;
        else {
            foreach($hours as $weekday => $startTimes) {
                foreach ($startTimes as $startTime) {
                    if( ! $this->hasConflict($user, $term, $weekday, $startTime)) {
                        $userRoom = $this->composeUserRoom($room, $weekday, (int)$startTime, $term, 1);
                        $user->addUserRoom($userRoom); 
                    }
                    else $message['hasConflict'] = true;
                }
            }
        }
        return $message;
    }
    
    private function roomConstraintsOkay($user, $room) {
        $constraints = $room->getConstraints();
        if(count($constraints) == 0) return true;
        else {
            foreach($constraints as $constraint) {
                if($user->getInstrument() == $constraint->getInstrument()) return true;
            }
        }
        return false;
    }
    
    private function hasConflict($user, $term, $weekday, $startTime) {
        $bookedHours = $user->getUserRooms();
        if($bookedHours) {
            foreach($bookedHours as $hour) {
                if($hour->getTerm()==$term 
                        && $hour->getDayOfWeek()==$weekday
                        && $hour->getStartTime()==$startTime ) {
                    return true;
                }
            }
        }
        return false;
    }
/*
    private function isRoomAvailable(Room $room, $term, $dayOfWeek, $startTime) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('ProomsRoomsBundle:UserRoom');
        $user = $this->get('security.context')->getToken()->getUser();
        $bookedHours = $repo->findByRoomAndTermAndWeekdayAndStartTime($room, $term, $dayOfWeek, $startTime);
        if(count($bookedHours)==0) return true;
        foreach ($bookedHours as $hour) {
            if($hour->getUser()->getStudentId() != $user->getStudentId()) return false;
        }
        return true;
    }
    
    private function isHourAvailable($term, $dayOfWeek, $startTime) {
        $user = $this->get('security.context')->getToken()->getUser();
        foreach ($user->getUserRooms() as $hour) {
            if($hour->getTerm()==$term 
                    && $hour->getStartTime()==$startTime
                    && $hour->getDayOfWeek()==$dayOfWeek) 
                return false;
        }
        return true;
    }
 */   
    private function composeUserRoom(Room $room,
                                        $dayOfWeek, $startTime, $term, $duration) {
        $userRoom = new UserRoom();
        $user = $this->get('security.context')->getToken()->getUser();
        $userRoom->setRoom($room);
        $userRoom->setUser($user);
        $userRoom->setDayOfWeek($dayOfWeek);
        $userRoom->setStartTime($startTime);
        $userRoom->setTerm($term);
        $userRoom->setDuration($duration);
        return $userRoom;
    }
}

