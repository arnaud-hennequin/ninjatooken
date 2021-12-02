<?php

namespace App\Entity\Forum;

use App\Entity\Clan\Clan;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Forum
 *
 * @ORM\Table(name="nt_forum")
 * @ORM\Entity(repositoryClass="App\Repository\ForumRepository")
 */
class Forum implements SluggableInterface
{
    use SluggableTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="old_id", type="integer", nullable=true)
     */
    private $old_id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var integer
     *
     * @ORM\Column(name="ordre", type="smallint")
     */
    private $ordre = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="can_user_create_thread", type="boolean")
     */
    private $canUserCreateThread = true;

    /**
     * @var integer
     *
     * @ORM\Column(name="num_threads", type="integer")
     */
    private $numThreads = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private $dateAjout;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Clan\Clan", inversedBy="forums", fetch="EAGER")
     */
    private $clan;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setDateAjout(new \DateTime());
    }

    public function __toString(){
        return $this->nom;
    }

    /**
     * @return string[]
     */
    public function getSluggableFields(): array
    {
        return ['nom'];
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
     * @return Forum
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
     * Set ordre
     *
     * @param integer $ordre
     * @return Forum
     */
    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return integer 
     */
    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    /**
     * Set dateAjout
     *
     * @param \DateTime $dateAjout
     * @return Forum
     */
    public function setDateAjout(\DateTime $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    /**
     * Get dateAjout
     *
     * @return \DateTime 
     */
    public function getDateAjout(): ?\DateTime
    {
        return $this->dateAjout;
    }

    /**
     * Set old_id
     *
     * @param integer $oldId
     * @return Forum
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
     * Gets the number of threads
     *
     * @return integer
     */
    public function getNumThreads(): ?int
    {
        return $this->numThreads;
    }

    /**
     * Sets the number of threads
     *
     * @param integer $threads
     */
    public function setNumThreads(int $threads): self
    {
        $this->numThreads = $threads;

        return $this;
    }

    /**
     * Increments the number of threads by the supplied
     * value.
     *
     * @param integer $by Value to increment threads by
     * @return integer The new thread total
     */
    public function incrementNumThreads(int $by = 1): ?int
    {
        return $this->numThreads += $by;
    }

    /**
     * Set clan
     *
     * @param Clan|null $clan
     * @return Forum
     */
    public function setClan(Clan $clan = null): self
    {
        $this->clan = $clan;

        return $this;
    }

    /**
     * Get clan
     *
     * @return Clan
     */
    public function getClan(): ?Clan
    {
        return $this->clan;
    }

    /**
     * Set canUserCreateThread
     *
     * @param boolean $canUserCreateThread
     * @return Forum
     */
    public function setCanUserCreateThread(bool $canUserCreateThread): self
    {
        $this->canUserCreateThread = $canUserCreateThread;

        return $this;
    }

    /**
     * Get canUserCreateThread
     *
     * @return boolean 
     */
    public function getCanUserCreateThread(): ?bool
    {
        return $this->canUserCreateThread;
    }
}
