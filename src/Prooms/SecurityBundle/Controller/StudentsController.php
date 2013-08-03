<?php

namespace Prooms\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Prooms\SecurityBundle\Entity\Student;
use Symfony\Component\HttpFoundation\Response;
use Prooms\SecurityBundle\Service\FileService;
use JMS\SecurityExtraBundle\Annotation\Secure;

class StudentsController extends Controller
{
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function listAction() {
        $students = $this->getDoctrine()
            ->getRepository('ProomsSecurityBundle:Student')
            ->findAll();
        
        return $this->render('ProomsSecurityBundle:Students:list.html.twig', array(
            'students' => $students
        ));
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function downloadStudentsAction() {
        $students = $this->getDoctrine()
            ->getRepository('ProomsSecurityBundle:Student')
            ->findAll();
        
        $lines  = array (
            array('GPA', 'Permission to Publish', 'LastName', 'FirstName',
                'Title', 'StudentNo.', 'Address1', 'Address2', 'City',
                'PostalCode', 'Country', 'Phone1', 'Cell', 'Email', 'Degree',
                'Year', 'Major', 'Division', 'ConInstrument'
            ),
        );

        foreach ( $students as $student ) {
            array_push($lines, array(
                $student->getGPA(),
                $student->getPermissionToPublishPersonalInfo(),
                $student->getLastname(),
                $student->getFirstname(),
                $student->getTitle(),
                $student->getId(),
                $student->getAddress1(),
                $student->getAddress2(),
                $student->getCity(),
                $student->getPostalCode(),
                $student->getCountry(),
                $student->getPhone(),
                $student->getCell(),
                $student->getEmail(),
                $student->getDegree(),
                $student->getYear(),
                $student->getMajor(),
                $student->getInstrument(),
                $student->getConInstrument()
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
                'Content-Type'          => 'text/csv',
                'Content-Disposition'   => 'attachment; filename="csv-students.csv"'
            )
        );
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function addAction() {
        $student = new Student();
        $form = $this->createForm('student', $student);
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($student);
                $password = $encoder->encodePassword($student->getPassword(), $student->getSalt());
                $student->setPassword($password);
                $user = $this->get('security.context')->getToken()->getUser();
                //if current user is a student, update student.lastModifiedByStudent field
                if($user->isStudent()) {
                    $student->setLastModifiedByStudent();
                }
                $em = $this->getDoctrine()->getManager();
                $em->persist($student);
                $em->flush();
                $url = $this->generateUrl('prooms_students_view', array(
                    'id' => $student->getId()
                ));
                return $this->redirect($url);
            }
        }
        return $this->render('ProomsSecurityBundle:Students:add.html.twig', array(
            'form' => $form->createView()
        ));
        
        return $form;
        
    }
    
    /**
     * @Secure(roles="ROLE_STAFF, ROLE_STUDENT")
     */
    public function editAction($id) {
        $student = $this->getDoctrine()
            ->getRepository('ProomsSecurityBundle:Student')
            ->find($id);
        $form = $this->createForm('student', $student);
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                if($this->get('security.context')->isGranted('ROLE_STUDENT')) {
                    //if current user is a student, update student.lastModifiedByStudent field
                    $student->setLastModifiedByStudent();
                    $student->setStatus(true);
                }
                
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                
                $url = $this->generateUrl('prooms_students_view', array(
                    'id' => $student->getId()
                ));
                return $this->redirect($url);
            }
        }
        return $this->render('ProomsSecurityBundle:Students:edit.html.twig', array(
            'student' => $student,
            'form' => $form->createView()
        ));
        
