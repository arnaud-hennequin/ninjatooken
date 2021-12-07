<?php

namespace App\Entity\Clan;

use App\Entity\User\User;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ClanPostulation
 *
 * @ORM\Table(name="nt_clanpostulation")
 * @ORM\Entity(repositoryClass="App\Repository\ClanPostulationRepository")
 */
class ClanPostulation
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     * @ORM\JoinColumn(name="postulant_id", referencedColumnName="id", onDelete="CASCADE")
     * @var User
     */
    private User $postulant;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Clan\Clan", fetch="EAGER")
     * @ORM\JoinColumn(name="clan_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Clan $clan;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private DateTime $dateAjout;

    /**
     * @var DateTime|null
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
        $this->setDateChangementEtat(new DateTime());
        $this->setDateAjout(new DateTime());
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
     * Set dateAjout
     *
     * @param DateTime $dateAjout
     * @return ClanPostulation
     */
    public function setDateAjout(DateTime $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    /**
     * Get dateAjout
     *
     * @return DateTime|null
     */
    public function getDateAjout(): ?DateTime
    {
        return $this->dateAjout;
    }

    /**
     * Set postulant
     *
     * @param User|null $postulant
     * @return ClanPostulation
     */
    public function setPostulant(?UserInterface $postulant = null): self
    {
        $this->postulant = $postulant;

        return $this;
    }

    /**
     * Get postulant
     *
     * @return User|null
     */
    public function getPostulant(): ?User
    {
        return $this->postulant;
    }

    /**
     * Set clan
     *
     * @param Clan|null $clan
     * @return ClanPostulation
     */
    public function setClan(?Clan $clan = null): self
    {
        $this->clan = $clan;

        return $this;
    }

    /**
     * Get clan
     *
     * @return Clan|null
     */
    public function getClan(): ?Clan
    {
        return $this->clan;
    }

    /**
     * Set etat
     *
     * @param integer $etat
     * @return ClanPostulation
     */
    public function setEtat(int $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * Get etat
     *
     * @return int|null
     */
    public function getEtat(): ?int
    {
        return $this->etat;
    }

    /**
     * Set dateChangementEtat
     *
     * @param DateTime|null $dateChangementEtat
     * @return ClanPostulation
     */
    public function setDateChangementEtat(?DateTime $dateChangementEtat): self
    {
        $this->dateChangementEtat = $dateChangementEtat;

        return $this;
    }

    /**
     * Get dateChangementEtat
     *
     * @return DateTime|null
     */
    public function getDateChangementEtat(): ?DateTime
    {
        return $this->dateChangementEtat;
    }
}
