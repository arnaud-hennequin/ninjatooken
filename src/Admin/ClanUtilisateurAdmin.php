<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class ClanUtilisateurAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $filter
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('droit')
            ->add('canEditClan')
        ;
    }

    /**
     * @param ListMapper $list
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id');

        if(!$this->isChild())
            $list->add('clan', null, array('label' => 'Clan'));

        $list
            ->add('recruteur', null, array('label' => 'Recruteur'))
            ->add('membre', null, array('label' => 'Membre'))
            ->add('droit', null, array('label' => 'Droit', 'editable' => true))
            ->add('canEditClan', null, array('label' => 'Peut éditer le clan', 'editable' => true))
            ->add('dateAjout', null, array('label' => 'Ajouté le'))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    /**
     * @param FormMapper $form
     */
    protected function configureFormFields(FormMapper $form): void
    {
        if(!$this->isChild()){
            $form->add('clan', ModelListType::class, array(
                'btn_add'       => 'Ajouter Clan',
                'btn_list'      => 'List',
                'btn_delete'    => false,
            ), array(
                'placeholder' => 'Pas de clan'
            ));
        }

        $form
            ->add('recruteur', ModelListType::class, array(
                'btn_add'       => 'Ajouter Utilisateur',
                'btn_list'      => 'List',
                'btn_delete'    => false,
            ), array(
                'placeholder' => 'Pas de recruteur'
            ))
            ->add('membre', ModelListType::class, array(
                'btn_add'       => 'Ajouter Utilisateur',
                'btn_list'      => 'List',
                'btn_delete'    => false,
            ), array(
                'placeholder' => 'Pas de membre'
            ))
            ->add('droit', ChoiceType::class, array(
                'label' => 'Droit',
                'multiple' => false,
                'expanded' => false,
                'choices'  => array('Shishō', 'Taishō', 'Jōnin', 'Chūnin')
            ))
            ->add('canEditClan', ChoiceType::class, array(
                'label' => 'Peut éditer le clan',
                'multiple' => false,
                'expanded' => true,
                'choices'  => array('Oui' => true, 'Non' => false)
            ))
            ->add('dateAjout', DateTimeType::class, array(
                'label' => 'Date de recrutement'
            ))
        ;
    }

    /**
     * @param ShowMapper $show
     */
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
