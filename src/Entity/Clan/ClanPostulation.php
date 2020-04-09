<?php

namespace App\Entity\Clan;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClanPostulation
 *
 * @ORM\Table(name="nt_clanpostulation")
 * @ORM\Entity(repositoryClass="App\Entity\Clan\ClanPostulationRepository")
 */
class ClanPostulation
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     * @ORM\JoinColumn(name="postulant_id", referencedColumnName="id", onDelete="CASCADE")
     * @var User
     */
    private $postulant;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Clan\Clan", fetch="EAGER")
     * @ORM\JoinColumn(name="clan_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $clan;

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
        $this->setDateChangementEtat(new \DateTime());
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
     * @return ClanPostulation
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
     * Set postulant
     *
     * @param \App\Entity\User\User $postulant
     * @return ClanPostulation
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
     * Set clan
     *
     * @param \App\Entity\Clan\Clan $clan
     * @return ClanPostulation
     */
    public function setClan(\App\Entity\Clan\Clan $clan = null)
    {
        $this->clan = $clan;

        return $this;
    }

    /**
     * Get clan
     *
     * @return \App\Entity\Clan\Clan 
     */
    public function getClan()
    {
        return $this->clan;
    }

    /**
     * Set etat
     *
     * @param integer $etat
     * @return ClanPostulation
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
     * @return ClanPostulation
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
