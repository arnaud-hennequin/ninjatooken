<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class MessageUserAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('destinataire')
            ->add('dateRead')
            ->add('hasDeleted')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, ['label' => 'Id'])
            ->add('destinataire', null, ['label' => 'Destinataire'])
            ->add('dateRead', null, ['label' => 'Lu le'])
            ->add('hasDeleted', null, ['editable' => true, 'label' => 'Supprimé ?'])
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('destinataire', ModelListType::class, [
                'label' => 'Destinataire',
                'btn_add' => 'Ajouter',
                'btn_list' => 'Sélectionner',
                'btn_delete' => false,
            ])
            ->add('dateRead', DateTimeType::class, [
                'required' => false,
                'label' => 'Lu le',
            ])
            ->add('hasDeleted', ChoiceType::class, [
                'label' => 'Supprimé ?',
                'multiple' => false,
                'expanded' => true,
                'choices' => ['Oui' => true, 'Non' => false],
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('destinataire')
            ->add('dateRead')
            ->add('hasDeleted')
        ;
    }
}
