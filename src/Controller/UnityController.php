<?php

namespace App\Controller;

use App\Entity\Game\Lobby;
use App\Entity\Game\Ninja;
use App\Entity\User\Capture;
use App\Entity\User\Friend;
use App\Entity\User\Message;
use App\Entity\User\MessageUser;
use App\Entity\User\User;
use App\Entity\User\UserInterface;
use App\Repository\FriendRepository;
use App\Repository\LobbyRepository;
use App\Utils\GameData;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UnityController extends AbstractController
{
    private string $time;
    private string $crypt;
    private string $cryptUnity;
    private string $phpsessid;
    private string $gameversion;
    private int $idUtilisateur;

    #[Route('/unity/xml/game/update', name: 'ninja_tooken_game_unity_update')]
    public function update(Request $request, TranslatorInterface $translator, AuthorizationCheckerInterface $authorizationChecker, TokenStorageInterface $tokenStorage, GameData $gameData, EntityManagerInterface $em): Response
    {
        $session = $request->getSession();

        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            /** @var UserInterface $user */
            $user = $tokenStorage->getToken()->getUser();

            $this->time = preg_replace('/[^0-9]/i', '', (string) $request->get('time'));
            $this->crypt = $request->headers->get('X-COMMON');

            $this->phpsessid = $session->getName().'='.$session->getId();

            $this->gameversion = $this->getParameter('unity.version');
            $this->idUtilisateur = $user->getId();
            $this->cryptUnity = $this->getParameter('unity.crypt');

            $data = '0';

            $a = $request->get('a');
            if (!empty($a)) {
                $user->setUpdatedAt(new \DateTime());

                $em->persist($user);
                $em->flush();

                $ninja = $user->getNinja();

                switch ($a) {
                    // mise à jour du grade
                    case 'g':
                        $l = $request->get('l');
                        if ($this->isCryptingOk($a.$l)) {
                            $grade = explode(':', $l);
                            if (2 === count($grade)) {
                                $doc = $gameData->getDocument();
                                $max = (float) $doc->getElementsByTagName('experience')->item(0)->getElementsByTagName('x')->item(99)->getAttribute('val');
                                if ($ninja->getExperience() - $ninja->getGrade() * $max > $max) {
                                    $gr = intval($grade[0]);
                                    if ($gr === $ninja->getGrade() + 1) {
                                        $ninja->setGrade($gr);
                                        $ninja->setAptitudeForce(0);
                                        $ninja->setAptitudeVitesse(0);
                                        $ninja->setAptitudeVie(0);
                                        $ninja->setAptitudeChakra(0);
                                        $ninja->setJutsuBoule(0);
                                        $ninja->setJutsuDoubleSaut(0);
                                        $ninja->setJutsuBouclier(0);
                                        $ninja->setJutsuMarcherMur(0);
                                        $ninja->setJutsuDeflagration(0);
                                        $ninja->setJutsuTransformationAqueuse(0);
                                        $ninja->setJutsuMetamorphose(0);
                                        $ninja->setJutsuMultishoot(0);
                                        $ninja->setJutsuInvisibilite(0);
                                        $ninja->setJutsuResistanceExplosion(0);
                                        $ninja->setJutsuPhoenix(0);
                                        $ninja->setJutsuVague(0);
                                        $ninja->setJutsuPieux(0);
                                        $ninja->setJutsuTeleportation(0);
                                        $ninja->setJutsuTornade(0);
                                        $ninja->setJutsuKusanagi(0);
                                        $ninja->setJutsuAcierRenforce(0);
                                        $ninja->setJutsuChakraVie(0);
                                        $ninja->setJutsuFujin(0);
                                        $ninja->setJutsuRaijin(0);
                                        $ninja->setJutsuSarutahiko(0);
                                        $ninja->setJutsuSusanoo(0);
                                        $ninja->setJutsuKagutsuchi(0);

                                        $em->persist($ninja);
                                        $em->flush();

                                        $data = $gr;
                                    } else {
                                        $data = $ninja->getGrade();
                                    }
                                } else {
                                    $data = $ninja->getGrade();
                                }
                            } else {
                                $data = $ninja->getGrade();
                            }
                        } else {
                            $data = $ninja->getGrade();
                        }
                        break;
                        // mise à jour du compteur de mission
                    case 'm':
                        $t = $request->get('t');
                        if ($this->isCryptingOk($a.$t)) {
                            if ('a' === $t) {
                                $ninja->setMissionAssassinnat($ninja->getMissionAssassinnat() + 1);

                                $em->persist($ninja);
                                $em->flush();

                                $data = $ninja->getMissionAssassinnat();
                            } elseif ('c' === $t) {
                                $ninja->setMissionCourse($ninja->getMissionCourse() + 1);

                                $em->persist($ninja);
                                $em->flush();

                                $data = $ninja->getMissionCourse();
                            }
                        }
                        break;
                        // mise à jour des achievements
                    case 'a':
                        $l = $request->get('l');
                        if ($this->isCryptingOk($a.$l)) {
                            $ninja->setAccomplissement($l);

                            $em->persist($ninja);
                            $em->flush();
                            $data = '1';
                        }
                        break;
                        // mise à jour de la skin
                    case 's':
                        $l = $request->get('l');
                        if ($this->isCryptingOk($a.$l)) {
                            $skins = explode(':', $l);
                            if (6 === count($skins)) {
                                $ninja->setMasque((int) $skins[0]);
                                $ninja->setMasqueCouleur((int) $skins[1]);
                                $ninja->setMasqueDetail((int) $skins[2]);
                                $ninja->setCostume((int) $skins[3]);
                                $ninja->setCostumeCouleur((int) $skins[4]);
                                $ninja->setCostumeDetail((int) $skins[5]);

                                $em->persist($ninja);
                                $em->flush();
                                $data = '1';
                            }
                        }
                        break;
                        // mise à jour de la classe
                    case 'c':
                        $c = $request->get('c');
                        $classe = $ninja->getClasse();
                        if (empty($classe)) {
                            $convert = [
                                '355' => 'feu',
                                '356' => 'eau',
                                '357' => 'terre',
                                '358' => 'foudre',
                                '359' => 'vent',
                            ];
                            $ninja->setClasse($convert[$c]);

                            $em->persist($ninja);
                            $em->flush();
                            $data = '1';
                        }
                        break;
                        // mise à jour de l'expérience
                    case 'e':
                        $e = (int) $request->get('e');
                        $data = $ninja->getExperience();
                        if ($this->isCryptingOk($data.$a.$e)) {
                            $ninja->setExperience($data + $e);

                            $em->persist($ninja);
                            $em->flush();
                            $data = $ninja->getExperience();
                        }
                        break;
                        // mise à jour des niveaux
                    case 'l':
                        $l = $request->get('l');
                        if ($this->isCryptingOk($a.$l)) {
                            $levels = explode(':', $l);
                            if (27 === count($levels)) {
                                $doc = $gameData->getDocument();
                                $levelUp = $doc->getElementsByTagName('levelUp')->item(0);
                                $capaciteV = (float) $levelUp->getElementsByTagName('capacite')->item(0)->getAttribute('val');
                                $capaciteD = (float) $levelUp->getElementsByTagName('capacite')->item(0)->getAttribute('depart');
                                $aptitudeV = (float) $levelUp->getElementsByTagName('aptitude')->item(0)->getAttribute('val');
                                $aptitudeD = (float) $levelUp->getElementsByTagName('aptitude')->item(0)->getAttribute('depart');

                                $experience = $doc->getElementsByTagName('experience')->item(0)->getElementsByTagName('x');
                                $k = 0;
                                foreach ($experience as $exp) {
                                    if ($exp->getAttribute('val') > $ninja->getExperience()) {
                                        break;
                                    }
                                    ++$k;
                                }
                                $capaciteMax = ($capaciteD + $k * $capaciteV);
                                $aptitudeMax = ($aptitudeD + $k * $aptitudeV);

                                $capaciteDem = (float) $levels[0] + (float) $levels[1] + (float) $levels[2] + (float) $levels[3];
                                $aptitudeDem = (float) $levels[4] + (float) $levels[5] + (float) $levels[6] + (float) $levels[7] +
                                    (float) $levels[8] + (float) $levels[9] + (float) $levels[10] + (float) $levels[11] +
                                    (float) $levels[12] + (float) $levels[13] + (float) $levels[14] + (float) $levels[15] +
                                    (float) $levels[16] + (float) $levels[17] + (float) $levels[18] + (float) $levels[19] +
                                    (float) $levels[20] + (float) $levels[21];

                                if ($capaciteMax >= $capaciteDem && $aptitudeMax >= $aptitudeDem) {
                                    $classe = $ninja->getClasse();
                                    if ('terre' === $classe) {
                                        $levels[9] = 0;
                                        $levels[11] = 0;
                                        $levels[12] = 0;
                                        $levels[13] = 0;
                                        $levels[14] = 0;
                                        $levels[15] = 0;
                                        $levels[17] = 0;
                                        $levels[18] = 0;
                                        $levels[22] = 0;
                                        $levels[23] = 0;
                                        $levels[25] = 0;
                                        $levels[26] = 0;
                                    } elseif ('eau' === $classe) {
                                        $levels[10] = 0;
                                        $levels[11] = 0;
                                        $levels[12] = 0;
                                        $levels[13] = 0;
                                        $levels[14] = 0;
                                        $levels[16] = 0;
                                        $levels[17] = 0;
                                        $levels[18] = 0;
                                        $levels[22] = 0;
                                        $levels[23] = 0;
                                        $levels[24] = 0;
                                        $levels[26] = 0;
                                    } elseif ('feu' === $classe) {
                                        $levels[9] = 0;
                                        $levels[10] = 0;
                                        $levels[11] = 0;
                                        $levels[12] = 0;
                                        $levels[15] = 0;
                                        $levels[16] = 0;
                                        $levels[17] = 0;
                                        $levels[18] = 0;
                                        $levels[22] = 0;
                                        $levels[23] = 0;
                                        $levels[24] = 0;
                                        $levels[25] = 0;
                                    } elseif ('foudre' === $classe) {
                                        $levels[9] = 0;
                                        $levels[10] = 0;
                                        $levels[12] = 0;
                                        $levels[13] = 0;
                                        $levels[14] = 0;
                                        $levels[15] = 0;
                                        $levels[16] = 0;
                                        $levels[18] = 0;
                                        $levels[22] = 0;
                                        $levels[24] = 0;
                                        $levels[25] = 0;
                                        $levels[26] = 0;
                                    } elseif ('vent' === $classe) {
                                        $levels[9] = 0;
                                        $levels[10] = 0;
                                        $levels[11] = 0;
                                        $levels[13] = 0;
                                        $levels[14] = 0;
                                        $levels[15] = 0;
                                        $levels[16] = 0;
                                        $levels[17] = 0;
                                        $levels[23] = 0;
                                        $levels[24] = 0;
                                        $levels[25] = 0;
                                        $levels[26] = 0;
                                    }
                                    $ninja->setAptitudeForce((int) $levels[0]);
                                    $ninja->setAptitudeVitesse((int) $levels[1]);
                                    $ninja->setAptitudeVie((int) $levels[2]);
                                    $ninja->setAptitudeChakra((int) $levels[3]);
                                    $ninja->setJutsuBoule(min(30, (int) $levels[4]));
                                    $ninja->setJutsuDoubleSaut(min(30, (int) $levels[5]));
                                    $ninja->setJutsuBouclier(min(30, (int) $levels[6]));
                                    $ninja->setJutsuMarcherMur(min(30, (int) $levels[7]));
                                    $ninja->setJutsuDeflagration(min(30, (int) $levels[8]));
                                    $ninja->setJutsuTransformationAqueuse(min(30, (int) $levels[9]));
                                    $ninja->setJutsuMetamorphose(min(30, (int) $levels[10]));
                                    $ninja->setJutsuMultishoot(min(30, (int) $levels[11]));
                                    $ninja->setJutsuInvisibilite(min(30, (int) $levels[12]));
                                    $ninja->setJutsuResistanceExplosion(min(30, (int) $levels[13]));
                                    $ninja->setJutsuPhoenix(min(30, (int) $levels[14]));
                                    $ninja->setJutsuVague(min(30, (int) $levels[15]));
                                    $ninja->setJutsuPieux(min(30, (int) $levels[16]));
                                    $ninja->setJutsuTeleportation(min(30, (int) $levels[17]));
                                    $ninja->setJutsuTornade(min(30, (int) $levels[18]));
                                    $ninja->setJutsuKusanagi(min(30, (int) $levels[19]));
                                    $ninja->setJutsuAcierRenforce(min(30, (int) $levels[20]));
                                    $ninja->setJutsuChakraVie(min(30, (int) $levels[21]));
                                    $ninja->setJutsuFujin(min(30, (int) $levels[22]));
                                    $ninja->setJutsuRaijin(min(30, (int) $levels[23]));
                                    $ninja->setJutsuSarutahiko(min(30, (int) $levels[24]));
                                    $ninja->setJutsuSusanoo(min(30, (int) $levels[25]));
                                    $ninja->setJutsuKagutsuchi(min(30, (int) $levels[26]));

                                    $em->persist($ninja);
                                    $em->flush();
                                    $data = '1';
                                }
                            }
                        }
                        break;
                        // check le cheat
                    case 't':
                        $t = $request->get('t');
                        $l = $request->get('l');
                        $userCheck = null;
                        if ($this->isCryptingOk($a.$t.$l)) {
                            $levels = explode(':', $t);
                            if (28 === count($levels)) {
                                $userCheck = $em->getRepository(User::class)->findOneBy(['id' => (int) $l]);
                                if ($userCheck) {
                                    $ninjaCheck = $userCheck->getNinja();
                                    if ($ninjaCheck) {
                                        // chargement du xml des données du jeu
                                        $doc = $gameData->getDocument();

                                        // l'expérience (et données associées)
                                        $experience = $ninjaCheck->getExperience();
                                        // le grade
                                        $dan = $ninjaCheck->getGrade();
                                        $xpXML = $doc->getElementsByTagName('experience')->item(0)->getElementsByTagName('x');
                                        $k = 0;
                                        $xp = $experience - $dan * (float) $xpXML->item($xpXML->length - 2)->getAttribute('val');
                                        foreach ($xpXML as $exp) {
                                            if ($exp->getAttribute('val') <= $xp) {
                                                ++$k;
                                            } else {
                                                break;
                                            }
                                        }
                                        $niveau = $xpXML->item($k > 0 ? $k - 1 : 0)->getAttribute('niveau');

                                        // évite d'enregistrer la valeur en bdd
                                        $force = $ninjaCheck->getAptitudeForce();
                                        $marcherMur = $ninjaCheck->getJutsuMarcherMur();
                                        $vitesse = $ninjaCheck->getAptitudeVitesse();

                                        if ($ninjaCheck->getMissionAssassinnat() >= 25) {
                                            $force += 5;
                                        }
                                        if ($ninjaCheck->getMissionCourse() >= 40) {
                                            $marcherMur += 5;
                                            $vitesse += 5;
                                        }

                                        if ($force === (int) $levels[0]
                                            && $vitesse === (int) $levels[1]
                                            && $ninjaCheck->getAptitudeVie() === (int) $levels[2]
                                            && $ninjaCheck->getAptitudeChakra() === (int) $levels[3]
                                            && $ninjaCheck->getJutsuBoule() === (int) $levels[4]
                                            && $ninjaCheck->getJutsuDoubleSaut() === (int) $levels[5]
                                            && $ninjaCheck->getJutsuBouclier() === (int) $levels[6]
                                            && $marcherMur === (int) $levels[7]
                                            && $ninjaCheck->getJutsuDeflagration() === (int) $levels[8]
                                            && (
                                                (
                                                    'feu' === $ninjaCheck->getClasse()
                                                    && $ninjaCheck->getJutsuResistanceExplosion() === (int) $levels[13]
                                                    && $ninjaCheck->getJutsuPhoenix() === (int) $levels[14]
                                                    && $ninjaCheck->getJutsuKagutsuchi() === (int) $levels[26]
                                                )
                                                || (
                                                    'eau' === $ninjaCheck->getClasse()
                                                    && $ninjaCheck->getJutsuTransformationAqueuse() === (int) $levels[9]
                                                    && $ninjaCheck->getJutsuVague() === (int) $levels[15]
                                                    && $ninjaCheck->getJutsuSusanoo() === (int) $levels[25]
                                                )
                                                || (
                                                    'terre' === $ninjaCheck->getClasse()
                                                    && $ninjaCheck->getJutsuMetamorphose() === (int) $levels[10]
                                                    && $ninjaCheck->getJutsuPieux() === (int) $levels[16]
                                                    && $ninjaCheck->getJutsuSarutahiko() === (int) $levels[24]
                                                )
                                                || (
                                                    'foudre' === $ninjaCheck->getClasse()
                                                    && $ninjaCheck->getJutsuMultishoot() === (int) $levels[11]
                                                    && $ninjaCheck->getJutsuTeleportation() === (int) $levels[17]
                                                    && $ninjaCheck->getJutsuRaijin() === (int) $levels[23]
                                                )
                                                || (
                                                    'vent' === $ninjaCheck->getClasse()
                                                    && $ninjaCheck->getJutsuInvisibilite() === (int) $levels[12]
                                                    && $ninjaCheck->getJutsuTornade() === (int) $levels[18]
                                                    && $ninjaCheck->getJutsuFujin() === (int) $levels[22]
                                                )
                                            )
                                            && $ninjaCheck->getJutsuKusanagi() === (int) $levels[19]
                                            && $ninjaCheck->getJutsuAcierRenforce() === (int) $levels[20]
                                            && $ninjaCheck->getJutsuChakraVie() === (int) $levels[21]
                                            && $niveau === $levels[27]
                                        ) {
                                            $data = '1';
                                        }
                                    }
                                // peut être un visiteur : on laisse ouvert pour les petits niveaux
                                } else {
                                    $num = 0;
                                    foreach ($levels as $v) {
                                        $num += (int) $v;
                                    }
                                    if ($num < 35) {
                                        $data = '1';
                                    }
                                }
                            }
                        }

                        if ($userCheck) {
                            // check qu'un joueur avec multi-compte n'est pas déjà connecté dans une partie
                            /*if ($data=='1' && $this->idUtilisateur!=(int)$l) {
                                $ips = $userCheck->getIps();
                                if (!empty($ips)) {
                                    // la liste des ips connues de l'utilisateur à vérifier
                                    $ipsCompare = array();
                                    foreach($ips as $ip) {
                                        $ipsCompare[] = $ip->getIp();
                                    }
                                    // boucle sur les parties
                                    $lobbies = $em->getRepository(Lobby::class)->findAll();
                                    if ($lobbies) {
                                        foreach($lobbies as $lobby) {
                                            // les utilisateurs des parties
                                            $users = $lobby->getUsers();
                                            if ($users) {
                                                foreach($users as $user) {
                                                    // les ips des utilisateurs
                                                    $userIps = $user->getIps();
                                                    if ($userIps) {
                                                        foreach($userIps as $ip) {
                                                            if (in_array($ip->getIp(), $ipsCompare)) {
                                                                $data = '0';
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    if ($data=='0')break;
                                                }
                                            }
                                            if ($data=='0')break;
                                        }
                                    }
                                }
                            }*/

                            // enregistre dans le lobby
                            if ('1' === $data) {
                                $lobby = $em->getRepository(Lobby::class)
                                    ->createQueryBuilder('l')
                                    ->where(':user MEMBER OF l.users')
                                    ->setParameter('user', $user)
                                    ->setMaxResults(1)
                                    ->getQuery()
                                    ->getOneOrNullResult();

                                if ($lobby) {
                                    try {
                                        $lobby->setDateUpdate(new \DateTime());
                                        $lobby->addUser($userCheck);
                                        $em->persist($lobby);
                                        $em->flush();
                                    } catch (\Exception) {
                                    }
                                }
                            }
                        }
                        break;
                        // apparition du yokai
                    case 'y':
                        if ($this->isCryptingOk($a)) {
                            $step = 15; // toutes les n minutes (fixe)
                            $dateApparition = time() - 7200;

                            // vacance noel
                            if (date('YmdHi') >= '201212211800' && date('YmdHi') <= '201212261200') {
                                $dateApparition = time() + ($step - date('i') % $step) * 60 - date('s');
                            }

                            $data = date('Y-m-d H:i:s').'|'.date('Y-m-d H:i:s', (int) $dateApparition);
                        }
                        break;
                        // ajoute un amis
                    case 'f':
                        $l = $request->get('l');
                        if ($this->isCryptingOk($a.$l)) {
                            $userFriend = $em->getRepository(User::class)->findOneBy(['username' => base64_decode($l)]);
                            if ($userFriend) {
                                $already = $em->getRepository(Friend::class)->findOneBy(['user' => $user, 'friend' => $userFriend]);
                                if (!$already) {
                                    // créé la liaison
                                    $friend = new Friend();
                                    $friend->setUser($userFriend);
                                    $friend->setFriend($user);

                                    $em->persist($friend);

                                    // créé le message
                                    $message = new Message();
                                    $message->setAuthor($user);
                                    $message->setNom($translator->trans('nouvelAmi.title'));
                                    $message->setContent($translator->trans('nouvelAmi.description'));

                                    // envoi au destinataire
                                    $messageUser = new MessageUser();
                                    $messageUser->setDestinataire($userFriend);
                                    $messageUser->setMessage($message);

                                    $em->persist($message);
                                    $em->persist($messageUser);

                                    $em->flush();
                                }
                                $data = '1';
                            }
                        }
                        break;
                        // upload vers imgur
                    case 'i':
                        $fileupload = $request->get('fileupload');
                        if ($this->isCryptingOk($a)) {
                            $clientId = $this->getParameter('imgur')['clientId'];
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_HEADER, false);
                            curl_setopt($ch, CURLOPT_VERBOSE, false);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible;)');
                            curl_setopt($ch, CURLOPT_URL, 'https://api.imgur.com/3/image');
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Client-ID '.$clientId]);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                                'image' => $fileupload,
                                'type' => 'base64',
                                'name' => 'screenshot.jpg',
                            ]);
                            try {
                                $retour = curl_exec($ch);
                                if ($retour !== false && $json = json_decode((string) $retour, false, flags: JSON_THROW_ON_ERROR)) {
                                    // récupérer les chemins
                                    if (true === $json->success && 200 === $json->status) {
                                        $url = (string) $json->data->link;
                                        $deleteHash = (string) $json->data->deletehash;

                                        $capture = new Capture();
                                        $capture->setUser($user);
                                        $capture->setUrl($url);
                                        $capture->setUrlTmb(str_replace('.jpg', 's.jpg', $url));
                                        $capture->setDeleteHash($deleteHash);

                                        $em->persist($capture);
                                        $em->flush();
                                        $data = '1';
                                    }
                                }
                            } catch (\Exception) {
                            }
                        }
                        break;
                        // mise à jour du lobby
                    case 'lu':
                        $partie = $request->request->getInt('partie');
                        $maxPlayer = $request->get('maxPlayer');
                        $carte = $request->get('carte');
                        $jeu = $request->get('jeu');
                        $version = $request->get('version');
                        $players = $request->get('players');
                        $pwd = $request->get('pwd');
                        if ($this->isCryptingOk($a.$partie.$maxPlayer.$carte.$jeu.$version.$players.$pwd) && $partie !== 0) {
                            $players = explode('-', $players);
                            $users = [];
                            $userRepository = $em->getRepository(User::class);
                            foreach ($players as $player) {
                                $userPlayer = $userRepository->findOneBy(['id' => $player]);
                                if ($userPlayer) {
                                    $users[] = $userPlayer;
                                }
                            }

                            $lobby = $em->getRepository(Lobby::class)->findOneBy(['partie' => $partie]);
                            if ($lobby) {
                                // met à jour
                                if (count($users) > 0) {
                                    $lobby->clearUsers();
                                    foreach ($users as $userPlayer) {
                                        $lobby->addUser($userPlayer);
                                    }
                                    $lobby->setDateUpdate(new \DateTime());
                                    $em->persist($lobby);
                                } else {
                                    $em->remove($lobby);
                                }
                                $em->flush();
                            } elseif (count($users) > 0) {
                                $lobby = new Lobby();
                                $lobby->setCarte(intval($carte));
                                $lobby->setPartie($partie);
                                $lobby->setMaximum(intval($maxPlayer));
                                $lobby->setJeu(intval($jeu));
                                $lobby->setVersion((float) $version);
                                $lobby->setPrivee($pwd);
                                $lobby->setDateUpdate(new \DateTime());
                                foreach ($users as $userPlayer) {
                                    $lobby->addUser($userPlayer);
                                }
                                $em->persist($lobby);
                                $em->flush();
                            }

                            $data = '1';
                        }
                        break;
                        // suppression du lobby
                    case 'ld':
                        if ($this->isCryptingOk($a)) {
                            $lobby = $em->getRepository(Lobby::class)
                                ->createQueryBuilder('l')
                                ->where(':user MEMBER OF l.users')
                                ->setParameter('user', $user)
                                ->setMaxResults(1)
                                ->getQuery()
                                ->getOneOrNullResult();
                            if ($lobby) {
                                $lobby->setDateUpdate(new \DateTime());
                                $lobby->removeUser($user);

                                if (0 === count($lobby->getUsers())) {
                                    $em->remove($lobby);
                                } else {
                                    $em->persist($lobby);
                                }
                                $em->flush();
                            }
                            $data = '1';
                        }
                        break;
                        // amis dans le lobby
                    case 'lf':
                        if ($this->isCryptingOk($a)) {
                            /** @var LobbyRepository $lobbyRepository */
                            $lobbyRepository = $em->getRepository(Lobby::class);

                            // fait le ménage dans les lobby
                            $lobbyRepository->deleteOld();

                            // récupère les amis dans le lobby
                            $friends = $lobbyRepository->createQueryBuilder('l')
                                ->select('l.partie', 'u.username')
                                ->innerJoin('App\Entity\User\Friend', 'f', 'WITH', 'f.friend MEMBER OF l.users')
                                ->leftJoin('App\Entity\User\User', 'u', 'WITH', 'f.friend = u')
                                ->andWhere('f.user = :user')
                                ->andWhere('f.isConfirmed = true')
                                ->andWhere('f.isBlocked = false')
                                ->setParameter('user', $user)
                                ->setFirstResult(0)
                                ->setMaxResults(100)
                                ->getQuery();
                            $content = '<?xml version="1.0" encoding="UTF-8"?>';
                            $content .= '<root>';
                            $content .= '<games>';
                            $friends = $friends->getScalarResult();
                            if ($friends) {
                                foreach ($friends as $friend) {
                                    $content .= '<t game="'.addslashes($friend['partie']).'"><![CDATA['.$friend['username'].']]></t>';
                                }
                            }
                            $content .= '</games>';
                            $content .= '</root>';

                            $response = new Response($content, 200, ['Content-Type' => 'text/xml']);
                            $response->headers->set('Notice', $session->getName().'='.$session->getId());
                            $response->headers->set('X-GAMEVERSION', $this->gameversion);

                            return $response;
                        }
                        break;
                }
            }
            $response = new Response((string) $data, 200, ['Content-Type' => 'text/plain']);
            $response->headers->set('Notice', $session->getName().'='.$session->getId());
            $response->headers->set('X-GAMEVERSION', $this->gameversion);

            return $response;
        }

        return new Response(!empty($session->get('visit')) ? '1' : '0', 200, ['Content-Type' => 'text/plain']);
    }

    #[Route('/unity/xml/game/connect', name: 'ninja_tooken_game_unity_connect')]
    public function connect(Request $request, AuthorizationCheckerInterface $authorizationChecker, Packages $assetsManager, CacheManager $cacheManager, TokenStorageInterface $tokenStorage, GameData $gameData, EntityManagerInterface $em): Response
    {
        // initialisation
        $content = '<?xml version="1.0" encoding="UTF-8"?><root>';
        $retour = '0';
        $friendsUsername = [];

        // données récupérées
        $this->time = preg_replace('/[^0-9]/i', '', (string) $request->get('time'));
        $this->crypt = $request->headers->get('X-COMMON');

        $session = $request->getSession();
        $this->phpsessid = $session->getName().'='.$session->getId();

        $this->gameversion = $this->getParameter('unity.version');
        $this->cryptUnity = $this->getParameter('unity.crypt');
        $this->idUtilisateur = 0;

        // variables postées
        $visiteur = $request->get('visiteur');

        $maxid = (int) $em->getRepository(User::class)
            ->createQueryBuilder('u')
            ->select('MAX(u.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // chargement du xml des données du jeu
        $doc = $gameData->getDocument();
        $xpXML = $doc->getElementsByTagName('experience')->item(0)->getElementsByTagName('x');
        $XP_LEVEL_100 = (int) $xpXML->item($xpXML->length - 2)->getAttribute('val');

        if ($authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY') || $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            /** @var UserInterface $user */
            $user = $tokenStorage->getToken()->getUser();
            $this->idUtilisateur = $user->getId();
            // les données du joueur

            // l'avatar
            $avatar = $user->getAvatar();
            if (empty($avatar)) {
                if ('m' === $user->getGender()) {
                    $avatar = $assetsManager->getUrl('images/boyz.jpg');
                } else {
                    $avatar = $assetsManager->getUrl('images/girlz.jpg');
                }
            } else {
                $avatar = $cacheManager->getBrowserPath('avatar/'.$avatar, 'avatar');
            }

            $content .= '<login avatar="'.$avatar.'" id="'.$user->getId().'" maxid="'.$maxid.'"><![CDATA['.$user->getUsername().']]></login>';

            $ninja = $user->getNinja();
            if ($ninja) {
                // fait le ménage dans les lobby
                $lobbies = $em->getRepository(Lobby::class)
                    ->createQueryBuilder('l')
                    ->where(':user MEMBER OF l.users')
                    ->setParameter('user', $user)
                    ->getQuery()
                    ->getResult();
                if ($lobbies) {
                    foreach ($lobbies as $l) {
                        $em->remove($l);
                    }
                    $em->flush();
                }
            } else {
                // on créé l'entité "ninja"
                $ninja = new Ninja();
                $ninja->setUser($user);

                $em->persist($ninja);
                $em->flush();
            }
            // calcul l'age
            $age = '10';
            $dateBirth = $user->getDateOfBirth();
            if ($dateBirth && $dateBirth != new \DateTime('0000-00-00 00:00:00')) {
                $age = $dateBirth->diff(new \DateTime())->format('%y');
            }
            // les données du ninja
            $clan = '';
            if (null !== $user->getClan()) {
                $clan = $user->getClan()->getClan()->getTag();
            }

            // permet de définir le niveau du ninja à minimum 100
            if ($ninja->getExperience() < $XP_LEVEL_100) {
                $ninja->setExperience($XP_LEVEL_100);
                $em->persist($ninja);
                $em->flush();
            }

            $content .= '<params force="'.$ninja->getAptitudeForce().'" vitesse="'.$ninja->getAptitudeVitesse().'" vie="'.$ninja->getAptitudeVie().'" chakra="'.$ninja->getAptitudeChakra().'" experience="'.$ninja->getExperience().'" grade="'.$ninja->getGrade().'" bouleElementaire="'.$ninja->getJutsuBoule().'" doubleSaut="'.$ninja->getJutsuDoubleSaut().'" bouclierElementaire="'.$ninja->getJutsuBouclier().'" marcherMur="'.$ninja->getJutsuMarcherMur().'" deflagrationElementaire="'.$ninja->getJutsuDeflagration().'" transformationAqueuse="'.$ninja->getJutsuTransformationAqueuse().'" changerObjet="'.$ninja->getJutsuMetamorphose().'" multishoot="'.$ninja->getJutsuMultishoot().'" invisibleman="'.$ninja->getJutsuInvisibilite().'" resistanceExplosion="'.$ninja->getJutsuResistanceExplosion().'" phoenix="'.$ninja->getJutsuPhoenix().'" vague="'.$ninja->getJutsuVague().'" pieux="'.$ninja->getJutsuPieux().'" tornade="'.$ninja->getJutsuTornade().'" teleportation="'.$ninja->getJutsuTeleportation().'" kusanagi="'.$ninja->getJutsuKusanagi().'" acierRenforce="'.$ninja->getJutsuAcierRenforce().'" chakraVie="'.$ninja->getJutsuChakraVie().'" kamiRaijin="'.$ninja->getJutsuRaijin().'" kamiSarutahiko="'.$ninja->getJutsuSarutahiko().'" kamiFujin="'.$ninja->getJutsuFujin().'" kamiSusanoo="'.$ninja->getJutsuSusanoo().'" kamiKagutsuchi="'.$ninja->getJutsuKagutsuchi().'" classe="'.$ninja->getClasse().'" masque="'.$ninja->getMasque().'" couleurMasque="'.$ninja->getMasqueCouleur().'" detailMasque="'.$ninja->getMasqueDetail().'" costume="'.$ninja->getCostume().'" couleurCostume="'.$ninja->getCostumeCouleur().'" detailCostume="'.$ninja->getCostumeDetail().'" assassinnat="'.$ninja->getMissionAssassinnat().'" course="'.$ninja->getMissionCourse().'" langue="'.$request->getLocale().'" accomplissement="'.$ninja->getAccomplissement().'" age="'.$age.'" sexe="'.('f' === $user->getGender() ? 'F' : 'H').'" roles="'.implode('-', $user->getRoles()).'" clan="'.$clan.'"/>';

            // liste d'amis
            /** @var FriendRepository $friendRepository */
            $friendRepository = $em->getRepository(Friend::class);
            $friends = $friendRepository->getFriends($user, 100, 0);
            if ($friends) {
                foreach ($friends as $friend) {
                    $friendsUsername[] = '<t><![CDATA['.$friend->getFriend()->getUsername().']]></t>';
                }
            }
            $retour = '1';
        } elseif (!empty($visiteur)) {
            $content .= '<login avatar="" id="'.($maxid + date('Hms')).'" maxid="'.$maxid.'"><![CDATA[Visiteur_'.date('Hms').']]></login>';
            $content .= '<params force="50" vitesse="50" vie="50" chakra="55" experience="'.$XP_LEVEL_100.'" grade="0" bouleElementaire="0" doubleSaut="30" bouclierElementaire="30" marcherMur="10" deflagrationElementaire="10" transformationAqueuse="0" changerObjet="0" multishoot="0" invisibleman="0" resistanceExplosion="0" phoenix="0" vague="0" pieux="0" tornade="0" teleportation="0" kusanagi="0" acierRenforce="0" chakraVie="0" kamiRaijin="0" kamiSarutahiko="0" kamiFujin="0" kamiSusanoo="0" kamiKagutsuchi="0" classe="" masque="0" couleurMasque="0" detailMasque="0" costume="0" couleurCostume="0" detailCostume="0" assassinnat="0" course="0" langue="'.$request->getLocale().'" accomplissement="0000000000000000000000000" age="10" sexe="H" roles="ROLE_USER" clan=""/>';
            $retour = '1';
        }

        $content .= '<friends>';
        $content .= implode('', $friendsUsername);
        $content .= '</friends>';
        $content .= preg_replace('/\r\n|\r|\n|\t|\s\s+/m', '', (string) $gameData->getRaw());
        $content .= '<retour>'.$retour.'</retour>';
        $content .= '</root>';

        $response = new Response($content, 200, ['Content-Type' => 'text/xml']);
        $response->headers->set('Notice', $this->phpsessid);
        $response->headers->set('X-GAMEVERSION', $this->gameversion);

        return $response;
    }

    // fonction de cryptage
    private function isCryptingOk(string $val = ''): bool
    {
        return $this->crypt === hash('sha256', $this->cryptUnity.$this->phpsessid.$this->time.$val.$this->idUtilisateur.$this->phpsessid.$this->gameversion, false);
    }
}
