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

class ClanPropositionAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $filter
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('dateAjout')
            ->add('dateChangementEtat')
            ->add('etat')
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
            $list->add('recruteur', null, array('label' => 'Recruteur'));

        $list
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
     * @param FormMapper $form
     */
    protected function configureFormFields(FormMapper $form): void
    {
        if(!$this->isChild())
            $form->add('recruteur', ModelListType::class, array(
                'label'         => 'Recruteur',
                'btn_add'       => 'Ajouter',
                'btn_list'      => 'Sélectionner',
                'btn_delete'    => false
            ));

        $form
            ->add('postulant', ModelListType::class, array(
                'label'         => 'Postulant',
                'btn_add'       => 'Ajouter',
                'btn_list'      => 'Sélectionner',
                'btn_delete'    => false
            ))
            ->add('dateAjout', DateTimeType::class, array(
                'required' => false,
                'label' => 'Créé le'
            ))
            ->add('dateChangementEtat', DateTimeType::class, array(
                'required' => false,
                'label' => 'Modifié le'
            ))
            ->add('etat', ChoiceType::class, array(
                'label' => 'État',
                'multiple' => false,
                'expanded' => false,
                'choices'  => array('En attente', 'Accepté', 'Refusé')
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
            ->add('recruteur')
            ->add('postulant')
            ->add('dateAjout')
            ->add('dateChangementEtat')
            ->add('etat')
        ;
    }
}
