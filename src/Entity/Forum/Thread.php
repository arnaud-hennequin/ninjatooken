<?php
namespace App\Entity\Forum;

use App\Entity\User\User;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;
use Symfony\Component\Security\Core\User\UserInterface;
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
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(name="old_id", type="integer", nullable=true)
     */
    private $old_id;

    /**
     * Tells if the thread is viewable on top of list
     *
     * @var bool
     *
     * @ORM\Column(name="is_postit", type="boolean")
     */
    private $isPostit = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_event", type="boolean")
     */
    private $isEvent = false;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_event_start", type="date", nullable=true)
     */
    private $dateEventStart;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_event_end", type="date", nullable=true)
     */
    private $dateEventEnd;

    /**
     * Tells if new comments can be added in this thread
     *
     * @var bool
     *
     * @ORM\Column(name="is_commentable", type="boolean")
     */
    private $isCommentable = true;

    /**
     * forum
     *
     * @var Forum
     *
     * @ORM\ManyToOne(targetEntity="Forum", fetch="EAGER")
     * @ORM\JoinColumn(name="forum_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $forum;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\NotBlank()
     */
    private $nom;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private $dateAjout;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     * @Assert\NotBlank()
     */
    protected $body;

    /**
     * @var string
     *
     * @ORM\Column(name="url_video", type="string", length=255, nullable=true)
     */
    private $urlVideo;

    /**
    * Author of the comment
    *
    * @ORM\ManyToOne(targetEntity="App\Entity\User\User", fetch="LAZY")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE")
    * @var User
    */
    private $author;

    /**
     * Denormalized number of comments
     *
     * @var integer
     *
     * @ORM\Column(name="num_comments", type="integer")
     */
    private $numComments = 0;

    /**
     * Denormalized date of the last comment
     *
     * @var DateTime
     *
     * @ORM\Column(name="last_comment_at", type="datetime", nullable=true)
     */
    private $lastCommentAt;

    /**
     * Denormalized author of the last comment
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     * @ORM\JoinColumn(name="lastCommentBy_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * @var User
     */
    private $lastCommentBy;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setDateAjout(new DateTime());
        $this->setLastCommentAt(new DateTime());
    }

    public function __toString(){
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
            if (! empty($fieldValue)) {
                $usableValues[] = $fieldValue;
            }
        }

        $this->ensureAtLeastOneUsableValue($values, $usableValues);

        // generate the slug itself
        $sluggableText = implode(' ', $usableValues);

        $unicodeString = (new AsciiSlugger())->slug($sluggableText, $this->getSlugDelimiter());

        $slug = strtolower($unicodeString->toString());

        if (empty($slug)) {
            $slug = md5($this->id);
        }

        return $slug;
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
     * @return Thread
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
     * Set forum
     *
     * @param Forum|null $forum
     * @return Thread
     */
    public function setForum(Forum $forum = null): self
    {
        $this->forum = $forum;

        return $this;
    }

    /**
     * Get forum
     *
     * @return Forum 
     */
    public function getForum(): ?Forum
    {
        return $this->forum;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return Thread
     */
    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * Set urlVideo
     *
     * @param string $urlVideo
     * @return Thread
     */
    public function setUrlVideo(string $urlVideo): self
    {
        $this->urlVideo = $urlVideo;

        return $this;
    }

    /**
     * Get urlVideo
     *
     * @return string 
     */
    public function getUrlVideo(): ?string
    {
        return $this->urlVideo;
    }

    /**
     * Set old_id
     *
     * @param integer $oldId
     * @return Thread
     */
    public function setOldId(int $oldId): self
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
     * Set dateAjout
     *
     * @param DateTime $dateAjout
     * @return Thread
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
     * Set isEvent
     *
     * @param boolean $isEvent
     * @return Thread
     */
    public function setIsEvent(bool $isEvent): self
    {
        $this->isEvent = $isEvent;

        return $this;
    }

    /**
     * Get isEvent
     *
     * @return boolean 
     */
    public function getIsEvent(): ?bool
    {
        return $this->isEvent;
    }

    /**
    * Set author's name
    * 
    * @param UserInterface $author 
    */
    public function setAuthor(UserInterface $author)
    {
        $this->author = $author;
    }

    /**
    * Get author's name
    * 
    * @return User 
    */
    public function getAuthor(): ?User
    {
        return $this->author;
    }


    /**
     * Set isPostit
     *
     * @param boolean $isPostit
     * @return Thread
     */
    public function setIsPostit(bool $isPostit): self
    {
        $this->isPostit = $isPostit;

        return $this;
    }

    /**
     * Get isPostit
     *
     * @return boolean 
     */
    public function getIsPostit(): ?bool
    {
        return $this->isPostit;
    }

    /**
     * Set lastCommentBy
     *
     * @param User|null $lastCommentBy
     * @return Thread
     */
    public function setLastCommentBy(User $lastCommentBy = null): self
    {
        $this->lastCommentBy = $lastCommentBy;

        return $this;
    }

    /**
     * Get lastCommentBy
     *
     * @return User 
     */
    public function getLastCommentBy(): ?User
    {
        return $this->lastCommentBy;
    }

    /**
     * Gets the number of comments
     *
     * @return integer
     */
    public function getNumComments(): ?int
    {
        return $this->numComments;
    }

    /**
     * Sets the number of comments
     *
     * @param integer $numComments
     */
    public function setNumComments(int $numComments)
    {
        $this->numComments = $numComments;
    }

    /**
     * Increments the number of comments by the supplied
     * value.
     *
     * @param integer $by Value to increment comments by
     * @return integer The new comment total
     */
    public function incrementNumComments(int $by = 1): ?int
    {
        return $this->numComments += $by;
    }

    /**
     * @return DateTime
     */
    public function getLastCommentAt(): ?DateTime
    {
        return $this->lastCommentAt;
    }

    /**
     * @param DateTime
     * @return Thread
     */
    public function setLastCommentAt($lastCommentAt): self
    {
        $this->lastCommentAt = $lastCommentAt;
        
        return $this;
    }

    /**
     * Set dateEventStart
     *
     * @param DateTime $dateEventStart
     * @return Thread
     */
    public function setDateEventStart(DateTime $dateEventStart): self
    {
        $this->dateEventStart = $dateEventStart;

        return $this;
    }

    /**
     * Get dateEventStart
     *
     * @return DateTime 
     */
    public function getDateEventStart(): ?DateTime
    {
        return $this->dateEventStart;
    }

    /**
     * Set dateEventEnd
     *
     * @param DateTime $dateEventEnd
     * @return Thread
     */
    public function setDateEventEnd(DateTime $dateEventEnd): self
    {
        $this->dateEventEnd = $dateEventEnd;

        return $this;
    }

    /**
     * Get dateEventEnd
     *
     * @return DateTime 
     */
    public function getDateEventEnd(): ?DateTime
    {
        return $this->dateEventEnd;
    }

    /**
     * Set isCommentable
     *
     * @param boolean $isCommentable
     * @return Thread
     */
    public function setIsCommentable(bool $isCommentable): self
    {
        $this->isCommentable = $isCommentable;

        return $this;
    }

    /**
     * Get isCommentable
     *
     * @return boolean 
     */
    public function getIsCommentable(): ?bool
    {
        return $this->isCommentable;
    }
}
