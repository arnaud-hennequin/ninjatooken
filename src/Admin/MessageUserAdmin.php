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

class MessageUserAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $filter
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('destinataire')
            ->add('dateRead')
            ->add('hasDeleted')
        ;
    }

    /**
     * @param ListMapper $list
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, array('label' => 'Id'))
            ->add('destinataire', null, array('label' => 'Destinataire'))
            ->add('dateRead', null, array('label' => 'Lu le'))
            ->add('hasDeleted', null, array('editable' => true, 'label' => 'Supprimé ?'))
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
        $form
            ->add('destinataire', ModelListType::class, array(
                'label'         => 'Destinataire',
                'btn_add'       => 'Ajouter',
                'btn_list'      => 'Sélectionner',
                'btn_delete'    => false
            ))
            ->add('dateRead', DateTimeType::class, array(
                'required' => false,
                'label' => 'Lu le'
            ))
            ->add('hasDeleted', ChoiceType::class, array(
                'label' => 'Supprimé ?',
                'multiple' => false,
                'expanded' => true,
                'choices'  => array('Oui' => true, 'Non' => false)
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
            ->add('destinataire')
            ->add('dateRead')
            ->add('hasDeleted')
        ;
    }
}
