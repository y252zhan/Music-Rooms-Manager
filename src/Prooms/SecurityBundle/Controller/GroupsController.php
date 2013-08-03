<?php

namespace Prooms\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Prooms\SecurityBundle\Entity\Group;

class GroupsController extends Controller
{
    public function addAction() {
        $group = new Group();
        $form = $this->createForm('group', $group);
        
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($group);
                $em->flush();
                $url = $this->generateUrl('prooms_groups_list');
                return $this->redirect($url);
            }
        }
        
        return $this->render('ProomsSecurityBundle:Groups:add.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    public function listAction() {
        $groups = $this->getDoctrine()
            ->getRepository('ProomsSecurityBundle:Group')
            ->findAll();
        
        return $this->render('ProomsSecurityBundle:Groups:list.html.twig', array(
            'groups' => $groups
        ));
    }
    
    public function deleteAction($id) {
        $group = $this->getDoctrine()
            ->getRepository('ProomsSecurityBundle:Group')
            ->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($group);
        $em->flush();
        $url = $this->generateUrl('prooms_groups_list');
                return $this->redirect($url);
    }
}
