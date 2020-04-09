<?php

namespace App\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\ResettingFormType as BaseType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ResettingFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('new', RepeatedType::class, array(
            'type' => PasswordType::class,
            'first_options' => array(
                'label' => 'resetting.new_password',
                'label_attr' => array('class' => 'libelle')
            ),
            'second_options' => array(
                'label' => 'resetting.new_password_confirmation',
                'label_attr' => array('class' => 'libelle')
            ),
            'invalid_message' => 'resetting.mismatch',
        ));
    }

    public function getName()
    {
        return 'ninjatooken_user_resetting';
    }
}
