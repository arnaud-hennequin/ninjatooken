<?php

namespace App\Entity\Clan;

use App\Entity\User\User;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ClanUtilisateur
 *
 * @ORM\Table(name="nt_clanutilisateur")
 * @ORM\Entity(repositoryClass="App\Repository\ClanUtilisateurRepository")
 */
class ClanUtilisateur
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
     * @ORM\OneToOne(targetEntity="App\Entity\User\User", inversedBy="clan", cascade={"persist"}, fetch="LAZY")
     * @var User
     */
    private ?User $membre;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="recruts", cascade={"persist"}, fetch="LAZY")
     * @var User
     */
    private ?User $recruteur;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Clan\Clan", inversedBy="membres", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="clan_id", referencedColumnName="id", onDelete="cascade")
     */
    private ?Clan $clan;

    /**
     * @var integer
     *
     * @ORM\Column(name="droit", type="smallint")
     */
    private int $droit = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="can_edit_clan", type="boolean")
     */
    private bool $canEditClan = false;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private DateTime $dateAjout;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setDateAjout(new DateTime());
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set droit
     *
     * @param integer $droit
     * @return ClanUtilisateur
     */
    public function setDroit(int $droit): self
    {
        $this->droit = $droit;

        return $this;
    }

    /**
     * Get droit
     *
     * @return integer 
     */
    public function getDroit(): int
    {
        return $this->droit;
    }

    /**
     * Set canEditClan
     *
     * @param boolean $canEditClan
     * @return ClanUtilisateur
     */
    public function setCanEditClan(bool $canEditClan): self
    {
        $this->canEditClan = $canEditClan;

        return $this;
    }

    /**
     * Get canEditClan
     *
     * @return boolean 
     */
    public function getCanEditClan(): bool
    {
        return $this->canEditClan;
    }

    /**
     * Set dateAjout
     *
     * @param DateTime $dateAjout
     * @return ClanUtilisateur
     */
    public function setDateAjout(DateTime $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    /**
     * Get dateAjout
     *
     * @return DateTime
     */
    public function getDateAjout(): ?DateTime
    {
        return $this->dateAjout;
    }

    /**
     * Set membre
     *
     * @param User|null $membre
     * @return ClanUtilisateur
     */
    public function setMembre(?UserInterface $membre = null): self
    {
        if($this->membre)
            $this->membre->setClan(null);
        if($membre)
            $membre->setClan($this);
        $this->membre = $membre;

        return $this;
    }

    /**
     * Get membre
     *
     * @return User
     */
    public function getMembre(): ?User
    {
        return $this->membre;
    }

    /**
     * Set recruteur
     *
     * @param User|null $recruteur
     * @return ClanUtilisateur
     */
    public function setRecruteur(?UserInterface $recruteur = null): self
    {
        if($this->recruteur)
            $this->recruteur->removeRecrut($this);
        if($recruteur)
            $recruteur->addRecrut($this);

        $this->recruteur = $recruteur;

        return $this;
    }

    /**
     * Get recruteur
     *
     * @return User
     */
    public function getRecruteur(): ?User
    {
        return $this->recruteur;
    }

    /**
     * Set clan
     *
     * @param Clan|null $clan
     * @return ClanUtilisateur
     */
    public function setClan(Clan $clan = null): self
    {
        $this->clan = $clan;

        return $this;
    }

    /**
     * Get clan
     *
     * @return Clan
     */
    public function getClan(): ?Clan
    {
        return $this->clan;
    }
}
