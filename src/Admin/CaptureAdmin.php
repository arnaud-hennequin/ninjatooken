<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class CaptureAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('id')
            ->add('url')
            ->add('urlTmb')
            ->add('deleteHash')
            ->add('dateAjout')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('id');

        if(!$this->isChild())
            $listMapper->add('user', null, array('label' => 'Utilisateur'));

        $listMapper
            ->add('url', null, array('label' => 'Url'))
            ->add('urlTmb', null, array('label' => 'Url de la vignette'))
            ->add('deleteHash', null, array('label' => 'Hash de suppression'))
            ->add('dateAjout', null, array('label' => 'Créé le'))
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
        // get the current instance
        $capture = $this->getSubject();
        $url = '';
        $thumb = '';
        if ($capture && $capture->getId() !== null) {
            $url = $capture->getUrl();
            $thumb = $capture->getUrlTmb();
        }

        if(!$this->isChild())
            $formMapper->add('user', ModelListType::class, array(
                'label'         => 'Utilisateur',
                'btn_add'       => 'Ajouter',
                'btn_list'      => 'Sélectionner',
                'btn_delete'    => false
            ));

        $formMapper
            ->add('url', TextType::class, array(
                'label' => 'Url',
                'help' => (!empty($url)?'<img src="'.$url.'" class="thumbnail"/>':'').''
            ))
            ->add('urlTmb', TextType::class, array(
                'label' => 'Url de la vignette',
                'help' => (!empty($thumb)?'<img src="'.$thumb.'" class="thumbnail"/>':'').''
            ))
            ->add('deleteHash', TextType::class, array(
                'label' => 'Hash de suppression'
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
            ->add('url')
            ->add('urlTmb')
            ->add('deleteHash')
            ->add('dateAjout')
        ;
    }
}
