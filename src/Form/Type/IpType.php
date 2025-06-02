<?php

namespace App\Form\Type;

use App\Entity\User\Ip;
use App\Form\DataTransformer\IpToLongTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Ip>
 */
class IpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $transformer = new IpToLongTransformer();
        $builder->addViewTransformer($transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'invalid_message' => 'The selected issue does not exist',
        ]);
    }
}
