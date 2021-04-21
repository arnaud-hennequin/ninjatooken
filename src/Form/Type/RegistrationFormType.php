<?php

namespace App\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use App\Entity\User\User;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationFormType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('username', null, array(
                'label' => 'compte.register.pseudo',
                'label_attr' => array('class' => 'libelle'),
                'error_bubbling' => true
            ))
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'first_options' => array(
                    'label' => 'compte.register.motPasse',
                    'label_attr' => array('class' => 'libelle')
                ),
                'second_options' => array(
                    'label' => 'compte.register.motPasseRepeter',
                    'label_attr' => array('class' => 'libelle')
                ),
                'invalid_message' => 'ninja_tooken_user.password.mismatch',
                'error_bubbling' => true
            ))
            ->add('email', EmailType::class, array(
                'label' => 'compte.register.mail',
                'label_attr' => array('class' => 'libelle'),
                'error_bubbling' => true
            ))
            ->add('gender', ChoiceType::class, array(
                'choices' => array(
                    User::GENDER_MALE => 'gender_male',
                    User::GENDER_FEMALE => 'gender_female'
                ),
                'data' => User::GENDER_MALE,
                'expanded' => true,
                'label_attr' => array('class' => 'libelle')
            ))
            ->add('locale', ChoiceType::class, array(
                'choices' => array('fr' => 'Français', 'en' => 'English'),
                'data' => 'fr',
                'expanded' => true,
                'label_attr' => array('class' => 'libelle')
            ))
            ->add('receive_newsletter', CheckboxType::class, array(
                'label' => 'compte.register.receiveNewsletter',
                'required' => false
            ))
            ->add('receive_avertissement', CheckboxType::class, array(
                'label' => 'compte.register.receiveAvertissement',
                'required' => false
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            "data_class" => "App\Entity\User\User",
            "validation_groups" => array("Registration")
        ));
    }

    public function getName()
    {
        return 'ninjatooken_user_registration';
    }
}