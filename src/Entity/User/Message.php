<?php

namespace App\Entity\User;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Message.
 *
 * @ORM\Table(name="nt_message")
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 */
class Message
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="old_id", type="integer", nullable=true)
     */
    private ?int $old_id;

    /**
     * @ORM\Column(name="nom", type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\NotBlank()
     */
    private string $nom;

    /**
     * @ORM\Column(name="content", type="text")
     * @Assert\NotBlank()
     */
    private string $content;

    /**
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private DateTime $dateAjout;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="messages", fetch="EAGER")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?UserInterface $author;

    /**
     * @ORM\Column(name="has_deleted", type="boolean")
     */
    private bool $hasDeleted = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User\MessageUser", mappedBy="message", cascade={"remove"}, fetch="EAGER")
     */
    private Collection $receivers;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->receivers = new ArrayCollection();
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
     * Set nom.
     *
     * @return Message
     */
    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom.
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * Set content.
     *
     * @return Message
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set dateAjout.
     *
     * @return Message
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
     * Set author.
     */
    public function setAuthor(?UserInterface $author): Message
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     */
    public function getAuthor(): ?UserInterface
    {
        return $this->author;
    }

    /**
     * Set hasDeleted.
     *
     * @return Message
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
     * Set old_id.
     *
     * @return Message
     */
    public function setOldId(?int $oldId): self
    {
        $this->old_id = $oldId;

        return $this;
    }

    /**
     * Get old_id.
     */
    public function getOldId(): ?int
    {
        return $this->old_id;
    }

    /**
     * Add receivers.
     *
     * @return Message
     */
    public function addReceiver(MessageUser $receiver): self
    {
        $this->receivers[] = $receiver;
        $receiver->setMessage($this);

        return $this;
    }

    /**
     * Remove receivers.
     */
    public function removeReceiver(MessageUser $receiver)
    {
        $this->receivers->removeElement($receiver);
        $receiver->setMessage(null);
    }

    /**
     * Get receivers.
     */
    public function getReceivers(): Collection
    {
        return $this->receivers;
    }
}
