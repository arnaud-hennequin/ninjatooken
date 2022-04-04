<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, array(
                'label' => 'label.nom',
                'label_attr' => array('class' => 'libelle')
            ))
            ->add('body', TextareaType::class, array(
                'label' => 'label.body',
                'label_attr' => array('class' => 'libelle')
            ))
            ->add('dateEventStart', DateType::class, array(
                'format' => 'dd MMMM yyyy',
                'view_timezone' => "Europe/Paris",
                'model_timezone' => "Europe/Paris",
                'label' => 'label.dateEventStart',
                'label_attr' => array('class' => 'libelle'),
                'required' => false
            ))
            ->add('dateEventEnd', DateType::class, array(
                'format' => 'dd MMMM yyyy',
                'view_timezone' => "Europe/Paris",
                'model_timezone' => "Europe/Paris",
                'label' => 'label.dateEventEnd',
                'label_attr' => array('class' => 'libelle'),
                'required' => false
            ))
            ->add('url_video', TextType::class, array(
                'label' => 'label.url_video',
                'label_attr' => array('class' => 'libelle'),
                'required' => false
            ));
    }
}