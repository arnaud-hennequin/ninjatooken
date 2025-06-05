<?php

namespace App\Entity\Game;

use App\Entity\User\User;
use App\Entity\User\UserInterface;
use App\Repository\NinjaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Ninja.
 */
#[ORM\Table(name: 'nt_ninja')]
#[ORM\Entity(repositoryClass: NinjaRepository::class)]
class Ninja
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    /**
     * user of the ninja.
     */
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\OneToOne(inversedBy: 'ninja', targetEntity: User::class)]
    private ?UserInterface $user;

    #[ORM\Column(name: 'aptitude_force', type: Types::SMALLINT)]
    private int $aptitudeForce = 0;

    #[ORM\Column(name: 'aptitude_vitesse', type: Types::SMALLINT)]
    private int $aptitudeVitesse = 0;

    #[ORM\Column(name: 'aptitude_vie', type: Types::SMALLINT)]
    private int $aptitudeVie = 0;

    #[ORM\Column(name: 'aptitude_chakra', type: Types::SMALLINT)]
    private int $aptitudeChakra = 0;

    #[ORM\Column(name: 'jutsu_boule', type: Types::SMALLINT)]
    private int $jutsuBoule = 0;

    #[ORM\Column(name: 'jutsu_double_saut', type: Types::SMALLINT)]
    private int $jutsuDoubleSaut = 0;

    #[ORM\Column(name: 'jutsu_bouclier', type: Types::SMALLINT)]
    private int $jutsuBouclier = 0;

    #[ORM\Column(name: 'jutsu_marcher_mur', type: Types::SMALLINT)]
    private int $jutsuMarcherMur = 0;

    #[ORM\Column(name: 'jutsu_deflagration', type: Types::SMALLINT)]
    private int $jutsuDeflagration = 0;

    #[ORM\Column(name: 'jutsu_transformation_aqueuse', type: Types::SMALLINT)]
    private int $jutsuTransformationAqueuse = 0;

    #[ORM\Column(name: 'jutsu_metamorphose', type: Types::SMALLINT)]
    private int $jutsuMetamorphose = 0;

    #[ORM\Column(name: 'jutsu_multishoot', type: Types::SMALLINT)]
    private int $jutsuMultishoot = 0;

    #[ORM\Column(name: 'jutsu_invisibilite', type: Types::SMALLINT)]
    private int $jutsuInvisibilite = 0;

    #[ORM\Column(name: 'jutsu_resistance_explosion', type: Types::SMALLINT)]
    private int $jutsuResistanceExplosion = 0;

    #[ORM\Column(name: 'jutsu_phoenix', type: Types::SMALLINT)]
    private int $jutsuPhoenix = 0;

    #[ORM\Column(name: 'jutsu_vague', type: Types::SMALLINT)]
    private int $jutsuVague = 0;

    #[ORM\Column(name: 'jutsu_pieux', type: Types::SMALLINT)]
    private int $jutsuPieux = 0;

    #[ORM\Column(name: 'jutsu_teleportation', type: Types::SMALLINT)]
    private int $jutsuTeleportation = 0;

    #[ORM\Column(name: 'jutsu_tornade', type: Types::SMALLINT)]
    private int $jutsuTornade = 0;

    #[ORM\Column(name: 'jutsu_kusanagi', type: Types::SMALLINT)]
    private int $jutsuKusanagi = 0;

    #[ORM\Column(name: 'jutsu_acier_renforce', type: Types::SMALLINT)]
    private int $jutsuAcierRenforce = 0;

    #[ORM\Column(name: 'jutsu_chakra_vie', type: Types::SMALLINT)]
    private int $jutsuChakraVie = 0;

    #[ORM\Column(name: 'jutsu_fujin', type: Types::SMALLINT)]
    private int $jutsuFujin = 0;

    #[ORM\Column(name: 'jutsu_raijin', type: Types::SMALLINT)]
    private int $jutsuRaijin = 0;

    #[ORM\Column(name: 'jutsu_sarutahiko', type: Types::SMALLINT)]
    private int $jutsuSarutahiko = 0;

    #[ORM\Column(name: 'jutsu_susanoo', type: Types::SMALLINT)]
    private int $jutsuSusanoo = 0;

    #[ORM\Column(name: 'jutsu_kagutsuchi', type: Types::SMALLINT)]
    private int $jutsuKagutsuchi = 0;

    #[ORM\Column(name: 'grade', type: Types::SMALLINT)]
    private int $grade = 0;

    #[ORM\Column(name: 'experience', type: Types::BIGINT)]
    private string $experience = '0';

    #[ORM\Column(name: 'classe', type: Types::STRING, length: 25)]
    private string $classe = '';

    #[ORM\Column(name: 'masque', type: Types::SMALLINT)]
    private int $masque = 0;

    #[ORM\Column(name: 'masque_couleur', type: Types::SMALLINT)]
    private int $masqueCouleur = 0;

    #[ORM\Column(name: 'masque_detail', type: Types::SMALLINT)]
    private int $masqueDetail = 0;

    #[ORM\Column(name: 'costume', type: Types::SMALLINT)]
    private int $costume = 0;

    #[ORM\Column(name: 'costume_couleur', type: Types::SMALLINT)]
    private int $costumeCouleur = 0;

    #[ORM\Column(name: 'costume_detail', type: Types::SMALLINT)]
    private int $costumeDetail = 0;

    #[ORM\Column(name: 'mission_assassinnat', type: Types::SMALLINT)]
    private int $missionAssassinnat = 0;

    #[ORM\Column(name: 'mission_course', type: Types::SMALLINT)]
    private int $missionCourse = 0;

    #[ORM\Column(name: 'accomplissement', type: Types::STRING, length: 25)]
    private string $accomplissement = '0000000000000000000000000';

    public function __toString()
    {
        return (string) $this->getId();
    }

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set aptitudeForce.
     */
    public function setAptitudeForce(int $aptitudeForce): self
    {
        $this->aptitudeForce = $aptitudeForce;

        return $this;
    }

    /**
     * Get aptitudeForce.
     */
    public function getAptitudeForce(): int
    {
        return $this->aptitudeForce ?? 0;
    }

    /**
     * Set aptitudeVitesse.
     */
    public function setAptitudeVitesse(int $aptitudeVitesse): self
    {
        $this->aptitudeVitesse = $aptitudeVitesse;

        return $this;
    }

    /**
     * Get aptitudeVitesse.
     */
    public function getAptitudeVitesse(): int
    {
        return $this->aptitudeVitesse  ?? 0;
    }

    /**
     * Set aptitudeVie.
     */
    public function setAptitudeVie(int $aptitudeVie): self
    {
        $this->aptitudeVie = $aptitudeVie;

        return $this;
    }

    /**
     * Get aptitudeVie.
     */
    public function getAptitudeVie(): int
    {
        return $this->aptitudeVie ?? 0;
    }

    /**
     * Set aptitudeChakra.
     */
    public function setAptitudeChakra(int $aptitudeChakra): self
    {
        $this->aptitudeChakra = $aptitudeChakra;

        return $this;
    }

    /**
     * Get aptitudeChakra.
     */
    public function getAptitudeChakra(): int
    {
        return $this->aptitudeChakra ?? 0;
    }

    /**
     * Set jutsuBoule.
     */
    public function setJutsuBoule(int $jutsuBoule): self
    {
        $this->jutsuBoule = $jutsuBoule;

        return $this;
    }

    /**
     * Get jutsuBoule.
     */
    public function getJutsuBoule(): ?int
    {
        return $this->jutsuBoule;
    }

    /**
     * Set jutsuDoubleSaut.
     */
    public function setJutsuDoubleSaut(int $jutsuDoubleSaut): self
    {
        $this->jutsuDoubleSaut = $jutsuDoubleSaut;

        return $this;
    }

    /**
     * Get jutsuDoubleSaut.
     */
    public function getJutsuDoubleSaut(): ?int
    {
        return $this->jutsuDoubleSaut;
    }

    /**
     * Set jutsuBouclier.
     */
    public function setJutsuBouclier(int $jutsuBouclier): self
    {
        $this->jutsuBouclier = $jutsuBouclier;

        return $this;
    }

    /**
     * Get jutsuBouclier.
     */
    public function getJutsuBouclier(): ?int
    {
        return $this->jutsuBouclier;
    }

    /**
     * Set jutsuMarcherMur.
     */
    public function setJutsuMarcherMur(int $jutsuMarcherMur): self
    {
        $this->jutsuMarcherMur = $jutsuMarcherMur;

        return $this;
    }

    /**
     * Get jutsuMarcherMur.
     */
    public function getJutsuMarcherMur(): ?int
    {
        return $this->jutsuMarcherMur;
    }

    /**
     * Set jutsuDeflagration.
     */
    public function setJutsuDeflagration(int $jutsuDeflagration): self
    {
        $this->jutsuDeflagration = $jutsuDeflagration;

        return $this;
    }

    /**
     * Get jutsuDeflagration.
     */
    public function getJutsuDeflagration(): ?int
    {
        return $this->jutsuDeflagration;
    }

    /**
     * Set jutsuTransformationAqueuse.
     */
    public function setJutsuTransformationAqueuse(int $jutsuTransformationAqueuse): self
    {
        $this->jutsuTransformationAqueuse = $jutsuTransformationAqueuse;

        return $this;
    }

    /**
     * Get jutsuTransformationAqueuse.
     */
    public function getJutsuTransformationAqueuse(): ?int
    {
        return $this->jutsuTransformationAqueuse;
    }

    /**
     * Set jutsuMetamorphose.
     */
    public function setJutsuMetamorphose(int $jutsuMetamorphose): self
    {
        $this->jutsuMetamorphose = $jutsuMetamorphose;

        return $this;
    }

    /**
     * Get jutsuMetamorphose.
     */
    public function getJutsuMetamorphose(): ?int
    {
        return $this->jutsuMetamorphose;
    }

    /**
     * Set jutsuMultishoot.
     */
    public function setJutsuMultishoot(int $jutsuMultishoot): self
    {
        $this->jutsuMultishoot = $jutsuMultishoot;

        return $this;
    }

    /**
     * Get jutsuMultishoot.
     */
    public function getJutsuMultishoot(): ?int
    {
        return $this->jutsuMultishoot;
    }

    /**
     * Set jutsuInvisibilite.
     */
    public function setJutsuInvisibilite(int $jutsuInvisibilite): self
    {
        $this->jutsuInvisibilite = $jutsuInvisibilite;

        return $this;
    }

    /**
     * Get jutsuInvisibilite.
     */
    public function getJutsuInvisibilite(): ?int
    {
        return $this->jutsuInvisibilite;
    }

    /**
     * Set jutsuResistanceExplosion.
     */
    public function setJutsuResistanceExplosion(int $jutsuResistanceExplosion): self
    {
        $this->jutsuResistanceExplosion = $jutsuResistanceExplosion;

        return $this;
    }

    /**
     * Get jutsuResistanceExplosion.
     */
    public function getJutsuResistanceExplosion(): ?int
    {
        return $this->jutsuResistanceExplosion;
    }

    /**
     * Set jutsuPhoenix.
     */
    public function setJutsuPhoenix(int $jutsuPhoenix): self
    {
        $this->jutsuPhoenix = $jutsuPhoenix;

        return $this;
    }

    /**
     * Get jutsuPhoenix.
     */
    public function getJutsuPhoenix(): ?int
    {
        return $this->jutsuPhoenix;
    }

    /**
     * Set jutsuVague.
     */
    public function setJutsuVague(int $jutsuVague): self
    {
        $this->jutsuVague = $jutsuVague;

        return $this;
    }

    /**
     * Get jutsuVague.
     */
    public function getJutsuVague(): ?int
    {
        return $this->jutsuVague;
    }

    /**
     * Set jutsuPieux.
     */
    public function setJutsuPieux(int $jutsuPieux): self
    {
        $this->jutsuPieux = $jutsuPieux;

        return $this;
    }

    /**
     * Get jutsuPieux.
     */
    public function getJutsuPieux(): ?int
    {
        return $this->jutsuPieux;
    }

    /**
     * Set jutsuTeleportation.
     */
    public function setJutsuTeleportation(int $jutsuTeleportation): self
    {
        $this->jutsuTeleportation = $jutsuTeleportation;

        return $this;
    }

    /**
     * Get jutsuTeleportation.
     */
    public function getJutsuTeleportation(): ?int
    {
        return $this->jutsuTeleportation;
    }

    /**
     * Set jutsuTornade.
     */
    public function setJutsuTornade(int $jutsuTornade): self
    {
        $this->jutsuTornade = $jutsuTornade;

        return $this;
    }

    /**
     * Get jutsuTornade.
     */
    public function getJutsuTornade(): ?int
    {
        return $this->jutsuTornade;
    }

    /**
     * Set jutsuKusanagi.
     */
    public function setJutsuKusanagi(int $jutsuKusanagi): self
    {
        $this->jutsuKusanagi = $jutsuKusanagi;

        return $this;
    }

    /**
     * Get jutsuKusanagi.
     */
    public function getJutsuKusanagi(): ?int
    {
        return $this->jutsuKusanagi;
    }

    /**
     * Set jutsuAcierRenforce.
     */
    public function setJutsuAcierRenforce(int $jutsuAcierRenforce): self
    {
        $this->jutsuAcierRenforce = $jutsuAcierRenforce;

        return $this;
    }

    /**
     * Get jutsuAcierRenforce.
     */
    public function getJutsuAcierRenforce(): ?int
    {
        return $this->jutsuAcierRenforce;
    }

    /**
     * Set jutsuChakraVie.
     */
    public function setJutsuChakraVie(int $jutsuChakraVie): self
    {
        $this->jutsuChakraVie = $jutsuChakraVie;

        return $this;
    }

    /**
     * Get jutsuChakraVie.
     */
    public function getJutsuChakraVie(): ?int
    {
        return $this->jutsuChakraVie;
    }

    /**
     * Set grade.
     */
    public function setGrade(int $grade): self
    {
        $this->grade = $grade;

        return $this;
    }

    /**
     * Get grade.
     */
    public function getGrade(): ?int
    {
        return $this->grade;
    }

    /**
     * Set experience.
     */
    public function setExperience(int $experience): self
    {
        $this->experience = (string) $experience;

        return $this;
    }

    /**
     * Get experience.
     */
    public function getExperience(): int
    {
        return (int) $this->experience;
    }

    /**
     * Set classe.
     */
    public function setClasse(string $classe): self
    {
        $this->classe = $classe;

        return $this;
    }

    /**
     * Get classe.
     */
    public function getClasse(): ?string
    {
        return $this->classe;
    }

    /**
     * Set user.
     */
    public function setUser(?UserInterface $user = null): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * Set masque.
     */
    public function setMasque(int $masque): self
    {
        $this->masque = $masque;

        return $this;
    }

    /**
     * Get masque.
     */
    public function getMasque(): ?int
    {
        return $this->masque;
    }

    /**
     * Set masqueCouleur.
     */
    public function setMasqueCouleur(int $masqueCouleur): self
    {
        $this->masqueCouleur = $masqueCouleur;

        return $this;
    }

    /**
     * Get masqueCouleur.
     */
    public function getMasqueCouleur(): ?int
    {
        return $this->masqueCouleur;
    }

    /**
     * Set masqueDetail.
     */
    public function setMasqueDetail(int $masqueDetail): self
    {
        $this->masqueDetail = $masqueDetail;

        return $this;
    }

    /**
     * Get masqueDetail.
     */
    public function getMasqueDetail(): ?int
    {
        return $this->masqueDetail;
    }

    /**
     * Set costume.
     */
    public function setCostume(int $costume): self
    {
        $this->costume = $costume;

        return $this;
    }

    /**
     * Get costume.
     */
    public function getCostume(): ?int
    {
        return $this->costume;
    }

    /**
     * Set costumeCouleur.
     */
    public function setCostumeCouleur(int $costumeCouleur): self
    {
        $this->costumeCouleur = $costumeCouleur;

        return $this;
    }

    /**
     * Get costumeCouleur.
     */
    public function getCostumeCouleur(): ?int
    {
        return $this->costumeCouleur;
    }

    /**
     * Set costumeDetail.
     */
    public function setCostumeDetail(int $costumeDetail): self
    {
        $this->costumeDetail = $costumeDetail;

        return $this;
    }

    /**
     * Get costumeDetail.
     */
    public function getCostumeDetail(): ?int
    {
        return $this->costumeDetail;
    }

    /**
     * Set missionAssassinnat.
     */
    public function setMissionAssassinnat(int $missionAssassinnat): self
    {
        $this->missionAssassinnat = $missionAssassinnat;

        return $this;
    }

    /**
     * Get missionAssassinnat.
     */
    public function getMissionAssassinnat(): ?int
    {
        return $this->missionAssassinnat;
    }

    /**
     * Set missionCourse.
     */
    public function setMissionCourse(int $missionCourse): self
    {
        $this->missionCourse = $missionCourse;

        return $this;
    }

    /**
     * Get missionCourse.
     */
    public function getMissionCourse(): ?int
    {
        return $this->missionCourse;
    }

    /**
     * Set accomplissement.
     */
    public function setAccomplissement(string $accomplissement): self
    {
        $this->accomplissement = $accomplissement;

        return $this;
    }

    /**
     * Get accomplissement.
     */
    public function getAccomplissement(): ?string
    {
        return $this->accomplissement;
    }

    /**
     * Set jutsuFujin.
     */
    public function setJutsuFujin(int $jutsuFujin): self
    {
        $this->jutsuFujin = $jutsuFujin;

        return $this;
    }

    /**
     * Get jutsuFujin.
     */
    public function getJutsuFujin(): ?int
    {
        return $this->jutsuFujin;
    }

    /**
     * Set jutsuRaijin.
     */
    public function setJutsuRaijin(int $jutsuRaijin): self
    {
        $this->jutsuRaijin = $jutsuRaijin;

        return $this;
    }

    /**
     * Get jutsuRaijin.
     */
    public function getJutsuRaijin(): ?int
    {
        return $this->jutsuRaijin;
    }

    /**
     * Set jutsuSarutahiko.
     */
    public function setJutsuSarutahiko(int $jutsuSarutahiko): self
    {
        $this->jutsuSarutahiko = $jutsuSarutahiko;

        return $this;
    }

    /**
     * Get jutsuSarutahiko.
     */
    public function getJutsuSarutahiko(): ?int
    {
        return $this->jutsuSarutahiko;
    }

    /**
     * Set jutsuSusanoo.
     */
    public function setJutsuSusanoo(int $jutsuSusanoo): self
    {
        $this->jutsuSusanoo = $jutsuSusanoo;

        return $this;
    }

    /**
     * Get jutsuSusanoo.
     */
    public function getJutsuSusanoo(): ?int
    {
        return $this->jutsuSusanoo;
    }

    /**
     * Set jutsuKagutsuchi.
     */
    public function setJutsuKagutsuchi(int $jutsuKagutsuchi): self
    {
        $this->jutsuKagutsuchi = $jutsuKagutsuchi;

        return $this;
    }

    /**
     * Get jutsuKagutsuchi.
     */
    public function getJutsuKagutsuchi(): ?int
    {
        return $this->jutsuKagutsuchi;
    }
}
