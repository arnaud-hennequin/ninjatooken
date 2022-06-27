<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class ClanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $kamonChoices = [];
        for ($i = 1; $i < 135; ++$i) {
            $num = substr('0000'.$i, -4);
            $kamonChoices['kamon'.$num] = '/kamon/kamon'.$num.'.png';
        }
        $builder
            ->add('nom', TextType::class, [
                'label' => 'label.nom',
                'label_attr' => ['class' => 'libelle'],
            ])
            ->add('tag', TextType::class, [
                'label' => 'label.tag',
                'label_attr' => ['class' => 'libelle'],
                'required' => false,
            ])
            ->add('accroche', TextType::class, [
                'label' => 'label.accroche',
                'label_attr' => ['class' => 'libelle'],
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'label.description',
                'label_attr' => ['class' => 'libelle'],
            ])
            ->add('url', UrlType::class, [
                'label' => 'label.url',
                'label_attr' => ['class' => 'libelle'],
                'required' => false,
            ])
            ->add('kamon', ChoiceType::class, [
                'label' => 'label.kamon',
                'label_attr' => ['class' => 'libelle'],
                'multiple' => false,
                'choices' => $kamonChoices,
                'data' => isset($options['data']) ? $options['data']->getKamon() ?? key($kamonChoices) : key($kamonChoices),
            ])
            ->add('kamonUpload', FileType::class, [
                'label' => 'label.kamonUpload',
                'label_attr' => ['class' => 'libelle'],
                'data_class' => null,
                'required' => false,
            ])
            ->add('isRecruting', ChoiceType::class, [
                'label' => 'label.isRecruting',
                'label_attr' => ['class' => 'libelle'],
                'multiple' => false,
                'expanded' => true,
                'choices' => ['label.oui' => true, 'label.non' => false],
            ]);
    }
}
