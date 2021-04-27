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
            $listMapper->add('clan', null, array('label' => 'Clan'));

        $listMapper
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
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        if(!$this->isChild())
            $formMapper->add('clan', ModelListType::class, array(
                'btn_add'       => 'Ajouter Clan',
                'btn_list'      => 'List',
                'btn_delete'    => false,
            ), array(
                'placeholder' => 'Pas de clan'
            ));

        $formMapper
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
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('id')
            ->add('postulant')
            ->add('dateAjout')
            ->add('dateChangementEtat')
            ->add('etat')
        ;
    }
}
