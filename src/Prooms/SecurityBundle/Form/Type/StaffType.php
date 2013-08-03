<?php

namespace Prooms\SecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Prooms\SecurityBundle\Entity\User;

class StaffType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new StaffTypeFieldsSubscriber());
    }

    public function getName()
    {
        return 'staff';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Prooms\SecurityBundle\Entity\Staff',
            'csrf_protection' => true,
        ));
    }
}

class StaffTypeFieldsSubscriber implements EventSubscriberInterface
{
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

        $form->add('id', 'integer', array(
                'label'=> "Employee Id : ",
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
            ->add('username', 'text', array(
                'label'=> "User name : ",
                'required' => true,
                'error_bubbling' => true,
            )); 
        if (!$data || !$data->getId()) {
            $form->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'The password field must match.',
                'options' => array(
                    'attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options' => array('label' => 'Password : '),
                'second_options' => array('label' => 'Repeat Password : '),
            ));
        }
    }
}
