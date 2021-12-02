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

class FriendAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $filter
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('dateAjout')
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
            $list->add('user', null, array('label' => 'Utilisateur'));

        $list
            ->add('friend', null, array('label' => 'Ami'))
            ->add('isBlocked', null, array('editable' => true, 'label' => 'Bloqué'))
            ->add('isConfirmed', null, array('editable' => true, 'label' => 'Confirmé'))
            ->add('dateAjout', null, array('label' => 'Créé le'))
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
            $form->add('user', ModelListType::class, array(
                'label'         => 'Utilisateur',
                'btn_add'       => 'Ajouter',
                'btn_list'      => 'Sélectionner',
                'btn_delete'    => false
            ));

        $form
            ->add('friend', ModelListType::class, array(
                'label'         => 'Ami',
                'btn_add'       => 'Ajouter',
                'btn_list'      => 'Sélectionner',
                'btn_delete'    => false
            ))
            ->add('isBlocked', ChoiceType::class, array(
                'label' => 'Bloqué ?',
                'multiple' => false,
                'expanded' => true,
                'choices'  => array('Oui' => true, 'Non' => false)
            ))
            ->add('isConfirmed', ChoiceType::class, array(
                'label' => 'Confirmé ?',
                'multiple' => false,
                'expanded' => true,
                'choices'  => array('Oui' => true, 'Non' => false)
            ))
            ->add('dateAjout', DateTimeType::class, array(
                'required' => false,
                'label' => 'Créé le'
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
            ->add('user')
            ->add('friend')
            ->add('isBlocked')
            ->add('isConfirmed')
            ->add('dateAjout')
        ;
    }
}
