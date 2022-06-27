<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'label.nom',
                'label_attr' => ['class' => 'libelle'],
            ])
            ->add('body', TextareaType::class, [
                'label' => 'label.body',
                'label_attr' => ['class' => 'libelle'],
            ])
            ->add('dateEventStart', DateType::class, [
                'format' => 'dd MMMM yyyy',
                'view_timezone' => 'Europe/Paris',
                'model_timezone' => 'Europe/Paris',
                'label' => 'label.dateEventStart',
                'label_attr' => ['class' => 'libelle'],
                'required' => false,
            ])
            ->add('dateEventEnd', DateType::class, [
                'format' => 'dd MMMM yyyy',
                'view_timezone' => 'Europe/Paris',
                'model_timezone' => 'Europe/Paris',
                'label' => 'label.dateEventEnd',
                'label_attr' => ['class' => 'libelle'],
                'required' => false,
            ])
            ->add('url_video', TextType::class, [
                'label' => 'label.url_video',
                'label_attr' => ['class' => 'libelle'],
                'required' => false,
            ]);
    }
}
