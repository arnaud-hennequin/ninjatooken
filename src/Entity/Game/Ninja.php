<?php

namespace App\Entity\Game;

use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Ninja
 *
 * @ORM\Table(name="nt_ninja")
 * @ORM\Entity(repositoryClass="App\Repository\NinjaRepository")
 */
class Ninja
{

    /**
     * @var integer|null
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
    * user of the ninja
    *
    * @ORM\OneToOne(targetEntity="App\Entity\User\User", inversedBy="ninja")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
    * @var User
    */
    private User $user;

    /**
     * @var integer
     *
     * @ORM\Column(name="aptitude_force", type="smallint")
     */
    private int $aptitudeForce = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="aptitude_vitesse", type="smallint")
     */
    private int $aptitudeVitesse = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="aptitude_vie", type="smallint")
     */
    private int $aptitudeVie = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="aptitude_chakra", type="smallint")
     */
    private int $aptitudeChakra = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_boule", type="smallint")
     */
    private int $jutsuBoule = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_double_saut", type="smallint")
     */
    private int $jutsuDoubleSaut = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_bouclier", type="smallint")
     */
    private int $jutsuBouclier = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_marcher_mur", type="smallint")
     */
    private int $jutsuMarcherMur = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_deflagration", type="smallint")
     */
    private int $jutsuDeflagration = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_transformation_aqueuse", type="smallint")
     */
    private int $jutsuTransformationAqueuse = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_metamorphose", type="smallint")
     */
    private int $jutsuMetamorphose = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_multishoot", type="smallint")
     */
    private int $jutsuMultishoot = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_invisibilite", type="smallint")
     */
    private int $jutsuInvisibilite = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_resistance_explosion", type="smallint")
     */
    private int $jutsuResistanceExplosion = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_phoenix", type="smallint")
     */
    private int $jutsuPhoenix = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_vague", type="smallint")
     */
    private int $jutsuVague = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_pieux", type="smallint")
     */
    private int $jutsuPieux = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_teleportation", type="smallint")
     */
    private int $jutsuTeleportation = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_tornade", type="smallint")
     */
    private int $jutsuTornade = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_kusanagi", type="smallint")
     */
    private int $jutsuKusanagi = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_acier_renforce", type="smallint")
     */
    private int $jutsuAcierRenforce = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_chakra_vie", type="smallint")
     */
    private int $jutsuChakraVie = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_fujin", type="smallint")
     */
    private int $jutsuFujin = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_raijin", type="smallint")
     */
    private int $jutsuRaijin = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_sarutahiko", type="smallint")
     */
    private int $jutsuSarutahiko = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_susanoo", type="smallint")
     */
    private int $jutsuSusanoo = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="jutsu_kagutsuchi", type="smallint")
     */
    private int $jutsuKagutsuchi = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="grade", type="smallint")
     */
    private int $grade = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="experience", type="bigint")
     */
    private int $experience = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="classe", type="string", length=25)
     */
    private string $classe = "";

    /**
     * @var integer
     *
     * @ORM\Column(name="masque", type="smallint")
     */
    private int $masque = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="masque_couleur", type="smallint")
     */
    private int $masqueCouleur = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="masque_detail", type="smallint")
     */
    private int $masqueDetail = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="costume", type="smallint")
     */
    private int $costume = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="costume_couleur", type="smallint")
     */
    private int $costumeCouleur = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="costume_detail", type="smallint")
     */
    private int $costumeDetail = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="mission_assassinnat", type="smallint")
     */
    private int $missionAssassinnat = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="mission_course", type="smallint")
     */
    private int $missionCourse = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="accomplissement", type="string", length=25)
     */
    private string $accomplissement = "0000000000000000000000000";

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getId();
    }

    /**
     * Get id
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set aptitudeForce
     *
     * @param integer $aptitudeForce
     * @return Ninja
     */
    public function setAptitudeForce(int $aptitudeForce): self
    {
        $this->aptitudeForce = $aptitudeForce;

        return $this;
    }

    /**
     * Get aptitudeForce
     *
     * @return int|null
     */
    public function getAptitudeForce(): ?int
    {
        return $this->aptitudeForce;
    }

    /**
     * Set aptitudeVitesse
     *
     * @param integer $aptitudeVitesse
     * @return Ninja
     */
    public function setAptitudeVitesse(int $aptitudeVitesse): self
    {
        $this->aptitudeVitesse = $aptitudeVitesse;

        return $this;
    }

    /**
     * Get aptitudeVitesse
     *
     * @return int|null
     */
    public function getAptitudeVitesse(): ?int
    {
        return $this->aptitudeVitesse;
    }

    /**
     * Set aptitudeVie
     *
     * @param integer $aptitudeVie
     * @return Ninja
     */
    public function setAptitudeVie(int $aptitudeVie): self
    {
        $this->aptitudeVie = $aptitudeVie;

        return $this;
    }

    /**
     * Get aptitudeVie
     *
     * @return int|null
     */
    public function getAptitudeVie(): ?int
    {
        return $this->aptitudeVie;
    }

    /**
     * Set aptitudeChakra
     *
     * @param integer $aptitudeChakra
     * @return Ninja
     */
    public function setAptitudeChakra(int $aptitudeChakra): self
    {
        $this->aptitudeChakra = $aptitudeChakra;

        return $this;
    }

    /**
     * Get aptitudeChakra
     *
     * @return int|null
     */
    public function getAptitudeChakra(): ?int
    {
        return $this->aptitudeChakra;
    }

    /**
     * Set jutsuBoule
     *
     * @param integer $jutsuBoule
     * @return Ninja
     */
    public function setJutsuBoule(int $jutsuBoule): self
    {
        $this->jutsuBoule = $jutsuBoule;

        return $this;
    }

    /**
     * Get jutsuBoule
     *
     * @return int|null
     */
    public function getJutsuBoule(): ?int
    {
        return $this->jutsuBoule;
    }

    /**
     * Set jutsuDoubleSaut
     *
     * @param integer $jutsuDoubleSaut
     * @return Ninja
     */
    public function setJutsuDoubleSaut(int $jutsuDoubleSaut): self
    {
        $this->jutsuDoubleSaut = $jutsuDoubleSaut;

        return $this;
    }

    /**
     * Get jutsuDoubleSaut
     *
     * @return int|null
     */
    public function getJutsuDoubleSaut(): ?int
    {
        return $this->jutsuDoubleSaut;
    }

    /**
     * Set jutsuBouclier
     *
     * @param integer $jutsuBouclier
     * @return Ninja
     */
    public function setJutsuBouclier(int $jutsuBouclier): self
    {
        $this->jutsuBouclier = $jutsuBouclier;

        return $this;
    }

    /**
     * Get jutsuBouclier
     *
     * @return int|null
     */
    public function getJutsuBouclier(): ?int
    {
        return $this->jutsuBouclier;
    }

    /**
     * Set jutsuMarcherMur
     *
     * @param integer $jutsuMarcherMur
     * @return Ninja
     */
    public function setJutsuMarcherMur(int $jutsuMarcherMur): self
    {
        $this->jutsuMarcherMur = $jutsuMarcherMur;

        return $this;
    }

    /**
     * Get jutsuMarcherMur
     *
     * @return int|null
     */
    public function getJutsuMarcherMur(): ?int
    {
        return $this->jutsuMarcherMur;
    }

    /**
     * Set jutsuDeflagration
     *
     * @param integer $jutsuDeflagration
     * @return Ninja
     */
    public function setJutsuDeflagration(int $jutsuDeflagration): self
    {
        $this->jutsuDeflagration = $jutsuDeflagration;

        return $this;
    }

    /**
     * Get jutsuDeflagration
     *
     * @return int|null
     */
    public function getJutsuDeflagration(): ?int
    {
        return $this->jutsuDeflagration;
    }

    /**
     * Set jutsuTransformationAqueuse
     *
     * @param integer $jutsuTransformationAqueuse
     * @return Ninja
     */
    public function setJutsuTransformationAqueuse(int $jutsuTransformationAqueuse): self
    {
        $this->jutsuTransformationAqueuse = $jutsuTransformationAqueuse;

        return $this;
    }

    /**
     * Get jutsuTransformationAqueuse
     *
     * @return int|null
     */
    public function getJutsuTransformationAqueuse(): ?int
    {
        return $this->jutsuTransformationAqueuse;
    }

    /**
     * Set jutsuMetamorphose
     *
     * @param integer $jutsuMetamorphose
     * @return Ninja
     */
    public function setJutsuMetamorphose(int $jutsuMetamorphose): self
    {
        $this->jutsuMetamorphose = $jutsuMetamorphose;

        return $this;
    }

    /**
     * Get jutsuMetamorphose
     *
     * @return int|null
     */
    public function getJutsuMetamorphose(): ?int
    {
        return $this->jutsuMetamorphose;
    }

    /**
     * Set jutsuMultishoot
     *
     * @param integer $jutsuMultishoot
     * @return Ninja
     */
    public function setJutsuMultishoot(int $jutsuMultishoot): self
    {
        $this->jutsuMultishoot = $jutsuMultishoot;

        return $this;
    }

    /**
     * Get jutsuMultishoot
     *
     * @return int|null
     */
    public function getJutsuMultishoot(): ?int
    {
        return $this->jutsuMultishoot;
    }

    /**
     * Set jutsuInvisibilite
     *
     * @param integer $jutsuInvisibilite
     * @return Ninja
     */
    public function setJutsuInvisibilite(int $jutsuInvisibilite): self
    {
        $this->jutsuInvisibilite = $jutsuInvisibilite;

        return $this;
    }

    /**
     * Get jutsuInvisibilite
     *
     * @return int|null
     */
    public function getJutsuInvisibilite(): ?int
    {
        return $this->jutsuInvisibilite;
    }

    /**
     * Set jutsuResistanceExplosion
     *
     * @param integer $jutsuResistanceExplosion
     * @return Ninja
     */
    public function setJutsuResistanceExplosion(int $jutsuResistanceExplosion): self
    {
        $this->jutsuResistanceExplosion = $jutsuResistanceExplosion;

        return $this;
    }

    /**
     * Get jutsuResistanceExplosion
     *
     * @return int|null
     */
    public function getJutsuResistanceExplosion(): ?int
    {
        return $this->jutsuResistanceExplosion;
    }

    /**
     * Set jutsuPhoenix
     *
     * @param integer $jutsuPhoenix
     * @return Ninja
     */
    public function setJutsuPhoenix(int $jutsuPhoenix): self
    {
        $this->jutsuPhoenix = $jutsuPhoenix;

        return $this;
    }

    /**
     * Get jutsuPhoenix
     *
     * @return int|null
     */
    public function getJutsuPhoenix(): ?int
    {
        return $this->jutsuPhoenix;
    }

    /**
     * Set jutsuVague
     *
     * @param integer $jutsuVague
     * @return Ninja
     */
    public function setJutsuVague(int $jutsuVague): self
    {
        $this->jutsuVague = $jutsuVague;

        return $this;
    }

    /**
     * Get jutsuVague
     *
     * @return int|null
     */
    public function getJutsuVague(): ?int
    {
        return $this->jutsuVague;
    }

    /**
     * Set jutsuPieux
     *
     * @param integer $jutsuPieux
     * @return Ninja
     */
    public function setJutsuPieux(int $jutsuPieux): self
    {
        $this->jutsuPieux = $jutsuPieux;

        return $this;
    }

    /**
     * Get jutsuPieux
     *
     * @return int|null
     */
    public function getJutsuPieux(): ?int
    {
        return $this->jutsuPieux;
    }

    /**
     * Set jutsuTeleportation
     *
     * @param integer $jutsuTeleportation
     * @return Ninja
     */
    public function setJutsuTeleportation(int $jutsuTeleportation): self
    {
        $this->jutsuTeleportation = $jutsuTeleportation;

        return $this;
    }

    /**
     * Get jutsuTeleportation
     *
     * @return int|null
     */
    public function getJutsuTeleportation(): ?int
    {
        return $this->jutsuTeleportation;
    }

    /**
     * Set jutsuTornade
     *
     * @param integer $jutsuTornade
     * @return Ninja
     */
    public function setJutsuTornade(int $jutsuTornade): self
    {
        $this->jutsuTornade = $jutsuTornade;

        return $this;
    }

    /**
     * Get jutsuTornade
     *
     * @return int|null
     */
    public function getJutsuTornade(): ?int
    {
        return $this->jutsuTornade;
    }

    /**
     * Set jutsuKusanagi
     *
     * @param integer $jutsuKusanagi
     * @return Ninja
     */
    public function setJutsuKusanagi(int $jutsuKusanagi): self
    {
        $this->jutsuKusanagi = $jutsuKusanagi;

        return $this;
    }

    /**
     * Get jutsuKusanagi
     *
     * @return int|null
     */
    public function getJutsuKusanagi(): ?int
    {
        return $this->jutsuKusanagi;
    }

    /**
     * Set jutsuAcierRenforce
     *
     * @param integer $jutsuAcierRenforce
     * @return Ninja
     */
    public function setJutsuAcierRenforce(int $jutsuAcierRenforce): self
    {
        $this->jutsuAcierRenforce = $jutsuAcierRenforce;

        return $this;
    }

    /**
     * Get jutsuAcierRenforce
     *
     * @return int|null
     */
    public function getJutsuAcierRenforce(): ?int
    {
        return $this->jutsuAcierRenforce;
    }

    /**
     * Set jutsuChakraVie
     *
     * @param integer $jutsuChakraVie
     * @return Ninja
     */
    public function setJutsuChakraVie(int $jutsuChakraVie): self
    {
        $this->jutsuChakraVie = $jutsuChakraVie;

        return $this;
    }

    /**
     * Get jutsuChakraVie
     *
     * @return int|null
     */
    public function getJutsuChakraVie(): ?int
    {
        return $this->jutsuChakraVie;
    }

    /**
     * Set grade
     *
     * @param integer $grade
     * @return Ninja
     */
    public function setGrade(int $grade): self
    {
        $this->grade = $grade;

        return $this;
    }

    /**
     * Get grade
     *
     * @return int|null
     */
    public function getGrade(): ?int
    {
        return $this->grade;
    }

    /**
     * Set experience
     *
     * @param integer $experience
     * @return Ninja
     */
    public function setExperience(int $experience): self
    {
        $this->experience = $experience;

        return $this;
    }

    /**
     * Get experience
     *
     * @return int|null
     */
    public function getExperience(): ?int
    {
        return $this->experience;
    }

    /**
     * Set classe
     *
     * @param string $classe
     * @return Ninja
     */
    public function setClasse(string $classe): self
    {
        $this->classe = $classe;

        return $this;
    }

    /**
     * Get classe
     *
     * @return string|null
     */
    public function getClasse(): ?string
    {
        return $this->classe;
    }

    /**
     * Set user
     *
     * @param User|null $user
     * @return Ninja
     */
    public function setUser(?UserInterface $user = null): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set masque
     *
     * @param integer $masque
     * @return Ninja
     */
    public function setMasque(int $masque): self
    {
        $this->masque = $masque;

        return $this;
    }

    /**
     * Get masque
     *
     * @return int|null
     */
    public function getMasque(): ?int
    {
        return $this->masque;
    }

    /**
     * Set masqueCouleur
     *
     * @param integer $masqueCouleur
     * @return Ninja
     */
    public function setMasqueCouleur(int $masqueCouleur): self
    {
        $this->masqueCouleur = $masqueCouleur;

        return $this;
    }

    /**
     * Get masqueCouleur
     *
     * @return int|null
     */
    public function getMasqueCouleur(): ?int
    {
        return $this->masqueCouleur;
    }

    /**
     * Set masqueDetail
     *
     * @param integer $masqueDetail
     * @return Ninja
     */
    public function setMasqueDetail(int $masqueDetail): self
    {
        $this->masqueDetail = $masqueDetail;

        return $this;
    }

    /**
     * Get masqueDetail
     *
     * @return int|null
     */
    public function getMasqueDetail(): ?int
    {
        return $this->masqueDetail;
    }

    /**
     * Set costume
     *
     * @param integer $costume
     * @return Ninja
     */
    public function setCostume(int $costume): self
    {
        $this->costume = $costume;

        return $this;
    }

    /**
     * Get costume
     *
     * @return int|null
     */
    public function getCostume(): ?int
    {
        return $this->costume;
    }

    /**
     * Set costumeCouleur
     *
     * @param integer $costumeCouleur
     * @return Ninja
     */
    public function setCostumeCouleur(int $costumeCouleur): self
    {
        $this->costumeCouleur = $costumeCouleur;

        return $this;
    }

    /**
     * Get costumeCouleur
     *
     * @return int|null
     */
    public function getCostumeCouleur(): ?int
    {
        return $this->costumeCouleur;
    }

    /**
     * Set costumeDetail
     *
     * @param integer $costumeDetail
     * @return Ninja
     */
    public function setCostumeDetail(int $costumeDetail): self
    {
        $this->costumeDetail = $costumeDetail;

        return $this;
    }

    /**
     * Get costumeDetail
     *
     * @return int|null
     */
    public function getCostumeDetail(): ?int
    {
        return $this->costumeDetail;
    }

    /**
     * Set missionAssassinnat
     *
     * @param integer $missionAssassinnat
     * @return Ninja
     */
    public function setMissionAssassinnat(int $missionAssassinnat): self
    {
        $this->missionAssassinnat = $missionAssassinnat;

        return $this;
    }

    /**
     * Get missionAssassinnat
     *
     * @return int|null
     */
    public function getMissionAssassinnat(): ?int
    {
        return $this->missionAssassinnat;
    }

    /**
     * Set missionCourse
     *
     * @param integer $missionCourse
     * @return Ninja
     */
    public function setMissionCourse(int $missionCourse): self
    {
        $this->missionCourse = $missionCourse;

        return $this;
    }

    /**
     * Get missionCourse
     *
     * @return int|null
     */
    public function getMissionCourse(): ?int
    {
        return $this->missionCourse;
    }

    /**
     * Set accomplissement
     *
     * @param string $accomplissement
     * @return Ninja
     */
    public function setAccomplissement(string $accomplissement): self
    {
        $this->accomplissement = $accomplissement;

        return $this;
    }

    /**
     * Get accomplissement
     *
     * @return string|null
     */
    public function getAccomplissement(): ?string
    {
        return $this->accomplissement;
    }

    /**
     * Set jutsuFujin
     *
     * @param integer $jutsuFujin
     * @return Ninja
     */
    public function setJutsuFujin(int $jutsuFujin): self
    {
        $this->jutsuFujin = $jutsuFujin;
    
        return $this;
    }

    /**
     * Get jutsuFujin
     *
     * @return int|null
     */
    public function getJutsuFujin(): ?int
    {
        return $this->jutsuFujin;
    }

    /**
     * Set jutsuRaijin
     *
     * @param integer $jutsuRaijin
     * @return Ninja
     */
    public function setJutsuRaijin(int $jutsuRaijin): self
    {
        $this->jutsuRaijin = $jutsuRaijin;
    
        return $this;
    }

    /**
     * Get jutsuRaijin
     *
     * @return int|null
     */
    public function getJutsuRaijin(): ?int
    {
        return $this->jutsuRaijin;
    }

    /**
     * Set jutsuSarutahiko
     *
     * @param integer $jutsuSarutahiko
     * @return Ninja
     */
    public function setJutsuSarutahiko(int $jutsuSarutahiko): self
    {
        $this->jutsuSarutahiko = $jutsuSarutahiko;
    
        return $this;
    }

    /**
     * Get jutsuSarutahiko
     *
     * @return int|null
     */
    public function getJutsuSarutahiko(): ?int
    {
        return $this->jutsuSarutahiko;
    }

    /**
     * Set jutsuSusanoo
     *
     * @param integer $jutsuSusanoo
     * @return Ninja
     */
    public function setJutsuSusanoo(int $jutsuSusanoo): self
    {
        $this->jutsuSusanoo = $jutsuSusanoo;
    
        return $this;
    }

    /**
     * Get jutsuSusanoo
     *
     * @return int|null
     */
    public function getJutsuSusanoo(): ?int
    {
        return $this->jutsuSusanoo;
    }

    /**
     * Set jutsuKagutsuchi
     *
     * @param integer $jutsuKagutsuchi
     * @return Ninja
     */
    public function setJutsuKagutsuchi(int $jutsuKagutsuchi): self
    {
        $this->jutsuKagutsuchi = $jutsuKagutsuchi;
    
        return $this;
    }

    /**
     * Get jutsuKagutsuchi
     *
     * @return int|null
     */
    public function getJutsuKagutsuchi(): ?int
    {
        return $this->jutsuKagutsuchi;
    }
}