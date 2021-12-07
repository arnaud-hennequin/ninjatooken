<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class ThreadAdmin extends AbstractAdmin
{

    protected array $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'dateAjout'
    );

    //Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('General')
                ->add('nom', TextType::class, array(
                    'label' => 'Nom'
                ));

        if(!$this->isChild())
            $form->add('forum', ModelListType::class, array(
                    'btn_add'       => 'Add forum',
                    'btn_list'      => 'List',
                    'btn_delete'    => false,
                ), array(
                    'placeholder' => 'No forum selected'
                ));

        $form
                ->add('author', ModelListType::class, array(
                    'btn_add'       => 'Add author',
                    'btn_list'      => 'List',
                    'btn_delete'    => false,
                ), array(
                    'placeholder' => 'No author selected'
                ))
                ->add('body', TextareaType::class, array(
                    'label' => 'Contenu',
                    'attr' => array(
                        'class' => 'tinymce',
                        'tinymce'=>'{"theme":"simple"}'
                    )
                ))
                ->add('old_id', IntegerType::class, array(
                    'label' => 'Ancien identifiant',
                    'required' => false
                ))
                ->add('isPostit', ChoiceType::class, array(
                    'label' => 'Afficher en postit',
                    'multiple' => false,
                    'expanded' => true,
                    'choices'  => array('Oui' => true, 'Non' => false)
                ))
                ->add('isCommentable', ChoiceType::class, array(
                    'label' => 'Verrouillé',
                    'multiple' => false,
                    'expanded' => true,
                    'choices'  => array('Oui' => true, 'Non' => false)
                ))
                ->add('isEvent', ChoiceType::class, array(
                    'label' => 'Event',
                    'multiple' => false,
                    'expanded' => true,
                    'choices'  => array('Oui' => true, 'Non' => false)
                ))
                ->add('dateEventStart', DateTimeType::class, array(
                    'label' => 'Début de l\'event',
                    'required' => false
                ))
                ->add('dateEventEnd', DateTimeType::class, array(
                    'label' => 'Fin de l\'event',
                    'required' => false
                ))
                ->add('urlVideo', UrlType::class, array(
                    'label' => 'url de la vidéo',
                    'required' => false
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
            ->add('nom')
        ;
    }

    //Fields to be shown on lists
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('nom')
            ->add('author.username');

        if(!$this->isChild())
            $list->add('forum.nom');

        $list
            ->add('isCommentable', null, array('editable' => true))
            ->add('isPostit', null, array('editable' => true))
            ->add('isEvent', null, array('editable' => true))
            ->add('dateAjout')
        ;
    }

    /**
    * {@inheritdoc}
    */
    protected function configureTabMenu(MenuItemInterface $menu, string $action, ?AdminInterface $childAdmin = null): void
    {
        if (!$childAdmin && !in_array($action, ['edit', 'show'])) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        $id = $admin->getRequest()->get('id');

        $menu->addChild(
            'Topic',
            $admin->generateMenuUrl('edit', array('id' => $id))
        );

        $menu->addChild(
            'Commentaires',
            $admin->generateMenuUrl('admin.comment.list', array('id' => $id))
        );

    }
}