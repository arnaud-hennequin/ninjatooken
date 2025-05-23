<?php

namespace App\Entity\Game;

use App\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Lobby.
 */
#[ORM\Table(name: 'nt_lobby')]
#[ORM\Entity(repositoryClass: \App\Repository\LobbyRepository::class)]
class Lobby
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    /**
     * @var Collection<User>
     */
    #[ORM\JoinTable(name: 'nt_lobby_user')]
    #[ORM\JoinColumn(name: 'lobby_id', referencedColumnName: 'id', onDelete: 'cascade')]
    #[ORM\InverseJoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'cascade')]
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'lobbies')]
    private ?Collection $users;

    #[ORM\Column(name: 'carte', type: 'smallint')]
    private int $carte = 0;

    #[ORM\Column(name: 'partie', type: 'smallint')]
    private int $partie = 0;

    #[ORM\Column(name: 'maximum', type: 'smallint')]
    private int $maximum = 2;

    #[ORM\Column(name: 'jeu', type: 'smallint')]
    private int $jeu = 0;

    #[ORM\Column(name: 'privee', type: 'string', length: 30)]
    private string $privee = '';

    #[ORM\Column(name: 'version', type: 'decimal', precision: 10, scale: 6)]
    private float $version = 0;

    #[ORM\Column(name: 'date_debut', type: 'datetime')]
    private \DateTime $dateDebut;

    #[ORM\Column(name: 'date_update', type: 'datetime')]
    private \DateTime $dateUpdate;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->setDateDebut(new \DateTime());
        $this->setDateUpdate(new \DateTime());
    }

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set carte.
     */
    public function setCarte(int $carte): self
    {
        $this->carte = $carte;

        return $this;
    }

    /**
     * Get carte.
     */
    public function getCarte(): ?int
    {
        return $this->carte;
    }

    /**
     * Set partie.
     */
    public function setPartie(int $partie): self
    {
        $this->partie = $partie;

        return $this;
    }

    /**
     * Get partie.
     */
    public function getPartie(): ?int
    {
        return $this->partie;
    }

    /**
     * Set maximum.
     */
    public function setMaximum(int $maximum): self
    {
        $this->maximum = $maximum;

        return $this;
    }

    /**
     * Get maximum.
     */
    public function getMaximum(): ?int
    {
        return $this->maximum;
    }

    /**
     * Set jeu.
     */
    public function setJeu(int $jeu): self
    {
        $this->jeu = $jeu;

        return $this;
    }

    /**
     * Get jeu.
     */
    public function getJeu(): ?int
    {
        return $this->jeu;
    }

    /**
     * Set privee.
     */
    public function setPrivee(string $privee): self
    {
        $this->privee = $privee;

        return $this;
    }

    /**
     * Get privee.
     */
    public function getPrivee(): ?string
    {
        return $this->privee;
    }

    /**
     * Set version.
     */
    public function setVersion(float $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version.
     */
    public function getVersion(): ?float
    {
        return $this->version;
    }

    /**
     * Set dateDebut.
     */
    public function setDateDebut(\DateTime $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Get dateDebut.
     */
    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    /**
     * Set dateUpdate.
     */
    public function setDateUpdate(\DateTime $dateUpdate): self
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate.
     */
    public function getDateUpdate(): ?\DateTime
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
     * Clear users.
     */
    public function clearUsers()
    {
        $this->users->clear();
    }
}
