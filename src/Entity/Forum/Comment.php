<?php

namespace App\Entity\Forum;

use App\Entity\User\User;
use App\Entity\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'nt_comment')]
#[ORM\Entity(repositoryClass: \App\Repository\CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    /**
     * Thread of this comment.
     */
    #[ORM\JoinColumn(name: 'thread_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Thread::class, fetch: 'LAZY')]
    private ?Thread $thread;

    #[ORM\Column(name: 'date_ajout', type: 'datetime')]
    private \DateTime $dateAjout;

    /**
     * Comment text.
     */
    #[ORM\Column(name: 'body', type: 'text')]
    #[Assert\NotBlank]
    private string $body;

    /**
     * Author of the comment.
     */
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', onDelete: 'CASCADE', nullable: true)]
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'LAZY')]
    private ?UserInterface $author;

    #[ORM\Column(name: 'old_id', type: 'integer', nullable: true)]
    private ?int $old_id;

    public function __construct()
    {
        $this->setDateAjout(new \DateTime());
    }

    /**
     * Get id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set author's name.
     */
    public function setAuthor(UserInterface $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author's name.
     */
    public function getAuthor(): UserInterface
    {
        return $this->author;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getDateAjout(): ?\DateTime
    {
        return $this->dateAjout;
    }

    /**
     * Sets the creation date.
     */
    public function setDateAjout(\DateTime $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    public function getThread(): ?Thread
    {
        return $this->thread;
    }

    public function setThread(Thread $thread): self
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * Set old_id.
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
}
