<?php

namespace App\Entity\Clan;

use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * ClanProposition
 *
 * @ORM\Table(name="nt_clanproposition")
 * @ORM\Entity(repositoryClass="App\Repository\ClanPropositionRepository")
 */
class ClanProposition
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="recruteur_id", referencedColumnName="id", onDelete="CASCADE")
     * @var User
     */
    private $recruteur;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="postulant_id", referencedColumnName="id", onDelete="CASCADE")
     * @var User
     */
    private $postulant;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private $dateAjout;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_changement_etat", type="datetime", nullable=true)
     */
    private $dateChangementEtat;

    /**
     * @var integer
     *
     * @ORM\Column(name="etat", type="smallint")
     */
    private $etat = 0;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setDateAjout(new \DateTime());
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
     * @param \DateTime $dateAjout
     * @return ClanProposition
     */
    public function setDateAjout(\DateTime $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    /**
     * Get dateAjout
     *
     * @return \DateTime 
     */
    public function getDateAjout(): ?\DateTime
    {
        return $this->dateAjout;
    }

    /**
     * Set recruteur
     *
     * @param User|null $recruteur
     * @return ClanProposition
     */
    public function setRecruteur(User $recruteur = null): self
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
    public function setPostulant(User $postulant = null): self
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
     * @param \DateTime $dateChangementEtat
     * @return ClanProposition
     */
    public function setDateChangementEtat(\DateTime $dateChangementEtat): self
    {
        $this->dateChangementEtat = $dateChangementEtat;

        return $this;
    }

    /**
     * Get dateChangementEtat
     *
     * @return \DateTime 
     */
    public function getDateChangementEtat(): ?\DateTime
    {
        return $this->dateChangementEtat;
    }
}
