<?php

namespace App\Admin;

use App\Entity\Clan\ClanProposition;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

/**
 * @extends AbstractAdmin<ClanProposition>
 */
class ClanPropositionAdmin extends AbstractAdmin
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
            $list->add('recruteur', null, ['label' => 'Recruteur']);
        }

        $list
            ->add('postulant', null, ['label' => 'Postulant'])
            ->add('dateAjout', null, ['label' => 'Créé le'])
            ->add('dateChangementEtat', null, ['label' => 'Modifié le'])
            ->add('etat', null, ['label' => 'État'])
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
            $form->add('recruteur', ModelListType::class, [
                'label' => 'Recruteur',
                'btn_add' => 'Ajouter',
                'btn_list' => 'Sélectionner',
                'btn_delete' => false,
            ]);
        }

        $form
            ->add('postulant', ModelListType::class, [
                'label' => 'Postulant',
                'btn_add' => 'Ajouter',
                'btn_list' => 'Sélectionner',
                'btn_delete' => false,
            ])
            ->add('dateAjout', DateTimeType::class, [
                'required' => false,
                'label' => 'Créé le',
            ])
            ->add('dateChangementEtat', DateTimeType::class, [
                'required' => false,
                'label' => 'Modifié le',
            ])
            ->add('etat', ChoiceType::class, [
                'label' => 'État',
                'multiple' => false,
                'expanded' => false,
                'choices' => ['En attente' => 0, 'Accepté' => 1, 'Refusé' => 2],
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('recruteur')
            ->add('postulant')
            ->add('dateAjout')
            ->add('dateChangementEtat')
            ->add('etat')
        ;
    }
}
