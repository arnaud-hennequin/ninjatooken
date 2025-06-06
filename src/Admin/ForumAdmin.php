<?php

namespace App\Admin;

use App\Entity\Forum\Forum;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @extends AbstractAdmin<Forum>
 */
class ForumAdmin extends AbstractAdmin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('clan', ModelListType::class, [
                'btn_add' => 'Add Clan',
                'btn_list' => 'List',
                'btn_delete' => false,
            ], [
                'placeholder' => 'No clan selected',
            ])
            ->add('old_id', IntegerType::class, [
                'label' => 'Ancien identifiant',
                'required' => false,
            ])
            ->add('ordre', IntegerType::class, [
                'label' => 'Position',
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
            ->add('nom')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('nom')
            ->addIdentifier('clan.nom')
            ->add('ordre')
            ->add('dateAjout')
        ;
    }

    protected function configureTabMenu(MenuItemInterface $menu, string $action, ?AdminInterface $childAdmin = null): void
    {
        if (!$childAdmin && !in_array($action, ['edit', 'show'])) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;
        $id = $admin->getRequest()->get('id');

        $menu->addChild(
            'Forum',
            $admin->generateMenuUrl('edit', ['id' => $id])
        );

        $menu->addChild(
            'Topics',
            $admin->generateMenuUrl('admin.thread.list', ['id' => $id])
        );
    }
}
