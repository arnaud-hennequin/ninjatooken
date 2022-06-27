<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MessageAdmin extends AbstractAdmin
{
    protected array $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by' => 'dateAjout',
    ];

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('nom', null, ['label' => 'Nom'])
            ->add('content', null, ['label' => 'Contenu'])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('nom', null, ['label' => 'Nom']);

        if (!$this->isChild()) {
            $list->add('author', null, ['label' => 'Auteur']);
        }

        $list
            ->add('dateAjout', null, ['label' => 'Créé le'])
            ->add('hasDeleted', null, ['editable' => true, 'label' => 'Supprimé ?'])
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        if (!$this->isChild()) {
            $form->add('author', ModelListType::class, [
                'label' => 'Auteur',
                'btn_add' => 'Ajouter',
                'btn_list' => 'Sélectionner',
                'btn_delete' => false,
            ]);
        }

        $form
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => [
                    'class' => 'tinymce',
                    'tinymce' => '{"theme":"simple"}',
                ],
            ])
            ->add('old_id', TextType::class, [
                'required' => false,
                'label' => 'Ancien identifiant',
            ])
            ->add('hasDeleted', ChoiceType::class, [
                'label' => 'Supprimé ?',
                'multiple' => false,
                'expanded' => true,
                'choices' => ['Oui' => true, 'Non' => false],
            ])
            ->add('receivers', CollectionType::class, [
                'type_options' => ['delete' => false],
                'by_reference' => false,
                'label' => 'Destinataires',
            ], [
                'edit' => 'inline',
                'inline' => 'table',
            ])
            ->add('dateAjout', DateTimeType::class, [
                'required' => false,
                'label' => 'Créé le',
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('old_id')
            ->add('author')
            ->add('nom')
            ->add('content')
            ->add('dateAjout')
            ->add('hasDeleted')
        ;
    }
}
