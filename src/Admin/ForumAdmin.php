<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ForumAdmin extends AbstractAdmin
{
    protected array $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'dateAjout'
    );

    //Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('nom', TextType::class, array(
                'label' => 'Nom'
            ))
            ->add('clan', ModelListType::class, array(
                'btn_add'       => 'Add Clan',
                'btn_list'      => 'List',
                'btn_delete'    => false,
            ), array(
                'placeholder' => 'No clan selected'
            ))
            ->add('old_id', IntegerType::class, array(
                'label' => 'Ancien identifiant',
                'required' => false
            ))
            ->add('ordre', IntegerType::class, array(
                'label' => 'Position'
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
            ->addIdentifier('clan.nom')
            ->add('ordre')
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
            'Forum',
            $admin->generateMenuUrl('edit', array('id' => $id))
        );

        $menu->addChild(
            'Topics',
            $admin->generateMenuUrl('admin.thread.list', array('id' => $id))
        );

    }
}