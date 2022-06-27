<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CaptureAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('url')
            ->add('urlTmb')
            ->add('deleteHash')
            ->add('dateAjout')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id');

        if (!$this->isChild()) {
            $list->add('user', null, ['label' => 'Utilisateur']);
        }

        $list
            ->add('url', null, ['label' => 'Url'])
            ->add('urlTmb', null, ['label' => 'Url de la vignette'])
            ->add('deleteHash', null, ['label' => 'Hash de suppression'])
            ->add('dateAjout', null, ['label' => 'Créé le'])
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
        // get the current instance
        $capture = $this->getSubject();
        $url = '';
        $thumb = '';
        if (null !== $capture->getId()) {
            $url = $capture->getUrl();
            $thumb = $capture->getUrlTmb();
        }

        if (!$this->isChild()) {
            $form->add('user', ModelListType::class, [
                'label' => 'Utilisateur',
                'btn_add' => 'Ajouter',
                'btn_list' => 'Sélectionner',
                'btn_delete' => false,
            ]);
        }

        $form
            ->add('url', TextType::class, [
                'label' => 'Url',
                'help' => (!empty($url) ? '<img src="'.$url.'" class="thumbnail"/>' : '').'',
            ])
            ->add('urlTmb', TextType::class, [
                'label' => 'Url de la vignette',
                'help' => (!empty($thumb) ? '<img src="'.$thumb.'" class="thumbnail"/>' : '').'',
            ])
            ->add('deleteHash', TextType::class, [
                'label' => 'Hash de suppression',
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
            ->add('url')
            ->add('urlTmb')
            ->add('deleteHash')
            ->add('dateAjout')
        ;
    }
}
