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
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'dateAjout'
    );

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('nom', null, array('label' => 'Nom'))
            ->add('content', null, array('label' => 'Contenu'))
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('nom', null, array('label' => 'Nom'));

        if(!$this->isChild())
            $listMapper->add('author', null, array('label' => 'Auteur'));

        $listMapper
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
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        if(!$this->isChild())
            $formMapper->add('author', ModelListType::class, array(
                'label'         => 'Auteur',
                'btn_add'       => 'Ajouter',
                'btn_list'      => 'Sélectionner',
                'btn_delete'    => false
            ));

        $formMapper
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
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
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
