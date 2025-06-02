<?php

namespace App\Admin;

use App\Entity\Game\Ninja;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @extends AbstractAdmin<Ninja>
 */
class NinjaAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
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
            ->add('jutsuTransformationAqueuse')
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

    protected function configureListFields(ListMapper $list): void
    {
        $list
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
            ->add('jutsuTransformationAqueuse')
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
        $form
            ->add('aptitudeForce', IntegerType::class, [
                'label' => 'Force',
            ])
            ->add('aptitudeVitesse', IntegerType::class, [
                'label' => 'Vitesse',
            ])
            ->add('aptitudeVie', IntegerType::class, [
                'label' => 'Vie',
            ])
            ->add('aptitudeChakra', IntegerType::class, [
                'label' => 'Chakra',
            ])
            ->add('jutsuBoule', IntegerType::class, [
                'label' => 'Boule d\'énergie',
            ])
            ->add('jutsuDoubleSaut', IntegerType::class, [
                'label' => 'Double saut',
            ])
            ->add('jutsuBouclier', IntegerType::class, [
                'label' => 'Bouclier d\'énergie',
            ])
            ->add('jutsuMarcherMur', IntegerType::class, [
                'label' => 'Marcher sur les murs',
            ])
            ->add('jutsuDeflagration', IntegerType::class, [
                'label' => 'Déflagration',
            ])
            ->add('jutsuTransformationAqueuse', IntegerType::class, [
                'label' => 'Transformation aqueuse',
            ])
            ->add('jutsuMetamorphose', IntegerType::class, [
                'label' => 'Changer en rocher',
            ])
            ->add('jutsuMultishoot', IntegerType::class, [
                'label' => 'Multishoot',
            ])
            ->add('jutsuInvisibilite', IntegerType::class, [
                'label' => 'Invisibilité',
            ])
            ->add('jutsuResistanceExplosion', IntegerType::class, [
                'label' => 'Résistance aux explosions',
            ])
            ->add('jutsuPhoenix', IntegerType::class, [
                'label' => 'Pheonix',
            ])
            ->add('jutsuVague', IntegerType::class, [
                'label' => 'Tsunami',
            ])
            ->add('jutsuPieux', IntegerType::class, [
                'label' => 'Pieux',
            ])
            ->add('jutsuTeleportation', IntegerType::class, [
                'label' => 'Téléportation',
            ])
            ->add('jutsuTornade', IntegerType::class, [
                'label' => 'Tornade',
            ])
            ->add('jutsuKusanagi', IntegerType::class, [
                'label' => 'Kusanagi',
            ])
            ->add('jutsuAcierRenforce', IntegerType::class, [
                'label' => 'Acier renforcé',
            ])
            ->add('jutsuChakraVie', IntegerType::class, [
                'label' => 'Chakra de vie',
            ])
            ->add('jutsuFujin', IntegerType::class, [
                'label' => 'Fujin',
            ])
            ->add('jutsuRaijin', IntegerType::class, [
                'label' => 'Raijin',
            ])
            ->add('jutsuSarutahiko', IntegerType::class, [
                'label' => 'Sarutahiko',
            ])
            ->add('jutsuSusanoo', IntegerType::class, [
                'label' => 'Susanoo',
            ])
            ->add('jutsuKagutsuchi', IntegerType::class, [
                'label' => 'Kagutsuchi',
            ])
            ->add('grade', IntegerType::class, [
                'label' => 'Dan',
            ])
            ->add('experience', IntegerType::class, [
                'label' => 'Expérience',
            ])
            ->add('classe', TextType::class, [
                'label' => 'Classe',
            ])
            ->add('masque', IntegerType::class, [
                'label' => 'Masque',
            ])
            ->add('masqueCouleur', IntegerType::class, [
                'label' => 'Couleur de masque',
            ])
            ->add('masqueDetail', IntegerType::class, [
                'label' => 'Détail de masque',
            ])
            ->add('costume', IntegerType::class, [
                'label' => 'Costume',
            ])
            ->add('costumeCouleur', IntegerType::class, [
                'label' => 'Couleur de costume',
            ])
            ->add('costumeDetail', IntegerType::class, [
                'label' => 'Détail de costume',
            ])
            ->add('missionAssassinnat', IntegerType::class, [
                'label' => 'Assassinnat',
            ])
            ->add('missionCourse', IntegerType::class, [
                'label' => 'Course',
            ])
            ->add('accomplissement', TextType::class, [
                'label' => 'Accomplissement',
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
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
            ->add('jutsuTransformationAqueuse')
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
