<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, array(
                'label' => 'label.sujet',
                'label_attr' => array('class' => 'libelle')
            ))
            ->add('content', TextareaType::class, array(
                'label' => 'label.message',
                'label_attr' => array('class' => 'libelle')
            ))
        ;
    }

    public function getName()
    {
        return 'message';
    }
}
