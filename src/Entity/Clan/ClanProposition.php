<?php

namespace App\Entity\Clan;

use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * ClanProposition
 *
 * @ORM\Table(name="nt_clanproposition")
 * @ORM\Entity(repositoryClass="App\Entity\Clan\ClanPropositionRepository")
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateAjout
     *
     * @param \DateTime $dateAjout
     * @return ClanProposition
     */
    public function setDateAjout($dateAjout)
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    /**
     * Get dateAjout
     *
     * @return \DateTime 
     */
    public function getDateAjout()
    {
        return $this->dateAjout;
    }

    /**
     * Set recruteur
     *
     * @param \App\Entity\User\User $recruteur
     * @return ClanProposition
     */
    public function setRecruteur(\App\Entity\User\User $recruteur = null)
    {
        $this->recruteur = $recruteur;

        return $this;
    }

    /**
     * Get recruteur
     *
     * @return \App\Entity\User\User 
     */
    public function getRecruteur()
    {
        return $this->recruteur;
    }

    /**
     * Set postulant
     *
     * @param \App\Entity\User\User $postulant
     * @return ClanProposition
     */
    public function setPostulant(\App\Entity\User\User $postulant = null)
    {
        $this->postulant = $postulant;

        return $this;
    }

    /**
     * Get postulant
     *
     * @return \App\Entity\User\User 
     */
    public function getPostulant()
    {
        return $this->postulant;
    }

    /**
     * Set etat
     *
     * @param integer $etat
     * @return ClanProposition
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return integer 
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * Set dateChangementEtat
     *
     * @param \DateTime $dateChangementEtat
     * @return ClanProposition
     */
    public function setDateChangementEtat($dateChangementEtat)
    {
        $this->dateChangementEtat = $dateChangementEtat;

        return $this;
    }

    /**
     * Get dateChangementEtat
     *
     * @return \DateTime 
     */
    public function getDateChangementEtat()
    {
        return $this->dateChangementEtat;
    }
}
