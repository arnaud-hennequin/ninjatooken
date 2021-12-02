<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class ClanPostulationAdmin extends AbstractAdmin
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
            $list->add('clan', null, array('label' => 'Clan'));

        $list
            ->add('postulant', null, array('label' => 'Postulant'))
            ->add('dateAjout', null, array('label' => 'Ajouté le'))
            ->add('dateChangementEtat', null, array('label' => 'Modifié le'))
            ->add('etat', null, array('label' => 'État', 'editable' => true))
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
            $form->add('clan', ModelListType::class, array(
                'btn_add'       => 'Ajouter Clan',
                'btn_list'      => 'List',
                'btn_delete'    => false,
            ), array(
                'placeholder' => 'Pas de clan'
            ));

        $form
            ->add('postulant', ModelListType::class, array(
                'btn_add'       => 'Ajouter Utilisateur',
                'btn_list'      => 'List',
                'btn_delete'    => false,
            ), array(
                'placeholder' => 'Pas de postulant'
            ))
            ->add('dateAjout', DateTimeType::class, array(
                'label' => 'Ajouté le'
            ))
            ->add('dateChangementEtat', DateTimeType::class, array(
                'label' => 'Modifié le'
            ))
            ->add('etat')
        ;
    }

    /**
     * @param ShowMapper $show
     */
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
