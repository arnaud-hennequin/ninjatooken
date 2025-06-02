<?php

namespace App\Entity\Clan;

use App\Entity\User\User;
use App\Entity\User\UserInterface;
use App\Repository\ClanUtilisateurRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * ClanUtilisateur.
 */
#[ORM\Table(name: 'nt_clanutilisateur')]
#[ORM\Entity(repositoryClass: ClanUtilisateurRepository::class)]
class ClanUtilisateur
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'clan', targetEntity: User::class, cascade: ['persist'], fetch: 'LAZY')]
    private ?UserInterface $membre = null;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist'], fetch: 'LAZY', inversedBy: 'recruts')]
    private ?UserInterface $recruteur = null;

    #[ORM\JoinColumn(name: 'clan_id', referencedColumnName: 'id', onDelete: 'cascade')]
    #[ORM\ManyToOne(targetEntity: Clan::class, cascade: ['persist'], fetch: 'LAZY', inversedBy: 'membres')]
    private ?Clan $clan = null;

    #[ORM\Column(name: 'droit', type: Types::SMALLINT)]
    private int $droit = 0;

    #[ORM\Column(name: 'can_edit_clan', type: Types::BOOLEAN)]
    private bool $canEditClan = false;

    #[ORM\Column(name: 'date_ajout', type: Types::DATETIME_MUTABLE)]
    private \DateTime $dateAjout;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setDateAjout(new \DateTime());
    }

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set droit.
     */
    public function setDroit(int $droit): self
    {
        $this->droit = $droit;

        return $this;
    }

    /**
     * Get droit.
     */
    public function getDroit(): int
    {
        return $this->droit;
    }

    /**
     * Set canEditClan.
     */
    public function setCanEditClan(bool $canEditClan): self
    {
        $this->canEditClan = $canEditClan;

        return $this;
    }

    /**
     * Get canEditClan.
     */
    public function getCanEditClan(): bool
    {
        return $this->canEditClan;
    }

    /**
     * Set dateAjout.
     */
    public function setDateAjout(\DateTime $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    /**
     * Get dateAjout.
     */
    public function getDateAjout(): ?\DateTime
    {
        return $this->dateAjout;
    }

    /**
     * Set membre.
     */
    public function setMembre(?UserInterface $membre = null): self
    {
        $this->membre?->setClan(null);
        $membre?->setClan($this);
        $this->membre = $membre;

        return $this;
    }

    /**
     * Get membre.
     */
    public function getMembre(): ?UserInterface
    {
        return $this->membre;
    }

    /**
     * Set recruteur.
     */
    public function setRecruteur(?UserInterface $recruteur = null): self
    {
        $this->recruteur?->removeRecrut($this);
        $recruteur?->addRecrut($this);

        $this->recruteur = $recruteur;

        return $this;
    }

    /**
     * Get recruteur.
     */
    public function getRecruteur(): ?UserInterface
    {
        return $this->recruteur;
    }

    /**
     * Set clan.
     */
    public function setClan(?Clan $clan = null): self
    {
        $this->clan = $clan;

        return $this;
    }

    /**
     * Get clan.
     */
    public function getClan(): ?Clan
    {
        return $this->clan;
    }
}
