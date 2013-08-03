<?php

namespace Prooms\SecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Prooms\SecurityBundle\Entity\StudentGroup;
use Prooms\SecurityBundle\Entity\Student;

class StudentGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new StudentGroupTypeFieldsSubscriber());
    }

    public function getName()
    {
        return 'student_group';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Prooms\SecurityBundle\Entity\StudentGroup',
            'csrf_protection' => true,
        ));
    }
}

class StudentGroupTypeFieldsSubscriber implements EventSubscriberInterface
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

        if ($data->getDegree() || $data->getMajor() || $data->getYear() 
                || $data->getInstrument() || $data->getGpaFloor() ) {
            $form->add('degree', 'choice', array(
                'label'=> "Degree : ",
                'required' => true,
                'choices' => Student::$degrees,
                'error_bubbling' => true,
            ))
            ->add('major', 'choice', array(
                'label'=> "Major : ",
                'required' => false,
                'choices' => Student::$majors,
                'empty_value' => 'Any',
                'error_bubbling' => true,
            ))
            ->add('year', 'integer', array(
                'label'=> "Year : ",
                'required' => false,
                'error_bubbling' => true,
            ))
            ->add('instrument', 'choice', array(
                'label'=> "Instrument : ",
                'required' => true,
                'choices' => array(
                    'piano' => 'Piano',
                    'other' => 'Other'
                ),
                'error_bubbling' => true,
            ))
            ->add('gpaFloor', 'integer', array(
                'label'=> "GPA floor : ",
                'required' => true,
                'error_bubbling' => true,
            ));
        }
        $form->add('maxHours', 'integer', array(
                'label'=> "Maximum hours : ",
                'required' => false,
                'error_bubbling' => true,
        ))
        ->add('openingDatetime', 'datetime', array(
            'label'=> "Opening date and time",
            'input' => 'datetime'
        ));
    }
}