<?php

namespace Prooms\RoomsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Prooms\RoomsBundle\Entity\Room;
use Prooms\RoomsBundle\Entity\RoomUnavailableHours;
use Symfony\Component\HttpFoundation\Response;
use Prooms\SecurityBundle\Service\FileService;
use JMS\SecurityExtraBundle\Annotation\Secure;

class RoomsController extends Controller
{
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function addAction()
    {
        $room = new Room();
        $form = $this->getAddOrEditRoomForm($room);
        
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $room->preUpload();
                $em->persist($room);
                $em->flush();
                $room->upload();
                $url = $this->generateUrl(
                    'prooms_rooms_show',
                    array('id' => $room->getId())
                );
                return $this->redirect($url);
            }
        }
   
        return $this->render('ProomsRoomsBundle:Rooms:add.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    //add and edit action share the same form
    private function getAddOrEditRoomForm(Room $room) {
        $form = $this->createFormBuilder($room)
            ->add('id', 'integer', array(
                'label' => "Room Number: "
            ))
            ->add('pianoType', 'choice', array(
                'label' => "Piano Type: ",
                'choices' => array(
                    'upright'=>'Upright',
                    'grand'=>'Grand',
                    'electric'=>'Electric',
                    'two upright' => 'Two Upright'
                ),
                'empty_data'  => null,
                'empty_value' => "Non specificato",
                'required'    => false,
            ))
            ->add('pianoDetail', 'choice', array(
                'label' => "Piano Detail: ",
                'choices' => array(
                    'yamaha' => 'Yamaha',
                    'kawai' => 'Kawai',
                    'boston' => 'Boston',
                    'petrof' => 'Petrof',
                    'yamaha avant grand' => 'Yamaha Avant Grand',
                    'steinway' => 'Steinway'
                ),
                'empty_data'  => null,
                'empty_value' => "Non specificato",
                'required'    => false,
            ))
            ->add('maxPeopleAllowed', 'integer', array(
                'label' => "Maximum number of people allowed : "
            ))
            ->add('description', 'textarea', array(
                'label' => "Description: "
            ))
            ->add('file')
            ->getForm();
        return $form;
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function deleteAction($id) {
        $room = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:Room')
            ->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($room);
        $em->flush();
        $room->removeUpload();
        $url = $this->generateUrl('prooms_rooms_list');
        return $this->redirect($url);
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function editAction($id) {
        $room = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:Room')
            ->find($id);
        $form = $this->getAddOrEditRoomForm($room);
        
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $room->preUpload();
                $em->flush();
                $room->upload();
                $url = $this->generateUrl('prooms_rooms_list');
                return $this->redirect($url);
            }
        }
   
        return $this->render('ProomsRoomsBundle:Rooms:editInfo.html.twig', array(
            'room' => $room,
            'form' => $form->createView()
        ));
    }
    
    public function listAction() {
        if($this->isSystemClosed()) {
            $url = $this->generateUrl('prooms_system_status_unavailable');
            return $this->redirect($url);
        }
        
        $group = NULL;
        
        //check if user has logged in
        if( $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') ){
            //check if user is a student
            if($this->get('security.context')->isGranted('ROLE_STUDENT')) {
                $user = $this->get('security.context')->getToken()->getUser();
                //if it is user first time logged in, redirect to edit student personal information page
                if ( $user->getStatus()==0 ) {
                    $url = $this->generateUrl('prooms_students_edit', array(
                        'id' =>$user->getId()
                    ));
                    return $this->redirect($url);
                }
                //else fetch student group
                else {
                    $em = $this->getDoctrine()->getManager();
                    $repo = $em->getRepository('ProomsSecurityBundle:StudentGroup');
                    $group = $repo->findGroupByStudent($user);
                    $now = new \DateTime();
                    if($group && $group->getOpeningDatetime() < $now) $group = NULL;
                }
            }
        }
        
        $rooms = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:Room')
            ->findAll();
        
        return $this->render('ProomsRoomsBundle:Rooms:list.html.twig', array(
            'rooms' => $rooms,
            'group' => $group
        ));
    }
    
    private function isSystemClosed() {
        $results = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:SystemStatus')
            ->findAll();
        if(count($results) == 0) return true;
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function downloadRoomsAction() {
        $rooms = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:Room')
            ->findAll();
        $lines=array(
            array('Room','Piano','Details','Piano details','Hours term 1', 'Hours term 2', 'Restrictions', 'Images--photographs of the rooms')
        );
        foreach ( $rooms as $room ) {
            array_push($lines, array( 
                $room->getId(),
                $room->getPianoType(),
                $room->getDescription(),
                $room->getPianoDetail(),
                '',
                '',
                $this->getRestrictionsString($room),
                ''
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
        
        return new Response(
            $content,
            200,
            array(
                'Content-Type' => 'text/csv',
                'Content-Disposition'   => 'attachment; filename="csv-rooms.csv"'
            )
        );
    }
    
    private function getRestrictionsString(Room $room) {
        $constraints = $room->getConstraints();
        $restrictionsString = "";
        if(count($constraints) == 0) $restrictionsString = "none";
        else {
            $count = 0;
            foreach ($constraints as $constraint) {
                if($count!=0) $restrictionsString .= ", ";
                $restrictionsString .= $constraint->getInstrument();
                $count++;
            }
            $restrictionsString .= " only";
        }
        return $restrictionsString;
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function uploadAction() {
        $fileService = new FileService();
        $form = $this->createFormBuilder($fileService)
            ->add('file', 'file', array(
                'required' => true,
            ))
            ->getForm();
        
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                
                //upload file and put under web/files
                $fileService->preUpload();
                $fileService->upload();
                
                //read file content and persist
                if (($handle = fopen($fileService->getAbsolutePath(), "r")) !== FALSE) {
                    //read first line as file header
                    $header = fgetcsv($handle, 1000, ",");
                    //read file content line by line, student infomation must be put in order to work
                    //Note: use Windows Comma Seperated (.csv) as format
                    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                        $room = new Room();
                        $room->setId($data[0]);
                        $room->setPianoType($data[1]);
                        $room->setDescription($data[2]);
                        $room->setPianoDetail($data[3]);
                        
                        $em->persist($room);
                    }
                    fclose($handle);
                }
                else {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        'Some error occured. Please double check you use Windows Comma Seperated (.csv) as file format.'
                    );
                }
                
                $em->flush();
                $fileService->removeUpload();
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'You have uploaded seccessfully!'
                );
                
                $url = $this->generateUrl('prooms_rooms_list');
                return $this->redirect($url);
            }
        }
        
        return $this->render('ProomsRoomsBundle:Rooms:upload.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function clearAllAction() {
        $rooms = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:Room')
            ->findAll();
        $em = $this->getDoctrine()->getManager();
        foreach($rooms as $room) {
            $em->remove($room);
        }
        $em->flush();
        
        $url = $this->generateUrl('prooms_rooms_list');
        return $this->redirect($url);
        
    }
            
    public function showAction($id) 
    {
        $room = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:Room')
            ->find($id);
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('ProomsRoomsBundle:UserRoom');
        
        $bookedHours = $repo->findByRoom($room);
        
        return $this->render('ProomsRoomsBundle:Rooms:show.html.twig', array(
            'room' => $room,
            'bookedHours' => $bookedHours
        ));
    }
}

