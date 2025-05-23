<?php

namespace App\Entity\Forum;

use App\Entity\Clan\Clan;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Forum.
 */
#[ORM\Table(name: 'nt_forum')]
#[ORM\Entity(repositoryClass: \App\Repository\ForumRepository::class)]
class Forum implements SluggableInterface
{
    use SluggableTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'old_id', type: 'integer', nullable: true)]
    private ?int $old_id;

    #[ORM\Column(name: 'nom', type: 'string', length: 255)]
    private string $nom;

    #[ORM\Column(name: 'ordre', type: 'smallint')]
    private int $ordre = 0;

    #[ORM\Column(name: 'can_user_create_thread', type: 'boolean')]
    private bool $canUserCreateThread = true;

    #[ORM\Column(name: 'num_threads', type: 'integer')]
    private int $numThreads = 0;

    #[ORM\Column(name: 'date_ajout', type: 'datetime')]
    private \DateTime $dateAjout;

    #[ORM\ManyToOne(targetEntity: Clan::class, inversedBy: 'forums', fetch: 'EAGER')]
    private ?Clan $clan;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setDateAjout(new \DateTime());
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
            $slug = md5((string) $this->id);
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
     * Set ordre.
     */
    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre.
     */
    public function getOrdre(): ?int
    {
        return $this->ordre;
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

    /**
     * Gets the number of threads.
     */
    public function getNumThreads(): ?int
    {
        return $this->numThreads;
    }

    /**
     * Sets the number of threads.
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
     * @param int $by Value to increment threads by
     *
     * @return int|null The new thread total
     */
    public function incrementNumThreads(int $by = 1): ?int
    {
        return $this->numThreads += $by;
    }

    /**
     * Set clan.
     */
    public function setClan(?Clan $clan = null): self
    {
        $this->clan = $clan;

        return $this;
    }

    /**
     * Get clan.
     */
    public function getClan(): ?Clan
    {
        return $this->clan;
    }

    /**
     * Set canUserCreateThread.
     */
    public function setCanUserCreateThread(bool $canUserCreateThread): self
    {
        $this->canUserCreateThread = $canUserCreateThread;

        return $this;
    }

    /**
     * Get canUserCreateThread.
     */
    public function getCanUserCreateThread(): ?bool
    {
        return $this->canUserCreateThread;
    }
}
