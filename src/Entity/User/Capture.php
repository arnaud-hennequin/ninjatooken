<?php

namespace App\Entity\User;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Capture
 *
 * @ORM\Table(name="nt_capture")
 * @ORM\Entity(repositoryClass="App\Repository\CaptureRepository")
 */
class Capture
{
    /**
     * @var integer|null
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private User $user;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\Url()
     * @Assert\NotBlank()
     */
    private string $url;

    /**
     * @var string
     *
     * @ORM\Column(name="url_tmb", type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\Url()
     * @Assert\NotBlank()
     */
    private string $urlTmb;

    /**
     * @var string
     *
     * @ORM\Column(name="delete_hash", type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\NotBlank()
     */
    private string $deleteHash;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private DateTime $dateAjout;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setDateAjout(new DateTime());
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
     * Set url
     *
     * @param string $url
     * @return Capture
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
     * Set urlTmb
     *
     * @param string $urlTmb
     * @return Capture
     */
    public function setUrlTmb(string $urlTmb): self
    {
        $this->urlTmb = $urlTmb;

        return $this;
    }

    /**
     * Get urlTmb
     *
     * @return string 
     */
    public function getUrlTmb(): ?string
    {
        return $this->urlTmb;
    }

    /**
     * Set deleteHash
     *
     * @param string $deleteHash
     * @return Capture
     */
    public function setDeleteHash(string $deleteHash): self
    {
        $this->deleteHash = $deleteHash;

        return $this;
    }

    /**
     * Get deleteHash
     *
     * @return string 
     */
    public function getDeleteHash(): ?string
    {
        return $this->deleteHash;
    }

    /**
     * Set dateAjout
     *
     * @param DateTime $dateAjout
     * @return Capture
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
     * Set user
     *
     * @param User|null $user
     * @return Capture
     */
    public function setUser(?UserInterface $user = null): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }
}
