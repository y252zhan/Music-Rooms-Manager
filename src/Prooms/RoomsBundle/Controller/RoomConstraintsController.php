<?php

namespace Prooms\RoomsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Prooms\RoomsBundle\Entity\Room;
use Prooms\RoomsBundle\Entity\RoomConstraint;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;
use Prooms\SecurityBundle\Entity\Student;

class RoomConstraintsController extends Controller
{
    public function listAction($roomId) {
        if( ! $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') ){
            $loginUrl = $this->generateUrl('login');
            return $this->redirect($loginUrl);
        }
        $room = $this->getDoctrine()
                ->getRepository('ProomsRoomsBundle:Room')
                ->find($roomId);
        $constraints = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:RoomConstraint')
            ->findByRoom($room);
        return $this->render('ProomsRoomsBundle:Constraints:list.html.twig', array(
            'room' => $room,
            'constraints' => $constraints
        ));
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function addAction($roomId) {
        if( ! $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') ){
            $loginUrl = $this->generateUrl('login');
            return $this->redirect($loginUrl);
        }
        $room = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:Room')
            ->find($roomId);
        $constraint = new RoomConstraint();
        
        $coinces = $this->generateChoices($room);
        $form = $this->createFormBuilder($constraint)
            ->add('instrument', 'choice', array(
                'label' => "Instrument: ",
                'choices' => $coinces,
                'required'    => true,
            ))
            ->getForm();
        
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $constraint->setRoom($room);
                $room->addConstraint($constraint);
                $em->persist($room);
                $em->flush();
                
                $url = $this->generateUrl(
                    'prooms_constraints_list', array('roomId' => $room->getId()));
                return $this->redirect($url);
            }
        }
        return $this->render('ProomsRoomsBundle:Constraints:add.html.twig', array(
            'form' => $form->createView(),
            'room' => $room
        ));
    }
    
    public function applyAction($roomId) {
        $room = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:Room')
            ->find($roomId);
        $hours = $this->applyConstraints($room);
        if(count($hours) == 0) {
            $url = $this->generateUrl(
                'prooms_constraints_list', array('roomId' => $room->getId()));
            $this->get('session')->getFlashBag()->add(
                'notice',
                'No change need to be applied.'
            );
            return $this->redirect($url);
        }else {
            $content = $this->generateCsvFileFromRemovedHours($hours);
            $em = $this->getDoctrine()->getManager();

            foreach($hours as $hour) {
                $em->remove($hour);
            }
            $em->flush();

            return new Response(
                $content,
                200,
                array(
                    'Content-Type'          => 'text/csv',
                    'Content-Disposition'   => 'attachment; filename="removed-student-hours.csv"'
                )
            );
        }
    }
    
    private function applyConstraints($room) {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('ProomsRoomsBundle:UserRoom');
        $bookedHours = $repo->findByRoom($room);
        $constraints = $room->getConstraints();
        $hoursNeedToBeRemoved = array();
        if( count($constraints) == 0 ) return $hoursNeedToBeRemoved;
        else {
            foreach( $bookedHours as $hour ) {
                $removedHour = $this->checkHourConstraint($hour, $constraints);
                if($removedHour) array_push ($hoursNeedToBeRemoved, $removedHour);
            }
            return $hoursNeedToBeRemoved;
        }
    }
    
    //check if studeht instrument is the same as any of the constraints
    //return NULL if yes
    //return UserRoom object if no
    private function checkHourConstraint($hour, $constraints) {
        foreach( $constraints as $constraint) {
            if( $hour->getUser()->getInstrument() == $constraint->getInstrument() ) {
                return NULL;
            }
        }
        return $hour;
    }
    
    private function generateCsvFileFromRemovedHours($hours) {
        $lines  = array (
            array('Room', 'Id','Lastname','Firstname', 'Email', 'Phone', 'Term', 'Day', 'Start time', 'Duration'),
        );

        foreach ( $hours as $hour ) {
            $student = $hour->getUser();
            $room = $hour->getRoom();
            array_push($lines, array(
                $room->getId(),
                $student->getId(),
                $student->getLastname(),
                $student->getFirstname(),
                $student->getEmail(),
                $student->getPhone(),
                $hour->getTerm(),
                $hour->getDayOfWeek(),
                8+$hour->getStartTime().":00",
                1
            ));
        }
        
        //use files/temp.csv as temp location, write content to csv file
        $path = __DIR__.'/../../../../web/' . 'files/temp.csv';
        $fp = fopen($path, 'w');

        foreach ($lines as $line) {
            fputcsv($fp, $line);
        }

        fclose($fp);
        
        //read content from csv file
        $content = file_get_contents($path);
        
        //remove file
        unlink($path);
        return $content;
    }
    
    private function generateChoices($room) {
        $choices = Student::$instruments;
        foreach($room->getConstraints() as $constraint) {
            unset($choices[$constraint->getInstrument()]);
        }
        return $choices;
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function deleteAction($roomId, $instrument) {
        if( ! $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') ){
            $loginUrl = $this->generateUrl('login');
            return $this->redirect($loginUrl);
        }
        $room = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:Room')
            ->find($roomId);
        $constraint = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:RoomConstraint')
            ->findOneBy(array(
                'room' => $room,
                'instrument' => $instrument
            ));
        
        $em = $this->getDoctrine()->getManager();
        $em->remove($constraint);
        $em->flush();
        $url = $this->generateUrl(
            'prooms_constraints_list', array('roomId' => $room->getId()));
        return $this->redirect($url);
    }
}

