<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Capture
 *
 * @ORM\Table(name="nt_capture")
 * @ORM\Entity(repositoryClass="App\Entity\User\CaptureRepository")
 */
class Capture
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\Url()
     * @Assert\NotBlank()
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="url_tmb", type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\Url()
     * @Assert\NotBlank()
     */
    private $urlTmb;

    /**
     * @var string
     *
     * @ORM\Column(name="delete_hash", type="string", length=255)
     * @Assert\Length(max=255)
     * @Assert\NotBlank()
     */
    private $deleteHash;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_ajout", type="datetime")
     */
    private $dateAjout;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setDateAjout(new \DateTime());
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Capture
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set urlTmb
     *
     * @param string $urlTmb
     * @return Capture
     */
    public function setUrlTmb($urlTmb)
    {
        $this->urlTmb = $urlTmb;

        return $this;
    }

    /**
     * Get urlTmb
     *
     * @return string 
     */
    public function getUrlTmb()
    {
        return $this->urlTmb;
    }

    /**
     * Set deleteHash
     *
     * @param string $deleteHash
     * @return Capture
     */
    public function setDeleteHash($deleteHash)
    {
        $this->deleteHash = $deleteHash;

        return $this;
    }

    /**
     * Get deleteHash
     *
     * @return string 
     */
    public function getDeleteHash()
    {
        return $this->deleteHash;
    }

    /**
     * Set dateAjout
     *
     * @param \DateTime $dateAjout
     * @return Capture
     */
    public function setDateAjout($dateAjout)
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    /**
     * Get dateAjout
     *
     * @return \DateTime 
     */
    public function getDateAjout()
    {
        return $this->dateAjout;
    }

    /**
     * Set user
     *
     * @param \App\Entity\User\User $user
     * @return Capture
     */
    public function setUser(\App\Entity\User\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \App\Entity\User\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
