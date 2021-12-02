<?php

namespace App\Entity\Clan;

use App\Entity\Forum\Forum;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * Clan
 *
 * @ORM\Table(name="nt_clan")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="App\Repository\ClanRepository")
 */
class Clan implements SluggableInterface, \Serializable
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
     * @ORM\OneToMany(targetEntity="App\Entity\Clan\ClanUtilisateur", mappedBy="clan", cascade={"remove"})
     * @ORM\OrderBy({"dateAjout" = "ASC"})
     */
    private $membres;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Forum\Forum", mappedBy="clan", cascade={"persist","remove"})
     */
    private $forums;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\NotBlank()
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="tag", type="string", length=5, nullable=true)
     * @Assert\Length(max=5)
     */
    private $tag;

    /**
     * @var string
     *
     * @ORM\Column(name="accroche", type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    private $accroche;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     * @Assert\Url()
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="kamon", type="string", length=255, nullable=true)
     * @Assert\Length(max=255)
     */
    private $kamon;

    /**
     * @var string
     *
     * @ORM\Column(name="kamon_upload", type="string", length=255, nullable=true)
     */
    private $kamonUpload;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private $dateAjout;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="online", type="boolean")
     */
    private $online = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_recruting", type="boolean")
     */
    private $isRecruting = true;

    /**
     * @Ignore()
     */

    private $tempKamon;

    /**
     * @Ignore()
     */
    public $file;


    /**
     * @Ignore()
     */
    public $delete = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->membres = new \Doctrine\Common\Collections\ArrayCollection();
        $this->forums = new \Doctrine\Common\Collections\ArrayCollection();

        $this->setDateAjout(new \DateTime());
    }

    public function __toString(){
        return $this->nom;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): ?string
    {
        return serialize([
            $this->nom,
            $this->tag,
            $this->accroche,
            $this->description,
            $this->url,
            $this->id
        ]);
    }

    public function unserialize($data)
    {
        list(
            $this->nom,
            $this->tag,
            $this->accroche,
            $this->description,
            $this->url,
            $this->id
        ) = unserialize($data);
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

        $unicodeString = (new \Symfony\Component\String\Slugger\AsciiSlugger())->slug($sluggableText, $this->getSlugDelimiter());

        $slug = strtolower($unicodeString->toString());

        if (empty($slug)) {
            $slug = md5($this->id);
        }

        return $slug;
    }

    public function getAbsoluteKamon(): ?string
    {
        return null === $this->kamonUpload || "" === $this->kamonUpload ? null : $this->getUploadRootDir().'/'.$this->kamonUpload;
    }

    public function getWebKamon(): ?string
    {
        return null === $this->kamonUpload || "" === $this->kamonUpload  ? null : $this->getUploadDir().'/'.$this->kamonUpload;
    }

    protected function getUploadRootDir(): string
    {
        return __DIR__.'/../../../public/'.$this->getUploadDir();
    }

    protected function getUploadDir(): string
    {
        return 'kamon';
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist()
    {
        $this->dateAjout = new \DateTime();
        $this->updatedAt = new \DateTime();

        if (null !== $this->file) {
            $this->setKamonUpload(uniqid(mt_rand(), true).".".$this->file->guessExtension());
        }
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();

        if (null !== $this->file) {
            $file = $this->id.'.'.$this->file->guessExtension();

            $fileAbsolute = $this->getUploadRootDir().$file;
            if(file_exists($fileAbsolute)) {
                unlink($fileAbsolute);
            }

            $this->setKamonUpload($file);
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        $this->file->move($this->getUploadRootDir(), $this->getKamonUpload());

        unset($this->file);
    }

    /**
     * @ORM\PreRemove()
     */
    public function storeFilenameForRemove()
    {
        $this->tempKamon = $this->getAbsoluteKamon();
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if($this->tempKamon && file_exists($this->tempKamon)) {
            unlink($this->tempKamon);
        }
    }

    /**
     * Sets file.
     *
     * @param UploadedFile|null $file
     */
    public function setFile(?UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return Clan
     */
    public function setNom(?string $nom): self
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
     * Set tag
     *
     * @param string $tag
     * @return Clan
     */
    public function setTag(?string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Get tag
     *
     * @return string 
     */
    public function getTag(): ?string
    {
        return $this->tag;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Clan
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set accroche
     *
     * @param string $accroche
     * @return Clan
     */
    public function setAccroche(?string $accroche): self
    {
        $this->accroche = $accroche;

        return $this;
    }

    /**
     * Get accroche
     *
     * @return string 
     */
    public function getAccroche(): ?string
    {
        return $this->accroche;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Clan
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Set kamon
     *
     * @param string $kamon
     * @return Clan
     */
    public function setKamon(string $kamon): self
    {
        $this->kamon = $kamon;

        return $this;
    }

    /**
     * Get kamon
     *
     * @return string 
     */
    public function getKamon(): ?string
    {
        return $this->kamon;
    }

    /**
     * Set dateAjout
     *
     * @param \DateTime $dateAjout
     * @return Clan
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
     * Set online
     *
     * @param boolean $online
     * @return Clan
     */
    public function setOnline(bool $online): self
    {
        $this->online = $online;

        return $this;
    }

    /**
     * Get online
     *
     * @return boolean 
     */
    public function getOnline(): ?bool
    {
        return $this->online;
    }

    /**
     * Set old_id
     *
     * @param integer $oldId
     * @return Clan
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
     * Add membres
     *
     * @param ClanUtilisateur $membre
     * @return Clan
     */
    public function addMembre(ClanUtilisateur $membre): self
    {
        $this->membres[] = $membre;
        $membre->setClan($this);

        return $this;
    }

    /**
     * Remove membres
     *
     * @param ClanUtilisateur $membre
     */
    public function removeMembre(ClanUtilisateur $membre)
    {
        $this->membres->removeElement($membre);
    }

    /**
     * Get membres
     *
     * @return Collection
     */
    public function getMembres()
    {
        return $this->membres;
    }

    /**
     * Set isRecruting
     *
     * @param boolean $isRecruting
     * @return Clan
     */
    public function setIsRecruting(bool $isRecruting): self
    {
        $this->isRecruting = $isRecruting;

        return $this;
    }

    /**
     * Get isRecruting
     *
     * @return boolean 
     */
    public function getIsRecruting(): ?bool
    {
        return $this->isRecruting;
    }

    /**
     * Add forums
     *
     * @param Forum $forums
     * @return Clan
     */
    public function addForum(Forum $forums): self
    {
        $this->forums[] = $forums;
        $forums->setClan($this);

        return $this;
    }

    /**
     * Remove forums
     *
     * @param Forum $forums
     */
    public function removeForum(Forum $forums)
    {
        $this->forums->removeElement($forums);
    }

    /**
     * Get forums
     *
     * @return Collection
     */
    public function getForums(): ?Collection
    {
        return $this->forums;
    }

    /**
     * Set kamonUpload
     *
     * @param string $kamonUpload
     * @return Clan
     */
    public function setKamonUpload(string $kamonUpload): self
    {
        $this->kamonUpload = $kamonUpload;

        return $this;
    }

    /**
     * Get kamonUpload
     *
     * @return string 
     */
    public function getKamonUpload(): ?string
    {
        return $this->kamonUpload;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
