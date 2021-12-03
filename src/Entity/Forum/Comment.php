<?php
namespace App\Entity\Forum;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="nt_comment")
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id = null;

    /**
     * Thread of this comment
     *
     * @var Thread
     * @ORM\ManyToOne(targetEntity="App\Entity\Forum\Thread", fetch="LAZY")
     * @ORM\JoinColumn(name="thread_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private Thread $thread;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private DateTime $dateAjout;

    /**
     * Comment text
     *
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     * @Assert\NotBlank
     */
    private string $body;

    /**
    * Author of the comment
    *
    * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="LAZY")
    * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
    * @var UserInterface|null
     */
    private ?UserInterface $author;

    /**
    * @var int|null
     *
    * @ORM\Column(name="old_id", type="integer", nullable=true)
    */
    private ?int $old_id;

    public function __construct()
    {
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
     * Set author's name
     *
     * @param UserInterface $author
     * @return Comment
     */
    public function setAuthor(UserInterface $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
    * Get author's name
    * 
    * @return UserInterface
     */
    public function getAuthor(): UserInterface
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param  string
     *
     * @return Comment
     */
    public function setBody($body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateAjout(): ?DateTime
    {
        return $this->dateAjout;
    }

    /**
     * Sets the creation date
     * @param DateTime $dateAjout
     *
     * @return Comment
     */
    public function setDateAjout(DateTime $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    /**
     * @return Thread
     */
    public function getThread(): ?Thread
    {
        return $this->thread;
    }

    /**
     * @param Thread $thread
     *
     * @return Comment
     */
    public function setThread(Thread $thread): self
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * Set old_id
     *
     * @param int|null $oldId
     * @return Comment
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
}