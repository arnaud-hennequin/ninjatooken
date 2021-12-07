<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class MessageAdmin extends AbstractAdmin
{
    protected array $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'dateAjout'
    );

    /**
     * @param DatagridMapper $filter
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('nom', null, array('label' => 'Nom'))
            ->add('content', null, array('label' => 'Contenu'))
        ;
    }

    /**
     * @param ListMapper $list
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('nom', null, array('label' => 'Nom'));

        if(!$this->isChild())
            $list->add('author', null, array('label' => 'Auteur'));

        $list
            ->add('dateAjout', null, array('label' => 'Créé le'))
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
        if(!$this->isChild())
            $form->add('author', ModelListType::class, array(
                'label'         => 'Auteur',
                'btn_add'       => 'Ajouter',
                'btn_list'      => 'Sélectionner',
                'btn_delete'    => false
            ));

        $form
            ->add('nom', TextType::class, array(
                'label' => 'Nom'
            ))
            ->add('content', TextareaType::class, array(
                'label' => 'Contenu',
                'attr' => array(
                    'class' => 'tinymce',
                    'tinymce'=>'{"theme":"simple"}'
                )
            ))
            ->add('old_id', TextType::class, array(
                'required' => false,
                'label' => 'Ancien identifiant'
            ))
            ->add('hasDeleted', ChoiceType::class, array(
                'label' => 'Supprimé ?',
                'multiple' => false,
                'expanded' => true,
                'choices'  => array('Oui' => true, 'Non' => false)
            ))
            ->add('receivers', CollectionType::class, array(
                'type_options' => array('delete' => false),
                'by_reference' => false,
                'label' => 'Destinataires'
            ), array(
                'edit' => 'inline',
                'inline' => 'table'
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
            ->add('old_id')
            ->add('author')
            ->add('nom')
            ->add('content')
            ->add('dateAjout')
            ->add('hasDeleted')
        ;
    }
}
