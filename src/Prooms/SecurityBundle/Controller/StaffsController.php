<?php

namespace Prooms\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Prooms\SecurityBundle\Entity\Staff;
use JMS\SecurityExtraBundle\Annotation\Secure;

class StaffsController extends Controller
{
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function listAction() {
        $staffs = $this->getDoctrine()
            ->getRepository('ProomsSecurityBundle:Staff')
            ->findAll();
        
        return $this->render('ProomsSecurityBundle:Staffs:list.html.twig', array(
            'staffs' => $staffs
        ));
    }
    
    public function addAction() {
        $staff = new Staff();
        $form = $this->createForm('staff', $staff);
        
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($staff);
                $password = $encoder->encodePassword($staff->getPassword(), $staff->getSalt());
                $staff->setPassword($password);
                $em = $this->getDoctrine()->getManager();
                $em->persist($staff);
                $em->flush();
                $url = $this->generateUrl('prooms_staffs_list');
                return $this->redirect($url);
            }
        }
        
        return $this->render('ProomsSecurityBundle:Staffs:add.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function editPasswordAction($id) {
        $staff = $this->getDoctrine()
            ->getRepository('ProomsSecurityBundle:Staff')
            ->find($id);
        $form = $this->createForm('staff', $staff);
        
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($staff);
                $password = $encoder->encodePassword($staff->getPassword(), $staff->getSalt());
                $staff->setPassword($password);
                $em = $this->getDoctrine()->getManager();
                $em->persist($staff);
                $em->flush();
                $url = $this->generateUrl('prooms_staffs_list');
                return $this->redirect($url);
            }
        }
        
        return $this->render('ProomsSecurityBundle:Staffs:add.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function editAction($id) {
        $staff = $this->getDoctrine()
            ->getRepository('ProomsSecurityBundle:Staff')
            ->find($id);
        $form = $this->createForm('staff', $staff);
        
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($staff);
                $em->flush();
                $url = $this->generateUrl('prooms_staffs_list');
                return $this->redirect($url);
            }
        }
        
        return $this->render('ProomsSecurityBundle:Staffs:edit.html.twig', array(
            'form' => $form->createView(),
            'staff' => $staff
        ));
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function deleteAction($id) {
        $staff = $this->getDoctrine()
            ->getRepository('ProomsSecurityBundle:Staff')
            ->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($staff);
        $em->flush();
        $url = $this->generateUrl('prooms_staffs_list');
                return $this->redirect($url);
    }
}
