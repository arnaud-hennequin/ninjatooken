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

class FriendAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('dateAjout')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id');

        if (!$this->isChild()) {
            $list->add('user', null, ['label' => 'Utilisateur']);
        }

        $list
            ->add('friend', null, ['label' => 'Ami'])
            ->add('isBlocked', null, ['editable' => true, 'label' => 'Bloqué'])
            ->add('isConfirmed', null, ['editable' => true, 'label' => 'Confirmé'])
            ->add('dateAjout', null, ['label' => 'Créé le'])
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
        if (!$this->isChild()) {
            $form->add('user', ModelListType::class, [
                'label' => 'Utilisateur',
                'btn_add' => 'Ajouter',
                'btn_list' => 'Sélectionner',
                'btn_delete' => false,
            ]);
        }

        $form
            ->add('friend', ModelListType::class, [
                'label' => 'Ami',
                'btn_add' => 'Ajouter',
                'btn_list' => 'Sélectionner',
                'btn_delete' => false,
            ])
            ->add('isBlocked', ChoiceType::class, [
                'label' => 'Bloqué ?',
                'multiple' => false,
                'expanded' => true,
                'choices' => ['Oui' => true, 'Non' => false],
            ])
            ->add('isConfirmed', ChoiceType::class, [
                'label' => 'Confirmé ?',
                'multiple' => false,
                'expanded' => true,
                'choices' => ['Oui' => true, 'Non' => false],
            ])
            ->add('dateAjout', DateTimeType::class, [
                'required' => false,
                'label' => 'Créé le',
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('user')
            ->add('friend')
            ->add('isBlocked')
            ->add('isConfirmed')
            ->add('dateAjout')
        ;
    }
}
