<?php

namespace App\Entity\User;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Message
 *
 * @ORM\Table(name="nt_message")
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 */
class Message
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
     * @var int|null
     *
     * @ORM\Column(name="old_id", type="integer", nullable=true)
     */
    private ?int $old_id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\NotBlank()
     */
    private string $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     * @Assert\NotBlank()
     */
    private string $content;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private DateTime $dateAjout;

    /**
     * @var UserInterface|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="messages", fetch="EAGER")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?UserInterface $author;

    /**
     * @var boolean
     *
     * @ORM\Column(name="has_deleted", type="boolean")
     */
    private bool $hasDeleted = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User\MessageUser", mappedBy="message", cascade={"remove"}, fetch="EAGER")
     */
    private Collection $receivers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->receivers = new ArrayCollection();
        $this->setDateAjout(new DateTime());
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
     * Set nom
     *
     * @param string $nom
     * @return Message
     */
    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Message
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set dateAjout
     *
     * @param DateTime $dateAjout
     * @return Message
     */
    public function setDateAjout(DateTime $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    /**
     * Get dateAjout
     *
     * @return DateTime
     */
    public function getDateAjout(): ?DateTime
    {
        return $this->dateAjout;
    }

    /**
     * Set author
     *
     * @param UserInterface|null $author
     * @return Message
     */
    public function setAuthor(?UserInterface $author): Message
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return UserInterface
     */
    public function getAuthor(): ?UserInterface
    {
        return $this->author;
    }

    /**
     * Set hasDeleted
     *
     * @param boolean $hasDeleted
     * @return Message
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
     * Set old_id
     *
     * @param int|null $oldId
     * @return Message
     */
    public function setOldId(?int $oldId): self
    {
        $this->old_id = $oldId;

        return $this;
    }

    /**
     * Get old_id
     *
     * @return integer 
     */
    public function getOldId(): ?int
    {
        return $this->old_id;
    }

    /**
     * Add receivers
     *
     * @param MessageUser $receiver
     * @return Message
     */
    public function addReceiver(MessageUser $receiver): self
    {
        $this->receivers[] = $receiver;
        $receiver->setMessage($this);

        return $this;
    }

    /**
     * Remove receivers
     *
     * @param MessageUser $receiver
     */
    public function removeReceiver(MessageUser $receiver)
    {
        $this->receivers->removeElement($receiver);
        $receiver->setMessage(null);
    }

    /**
     * Get receivers
     *
     * @return Collection
     */
    public function getReceivers(): Collection
    {
        return $this->receivers;
    }
}
