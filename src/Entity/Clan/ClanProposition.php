<?php

namespace App\Entity\Clan;

use App\Entity\User\UserInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * ClanProposition.
 *
 * @ORM\Table(name="nt_clanproposition")
 * @ORM\Entity(repositoryClass="App\Repository\ClanPropositionRepository")
 */
class ClanProposition
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="recruteur_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private UserInterface $recruteur;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="postulant_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private UserInterface $postulant;

    /**
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private DateTime $dateAjout;

    /**
     * @ORM\Column(name="date_changement_etat", type="datetime", nullable=true)
     */
    private ?DateTime $dateChangementEtat;

    /**
     * @ORM\Column(name="etat", type="smallint")
     */
    private int $etat = 0;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setDateAjout(new DateTime());
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
    public function setDateAjout(DateTime $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    /**
     * Get dateAjout.
     */
    public function getDateAjout(): ?DateTime
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
    public function setDateChangementEtat(?DateTime $dateChangementEtat): self
    {
        $this->dateChangementEtat = $dateChangementEtat;

        return $this;
    }

    /**
     * Get dateChangementEtat.
     */
    public function getDateChangementEtat(): ?DateTime
    {
        return $this->dateChangementEtat;
    }
}
