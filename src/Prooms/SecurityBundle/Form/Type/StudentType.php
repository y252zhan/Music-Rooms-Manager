<?php

namespace Prooms\SecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Prooms\SecurityBundle\Entity\User;
use Prooms\SecurityBundle\Entity\Student;

class StudentType extends AbstractType
{
    private $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->securityContext->getToken()->getUser();
        $builder->addEventSubscriber(new UserTypeFieldsSubscriber($user));
    }

    public function getName()
    {
        return 'student';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Prooms\SecurityBundle\Entity\Student',
            'csrf_protection' => true,
        ));
    }
}

class UserTypeFieldsSubscriber implements EventSubscriberInterface
{
    private $user;
    
    public function __construct(User $user)
    {
        $this->user = $user;
    }
    
    public static function getSubscribedEvents()
    {
        // Tells the dispatcher that you want to listen on the form.pre_set_data
        // event and that the preSetData method should be called.
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        // check if the product object is "new"
        // If you didn't pass any data to the form, the data is "null".
        // This should be considered a new "Product"
        if (!$data || !$data->getId()) {
            $form->add('id', 'integer', array(
                'label'=> "Student Id : ",
                'required' => true,
                'error_bubbling' => true,
            ))
            ->add('firstname', 'text', array(
                'label'=> "First name : ",
                'required' => true,
                'error_bubbling' => true,
            ))  
            ->add('lastname', 'text', array(
                'label'=> "Family name : ",
                'required' => true,
                'error_bubbling' => true,
            )) 
            ->add('username', 'text', array(
                'label'=> "User name : ",
                'required' => true,
                'error_bubbling' => true,
            ))
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'The password field must match.',
                'options' => array(
                    'attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options' => array('label' => 'Password : '),
                'second_options' => array('label' => 'Repeat Password : '),
            ));
        }
        $form->add('username', 'text', array(
                'label'=> "User name : ",
                'required' => true,
                'error_bubbling' => true,
            ));
        if ( $this->user->isStaff() ) {
        $form->add('gpa', 'integer', array(
                'label'=> "GPA(%) : ",
                'required' => true,
                'error_bubbling' => true,
            ))
            ->add('year', 'integer', array(
                'label'=> "Year : ",
                'required' => true,
                'error_bubbling' => true,
            ))
            ->add('degree', 'choice', array(
                'label'=> "Degree : ",
                'required' => true,
                'error_bubbling' => true,
                'choices' => Student::$degrees
            ))
            ->add('major', 'choice', array(
                'label'=> "Major : ",
                'required' => true,
                'error_bubbling' => true,
                'choices' => Student::$majors
            ))
            ->add('instrument', 'choice', array(
                'label'=> "Instrument : ",
                'required' => true,
                'error_bubbling' => true,
                'choices' => Student::$instruments
            ))
            ->add('conInstrument', 'text', array(
                'label'=> "ConInstrument : ",
                'required' => true,
                'error_bubbling' => true,
            ));
        }
        $form->add('phone', 'text', array(
                'label'=> "Phone : ",
                'required' => false,
                'error_bubbling' => true,
            ))
            ->add('cell', 'text', array(
                'label'=> "Cell : ",
                'required' => false,
                'error_bubbling' => true,
            ))
            ->add('email', 'text', array(
                'label'=> "Email : ",
                'required' => false,
                'error_bubbling' => true,
            ))
            ->add('address1', 'text', array(
                'label'=> "Address 1 : ",
                'required' => false,
                'error_bubbling' => true,
            ))
            ->add('address2', 'text', array(
                'label'=> "Address 2 : ",
                'required' => false,
                'error_bubbling' => true,
            ))
            ->add('city', 'text', array(
                'label'=> "City/Town : ",
                'required' => false,
                'error_bubbling' => true,
            ))
            ->add('country', 'text', array(
                'label'=> "Country : ",
                'required' => false,
                'error_bubbling' => true,
            ))
            ->add('postalCode', 'text', array(
                'label'=> "Postal Code : ",
                'required' => false,
                'error_bubbling' => true,
            ))
            ->add('permissionToPublishPersonalInfo', 'checkbox', array(
                'label'=> "I give permission for UBC Music to publish my phone number and email address.",
                'required' => false,
                'error_bubbling' => true,
            ));
    }
}

