<?php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class ClanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $kamonChoices = array();
        for($i=1;$i<135;$i++){
            $num = substr('0000'.$i,-4);
            $kamonChoices['kamon'.$num] = '/kamon/kamon'.$num.'.png';
        }
        $builder
            ->add('nom', TextType::class, array(
                'label' => 'label.nom',
                'label_attr' => array('class' => 'libelle')
            ))
            ->add('tag', TextType::class, array(
                'label' => 'label.tag',
                'label_attr' => array('class' => 'libelle'),
                'required' => false
            ))
            ->add('accroche', TextType::class, array(
                'label' => 'label.accroche',
                'label_attr' => array('class' => 'libelle'),
                'required' => false
            ))
            ->add('description', TextareaType::class, array(
                'label' => 'label.description',
                'label_attr' => array('class' => 'libelle')
            ))
            ->add('url', UrlType::class, array(
                'label' => 'label.url',
                'label_attr' => array('class' => 'libelle'),
                'required' => false
            ))
            ->add('kamon', ChoiceType::class, array(
                'label' => 'label.kamon',
                'label_attr' => array('class' => 'libelle'),
                'multiple' => false,
                'choices'  => $kamonChoices,
                'data' => (isset($options['data']) && $options['data']->getKamon() !== null) ? $options['data']->getKamon() : key($kamonChoices)
            ))
            ->add('kamonUpload', FileType::class, array(
                'label' => 'label.kamonUpload',
                'label_attr' => array('class' => 'libelle'),
                'data_class' => null,
                'required' => false
            ))
            ->add('isRecruting', ChoiceType::class, array(
                'label' => 'label.isRecruting',
                'label_attr' => array('class' => 'libelle'),
                'multiple' => false,
                'expanded' => true,
                'choices'  => array('label.oui' => true, 'label.non' => false)
            ));
    }
}