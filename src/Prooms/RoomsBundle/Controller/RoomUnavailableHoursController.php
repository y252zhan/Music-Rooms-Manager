<?php

namespace Prooms\RoomsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Prooms\RoomsBundle\Entity\Room;
use Prooms\RoomsBundle\Entity\RoomUnavailableHours;
use JMS\SecurityExtraBundle\Annotation\Secure;

class RoomUnavailableHoursController extends Controller
{
    public function viewAction($id) {
        $room = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:Room')
            ->find($id);
        
        return $this->render('ProomsRoomsBundle:Rooms:unavailableHours.html.twig', array(
            'room' => $room,
        ));
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function editAction($id, $term) {
        $room = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:Room')
            ->find($id);
        
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $newUnavailableHours = $this->getUnavailableHoursFromRequest($request);
            $room = $this->addUnavailableHoursToRoom($newUnavailableHours, $room, $term);
            
            $em = $this->getDoctrine()->getManager();
            //first remove all unavailable hours from current term
            $em->getRepository('ProomsRoomsBundle:RoomUnavailableHours')
                ->deleteTermHours($room, $term);
            //save room with new unavailable hours added
            $em->flush();
            
            //redirect to view room information page
            $url = $this->generateUrl('prooms_rooms_view_unavailable_hours', array(
                'id'=>$room->getId()
            ));
            return $this->redirect($url);
            
        }
        return $this->render('ProomsRoomsBundle:Rooms:editHours.html.twig', array(
            'room' => $room,
            'term' => $term,
        ));
    }
    
    private function getUnavailableHoursFromRequest($request){
        $newUnavailableHours = array();
        for ($i = 1; $i <= 7; $i++) {
            if($request->get("weekday_$i") != "") 
                $newUnavailableHours[$i] = explode(',', $request->get("weekday_$i"));
        }
        return $newUnavailableHours;
    }
    
    private function addUnavailableHoursToRoom($newUnavailableHours, $room, $term) {
        foreach($newUnavailableHours as $weekday => $startTimes) {
            foreach ($startTimes as $startTime) {
                $unavailableHour = $this->composeRoomUnavailableHourObj($room, $term, $weekday, (int)$startTime);
                $room->addUnavailableHours($unavailableHour);
            }
        }
        return $room;
    }
    
    private function composeRoomUnavailableHourObj(Room $room, $term, $dayOfWeek, $startTime) {
        $unavailableHour = new RoomUnavailableHours();
        $unavailableHour->setRoom($room);
        $unavailableHour->setDayOfWeek($dayOfWeek);
        $unavailableHour->setDuration(1);
        $unavailableHour->setTerm($term);
        $unavailableHour->setStartTime($startTime);
        return $unavailableHour;
    }
}
