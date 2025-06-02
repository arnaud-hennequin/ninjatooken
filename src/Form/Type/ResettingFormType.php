<?php

namespace App\Form\Type;

use App\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<User>
 */
class ResettingFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('new', RepeatedType::class, [
            'type' => PasswordType::class,
            'first_options' => [
                'label' => 'resetting.new_password',
                'label_attr' => ['class' => 'libelle'],
            ],
            'second_options' => [
                'label' => 'resetting.new_password_confirmation',
                'label_attr' => ['class' => 'libelle'],
            ],
            'invalid_message' => 'resetting.mismatch',
        ]);
    }
}
