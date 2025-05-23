<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommentAdmin extends AbstractAdmin
{
    protected array $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by' => 'dateAjout',
    ];

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('General');

        if (!$this->isChild()) {
            $form->add('thread', ModelListType::class, [
                'btn_add' => 'Add thread',
                'btn_list' => 'List',
                'btn_delete' => false,
            ], [
                'placeholder' => 'No thread selected',
            ]);
        }

        $form
                ->add('author', ModelListType::class, [
                    'btn_add' => 'Add author',
                    'btn_list' => 'List',
                    'btn_delete' => false,
                ], [
                    'placeholder' => 'No author selected',
                ])
                ->add('body', TextareaType::class, [
                    'label' => 'Contenu',
                    'attr' => [
                        'class' => 'tinymce',
                        'tinymce' => '{"theme":"simple"}',
                    ],
                ])
                ->add('dateAjout', DateTimeType::class, [
                    'label' => 'Date de création',
                ])
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('body')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id');

        if (!$this->isChild()) {
            $list->add('thread', null, ['label' => 'Topic']);
        }

        $list
            ->add('author', null, ['label' => 'Auteur'])
            ->add('dateAjout', null, ['label' => 'Créé le'])
        ;
    }
}
