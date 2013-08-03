<?php

namespace Prooms\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Prooms\SecurityBundle\Entity\StudentGroup;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Prooms\SecurityBundle\Service\FileService;
use Symfony\Component\HttpFoundation\Response;

class StudentGroupsController extends Controller
{
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function listAction() {
        $groups = $this->getDoctrine()
            ->getRepository('ProomsSecurityBundle:StudentGroup')
            ->findAll();
        
        $studentGroups = array();
        
        foreach($groups as $group) {
            if( ! $group->isDefault()) {
                array_push($studentGroups, $group);
            }
        }
        
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('ProomsSecurityBundle:StudentGroup');
        $defaultGroup = $repo->findDefaultGroup();
        
        return $this->render('ProomsSecurityBundle:StudentGroups:list.html.twig', array(
            'studentGroups' => $studentGroups,
            'defaultGroup'  => $defaultGroup,
        ));
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function addAction() {
        $studentGroup = new StudentGroup();
        $form = $this->createForm('student_group', $studentGroup);
        
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($studentGroup);
                $em->flush();
                $url = $this->generateUrl('prooms_student_groups_list');
                return $this->redirect($url);
            }
        }
        
        return $this->render('ProomsSecurityBundle:StudentGroups:add.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function addDefaultAction() {
        $studentGroup = new StudentGroup();
        $studentGroup->setMaxHours(0);
        $studentGroup->setOpeningDatetime(new \DateTime());
        $em = $this->getDoctrine()->getManager();
        $em->persist($studentGroup);
        $em->flush();
        $url = $this->generateUrl('prooms_student_groups_list');
        return $this->redirect($url);
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function editAction($id) {
        $studentGroup = $this->getDoctrine()
            ->getRepository('ProomsSecurityBundle:StudentGroup')
            ->find($id);
        $form = $this->createForm('student_group', $studentGroup);
        
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($studentGroup);
                $em->flush();
                $url = $this->generateUrl('prooms_student_groups_list');
                return $this->redirect($url);
            }
        }
        
        return $this->render('ProomsSecurityBundle:StudentGroups:edit.html.twig', array(
            'form' => $form->createView(),
            'studentGroup' => $studentGroup
        ));
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function deleteAction($id) {
        $studentGroup = $this->getDoctrine()
            ->getRepository('ProomsSecurityBundle:StudentGroup')
            ->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($studentGroup);
        $em->flush();
        $url = $this->generateUrl('prooms_student_groups_list');
                return $this->redirect($url);
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function downloadRoomsAction() {
        $studentGroups = $this->getDoctrine()
            ->getRepository('ProomsSecurityBundle:StudentGroup')
            ->findAll();
        $lines=array(
            array('Degree','Major','Instrument','Year','GPA','Max. hours', 'Access date', 'Access time')
        );
        foreach ( $studentGroups as $studentGroup ) {
            if(! $major = $studentGroup->getMajor()) $major = "any";
            if(! $year = $studentGroup->getYear()) $year = "any";
            $gpa = $studentGroup->getGpaFloor();
            if($gpa == 0) $gpa = "all";
            array_push($lines, array( 
                $studentGroup->getDegree(),
                $major,
                $studentGroup->getInstrument(),
                $year,
                $gpa,
                $studentGroup->getMaxHours(),
                $studentGroup->getOpeningDatetime()->format('d/m/Y'),
                $studentGroup->getOpeningDatetime()->format('H:i:s')
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
                'Content-Disposition'   => 'attachment; filename="csv-student_groups.csv"'
            )
        );
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
                try {
                    //read file content and persist
                    if (($handle = fopen($fileService->getAbsolutePath(), "r")) !== FALSE) {
                        //read first line as file header
                        $header = fgetcsv($handle, 1000, ",");
                        //read file content line by line, student infomation must be put in order to work
                        //Note: use Windows Comma Seperated (.csv) as format
                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                            $this->uploadStudentGroupFromLine($data);
                        }
                        fclose($handle);
                    }
                    else {
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            'Some error occured. Please double check you use Windows Comma Seperated (.csv) as file format.'
                        );
                    }
                }
                catch (\Exception $e) {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        $e->getMessage()
                    );
                    $url = $this->generateUrl('prooms_student_groups_list');
                    return $this->redirect($url);
                }
                
                $em->flush();
                $fileService->removeUpload();
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'You have uploaded seccessfully!'
                );
                
                $url = $this->generateUrl('prooms_student_groups_list');
                return $this->redirect($url);
            }
        }
        
        return $this->render('ProomsSecurityBundle:StudentGroups:upload.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    private function uploadStudentGroupFromLine($data) {
        $em = $this->getDoctrine()->getManager();
        $studentGroup = new StudentGroup();
        $studentGroup->setDegree($data[0]);
        
        if($data[1]=="any") {
            $studentGroup->setMajor(NULL);
        }
        else {
            $studentGroup->setMajor($data[1]);
        }
        
        $studentGroup->setInstrument($data[2]);
        
        if($data[3]=="any") {
            $studentGroup->setYear(NULL);
        }
        else {
            $studentGroup->setYear($data[3]);
        }
        
        if($data[4]=="all") {
            $studentGroup->setGpaFloor(0);
        }
        else {
            $studentGroup->setGpaFloor($data[4]);
        }
        $studentGroup->setMaxHours($data[5]);
        $studentGroup->setOpeningDatetime(new \DateTime($data[6] . " " . $data[7]));
        $validator = $this->get('validator');
        $errors = $validator->validate($studentGroup);

        if (count($errors) > 0) {
            $this->get('session')->getFlashBag()->add(
                'error',
                $errors[0]
            );
            $url = $this->generateUrl('prooms_student_groups_list');
            return $this->redirect($url);
        } else {
            $em->persist($studentGroup);
        }
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function clearAllAction() {
        $studentGroups = $this->getDoctrine()
            ->getRepository('ProomsSecurityBundle:StudentGroup')
            ->findAll();
        $em = $this->getDoctrine()->getManager();
        foreach($studentGroups as $studentGroup) {
            $em->remove($studentGroup);
        }
        $em->flush();
        
        $url = $this->generateUrl('prooms_student_groups_list');
        return $this->redirect($url);
    }
}

