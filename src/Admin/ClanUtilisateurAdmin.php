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
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('droit')
            ->add('canEditClan')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('id');

        if(!$this->isChild())
            $listMapper->add('clan', null, array('label' => 'Clan'));

        $listMapper
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
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        if(!$this->isChild()){
            $formMapper->add('clan', ModelListType::class, array(
                'btn_add'       => 'Ajouter Clan',
                'btn_list'      => 'List',
                'btn_delete'    => false,
            ), array(
                'placeholder' => 'Pas de clan'
            ));
        }

        $formMapper
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
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('id')
            ->add('droit')
            ->add('canEditClan')
            ->add('dateAjout')
        ;
    }
}
