<?php

namespace App\Entity\User;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Friend.
 *
 * @ORM\Table(name="nt_friend")
 * @ORM\Entity(repositoryClass="App\Repository\FriendRepository")
 */
class Friend
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?UserInterface $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="friend_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?UserInterface $friend;

    /**
     * @ORM\Column(name="is_blocked", type="boolean")
     */
    private bool $isBlocked = false;

    /**
     * @ORM\Column(name="is_confirmed", type="boolean")
     */
    private bool $isConfirmed = false;

    /**
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private DateTime $dateAjout;

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
     * Set isBlocked.
     *
     * @return Friend
     */
    public function setIsBlocked(bool $isBlocked): self
    {
        $this->isBlocked = $isBlocked;

        return $this;
    }

    /**
     * Get isBlocked.
     */
    public function getIsBlocked(): ?bool
    {
        return $this->isBlocked;
    }

    /**
     * Set isConfirmed.
     *
     * @return Friend
     */
    public function setIsConfirmed(bool $isConfirmed): self
    {
        $this->isConfirmed = $isConfirmed;

        return $this;
    }

    /**
     * Get isConfirmed.
     */
    public function getIsConfirmed(): ?bool
    {
        return $this->isConfirmed;
    }

    /**
     * Set dateAjout.
     *
     * @return Friend
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
     * Set user.
     *
     * @param User|null $user
     *
     * @return Friend
     */
    public function setUser(?UserInterface $user = null): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * Set friend.
     *
     * @return Friend
     */
    public function setFriend(?UserInterface $friend = null): self
    {
        $this->friend = $friend;

        return $this;
    }

    /**
     * Get friend.
     */
    public function getFriend(): ?UserInterface
    {
        return $this->friend;
    }
}
