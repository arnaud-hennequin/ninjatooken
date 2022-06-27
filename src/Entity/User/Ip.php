<?php

namespace App\Entity\User;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Group.
 *
 * @ORM\Table(name="nt_ip")
 * @ORM\Entity
 */
class Ip
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="ip", type="integer", options={"unsigned"=true})
     */
    private int $ip;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private DateTime $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private ?DateTime $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="ips")
     */
    private User $user;

    public function __construct()
    {
        $this->setCreatedAt(new DateTime());
    }

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set ip.
     *
     * @return Ip
     */
    public function setIp(int $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip.
     */
    public function getIp(): ?int
    {
        return $this->ip;
    }

    /**
     * Set createdAt.
     *
     * @return Ip
     */
    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt.
     *
     * @return Ip
     */
    public function setUpdatedAt(?DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Set user.
     *
     * @param User|null $user
     *
     * @return Ip
     */
    public function setUser(UserInterface $user = null): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     */
    public function getUser(): ?User
    {
        return $this->user;
    }
}
