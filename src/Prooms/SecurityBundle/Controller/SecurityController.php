<?php

namespace Prooms\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContext;
use Prooms\SecurityBundle\Entity\User;

class SecurityController extends Controller
{
    public function registerAction() 
    {
        $user = new User();
        $form = $this->createRegisterForm($user);
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                $user->setPassword($password);
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                
                $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                $context = $this->container->get('security.context');
                $context->setToken($token);
                
                $url = $this->generateUrl('prooms_rooms_list');
                return $this->redirect($url);
            }
        }
        return $this->render('ProomsSecurityBundle:Security:register.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    
    private function createRegisterForm(User $user)
    {
        $form = $this->createFormBuilder($user)
                ->add('studentId', 'integer')
                ->add('name', 'text')
                ->add('userName', 'text')
                ->add('password', 'repeated', array(
                    'type' => 'password',
                    'invalid_message' => 'The password field must match.',
                    'options' => array(
                        'attr' => array('class' => 'password-field')),
                    'required' => true,
                    'first_options' => array('label' => 'Password'),
                    'second_options' => array('label' => 'Repeat Password'),
                ))
                ->add('degree', 'text')
                ->add('major', 'text')
                ->add('year', 'integer')
                ->add('instrument', 'text')
                ->add('phone', 'text')
                ->add('email', 'email')
                ->add('mailingAddress', 'textarea')
                ->add('groups', 'entity', array(
                    'class' => 'ProomsSecurityBundle:Group',
                    'property' => 'role',
                    'multiple' => true,
                    'expanded' => true,
                    'required' => true
                ))
                ->getForm();
        return $form;
    }
    
    public function editAction() {
        $user = $this->get('security.context')->getToken()->getUser();
        $form = $this->createEditForm($user);
        
        $request = $this->getRequest();
        if ($request->isMethod('POST')) {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $url = $this->generateUrl('prooms_security_view');
                return $this->redirect($url);
            }
        }
   
        return $this->render('ProomsSecurityBundle:Security:edit.html.twig', array(
            'form' => $form->createView()
        ));
        
    }
    
    private function createEditForm(User $user)
    {
        $form = $this->createFormBuilder($user)
                ->add('degree', 'text')
                ->add('major', 'text')
                ->add('year', 'integer')
                ->add('instrument', 'text')
                ->add('phone', 'text')
                ->add('email', 'email')
                ->add('mailingAddress', 'textarea')
                ->getForm();
        return $form;
    }
    
    public function loginAction()
    {
        $request = $this->getRequest();
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render(
            'ProomsSecurityBundle:Security:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
            )
        );
    }
}
