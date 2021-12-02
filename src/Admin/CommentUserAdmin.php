<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class CommentUserAdmin extends AbstractAdmin
{

    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'dateAjout'
    );

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $matches = array('ninjatooken', 'user', 'comment');

        if ($this->isChild()) { // the admin class is a child, prefix it with the parent route name
            $this->baseRoutePattern = sprintf('%s/{id}/%s',
                $this->getParent()->getBaseRoutePattern(),
                $this->urlize($matches[2], '-')
            );
            $this->baseRouteName = sprintf('%s_%s',
                $this->getParent()->getBaseRouteName(),
                $this->urlize($matches[2])
            );
        } else {

            $this->baseRoutePattern = sprintf('/%s/%s/%s',
                $this->urlize($matches[0], '-'),
                $this->urlize($matches[1], '-'),
                $this->urlize($matches[2], '-')
            );
            $this->baseRouteName = sprintf('admin_%s_%s_%s',
                $this->urlize($matches[0]),
                $this->urlize($matches[1]),
                $this->urlize($matches[2])
            );
        }
    }

    //Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('General')
            ->add('thread', ModelListType::class, array(
                'btn_add'       => 'Add thread',
                'btn_list'      => 'List',
                'btn_delete'    => false,
            ), array(
                'placeholder' => 'No thread selected'
            ));

        if(!$this->isChild())
            $form->add('author', ModelListType::class, array(
                    'btn_add'       => 'Add author',
                    'btn_list'      => 'List',
                    'btn_delete'    => false,
                ), array(
                    'placeholder' => 'No author selected'
                ));

        $form
                ->add('body', TextareaType::class, array(
                    'label' => 'Contenu',
                    'attr' => array(
                        'class' => 'tinymce',
                        'tinymce'=>'{"theme":"simple"}'
                    )
                ))
                ->add('dateAjout', DateTimeType::class, array(
                    'label' => 'Date de création'
                ))
        ;
    }

    //Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('body')
        ;
    }

    //Fields to be shown on lists
    protected function configureListFields(ListMapper $list): void
    {

        $list
            ->addIdentifier('id')
            ->add('thread', null, array('label' => 'Topic'));

        if(!$this->isChild())
            $list->add('author', null, array('label' => 'Auteur'));

        $list
            ->add('dateAjout', null, array('label' => 'Créé le'))
        ;
    }
}