<?php

namespace App\Entity\User;

use App\Repository\MessageUserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * MessageUser.
 */
#[ORM\Table(name: 'nt_messageuser')]
#[ORM\Entity(repositoryClass: MessageUserRepository::class)]
class MessageUser
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'message_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Message::class, inversedBy: 'receivers')]
    private ?Message $message;

    #[ORM\JoinColumn(name: 'destinataire_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EAGER')]
    private ?UserInterface $destinataire;

    #[ORM\Column(name: 'date_read', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $dateRead;

    #[ORM\Column(name: 'has_deleted', type: Types::BOOLEAN)]
    private bool $hasDeleted = false;

    public function __toString()
    {
        $destination = $this->getDestinataire();
        if (null !== $destination) {
            return $destination->getUsername();
        }

        return ' - ';
    }

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set dateRead.
     */
    public function setDateRead(?\DateTime $dateRead): self
    {
        $this->dateRead = $dateRead;

        return $this;
    }

    /**
     * Get dateRead.
     */
    public function getDateRead(): ?\DateTime
    {
        return $this->dateRead;
    }

    /**
     * Set hasDeleted.
     */
    public function setHasDeleted(bool $hasDeleted): self
    {
        $this->hasDeleted = $hasDeleted;

        return $this;
    }

    /**
     * Get hasDeleted.
     */
    public function getHasDeleted(): ?bool
    {
        return $this->hasDeleted;
    }

    /**
     * Set destinataire.
     */
    public function setDestinataire(?UserInterface $destinataire = null): self
    {
        $this->destinataire = $destinataire;

        return $this;
    }

    /**
     * Get destinataire.
     */
    public function getDestinataire(): ?UserInterface
    {
        return $this->destinataire;
    }

    /**
     * Set message.
     */
    public function setMessage(?Message $message = null): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message.
     */
    public function getMessage(): ?Message
    {
        return $this->message;
    }
}
