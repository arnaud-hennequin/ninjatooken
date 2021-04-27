<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class NinjaAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('id')
            ->add('aptitudeForce')
            ->add('aptitudeVitesse')
            ->add('aptitudeVie')
            ->add('aptitudeChakra')
            ->add('jutsuBoule')
            ->add('jutsuDoubleSaut')
            ->add('jutsuBouclier')
            ->add('jutsuMarcherMur')
            ->add('jutsuDeflagration')
            ->add('jutsuMarcherEau')
            ->add('jutsuMetamorphose')
            ->add('jutsuMultishoot')
            ->add('jutsuInvisibilite')
            ->add('jutsuResistanceExplosion')
            ->add('jutsuPhoenix')
            ->add('jutsuVague')
            ->add('jutsuPieux')
            ->add('jutsuTeleportation')
            ->add('jutsuTornade')
            ->add('jutsuKusanagi')
            ->add('jutsuAcierRenforce')
            ->add('jutsuChakraVie')
            ->add('jutsuFujin')
            ->add('jutsuRaijin')
            ->add('jutsuSarutahiko')
            ->add('jutsuSusanoo')
            ->add('jutsuKagutsuchi')
            ->add('grade')
            ->add('experience')
            ->add('classe')
            ->add('masque')
            ->add('masqueCouleur')
            ->add('masqueDetail')
            ->add('costume')
            ->add('costumeCouleur')
            ->add('costumeDetail')
            ->add('missionAssassinnat')
            ->add('missionCourse')
            ->add('accomplissement')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('id')
            ->add('aptitudeForce')
            ->add('aptitudeVitesse')
            ->add('aptitudeVie')
            ->add('aptitudeChakra')
            ->add('jutsuBoule')
            ->add('jutsuDoubleSaut')
            ->add('jutsuBouclier')
            ->add('jutsuMarcherMur')
            ->add('jutsuDeflagration')
            ->add('jutsuMarcherEau')
            ->add('jutsuMetamorphose')
            ->add('jutsuMultishoot')
            ->add('jutsuInvisibilite')
            ->add('jutsuResistanceExplosion')
            ->add('jutsuPhoenix')
            ->add('jutsuVague')
            ->add('jutsuPieux')
            ->add('jutsuTeleportation')
            ->add('jutsuTornade')
            ->add('jutsuKusanagi')
            ->add('jutsuAcierRenforce')
            ->add('jutsuChakraVie')
            ->add('jutsuFujin')
            ->add('jutsuRaijin')
            ->add('jutsuSarutahiko')
            ->add('jutsuSusanoo')
            ->add('jutsuKagutsuchi')
            ->add('grade')
            ->add('experience')
            ->add('classe')
            ->add('masque')
            ->add('masqueCouleur')
            ->add('masqueDetail')
            ->add('costume')
            ->add('costumeCouleur')
            ->add('costumeDetail')
            ->add('missionAssassinnat')
            ->add('missionCourse')
            ->add('accomplissement')
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
        $formMapper
            ->add('aptitudeForce', IntegerType::class, array(
                'label' => 'Force'
            ))
            ->add('aptitudeVitesse', IntegerType::class, array(
                'label' => 'Vitesse'
            ))
            ->add('aptitudeVie', IntegerType::class, array(
                'label' => 'Vie'
            ))
            ->add('aptitudeChakra', IntegerType::class, array(
                'label' => 'Chakra'
            ))
            ->add('jutsuBoule', IntegerType::class, array(
                'label' => 'Boule d\'énergie'
            ))
            ->add('jutsuDoubleSaut', IntegerType::class, array(
                'label' => 'Double saut'
            ))
            ->add('jutsuBouclier', IntegerType::class, array(
                'label' => 'Bouclier d\'énergie'
            ))
            ->add('jutsuMarcherMur', IntegerType::class, array(
                'label' => 'Marcher sur les murs'
            ))
            ->add('jutsuDeflagration', IntegerType::class, array(
                'label' => 'Déflagration'
            ))
            ->add('jutsuMarcherEau', IntegerType::class, array(
                'label' => 'Marcher sur l\'eau'
            ))
            ->add('jutsuMetamorphose', IntegerType::class, array(
                'label' => 'Changer en rocher'
            ))
            ->add('jutsuMultishoot', IntegerType::class, array(
                'label' => 'Multishoot'
            ))
            ->add('jutsuInvisibilite', IntegerType::class, array(
                'label' => 'Invisibilité'
            ))
            ->add('jutsuResistanceExplosion', IntegerType::class, array(
                'label' => 'Résistance aux explosions'
            ))
            ->add('jutsuPhoenix', IntegerType::class, array(
                'label' => 'Pheonix'
            ))
            ->add('jutsuVague', IntegerType::class, array(
                'label' => 'Tsunami'
            ))
            ->add('jutsuPieux', IntegerType::class, array(
                'label' => 'Pieux'
            ))
            ->add('jutsuTeleportation', IntegerType::class, array(
                'label' => 'Téléportation'
            ))
            ->add('jutsuTornade', IntegerType::class, array(
                'label' => 'Tornade'
            ))
            ->add('jutsuKusanagi', IntegerType::class, array(
                'label' => 'Kusanagi'
            ))
            ->add('jutsuAcierRenforce', IntegerType::class, array(
                'label' => 'Acier renforcé'
            ))
            ->add('jutsuChakraVie', IntegerType::class, array(
                'label' => 'Chakra de vie'
            ))
            ->add('jutsuFujin', IntegerType::class, array(
                'label' => 'Fujin'
            ))
            ->add('jutsuRaijin', IntegerType::class, array(
                'label' => 'Raijin'
            ))
            ->add('jutsuSarutahiko', IntegerType::class, array(
                'label' => 'Sarutahiko'
            ))
            ->add('jutsuSusanoo', IntegerType::class, array(
                'label' => 'Susanoo'
            ))
            ->add('jutsuKagutsuchi', IntegerType::class, array(
                'label' => 'Kagutsuchi'
            ))
            ->add('grade', IntegerType::class, array(
                'label' => 'Dan'
            ))
            ->add('experience', IntegerType::class, array(
                'label' => 'Expérience'
            ))
            ->add('classe', TextType::class, array(
                'label' => 'Classe'
            ))
            ->add('masque', IntegerType::class, array(
                'label' => 'Masque'
            ))
            ->add('masqueCouleur', IntegerType::class, array(
                'label' => 'Couleur de masque'
            ))
            ->add('masqueDetail', IntegerType::class, array(
                'label' => 'Détail de masque'
            ))
            ->add('costume', IntegerType::class, array(
                'label' => 'Costume'
            ))
            ->add('costumeCouleur', IntegerType::class, array(
                'label' => 'Couleur de costume'
            ))
            ->add('costumeDetail', IntegerType::class, array(
                'label' => 'Détail de costume'
            ))
            ->add('missionAssassinnat', IntegerType::class, array(
                'label' => 'Assassinnat'
            ))
            ->add('missionCourse', IntegerType::class, array(
                'label' => 'Course'
            ))
            ->add('accomplissement', TextType::class, array(
                'label' => 'Accomplissement'
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
            ->add('aptitudeForce')
            ->add('aptitudeVitesse')
            ->add('aptitudeVie')
            ->add('aptitudeChakra')
            ->add('jutsuBoule')
            ->add('jutsuDoubleSaut')
            ->add('jutsuBouclier')
            ->add('jutsuMarcherMur')
            ->add('jutsuDeflagration')
            ->add('jutsuMarcherEau')
            ->add('jutsuMetamorphose')
            ->add('jutsuMultishoot')
            ->add('jutsuInvisibilite')
            ->add('jutsuResistanceExplosion')
            ->add('jutsuPhoenix')
            ->add('jutsuVague')
            ->add('jutsuPieux')
            ->add('jutsuTeleportation')
            ->add('jutsuTornade')
            ->add('jutsuKusanagi')
            ->add('jutsuAcierRenforce')
            ->add('jutsuChakraVie')
            ->add('jutsuFujin')
            ->add('jutsuRaijin')
            ->add('jutsuSarutahiko')
            ->add('jutsuSusanoo')
            ->add('jutsuKagutsuchi')
            ->add('grade')
            ->add('experience')
            ->add('classe')
            ->add('masque')
            ->add('masqueCouleur')
            ->add('masqueDetail')
            ->add('costume')
            ->add('costumeCouleur')
            ->add('costumeDetail')
            ->add('missionAssassinnat')
            ->add('missionCourse')
            ->add('accomplissement')
        ;
    }
}
