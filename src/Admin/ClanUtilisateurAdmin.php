<?php

namespace App\Admin;

use App\Entity\Clan\ClanUtilisateur;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

/**
 * @extends AbstractAdmin<ClanUtilisateur>
 */
class ClanUtilisateurAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('droit')
            ->add('canEditClan')
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
            ->add('recruteur', null, ['label' => 'Recruteur'])
            ->add('membre', null, ['label' => 'Membre'])
            ->add('droit', null, ['label' => 'Droit', 'editable' => true])
            ->add('canEditClan', null, ['label' => 'Peut éditer le clan', 'editable' => true])
            ->add('dateAjout', null, ['label' => 'Ajouté le'])
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
            ->add('recruteur', ModelListType::class, [
                'btn_add' => 'Ajouter Utilisateur',
                'btn_list' => 'List',
                'btn_delete' => false,
            ], [
                'placeholder' => 'Pas de recruteur',
            ])
            ->add('membre', ModelListType::class, [
                'btn_add' => 'Ajouter Utilisateur',
                'btn_list' => 'List',
                'btn_delete' => false,
            ], [
                'placeholder' => 'Pas de membre',
            ])
            ->add('droit', ChoiceType::class, [
                'label' => 'Droit',
                'multiple' => false,
                'expanded' => false,
                'choices' => ['Shishō' => 0, 'Taishō' => 1, 'Jōnin' => 2, 'Chūnin' => 3],
            ])
            ->add('canEditClan', ChoiceType::class, [
                'label' => 'Peut éditer le clan',
                'multiple' => false,
                'expanded' => true,
                'choices' => ['Oui' => true, 'Non' => false],
            ])
            ->add('dateAjout', DateTimeType::class, [
                'label' => 'Date de recrutement',
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('droit')
            ->add('canEditClan')
            ->add('dateAjout')
        ;
    }
}
