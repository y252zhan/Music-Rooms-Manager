<?php

namespace Prooms\RoomsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Prooms\RoomsBundle\Entity\SystemStatus;
use JMS\SecurityExtraBundle\Annotation\Secure;

//system is closed if system_status table is empty
//system is open if system_status table is not empty
class SystemStatusController extends Controller
{
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function viewAction() {
        $results = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:SystemStatus')
            ->findAll();
        
        $isSystemOpen = false;
        if(count($results) > 0) $isSystemOpen = true;
        
        return $this->render('ProomsRoomsBundle:SystemStatus:view.html.twig', array(
            'isSystemOpen' => $isSystemOpen
        ));
    }
    
    public function systemUnavailableAction() {
        return $this->render('ProomsRoomsBundle:SystemStatus:unavailable.html.twig');
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function closeAction() {
        $em = $this->getDoctrine()->getManager();
        $results = $this->getDoctrine()
            ->getRepository('ProomsRoomsBundle:SystemStatus')
            ->findAll();
        foreach($results as $result) {
            $em->remove($result);
        }
        $em->flush();
        $this->get('session')->getFlashBag()->add(
            'notice',
            "The system has been closed."
        );
        $url = $this->generateUrl('prooms_system_status_view');
        return $this->redirect($url);
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function openAction() {
        $em = $this->getDoctrine()->getManager();
        $systemStatus = new SystemStatus();
        $systemStatus->setSystemStatus(true);
        
        $em->persist($systemStatus);
        $em->flush();
        $this->get('session')->getFlashBag()->add(
            'notice',
            "The system has been reopened."
        );
        $url = $this->generateUrl('prooms_system_status_view');
        return $this->redirect($url);
    }
    
    
    
}

