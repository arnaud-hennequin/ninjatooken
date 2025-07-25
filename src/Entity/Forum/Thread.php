<?php

namespace App\Entity\Forum;

use App\Entity\User\User;
use App\Entity\User\UserInterface;
use App\Repository\ThreadRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'nt_thread')]
#[ORM\Entity(repositoryClass: ThreadRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Thread
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[ORM\Column(name: 'old_id', type: Types::INTEGER, nullable: true)]
    private ?int $old_id;

    /**
     * Tells if the thread is viewable on top of list.
     */
    #[ORM\Column(name: 'is_postit', type: Types::BOOLEAN)]
    private bool $isPostit = false;

    #[ORM\Column(name: 'is_event', type: Types::BOOLEAN)]
    private bool $isEvent = false;

    #[ORM\Column(name: 'date_event_start', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateEventStart;

    #[ORM\Column(name: 'date_event_end', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dateEventEnd;

    /**
     * Tells if new comments can be added in this thread.
     */
    #[ORM\Column(name: 'is_commentable', type: Types::BOOLEAN)]
    private bool $isCommentable = true;

    /**
     * forum.
     */
    #[ORM\JoinColumn(name: 'forum_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Forum::class, fetch: 'EAGER')]
    private ?Forum $forum;

    #[ORM\Column(name: 'nom', type: Types::STRING, length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $nom;

    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $slug = null;

    #[ORM\Column(name: 'date_ajout', type: Types::DATETIME_MUTABLE)]
    private \DateTime $dateAjout;

    #[ORM\Column(name: 'body', type: Types::TEXT)]
    #[Assert\NotBlank]
    protected string $body;

    #[ORM\Column(name: 'url_video', type: Types::STRING, length: 255, nullable: true)]
    private ?string $urlVideo;

    /**
     * Author of the comment.
     */
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'LAZY')]
    private ?UserInterface $author;

    /**
     * Denormalized number of comments.
     */
    #[ORM\Column(name: 'num_comments', type: Types::INTEGER)]
    private int $numComments = 0;

    /**
     * Denormalized date of the last comment.
     */
    #[ORM\Column(name: 'last_comment_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $lastCommentAt;

    /**
     * Denormalized author of the last comment.
     */
    #[ORM\JoinColumn(name: 'lastCommentBy_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?UserInterface $lastCommentBy;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setDateAjout(new \DateTime());
        $this->setLastCommentAt(new \DateTime());
    }

    public function __toString()
    {
        return $this->nom;
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

    public function getSlug(): string
    {
        return $this->slug ?? '';
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setSlug(): void
    {
        if ($this->slug !== null) {
            return;
        }

        $unicodeString = (new AsciiSlugger())->slug($this->id.'-'.$this->nom, '-');
        $slug = strtolower($unicodeString->toString());

        if ($slug === '') {
            $slug = $this->id;
            if ($slug === null) {
                $slug = uniqid('thread', true);
            }
            $slug = md5((string) $slug);
        }

        $this->slug = $slug;
    }

    /**
     * Set forum.
     */
    public function setForum(?Forum $forum = null): self
    {
        $this->forum = $forum;

        return $this;
    }

    /**
     * Get forum.
     */
    public function getForum(): ?Forum
    {
        return $this->forum;
    }

    /**
     * Set body.
     */
    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body.
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * Set urlVideo.
     */
    public function setUrlVideo(string $urlVideo): self
    {
        $this->urlVideo = $urlVideo;

        return $this;
    }

    /**
     * Get urlVideo.
     */
    public function getUrlVideo(): ?string
    {
        return $this->urlVideo;
    }

    /**
     * Set old_id.
     */
    public function setOldId(int $oldId): self
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
     * Set dateAjout.
     */
    public function setDateAjout(\DateTime $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    /**
     * Get dateAjout.
     */
    public function getDateAjout(): ?\DateTime
    {
        return $this->dateAjout;
    }

    /**
     * Set isEvent.
     */
    public function setIsEvent(bool $isEvent): self
    {
        $this->isEvent = $isEvent;

        return $this;
    }

    /**
     * Get isEvent.
     */
    public function getIsEvent(): ?bool
    {
        return $this->isEvent;
    }

    /**
     * Set author's name.
     */
    public function setAuthor(?UserInterface $author): void
    {
        $this->author = $author;
    }

    /**
     * Get author's name.
     */
    public function getAuthor(): ?UserInterface
    {
        return $this->author;
    }

    /**
     * Set isPostit.
     */
    public function setIsPostit(bool $isPostit): self
    {
        $this->isPostit = $isPostit;

        return $this;
    }

    /**
     * Get isPostit.
     */
    public function getIsPostit(): ?bool
    {
        return $this->isPostit;
    }

    /**
     * Set lastCommentBy.
     */
    public function setLastCommentBy(?UserInterface $lastCommentBy = null): self
    {
        $this->lastCommentBy = $lastCommentBy;

        return $this;
    }

    /**
     * Get lastCommentBy.
     */
    public function getLastCommentBy(): ?UserInterface
    {
        return $this->lastCommentBy;
    }

    /**
     * Gets the number of comments.
     */
    public function getNumComments(): ?int
    {
        return $this->numComments;
    }

    /**
     * Sets the number of comments.
     */
    public function setNumComments(int $numComments): void
    {
        $this->numComments = $numComments;
    }

    /**
     * Increments the number of comments by the supplied
     * value.
     *
     * @param int $by Value to increment comments by
     *
     * @return int|null The new comment total
     */
    public function incrementNumComments(int $by = 1): ?int
    {
        return $this->numComments += $by;
    }

    public function getLastCommentAt(): ?\DateTime
    {
        return $this->lastCommentAt;
    }

    public function setLastCommentAt(\DateTime $lastCommentAt): self
    {
        $this->lastCommentAt = $lastCommentAt;

        return $this;
    }

    /**
     * Set dateEventStart.
     */
    public function setDateEventStart(?\DateTime $dateEventStart): self
    {
        $this->dateEventStart = $dateEventStart;

        return $this;
    }

    /**
     * Get dateEventStart.
     */
    public function getDateEventStart(): ?\DateTime
    {
        return $this->dateEventStart;
    }

    /**
     * Set dateEventEnd.
     */
    public function setDateEventEnd(?\DateTime $dateEventEnd): self
    {
        $this->dateEventEnd = $dateEventEnd;

        return $this;
    }

    /**
     * Get dateEventEnd.
     */
    public function getDateEventEnd(): ?\DateTime
    {
        return $this->dateEventEnd;
    }

    /**
     * Set isCommentable.
     */
    public function setIsCommentable(bool $isCommentable): self
    {
        $this->isCommentable = $isCommentable;

        return $this;
    }

    /**
     * Get isCommentable.
     */
    public function getIsCommentable(): ?bool
    {
        return $this->isCommentable;
    }
}
