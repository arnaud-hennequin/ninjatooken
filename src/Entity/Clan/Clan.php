<?php

namespace App\Entity\Clan;

use App\Entity\Forum\Forum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Clan.
 */
#[ORM\Table(name: 'nt_clan')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: \App\Repository\ClanRepository::class)]
class Clan implements SluggableInterface
{
    use SluggableTrait;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'old_id', type: 'integer', nullable: true)]
    private ?int $old_id;

    /**
     * @var ?Collection<ClanUtilisateur>
     */
    #[ORM\OneToMany(targetEntity: ClanUtilisateur::class, mappedBy: 'clan', cascade: ['remove'])]
    #[ORM\OrderBy(['dateAjout' => 'ASC'])]
    private ?Collection $membres;

    /**
     * @var ?Collection<Forum>
     */
    #[ORM\OneToMany(targetEntity: Forum::class, mappedBy: 'clan', cascade: ['persist', 'remove'])]
    private ?Collection $forums;

    #[ORM\Column(name: 'nom', type: 'string', length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $nom;

    #[ORM\Column(name: 'tag', type: 'string', length: 5, nullable: true)]
    #[Assert\Length(max: 5)]
    private ?string $tag = null;

    #[ORM\Column(name: 'accroche', type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $accroche = null;

    #[ORM\Column(name: 'description', type: 'text')]
    #[Assert\NotBlank]
    private string $description;

    #[ORM\Column(name: 'url', type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    private ?string $url = null;

    #[ORM\Column(name: 'kamon', type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $kamon = '';

    #[ORM\Column(name: 'kamon_upload', type: 'string', length: 255, nullable: true)]
    private ?string $kamonUpload = null;

    #[ORM\Column(name: 'date_ajout', type: 'datetime')]
    private \DateTime $dateAjout;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt = null;

    #[ORM\Column(name: 'online', type: 'boolean')]
    private bool $online = true;

    #[ORM\Column(name: 'is_recruting', type: 'boolean')]
    private bool $isRecruting = true;

    #[Ignore]
    private ?string $tempKamon = null;

    #[Ignore]
    public ?UploadedFile $file = null;

    #[Ignore]
    public bool $delete = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->membres = new ArrayCollection();
        $this->forums = new ArrayCollection();

        $this->setDateAjout(new \DateTime());
    }

    public function __toString()
    {
        return $this->nom;
    }

    public function serialize(): ?string
    {
        return serialize([
            $this->nom,
            $this->tag,
            $this->accroche,
            $this->description,
            $this->url,
            $this->id,
        ]);
    }

    public function unserialize($data)
    {
        [
            $this->nom,
            $this->tag,
            $this->accroche,
            $this->description,
            $this->url,
            $this->id
        ] = unserialize($data);
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

    public function getAbsoluteKamon(): ?string
    {
        return '' === $this->kamonUpload ? null : $this->getUploadRootDir().'/'.$this->kamonUpload;
    }

    public function getWebKamon(): ?string
    {
        return '' === $this->kamonUpload ? null : $this->getUploadDir().'/'.$this->kamonUpload;
    }

    protected function getUploadRootDir(): string
    {
        return __DIR__.'/../../../public/'.$this->getUploadDir();
    }

    protected function getUploadDir(): string
    {
        return 'kamon';
    }

    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->dateAjout = new \DateTime();
        $this->updatedAt = new \DateTime();

        if (null !== $this->file) {
            $this->setKamonUpload(uniqid((string) mt_rand(), true).'.'.$this->file->guessExtension());
        }
    }

    #[ORM\PreUpdate]
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();

        if (null !== $this->file) {
            $file = $this->id.'.'.$this->file->guessExtension();

            $fileAbsolute = $this->getUploadRootDir().$file;
            if (file_exists($fileAbsolute)) {
                unlink($fileAbsolute);
            }

            $this->setKamonUpload($file);
        }
    }

    #[ORM\PostPersist]
    #[ORM\PostUpdate]
    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        $this->file->move($this->getUploadRootDir(), $this->getKamonUpload());

        unset($this->file);
    }

    #[ORM\PreRemove]
    public function storeFilenameForRemove()
    {
        $this->tempKamon = $this->getAbsoluteKamon();
    }

    #[ORM\PostRemove]
    public function removeUpload()
    {
        if ($this->tempKamon && file_exists($this->tempKamon)) {
            unlink($this->tempKamon);
        }
    }

    /**
     * Sets file.
     */
    public function setFile(?UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set nom.
     */
    public function setNom(?string $nom): self
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
     * Set tag.
     */
    public function setTag(?string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag.
     */
    public function getTag(): ?string
    {
        return $this->tag;
    }

    /**
     * Set description.
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set accroche.
     */
    public function setAccroche(?string $accroche): self
    {
        $this->accroche = $accroche;

        return $this;
    }

    /**
     * Get accroche.
     */
    public function getAccroche(): ?string
    {
        return $this->accroche;
    }

    /**
     * Set url.
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Set kamon.
     */
    public function setKamon(?string $kamon): self
    {
        $this->kamon = $kamon;

        return $this;
    }

    /**
     * Get kamon.
     */
    public function getKamon(): ?string
    {
        return $this->kamon;
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
     * Set online.
     */
    public function setOnline(bool $online): self
    {
        $this->online = $online;

        return $this;
    }

    /**
     * Get online.
     */
    public function getOnline(): ?bool
    {
        return $this->online;
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
     * Add membres.
     */
    public function addMembre(ClanUtilisateur $membre): self
    {
        $this->membres[] = $membre;
        $membre->setClan($this);

        return $this;
    }

    /**
     * Remove membres.
     */
    public function removeMembre(ClanUtilisateur $membre)
    {
        $this->membres->removeElement($membre);
    }

    /**
     * Get membres.
     */
    public function getMembres(): ?Collection
    {
        return $this->membres;
    }

    /**
     * Set isRecruting.
     */
    public function setIsRecruting(bool $isRecruting): self
    {
        $this->isRecruting = $isRecruting;

        return $this;
    }

    /**
     * Get isRecruting.
     */
    public function getIsRecruting(): ?bool
    {
        return $this->isRecruting;
    }

    /**
     * Add forums.
     */
    public function addForum(Forum $forums): self
    {
        $this->forums[] = $forums;
        $forums->setClan($this);

        return $this;
    }

    /**
     * Remove forums.
     */
    public function removeForum(Forum $forums)
    {
        $this->forums->removeElement($forums);
    }

    /**
     * Get forums.
     */
    public function getForums(): ?Collection
    {
        return $this->forums;
    }

    /**
     * Set kamonUpload.
     */
    public function setKamonUpload(string $kamonUpload): self
    {
        $this->kamonUpload = $kamonUpload;

        return $this;
    }

    /**
     * Get kamonUpload.
     */
    public function getKamonUpload(): ?string
    {
        return $this->kamonUpload;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
