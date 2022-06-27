<?php

namespace App\Entity\Forum;

use App\Entity\User\UserInterface;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="nt_thread")
 * @ORM\Entity(repositoryClass="App\Repository\ThreadRepository")
 */
class Thread implements SluggableInterface
{
    use SluggableTrait;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id = null;

    /**
     * @ORM\Column(name="old_id", type="integer", nullable=true)
     */
    private ?int $old_id;

    /**
     * Tells if the thread is viewable on top of list.
     *
     * @ORM\Column(name="is_postit", type="boolean")
     */
    private bool $isPostit = false;

    /**
     * @ORM\Column(name="is_event", type="boolean")
     */
    private bool $isEvent = false;

    /**
     * @ORM\Column(name="date_event_start", type="date", nullable=true)
     */
    private ?DateTime $dateEventStart;

    /**
     * @ORM\Column(name="date_event_end", type="date", nullable=true)
     */
    private ?DateTime $dateEventEnd;

    /**
     * Tells if new comments can be added in this thread.
     *
     * @ORM\Column(name="is_commentable", type="boolean")
     */
    private bool $isCommentable = true;

    /**
     * forum.
     *
     * @ORM\ManyToOne(targetEntity="Forum", fetch="EAGER")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private Forum $forum;

    /**
     * @ORM\Column(name="nom", type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\NotBlank()
     */
    private string $nom;

    /**
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private DateTime $dateAjout;

    /**
     * @ORM\Column(name="body", type="text")
     * @Assert\NotBlank()
     */
    protected string $body;

    /**
     * @ORM\Column(name="url_video", type="string", length=255, nullable=true)
     */
    private ?string $urlVideo;

    /**
     * Author of the comment.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?UserInterface $author;

    /**
     * Denormalized number of comments.
     *
     * @ORM\Column(name="num_comments", type="integer")
     */
    private int $numComments = 0;

    /**
     * Denormalized date of the last comment.
     *
     * @ORM\Column(name="last_comment_at", type="datetime", nullable=true)
     */
    private ?DateTime $lastCommentAt;

    /**
     * Denormalized author of the last comment.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     * @ORM\JoinColumn(name="lastCommentBy_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private ?UserInterface $lastCommentBy;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setDateAjout(new DateTime());
        $this->setLastCommentAt(new DateTime());
    }

    public function __toString()
    {
        return $this->nom;
    }

    /**
     * @return string[]
     */
    public function getSluggableFields(): array
    {
        return ['id', 'nom'];
    }

    public function shouldGenerateUniqueSlugs(): bool
    {
        return true;
    }

    public function generateSlugValue($values): ?string
    {
        $usableValues = [];
        foreach ($values as $fieldValue) {
            if (!empty($fieldValue)) {
                $usableValues[] = $fieldValue;
            }
        }

        $this->ensureAtLeastOneUsableValue($values, $usableValues);

        // generate the slug itself
        $sluggableText = implode(' ', $usableValues);

        $unicodeString = (new AsciiSlugger())->slug($sluggableText, $this->getSlugDelimiter());

        $slug = strtolower($unicodeString->toString());

        if (empty($slug)) {
            $slug = md5($this->id ?? uniqid('thread'));
        }

        return $slug;
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

    /**
     * Set forum.
     */
    public function setForum(Forum $forum = null): self
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
    public function setAuthor(?UserInterface $author)
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
    public function setNumComments(int $numComments)
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

    public function getLastCommentAt(): ?DateTime
    {
        return $this->lastCommentAt;
    }

    /**
     * @return Thread
     */
    public function setLastCommentAt(DateTime $lastCommentAt): self
    {
        $this->lastCommentAt = $lastCommentAt;

        return $this;
    }

    /**
     * Set dateEventStart.
     *
     * @return Thread
     */
    public function setDateEventStart(?DateTime $dateEventStart): self
    {
        $this->dateEventStart = $dateEventStart;

        return $this;
    }

    /**
     * Get dateEventStart.
     */
    public function getDateEventStart(): ?DateTime
    {
        return $this->dateEventStart;
    }

    /**
     * Set dateEventEnd.
     *
     * @return Thread
     */
    public function setDateEventEnd(?DateTime $dateEventEnd): self
    {
        $this->dateEventEnd = $dateEventEnd;

        return $this;
    }

    /**
     * Get dateEventEnd.
     */
    public function getDateEventEnd(): ?DateTime
    {
        return $this->dateEventEnd;
    }

    /**
     * Set isCommentable.
     *
     * @return Thread
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
