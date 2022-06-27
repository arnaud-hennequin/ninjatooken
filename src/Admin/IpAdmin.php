<?php

namespace App\Admin;

use App\Form\Type\IpType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

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

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('ip')
            ->add('createdAt')
            ->add('updatedAt')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('ip', null, ['label' => 'IP'])
            ->add('createdAt', null, ['label' => 'Créé le'])
            ->add('updatedAt', null, ['label' => 'Mis à jour le'])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('ip', IpType::class, [
                'label' => 'IP',
            ])
            ->add('createdAt', DateTimeType::class, [
                'label' => 'Créé le',
            ])
            ->add('updatedAt', DateTimeType::class, [
                'label' => 'Mis à jour le',
            ])
        ;
    }

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
