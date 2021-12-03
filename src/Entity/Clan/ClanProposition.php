<?php

namespace App\Entity\Clan;

use App\Entity\User\User;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ClanProposition
 *
 * @ORM\Table(name="nt_clanproposition")
 * @ORM\Entity(repositoryClass="App\Repository\ClanPropositionRepository")
 */
class ClanProposition
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="recruteur_id", referencedColumnName="id", onDelete="CASCADE")
     * @var User
     */
    private User $recruteur;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="postulant_id", referencedColumnName="id", onDelete="CASCADE")
     * @var User
     */
    private User $postulant;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private DateTime $dateAjout;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_changement_etat", type="datetime", nullable=true)
     */
    private ?DateTime $dateChangementEtat;

    /**
     * @var integer
     *
     * @ORM\Column(name="etat", type="smallint")
     */
    private int $etat = 0;

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
     * Set dateAjout
     *
     * @param DateTime $dateAjout
     * @return ClanProposition
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
     * Set recruteur
     *
     * @param User|null $recruteur
     * @return ClanProposition
     */
    public function setRecruteur(?UserInterface $recruteur = null): self
    {
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
     * Set postulant
     *
     * @param User|null $postulant
     * @return ClanProposition
     */
    public function setPostulant(?UserInterface $postulant = null): self
    {
        $this->postulant = $postulant;

        return $this;
    }

    /**
     * Get postulant
     *
     * @return User 
     */
    public function getPostulant(): ?User
    {
        return $this->postulant;
    }

    /**
     * Set etat
     *
     * @param integer $etat
     * @return ClanProposition
     */
    public function setEtat(int $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return integer 
     */
    public function getEtat(): ?int
    {
        return $this->etat;
    }

    /**
     * Set dateChangementEtat
     *
     * @param DateTime $dateChangementEtat
     * @return ClanProposition
     */
    public function setDateChangementEtat(?DateTime $dateChangementEtat): self
    {
        $this->dateChangementEtat = $dateChangementEtat;

        return $this;
    }

    /**
     * Get dateChangementEtat
     *
     * @return DateTime
     */
    public function getDateChangementEtat(): ?DateTime
    {
        return $this->dateChangementEtat;
    }
}
