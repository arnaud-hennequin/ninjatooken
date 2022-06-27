<?php

namespace App\Form\Type;

use App\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, [
                'label' => 'compte.register.pseudo',
                'label_attr' => ['class' => 'libelle'],
                'error_bubbling' => true,
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'compte.register.motPasse',
                    'label_attr' => ['class' => 'libelle'],
                ],
                'second_options' => [
                    'label' => 'compte.register.motPasseRepeter',
                    'label_attr' => ['class' => 'libelle'],
                ],
                'invalid_message' => 'ninja_tooken_user.password.mismatch',
                'error_bubbling' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'compte.register.mail',
                'label_attr' => ['class' => 'libelle'],
                'error_bubbling' => true,
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'gender_male' => User::GENDER_MALE,
                    'gender_female' => User::GENDER_FEMALE,
                ],
                'data' => User::GENDER_MALE,
                'expanded' => true,
                'label_attr' => ['class' => 'libelle'],
            ])
            ->add('locale', ChoiceType::class, [
                'choices' => ['Français' => 'fr', 'English' => 'en'],
                'data' => 'fr',
                'expanded' => true,
                'label_attr' => ['class' => 'libelle'],
            ])
            ->add('receive_newsletter', CheckboxType::class, [
                'label' => 'compte.register.receiveNewsletter',
                'required' => false,
            ])
            ->add('receive_avertissement', CheckboxType::class, [
                'label' => 'compte.register.receiveAvertissement',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => "App\Entity\User\User",
        ]);
    }
}
