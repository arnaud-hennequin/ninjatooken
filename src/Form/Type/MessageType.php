<?php

namespace App\Form\Type;

use App\Entity\User\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<Message>
 */
class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'label.sujet',
                'label_attr' => ['class' => 'libelle'],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'label.message',
                'label_attr' => ['class' => 'libelle'],
            ])
        ;
    }
}
