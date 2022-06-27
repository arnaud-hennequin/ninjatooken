<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class ClanPostulationAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('dateAjout')
            ->add('dateChangementEtat')
            ->add('etat')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id');

        if (!$this->isChild()) {
            $list->add('clan', null, ['label' => 'Clan']);
        }

        $list
            ->add('postulant', null, ['label' => 'Postulant'])
            ->add('dateAjout', null, ['label' => 'Ajouté le'])
            ->add('dateChangementEtat', null, ['label' => 'Modifié le'])
            ->add('etat', null, ['label' => 'État', 'editable' => true])
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
            $form->add('clan', ModelListType::class, [
                'btn_add' => 'Ajouter Clan',
                'btn_list' => 'List',
                'btn_delete' => false,
            ], [
                'placeholder' => 'Pas de clan',
            ]);
        }

        $form
            ->add('postulant', ModelListType::class, [
                'btn_add' => 'Ajouter Utilisateur',
                'btn_list' => 'List',
                'btn_delete' => false,
            ], [
                'placeholder' => 'Pas de postulant',
            ])
            ->add('dateAjout', DateTimeType::class, [
                'label' => 'Ajouté le',
            ])
            ->add('dateChangementEtat', DateTimeType::class, [
                'label' => 'Modifié le',
            ])
            ->add('etat')
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('postulant')
            ->add('dateAjout')
            ->add('dateChangementEtat')
            ->add('etat')
        ;
    }
}
