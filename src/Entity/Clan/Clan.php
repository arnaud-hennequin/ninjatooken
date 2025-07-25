<?php

namespace App\Entity\Clan;

use App\Entity\Forum\Forum;
use App\Repository\ClanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Clan.
 */
#[ORM\Table(name: 'nt_clan')]
#[ORM\Entity(repositoryClass: ClanRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Clan
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'old_id', type: Types::INTEGER, nullable: true)]
    private ?int $old_id;

    /**
     * @var ArrayCollection<int, ClanUtilisateur>
     */
    #[ORM\OneToMany(mappedBy: 'clan', targetEntity: ClanUtilisateur::class, cascade: ['remove'])]
    #[ORM\OrderBy(['dateAjout' => 'ASC'])]
    private Collection $membres;

    /**
     * @var ArrayCollection<int, Forum>
     */
    #[ORM\OneToMany(mappedBy: 'clan', targetEntity: Forum::class, cascade: ['persist', 'remove'])]
    private Collection $forums;

    #[ORM\Column(name: 'nom', type: Types::STRING, length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $nom;

    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $slug = null;

    #[ORM\Column(name: 'tag', type: Types::STRING, length: 5, nullable: true)]
    #[Assert\Length(max: 5)]
    private ?string $tag = null;

    #[ORM\Column(name: 'accroche', type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $accroche = null;

    #[ORM\Column(name: 'description', type: Types::TEXT)]
    #[Assert\NotBlank]
    private string $description;

    #[ORM\Column(name: 'url', type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    private ?string $url = null;

    #[ORM\Column(name: 'kamon', type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $kamon = '';

    #[ORM\Column(name: 'kamon_upload', type: Types::STRING, length: 255, nullable: true)]
    private ?string $kamonUpload = null;

    #[ORM\Column(name: 'date_ajout', type: Types::DATETIME_MUTABLE)]
    private \DateTime $dateAjout;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $updatedAt = null;

    #[ORM\Column(name: 'online', type: Types::BOOLEAN)]
    private bool $online = true;

    #[ORM\Column(name: 'is_recruting', type: Types::BOOLEAN)]
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

    public function unserialize(string $data): void
    {
        [
            $this->nom,
            $this->tag,
            $this->accroche,
            $this->description,
            $this->url,
            $this->id
        ] = unserialize($data, ['allowed_classes' => false]);
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
            $slug = md5((string) $this->id);
        }

        $this->slug = $slug;
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
    public function prePersist(): void
    {
        $this->dateAjout = new \DateTime();
        $this->updatedAt = new \DateTime();

        if (null !== $this->file) {
            $this->setKamonUpload(uniqid((string) mt_rand(), true).'.'.$this->file->guessExtension());
        }
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
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
    public function upload(): void
    {
        if (null === $this->file) {
            return;
        }

        $this->file->move($this->getUploadRootDir(), $this->getKamonUpload());

        $this->file = null;
    }

    #[ORM\PreRemove]
    public function storeFilenameForRemove(): void
    {
        $this->tempKamon = $this->getAbsoluteKamon();
    }

    #[ORM\PostRemove]
    public function removeUpload(): void
    {
        if ($this->tempKamon && file_exists($this->tempKamon)) {
            unlink($this->tempKamon);
        }
    }

    /**
     * Sets file.
     */
    public function setFile(?UploadedFile $file = null): void
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
        $this->membres->add($membre);
        $membre->setClan($this);

        return $this;
    }

    /**
     * Remove membres.
     */
    public function removeMembre(ClanUtilisateur $membre): void
    {
        $this->membres->removeElement($membre);
    }

    /**
     * @return ?Collection<int, ClanUtilisateur>
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
    public function removeForum(Forum $forums): void
    {
        $this->forums->removeElement($forums);
    }

    /**
     * @return ?Collection<int, Forum>
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
