<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class IpAdmin extends AbstractAdmin
{

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('delete')
            ->remove('create')
            ->remove('edit')
        ;
    }

    /**
     * @param DatagridMapper $filter
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('ip')
            ->add('createdAt')
            ->add('updatedAt')
        ;
    }

    /**
     * @param ListMapper $list
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('ip', null, array('label' => 'IP'))
            ->add('createdAt', null, array('label' => 'Créé le'))
            ->add('updatedAt', null, array('label' => 'Mis à jour le'))
        ;
    }

    /**
     * @param FormMapper $form
     */
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('ip', 'ip', array(
                'label' => 'IP'
            ))
            ->add('createdAt', DateTimeType::class, array(
                'label' => 'Créé le'
            ))
            ->add('updatedAt', DateTimeType::class, array(
                'label' => 'Mis à jour le'
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
            ->add('ip')
            ->add('createdAt')
            ->add('updatedAt')
        ;
    }
}
