<?php

namespace App\Entity\User;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Capture.
 *
 * @ORM\Table(name="nt_capture")
 * @ORM\Entity(repositoryClass="App\Repository\CaptureRepository")
 */
class Capture
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?UserInterface $user = null;

    /**
     * @ORM\Column(name="url", type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\Url()
     * @Assert\NotBlank()
     */
    private string $url;

    /**
     * @ORM\Column(name="url_tmb", type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\Url()
     * @Assert\NotBlank()
     */
    private string $urlTmb;

    /**
     * @ORM\Column(name="delete_hash", type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\NotBlank()
     */
    private string $deleteHash;

    /**
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private DateTime $dateAjout;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setDateAjout(new DateTime());
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
