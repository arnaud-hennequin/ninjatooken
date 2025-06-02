<?php

namespace App\Form\Type;

use App\Entity\Forum\Thread;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<Thread>
 */
class ThreadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'label.nom',
                'label_attr' => ['class' => 'libelle'],
            ])
            ->add('body', TextareaType::class, [
                'label' => 'label.body',
                'label_attr' => ['class' => 'libelle'],
            ]);
    }
}
