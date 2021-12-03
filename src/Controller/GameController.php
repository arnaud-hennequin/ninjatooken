<?php

namespace App\Controller;

use App\Repository\LobbyRepository;
use App\Repository\NinjaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Utils\GameData;
use App\Entity\User\User;

class GameController extends AbstractController
{
    public function parties(LobbyRepository $lobbyRepository): Response
    {
        return $this->render('game/parties.html.twig', [
            'games' => $lobbyRepository->getRecent(50)
        ]);
    }

    public function calculateur(TranslatorInterface $translator, GameData $gameData, AuthorizationCheckerInterface $authorizationChecker, TokenStorageInterface $tokenStorage): Response
    {
        $level = 0;
        $classe = "suiton";
        // les données du joueur connecté
        $ninja = null;
        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            $user = $tokenStorage->getToken()->getUser();
            $ninja = $user->getNinja();
            if ($ninja) {
                $c = $ninja->getClasse();
                if (!empty($c)) {
                    // l'expérience (et données associées)
                    $gameData->setExperience($ninja->getExperience(), $ninja->getGrade());
                    $level = $gameData->getLevelActuel();

                    $classeP = $this->getParameter('class');
                    $classe = strtolower($classeP[$c]);
                }
            }
        }

        $capacites = [
            'force' => [
                'nom' => $translator->trans('game.force', [], 'common'),
                'current' => $ninja?$ninja->getAptitudeForce():0
            ],
            'vitesse' => [
                'nom' => $translator->trans('game.vitesse', [], 'common'),
                'current' => $ninja?$ninja->getAptitudeVitesse():0
            ],
            'vie' => [
                'nom' => $translator->trans('game.vie', [], 'common'),
                'current' => $ninja?$ninja->getAptitudeVie():0
            ],
            'chakra' => [
                'nom' => $translator->trans('game.chakra', [], 'common'),
                'current' => $ninja?$ninja->getAptitudeChakra():0
            ]
        ];
        $aptitudes = [
            'bouleElementaire' => [
                'nom' => $translator->trans('game.bouleElementaire.nom', [], 'common'),
                'values' => [
                    'degat' => $translator->trans('game.bouleElementaire.degat', [], 'common'),
                    'rayon' => $translator->trans('game.bouleElementaire.rayon', [], 'common'),
                    'chakra' => $translator->trans('game.bouleElementaire.chakra', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuBoule():0
            ],
            'doubleSaut'  => [
                'nom' => $translator->trans('game.doubleSaut.nom', [], 'common'),
                'values' => [
                    'saut1' => $translator->trans('game.doubleSaut.saut1', [], 'common'),
                    'saut2' => $translator->trans('game.doubleSaut.saut2', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuDoubleSaut():0
            ],
            'bouclierElementaire'  => [
                'nom' => $translator->trans('game.bouclierElementaire.nom', [], 'common'),
                'values' => [
                    'reduction' => $translator->trans('game.bouclierElementaire.reduction', [], 'common'),
                    'chakra' => $translator->trans('game.bouclierElementaire.chakra', [], 'common'),
                    'last' => $translator->trans('game.bouclierElementaire.last', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuBouclier():0
            ],
            'marcherMur'  => [
                'nom' => $translator->trans('game.marcherMur.nom', [], 'common'),
                'values' => [
                    'chakra' => $translator->trans('game.marcherMur.chakra', [], 'common'),
                    'last' => $translator->trans('game.marcherMur.last', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuMarcherMur():0
            ],
            'acierRenforce'  => [
                'nom' => $translator->trans('game.acierRenforce.nom', [], 'common'),
                'values' => [
                    'degat' => $translator->trans('game.acierRenforce.degat', [], 'common'),
                    'chakra' => $translator->trans('game.acierRenforce.chakra', [], 'common'),
                    'last' => $translator->trans('game.acierRenforce.last', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuAcierRenforce():0
            ],
            'deflagrationElementaire'  => [
                'nom' => $translator->trans('game.deflagrationElementaire.nom', [], 'common'),
                'values' => [
                    'degat' => $translator->trans('game.deflagrationElementaire.degat', [], 'common'),
                    'chakra' => $translator->trans('game.deflagrationElementaire.chakra', [], 'common'),
                    'rayon' => $translator->trans('game.deflagrationElementaire.rayon', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuDeflagration():0
            ],
            'chakraVie'  => [
                'nom' => $translator->trans('game.chakraVie.nom', [], 'common'),
                'values' => [
                    'chakra' => $translator->trans('game.chakraVie.chakra', [], 'common'),
                    'last' => $translator->trans('game.chakraVie.last', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuChakraVie():0
            ],
            'resistanceExplosion'  => [
                'nom' => $translator->trans('game.resistanceExplosion.nom', [], 'common'),
                'values' => [
                    'reduction' => $translator->trans('game.resistanceExplosion.reduction', [], 'common'),
                    'last' => $translator->trans('game.resistanceExplosion.last', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuResistanceExplosion():0
            ],
            'transformationAqueuse'  => [
                'nom' => $translator->trans('game.transformationAqueuse.nom', [], 'common'),
                'values' => [
                    'reduction' => $translator->trans('game.transformationAqueuse.reduction', [], 'common'),
                    'last' => $translator->trans('game.transformationAqueuse.last', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuTransformationAqueuse():0
            ],
            'changerObjet'  => [
                'nom' => $translator->trans('game.changerObjet.nom', [], 'common'),
                'values' => [
                    'last' => $translator->trans('game.changerObjet.last', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuMetamorphose():0
            ],
            'multishoot'  => [
                'nom' => $translator->trans('game.multishoot.nom', [], 'common'),
                'values' => [
                    'speed' => $translator->trans('game.multishoot.speed', [], 'common'),
                    'chakra' => $translator->trans('game.multishoot.chakra', [], 'common'),
                    'last' => $translator->trans('game.multishoot.last', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuMultishoot():0
            ],
            'invisibleman'  => [
                'nom' => $translator->trans('game.invisibleman.nom', [], 'common'),
                'values' => [
                    'opacity' => $translator->trans('game.invisibleman.opacity', [], 'common'),
                    'last' => $translator->trans('game.invisibleman.last', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuInvisibilite():0
            ],
            'phoenix'  => [
                'nom' => $translator->trans('game.phoenix.nom', [], 'common'),
                'values' => [
                    'degat' => $translator->trans('game.phoenix.degat', [], 'common'),
                    'rayon' => $translator->trans('game.phoenix.rayon', [], 'common'),
                    'chakra' => $translator->trans('game.phoenix.chakra', [], 'common'),
                    'distance' => $translator->trans('game.phoenix.distance', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuPhoenix():0
            ],
            'vague'  => [
                'nom' => $translator->trans('game.vague.nom', [], 'common'),
                'values' => [
                    'degat' => $translator->trans('game.vague.degat', [], 'common'),
                    'temps' => $translator->trans('game.vague.temps', [], 'common'),
                    'chakra' => $translator->trans('game.vague.chakra', [], 'common'),
                    'distance' => $translator->trans('game.vague.distance', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuVague():0
            ],
            'pieux'  => [
                'nom' => $translator->trans('game.pieux.nom', [], 'common'),
                'values' => [
                    'degat' => $translator->trans('game.pieux.degat', [], 'common'),
                    'largeur' => $translator->trans('game.pieux.largeur', [], 'common'),
                    'longueur' => $translator->trans('game.pieux.longueur', [], 'common'),
                    'chakra' => $translator->trans('game.pieux.chakra', [], 'common'),
                    'distance' => $translator->trans('game.pieux.distance', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuPieux():0
            ],
            'teleportation'  => [
                'nom' => $translator->trans('game.teleportation.nom', [], 'common'),
                'values' => [
                    'vie' => $translator->trans('game.teleportation.vie', [], 'common'),
                    'chakra' => $translator->trans('game.teleportation.chakra', [], 'common'),
                    'distance' => $translator->trans('game.teleportation.distance', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuTeleportation():0
            ],
            'tornade'  => [
                'nom' => $translator->trans('game.tornade.nom', [], 'common'),
                'values' => [
                    'degat' => $translator->trans('game.tornade.degat', [], 'common'),
                    'temps' => $translator->trans('game.tornade.temps', [], 'common'),
                    'chakra' => $translator->trans('game.tornade.chakra', [], 'common'),
                    'distance' => $translator->trans('game.tornade.distance', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuTornade():0
            ],
            'kusanagi'  => [
                'nom' => $translator->trans('game.kusanagi.nom', [], 'common'),
                'values' => [
                    'degat' => $translator->trans('game.kusanagi.degat', [], 'common'),
                    'last' => $translator->trans('game.kusanagi.last', [], 'common'),
                    'chakra' => $translator->trans('game.kusanagi.chakra', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuKusanagi():0
            ],
            'kamiRaijin'  => [
                'nom' => $translator->trans('game.kamiRaijin.nom', [], 'common'),
                'values' => [
                    'effect' => $translator->trans('game.kamiRaijin.effect', [], 'common'),
                    'rayon' => $translator->trans('game.kamiRaijin.rayon', [], 'common'),
                    'temps' => $translator->trans('game.kamiRaijin.temps', [], 'common'),
                    'distance' => $translator->trans('game.kamiRaijin.distance', [], 'common'),
                    'chakra' => $translator->trans('game.kamiRaijin.chakra', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuRaijin():0
            ],
            'kamiSarutahiko'  => [
                'nom' => $translator->trans('game.kamiSarutahiko.nom', [], 'common'),
                'values' => [
                    'effect' => $translator->trans('game.kamiSarutahiko.effect', [], 'common'),
                    'rayon' => $translator->trans('game.kamiSarutahiko.rayon', [], 'common'),
                    'temps' => $translator->trans('game.kamiSarutahiko.temps', [], 'common'),
                    'distance' => $translator->trans('game.kamiSarutahiko.distance', [], 'common'),
                    'chakra' => $translator->trans('game.kamiSarutahiko.chakra', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuSarutahiko():0
            ],
            'kamiFujin'  => [
                'nom' => $translator->trans('game.kamiFujin.nom', [], 'common'),
                'values' => [
                    'rayon' => $translator->trans('game.kamiFujin.rayon', [], 'common'),
                    'temps' => $translator->trans('game.kamiFujin.temps', [], 'common'),
                    'distance' => $translator->trans('game.kamiFujin.distance', [], 'common'),
                    'chakra' => $translator->trans('game.kamiFujin.chakra', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuFujin():0
            ],
            'kamiSusanoo'  => [
                'nom' => $translator->trans('game.kamiSusanoo.nom', [], 'common'),
                'values' => [
                    'effect' => $translator->trans('game.kamiSusanoo.effect', [], 'common'),
                    'rayon' => $translator->trans('game.kamiSusanoo.rayon', [], 'common'),
                    'temps' => $translator->trans('game.kamiSusanoo.temps', [], 'common'),
                    'distance' => $translator->trans('game.kamiSusanoo.distance', [], 'common'),
                    'chakra' => $translator->trans('game.kamiSusanoo.chakra', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuSusanoo():0
            ],
            'kamiKagutsuchi'  => [
                'nom' => $translator->trans('game.kamiKagutsuchi.nom', [], 'common'),
                'values' => [
                    'effect' => $translator->trans('game.kamiKagutsuchi.effect', [], 'common'),
                    'rayon' => $translator->trans('game.kamiKagutsuchi.rayon', [], 'common'),
                    'temps' => $translator->trans('game.kamiKagutsuchi.temps', [], 'common'),
                    'distance' => $translator->trans('game.kamiKagutsuchi.distance', [], 'common'),
                    'chakra' => $translator->trans('game.kamiKagutsuchi.chakra', [], 'common')
                ],
                'current' => $ninja?$ninja->getJutsuKagutsuchi():0
            ]
        ];
        $dom = $gameData->getDocument();

        $levelUp = [];
        $cd = $dom->getElementsByTagName('levelUp')->item(0);
        $levelUp['capacite'] = json_encode([
            'val' => (int)$cd->getElementsByTagName('capacite')->item(0)->getAttribute('val'),
            'depart' => (int)$cd->getElementsByTagName('capacite')->item(0)->getAttribute('depart'),
        ]);
        $levelUp['aptitude'] = json_encode([
            'val' => (int)$cd->getElementsByTagName('aptitude')->item(0)->getAttribute('val'),
            'depart' => (int)$cd->getElementsByTagName('aptitude')->item(0)->getAttribute('depart'),
        ]);

        foreach ($capacites as $k => $val) {
            $xml = [];
            $cd = $dom->getElementsByTagName($k)->item(0)->getElementsByTagName('x');
            foreach ($cd as $v) {
                $xml[] = [
                    'val' => (float)str_replace('a','.',$v->getAttribute('val')),
                    'lvl' => (int)$v->getAttribute('niveau')
                 ];
            }
            $capacites[$k]['xml'] = json_encode($xml);
        }

        $classes = [];
        $cd = $dom->getElementsByTagName('classe')->item(0)->getElementsByTagName('x');
        foreach ($cd as $v) {
            $classes[$v->getAttribute('val')] = strtolower($v->getAttribute('name'));
        }
        foreach ($aptitudes as $k=>$val) {
            $xml = [];
            $cd = $dom->getElementsByTagName($k)->item(0)->getElementsByTagName('x');
            foreach ($cd as $v) {
                $attr = [];
                $attr['lvl'] = (int)$v->getAttribute('niveau');
                foreach ($val['values'] as $k1=>$v1) {
                    $attr[$k1] = (float)str_replace('a','.',$v->getAttribute($k1));
                }
                $xml[] = $attr;
            }
            $limit = $dom->getElementsByTagName($k)->item(0)->getAttribute('limit');
            $aptitudes[$k]['limit'] = $classes[$limit] ?? '';
            $aptitudes[$k]['niveau'] = $dom->getElementsByTagName($k)->item(0)->getAttribute('niveau');
            $aptitudes[$k]['xml'] = json_encode($xml);
            $aptitudes[$k]['values'] = json_encode($aptitudes[$k]['values']);
        }

        return $this->render('game/calculateur.html.twig', [
            'capacites' => $capacites,
            'aptitudes' => $aptitudes,
            'classes' => $this->getParameter('class'),
            'levelUp' => $levelUp,
            'level' => $level,
            'classe' => $classe
        ]);
    }

    public function classement(Request $request, NinjaRepository $ninjaRepository, $page): Response
    {
        $num = $this->getParameter('numReponse');
        $page = max(1, $page);

        $order = $request->get('order');
        if (empty($order)) {
            $order = 'experience';
        }

        $filter = $request->get('filter');

        $total = $ninjaRepository->getNumNinjas();

        $classe = $this->getParameter('class');
        $classeNum = [];
        foreach ($classe as $k=>$v) {
            $classeNum[$k] = $ninjaRepository->getNumNinjas($k);
        }

        return $this->render('game/classement.html.twig', [
            'order' => $order,
            'filter' => $filter,
            'joueurs' => $ninjaRepository->getNinjas($order, $filter, $num, $page),
            'page' => $page,
            'nombrePage' => ceil($total/$num),
            'nombre' => $num,
            'nombreNinja' => $total,
            'experienceTotal' => $ninjaRepository->getSumExperience(),
            'classes' => $classeNum
        ]);
    }

    public function recentGames(LobbyRepository $lobbyRepository, $max = 3): Response
    {
        return $this->render('game/games/recentList.html.twig', [
            'games' => $lobbyRepository->getRecent($max)
        ]);
    }

    public function signature(User $user, GameData $gameData, NinjaRepository $ninjaRepository): Response
    {
        $ninja = $user->getNinja();

        if ($ninja) {
            // l'expérience (et données associées)
            $gameData->setExperience($ninja->getExperience(), $ninja->getGrade());

            $user->level = $gameData->getLevelActuel();
            $user->ratio = $gameData->getRatio();

            // classement
			$user->classement = $ninjaRepository->getClassement($ninja->getExperience());

            // total de joueurs
            $user->total = $ninjaRepository->getNumNinjas();

        }
        return $this->render('game/signature.html.twig', ['user' => $user]);
    }
}
