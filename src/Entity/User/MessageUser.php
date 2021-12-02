<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * MessageUser
 *
 * @ORM\Table(name="nt_messageuser")
 * @ORM\Entity(repositoryClass="App\Repository\MessageUserRepository")
 */
class MessageUser
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
     * @var Message
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User\Message", inversedBy="receivers")
     * @ORM\JoinColumn(name="message_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $message;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="EAGER")
     * @ORM\JoinColumn(name="destinataire_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $destinataire;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_read", type="datetime", nullable=true)
     */
    private $dateRead;

    /**
     * @var boolean
     *
     * @ORM\Column(name="has_deleted", type="boolean")
     */
    private $hasDeleted = false;

    public function __toString()
    {
        $destination = $this->getDestinataire();
        if(null !== $destination)
            return $destination->getUsername();
		return ' - ';
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
     * Set dateRead
     *
     * @param \DateTime $dateRead
     * @return MessageUser
     */
    public function setDateRead(\DateTime $dateRead): self
    {
        $this->dateRead = $dateRead;

        return $this;
    }

    /**
     * Get dateRead
     *
     * @return \DateTime 
     */
    public function getDateRead(): ?\DateTime
    {
        return $this->dateRead;
    }

    /**
     * Set hasDeleted
     *
     * @param boolean $hasDeleted
     * @return MessageUser
     */
    public function setHasDeleted(bool $hasDeleted): self
    {
        $this->hasDeleted = $hasDeleted;

        return $this;
    }

    /**
     * Get hasDeleted
     *
     * @return boolean 
     */
    public function getHasDeleted(): ?bool
    {
        return $this->hasDeleted;
    }

    /**
     * Set destinataire
     *
     * @param User|null $destinataire
     * @return MessageUser
     */
    public function setDestinataire(User $destinataire = null): self
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    /**
     * Get destinataire
     *
     * @return User
     */
    public function getDestinataire(): ?User
    {
        return $this->destinataire;
    }

    /**
     * Set message
     *
     * @param Message|null $message
     * @return MessageUser
     */
    public function setMessage(Message $message = null): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return Message
     */
    public function getMessage(): ?Message
    {
        return $this->message;
    }
}
