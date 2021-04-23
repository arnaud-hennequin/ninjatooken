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
		return ' - ';
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateRead
     *
     * @param \DateTime $dateRead
     * @return MessageUser
     */
    public function setDateRead($dateRead)
    {
        $this->dateRead = $dateRead;

        return $this;
    }

    /**
     * Get dateRead
     *
     * @return \DateTime 
     */
    public function getDateRead()
    {
        return $this->dateRead;
    }

    /**
     * Set hasDeleted
     *
     * @param boolean $hasDeleted
     * @return MessageUser
     */
    public function setHasDeleted($hasDeleted)
    {
        $this->hasDeleted = $hasDeleted;

        return $this;
    }

    /**
     * Get hasDeleted
     *
     * @return boolean 
     */
    public function getHasDeleted()
    {
        return $this->hasDeleted;
    }

    /**
     * Set destinataire
     *
     * @param \App\Entity\User\User $destinataire
     * @return MessageUser
     */
    public function setDestinataire(\App\Entity\User\User $destinataire = null)
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    /**
     * Get destinataire
     *
     * @return \App\Entity\User\User 
     */
    public function getDestinataire()
    {
        return $this->destinataire;
    }

    /**
     * Set message
     *
     * @param \App\Entity\User\Message $message
     * @return MessageUser
     */
    public function setMessage(\App\Entity\User\Message $message = null)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return \App\Entity\User\Message 
     */
    public function getMessage()
    {
        return $this->message;
    }
}
