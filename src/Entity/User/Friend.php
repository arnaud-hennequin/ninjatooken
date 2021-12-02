<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Friend
 *
 * @ORM\Table(name="nt_friend")
 * @ORM\Entity(repositoryClass="App\Repository\FriendRepository")
 */
class Friend
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="friend_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $friend;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_blocked", type="boolean")
     */
    private $isBlocked = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_confirmed", type="boolean")
     */
    private $isConfirmed = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private $dateAjout;

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
     * Set isBlocked
     *
     * @param boolean $isBlocked
     * @return Friend
     */
    public function setIsBlocked(bool $isBlocked): self
    {
        $this->isBlocked = $isBlocked;

        return $this;
    }

    /**
     * Get isBlocked
     *
     * @return boolean 
     */
    public function getIsBlocked(): ?bool
    {
        return $this->isBlocked;
    }

    /**
     * Set isConfirmed
     *
     * @param boolean $isConfirmed
     * @return Friend
     */
    public function setIsConfirmed(bool $isConfirmed): self
    {
        $this->isConfirmed = $isConfirmed;

        return $this;
    }

    /**
     * Get isConfirmed
     *
     * @return boolean 
     */
    public function getIsConfirmed(): ?bool
    {
        return $this->isConfirmed;
    }

    /**
     * Set dateAjout
     *
     * @param \DateTime $dateAjout
     * @return Friend
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
     * Set user
     *
     * @param User|null $user
     * @return Friend
     */
    public function setUser(User $user = null): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set friend
     *
     * @param UserInterface|null $friend
     * @return Friend
     */
    public function setFriend(UserInterface $friend = null): self
    {
        $this->friend = $friend;

        return $this;
    }

    /**
     * Get friend
     *
     * @return User
     */
    public function getFriend(): ?User
    {
        return $this->friend;
    }
}