        return $form;
        
    }
    
    //student view personal information, linked to base twig
    public function viewPersonalInfoAction() {
        //check if logged in
        if( $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') ){
            $user = $this->get('security.context')->getToken()->getUser();
            //redirect ti view action
            $viewUrl = $this->generateUrl('prooms_students_view', array(
                'id' => $user->getId()
            ));
            return $this->redirect($viewUrl);
        }
        //redirect to login page
        $loginUrl = $this->generateUrl('login');
        return $this->redirect($loginUrl);
    }
    
    /**
     * @Secure(roles="ROLE_STAFF, ROLE_STUDENT")
     */
    public function viewAction($id) {
        $student = $this->getDoctrine()
            ->getRepository('ProomsSecurityBundle:Student')
            ->find($id); 
        //if current user is a staff, update student.lastReviewedByStaff field
        $user = $this->get('security.context')->getToken()->getUser();
        
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('ProomsSecurityBundle:StudentGroup');
        $group = $repo->findGroupByStudent($student);
        
        if($user->isStaff()) {
            $student->setLastReviewedByStaff();
            $em->flush();
        }
        
        return $this->render('ProomsSecurityBundle:Students:view.html.twig', array(
            'student' => $student,
            'group' => $group
        ));
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function deleteAction($id) {
        $student = $this->getDoctrine()
            ->getRepository('ProomsSecurityBundle:Student')
            ->find($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($student);
        $em->flush();
        $url = $this->generateUrl('prooms_students_list');
        return $this->redirect($url);
    }
    
    /**
     * @Secure(roles="ROLE_STAFF")
     */
    public function clearAllAction() {
        $em = $this->getDoctrine()->getManager();
        
        $students = $this->getDoctrine()
            ->getRepository('ProomsSecurityBundle:Student')
            ->findAll();
        foreach($students as $student) {
            if($student->isStudent()) {
                $em->remove($student);
            }
        }
        
        $em->flush();
        $url = $this->generateUrl('prooms_students_list');
        return $this->redirect($url);
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
                            $student = new Student();
                            
                            $student->setGPA($data[0]);
                            $student->setPermissionToPublishPersonalInfo($data[1]);
                            $student->setLastname($data[2]);
                            $student->setFirstname($data[3]);
                            $student->setTitle($data[4]);
                            $student->setId($data[5]);
                            $student->setAddress1($data[6]);
                            $student->setAddress2($data[7]);
                            $student->setCity($data[8]);
                            $student->setPostalCode($data[9]);
                            $student->setCountry($data[10]);
                            $student->setPhone($data[11]);
                            $student->setCell($data[12]);
                            $student->setEmail($data[13]);
                            $student->setDegree($data[14]);
                            $student->setYear($data[15]);
                            $student->setMajor($data[16]);
                            $student->setInstrument($data[17]);
                            $student->setConInstrument($data[18]);
                            //set student status as 'hsa not confirmed personal information yet'
                            $student->setStatus(false);
                            
                            
                            //initialy set user name as S followed by student number, 
                            //password be first 6 chars of lastname + firstname
                            $student->setUsername("S" . $student->getId());
                            $name = strtolower($student->getLastName() . $student->getFirstName());
                            $temp_pass = substr($name, 0, 6);
                            $factory = $this->get('security.encoder_factory');
                            $encoder = $factory->getEncoder($student);
                            $password = $encoder->encodePassword($temp_pass, $student->getSalt());
                            $student->setPassword($password);
                            
                            $validator = $this->get('validator');
                            $errors = $validator->validate($student);

                            if (count($errors) > 0) {
                                $this->get('session')->getFlashBag()->add(
                                    'error',
                                    $errors[0]
                                );
                                $url = $this->generateUrl('prooms_students_list');
                                return $this->redirect($url);
                            } else {
                                $em->persist($student);
                            }
                            
                            //Do we have a better way of handle this??
                            //this setting is only adjusted for the running script
                            if((memory_get_usage() / 1024 /1024) < 70) {
                                try {
                                    ini_set('memory_limit', (ini_get('memory_limit')+1).'M');
                                } catch(Exception $e) {
                                    $this->get('session')->getFlashBag()->add(
                                        'error',
                                        'Out of memory'
                                    );
                                }
                            }
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
                }
                catch (\Exception $e) {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        $e->getMessage()
                    );
                    $url = $this->generateUrl('prooms_students_list');
                    return $this->redirect($url);
                }
                
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'You have uploaded seccessfully!'
                );
                
                $url = $this->generateUrl('prooms_students_list');
                return $this->redirect($url);
            }
        }
        
        return $this->render('ProomsSecurityBundle:Students:upload.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
