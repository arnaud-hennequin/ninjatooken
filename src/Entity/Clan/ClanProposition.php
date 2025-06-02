<?php

namespace App\Entity\Clan;

use App\Entity\User\User;
use App\Entity\User\UserInterface;
use App\Repository\ClanPropositionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * ClanProposition.
 */
#[ORM\Table(name: 'nt_clanproposition')]
#[ORM\Entity(repositoryClass: ClanPropositionRepository::class)]
class ClanProposition
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'recruteur_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    private ?UserInterface $recruteur;

    #[ORM\JoinColumn(name: 'postulant_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    private ?UserInterface $postulant;

    #[ORM\Column(name: 'date_ajout', type: Types::DATETIME_MUTABLE)]
    private \DateTime $dateAjout;

    #[ORM\Column(name: 'date_changement_etat', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $dateChangementEtat;

    #[ORM\Column(name: 'etat', type: Types::SMALLINT)]
    private int $etat = 0;

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
     * Set recruteur.
     */
    public function setRecruteur(?UserInterface $recruteur = null): self
    {
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
     * Set postulant.
     */
    public function setPostulant(?UserInterface $postulant = null): self
    {
        $this->postulant = $postulant;

        return $this;
    }

    /**
     * Get postulant.
     */
    public function getPostulant(): ?UserInterface
    {
        return $this->postulant;
    }

    /**
     * Set etat.
     */
    public function setEtat(int $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat.
     */
    public function getEtat(): ?int
    {
        return $this->etat;
    }

    /**
     * Set dateChangementEtat.
     */
    public function setDateChangementEtat(?\DateTime $dateChangementEtat): self
    {
        $this->dateChangementEtat = $dateChangementEtat;

        return $this;
    }

    /**
     * Get dateChangementEtat.
     */
    public function getDateChangementEtat(): ?\DateTime
    {
        return $this->dateChangementEtat;
    }
}
