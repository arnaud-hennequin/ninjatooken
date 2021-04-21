<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ClanPropositionAdmin extends AbstractAdmin
{
    protected $parentAssociationMapping = 'recruteur';

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('dateAjout')
            ->add('dateChangementEtat')
            ->add('etat')
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
            $listMapper->add('recruteur', null, array('label' => 'Recruteur'));

        $listMapper
            ->add('postulant', null, array('label' => 'Postulant'))
            ->add('dateAjout', null, array('label' => 'Créé le'))
            ->add('dateChangementEtat', null, array('label' => 'Modifié le'))
            ->add('etat', null, array('label' => 'État'))
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
        if(!$this->isChild())
            $formMapper->add('recruteur', 'sonata_type_model_list', array(
                'label'         => 'Recruteur',
                'btn_add'       => 'Ajouter',
                'btn_list'      => 'Sélectionner',
                'btn_delete'    => false
            ));

        $formMapper
            ->add('postulant', 'sonata_type_model_list', array(
                'label'         => 'Postulant',
                'btn_add'       => 'Ajouter',
                'btn_list'      => 'Sélectionner',
                'btn_delete'    => false
            ))
            ->add('dateAjout', 'datetime', array(
                'required' => false,
                'label' => 'Créé le'
            ))
            ->add('dateChangementEtat', 'datetime', array(
                'required' => false,
                'label' => 'Modifié le'
            ))
            ->add('etat', 'choice', array(
                'label' => 'État',
                'multiple' => false,
                'expanded' => false,
                'choices'  => array('En attente', 'Accepté', 'Refusé')
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
            ->add('recruteur')
            ->add('postulant')
            ->add('dateAjout')
            ->add('dateChangementEtat')
            ->add('etat')
        ;
    }
}
