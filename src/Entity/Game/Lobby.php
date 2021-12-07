<?php

namespace App\Entity\Game;

use App\Entity\User\User;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Lobby
 *
 * @ORM\Table(name="nt_lobby")
 * @ORM\Entity(repositoryClass="App\Repository\LobbyRepository")
 */
class Lobby
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
     * user
     *
     * @var User
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\User\User", mappedBy="lobbies")
     * @ORM\JoinTable(name="nt_lobby_user",
     *      joinColumns={@ORM\JoinColumn(name="lobby_id", referencedColumnName="id", onDelete="cascade")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="cascade")}
     * )
     */
    private Collection $users;

    /**
     * @var integer
     *
     * @ORM\Column(name="carte", type="smallint")
     */
    private int $carte = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="partie", type="smallint")
     */
    private int $partie = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="maximum", type="smallint")
     */
    private int $maximum = 2;

    /**
     * @var integer
     *
     * @ORM\Column(name="jeu", type="smallint")
     */
    private int $jeu = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="privee", type="string", length=30)
     */
    private string $privee = '';

    /**
     * @var float
     *
     * @ORM\Column(name="version", type="decimal", precision=10, scale=6)
     */
    private float $version = 0;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_debut", type="datetime")
     */
    private DateTime $dateDebut;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_update", type="datetime")
     */
    private DateTime $dateUpdate;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->setDateDebut(new DateTime());
        $this->setDateUpdate(new DateTime());
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
     * Set carte
     *
     * @param integer $carte
     * @return Lobby
     */
    public function setCarte(int $carte): self
    {
        $this->carte = $carte;

        return $this;
    }

    /**
     * Get carte
     *
     * @return int|null
     */
    public function getCarte(): ?int
    {
        return $this->carte;
    }

    /**
     * Set partie
     *
     * @param integer $partie
     * @return Lobby
     */
    public function setPartie(int $partie): self
    {
        $this->partie = $partie;

        return $this;
    }

    /**
     * Get partie
     *
     * @return int|null
     */
    public function getPartie(): ?int
    {
        return $this->partie;
    }

    /**
     * Set maximum
     *
     * @param integer $maximum
     * @return Lobby
     */
    public function setMaximum(int $maximum): self
    {
        $this->maximum = $maximum;

        return $this;
    }

    /**
     * Get maximum
     *
     * @return int|null
     */
    public function getMaximum(): ?int
    {
        return $this->maximum;
    }

    /**
     * Set jeu
     *
     * @param integer $jeu
     * @return Lobby
     */
    public function setJeu(int $jeu): self
    {
        $this->jeu = $jeu;

        return $this;
    }

    /**
     * Get jeu
     *
     * @return int|null
     */
    public function getJeu(): ?int
    {
        return $this->jeu;
    }

    /**
     * Set privee
     *
     * @param string $privee
     * @return Lobby
     */
    public function setPrivee(string $privee): self
    {
        $this->privee = $privee;

        return $this;
    }

    /**
     * Get privee
     *
     * @return string|null
     */
    public function getPrivee(): ?string
    {
        return $this->privee;
    }

    /**
     * Set version
     *
     * @param float $version
     * @return Lobby
     */
    public function setVersion(float $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return float|null
     */
    public function getVersion(): ?float
    {
        return $this->version;
    }

    /**
     * Set dateDebut
     *
     * @param DateTime $dateDebut
     * @return Lobby
     */
    public function setDateDebut(DateTime $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Get dateDebut
     *
     * @return DateTime|null
     */
    public function getDateDebut(): ?DateTime
    {
        return $this->dateDebut;
    }

    /**
     * Set dateUpdate
     *
     * @param DateTime $dateUpdate
     * @return Lobby
     */
    public function setDateUpdate(DateTime $dateUpdate): self
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return DateTime|null
     */
    public function getDateUpdate(): ?DateTime
    {
        return $this->dateUpdate;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): ?Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addLobby($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeLobby($this);
        }

        return $this;
    }

    /**
     * Clear users
     *
     */
    public function clearUsers()
    {
        $this->users->clear();
    }
}
