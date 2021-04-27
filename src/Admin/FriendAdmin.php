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
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('dateAjout')
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
            $listMapper->add('user', null, array('label' => 'Utilisateur'));

        $listMapper
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
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        if(!$this->isChild())
            $formMapper->add('user', ModelListType::class, array(
                'label'         => 'Utilisateur',
                'btn_add'       => 'Ajouter',
                'btn_list'      => 'Sélectionner',
                'btn_delete'    => false
            ));

        $formMapper
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
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('id')
            ->add('user')
            ->add('friend')
            ->add('isBlocked')
            ->add('isConfirmed')
            ->add('dateAjout')
        ;
    }
}
