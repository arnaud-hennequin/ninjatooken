<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Admin\AdminInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use     Symfony\Component\Form\Extension\Core\Type\UrlType;

class ClanAdmin extends AbstractAdmin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'dateAjout'
    );

    //Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $admin = $formMapper->getAdmin();
        $current = $admin->getSubject();

        $formMapper
            ->add('nom', TextType::class, array(
                'label' => 'Nom'
            ))
            ->add('tag', TextType::class, array(
                'label' => 'Tag',
                'required' => false
            ))
            ->add('old_id', IntegerType::class, array(
                'label' => 'Ancien identifiant',
                'required' => false
            ))
            ->add('accroche', TextType::class, array(
                'label' => 'Accroche',
                'required' => false
            ))
            ->add('description', TextareaType::class, array(
                'label' => 'Description',
                'required' => false,
                'attr' => array(
                    'class' => 'tinymce',
                    'tinymce'=>'{"theme":"simple"}'
                )
            ))
            ->add('url', UrlType::class, array(
                'label' => 'Url perso',
                'required' => false
            ))
            ->add('kamon', TextType::class, array(
                'label' => 'Kamon'
            ))
            ->add('dateAjout', DateTimeType::class, array(
                'label' => 'Date de création'
            ))
            ->add('online', ChoiceType::class, array(
                'label' => 'Afficher le clan',
                'multiple' => false,
                'expanded' => true,
                'choices'  => array('Oui' => true, 'Non' => false)
            ))
            ->add('isRecruting', ChoiceType::class, array(
                'label' => 'Le clan recrute',
                'multiple' => false,
                'expanded' => true,
                'choices'  => array('Oui' => true, 'Non' => false)
            ))
        ;
    }

    //Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('nom')
            ->add('tag')
        ;
    }

    //Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('nom')
            ->add('tag')
            ->add('online', null, array('editable' => true))
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
            'Clan',
            $admin->generateMenuUrl('edit', array('id' => $id))
        );

        $menu->addChild(
            'Forums',
            $admin->generateMenuUrl('admin.forum.list', array('id' => $id))
        );

        $menu->addChild(
            'Membres',
            $admin->generateMenuUrl('admin.clan_utilisateur.list', array('id' => $id))
        );

        $menu->addChild(
            'Postulations',
            $admin->generateMenuUrl('admin.clan_postulation.list', array('id' => $id))
        );

    }
}