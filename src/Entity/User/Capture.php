<?php

namespace App\Entity\User;

use App\Repository\CaptureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Capture.
 */
#[ORM\Table(name: 'nt_capture')]
#[ORM\Entity(repositoryClass: CaptureRepository::class)]
class Capture
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?UserInterface $user = null;

    #[ORM\Column(name: 'url', type: Types::STRING, length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[Assert\NotBlank]
    private string $url;

    #[ORM\Column(name: 'url_tmb', type: Types::STRING, length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[Assert\NotBlank]
    private string $urlTmb;

    #[ORM\Column(name: 'delete_hash', type: Types::STRING, length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private string $deleteHash;

    #[ORM\Column(name: 'date_ajout', type: Types::DATETIME_MUTABLE)]
    private \DateTime $dateAjout;

    /**
     * Constructor.
     */
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
     * Set urlTmb.
     */
    public function setUrlTmb(string $urlTmb): self
    {
        $this->urlTmb = $urlTmb;

        return $this;
    }

    /**
     * Get urlTmb.
     */
    public function getUrlTmb(): ?string
    {
        return $this->urlTmb;
    }

    /**
     * Set deleteHash.
     */
    public function setDeleteHash(string $deleteHash): self
    {
        $this->deleteHash = $deleteHash;

        return $this;
    }

    /**
     * Get deleteHash.
     */
    public function getDeleteHash(): ?string
    {
        return $this->deleteHash;
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
     * Set user.
     */
    public function setUser(?UserInterface $user = null): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }
}
