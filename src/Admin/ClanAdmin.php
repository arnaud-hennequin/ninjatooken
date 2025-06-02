<?php

namespace App\Admin;

use App\Entity\Clan\Clan;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

/**
 * @extends AbstractAdmin<Clan>
 */
class ClanAdmin extends AbstractAdmin
{
    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('tag', TextType::class, [
                'label' => 'Tag',
                'required' => false,
            ])
            ->add('old_id', IntegerType::class, [
                'label' => 'Ancien identifiant',
                'required' => false,
            ])
            ->add('accroche', TextType::class, [
                'label' => 'Accroche',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'tinymce',
                    'tinymce' => '{"theme":"simple"}',
                ],
            ])
            ->add('url', UrlType::class, [
                'label' => 'Url perso',
                'required' => false,
            ])
            ->add('kamon', TextType::class, [
                'label' => 'Kamon',
            ])
            ->add('dateAjout', DateTimeType::class, [
                'label' => 'Date de création',
            ])
            ->add('online', ChoiceType::class, [
                'label' => 'Afficher le clan',
                'multiple' => false,
                'expanded' => true,
                'choices' => ['Oui' => true, 'Non' => false],
            ])
            ->add('isRecruting', ChoiceType::class, [
                'label' => 'Le clan recrute',
                'multiple' => false,
                'expanded' => true,
                'choices' => ['Oui' => true, 'Non' => false],
            ])
        ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('nom')
            ->add('tag')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('nom')
            ->add('tag')
            ->add('online', null, ['editable' => true])
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
            'Clan',
            $admin->generateMenuUrl('edit', ['id' => $id])
        );

        $menu->addChild(
            'Forums',
            $admin->generateMenuUrl('admin.forum.list', ['id' => $id])
        );

        $menu->addChild(
            'Membres',
            $admin->generateMenuUrl('admin.clan_utilisateur.list', ['id' => $id])
        );

        $menu->addChild(
            'Postulations',
            $admin->generateMenuUrl('admin.clan_postulation.list', ['id' => $id])
        );
    }
}
