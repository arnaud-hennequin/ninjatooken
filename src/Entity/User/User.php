<?php
namespace App\Entity\User;

use Sonata\UserBundle\Entity\BaseUser;
use Sonata\UserBundle\Model\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use FOS\UserBundle\Util\Canonicalizer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="nt_user")
 * @ORM\Entity(repositoryClass="App\Entity\User\UserRepository")
 *
 * @UniqueEntity(fields="usernameCanonical", errorPath="username", message="fos_user.username.already_used", groups={"Registration", "Profile"})
 * @UniqueEntity(fields="emailCanonical", errorPath="email", message="fos_user.email.already_used", groups={"Registration", "Profile"})
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User\Group", fetch="LAZY")
     * @ORM\JoinTable(name="nt_user_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Game\Ninja", mappedBy="user", cascade={"persist", "remove"}, fetch="EAGER")
     */
    private $ninja;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Clan\ClanUtilisateur", mappedBy="membre", cascade={"persist", "remove"}, fetch="EAGER")
     */
    private $clan;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Clan\ClanUtilisateur", mappedBy="recruteur", cascade={"persist", "remove"}, fetch="LAZY")
     * @ORM\OrderBy({"dateAjout" = "ASC"})
     */
    private $recruts;

    /**
     * @var int
     *
     * @ORM\Column(name="old_id", type="integer", nullable=true)
     */
    private $old_id;

    /**
    * @var string
    *
    * @ORM\Column(name="old_login", type="string", length=255, nullable=true)
    */
    private $old_login;

    /**
     * @Gedmo\Slug(fields={"username"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
    * @var string
    *
    * @ORM\Column(name="description", type="text", nullable=true)
    */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
     */
    private $avatar;

    // propriété utilisé temporairement pour la suppression
    private $tempAvatar;
    private $file;

    /**
     * @var boolean
     *
     * @ORM\Column(name="receive_newsletter", type="boolean")
     */
    private $receiveNewsletter = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="receive_avertissement", type="boolean")
     */
    private $receiveAvertissement = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="locked", type="boolean")
     */
    private $locked = false;

    /**
     * @var array
     *
     * @ORM\Column(name="old_usernames", type="array")
     */
    private $oldUsernames;

    /**
     * @var string
     *
     * @ORM\Column(name="old_usernames_canonical", type="string")
     */
    private $oldUsernamesCanonical;

    /**
     * @var string
     *
     * @ORM\Column(name="auto_login", type="string", length=255, nullable=true)
     */
    private $autoLogin;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User\Ip", mappedBy="user", cascade={"persist", "remove"}, fetch="LAZY")
     */
    private $ips;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User\Message", mappedBy="author", fetch="LAZY")
     */
    private $messages;

    public function __construct()
    {
        parent::__construct();

        $this->setGender(UserInterface::GENDER_MALE);
        $this->oldUsernames = array();
        $this->oldUsernamesCanonical = "";
        $this->roles = array('ROLE_USER');
        $this->setConfirmationToken(null);
        $this->setAutoLogin(null);
        $this->setTimezone('Europe/Paris');
        $this->setDescription('');
        $this->setAvatar('');
        $this->recruts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ips = new \Doctrine\Common\Collections\ArrayCollection();
        $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getAbsoluteAvatar()
    {
        return null === $this->avatar || "" === $this->avatar ? null : $this->getUploadRootDir().'/'.$this->avatar;
    }

    public function getWebAvatar()
    {
        return null === $this->avatar || "" === $this->avatar  ? null : $this->getUploadDir().'/'.$this->avatar;
    }

    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../public/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'avatar';
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist(): void
    {
        parent::prePersist();

        if (null !== $this->file) {
            $this->setAvatar(uniqid(mt_rand(), true).".".$this->file->guessExtension());
        }
    }

    /**
     * @ORM\PreUpdate()
     */
    public function preUpdate(): void
    {
        parent::preUpdate();
        if (null !== $this->file) {
            $file = $this->id.'.'.$this->file->guessExtension();

            $fileAbsolute = $this->getUploadRootDir().$file;
            if(file_exists($fileAbsolute))
                unlink($fileAbsolute);

            $this->setAvatar($file);
        }

        // met à jour les anciens pseudos
        $canonicalizer = new Canonicalizer();
        $oldUsernamesCanonical = '';
        $oldUsernames = $this->getOldUsernames();
        if(!empty($oldUsernames)){
            $oldUsernamesCanonical .= ',';
            foreach($oldUsernames as $oldUsername){
                $oldUsernamesCanonical .= $canonicalizer->canonicalize($oldUsername).',';
            }
        }
        $this->setOldUsernamesCanonical($oldUsernamesCanonical);
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

        $this->file->move($this->getUploadRootDir(), $this->getAvatar());

        unset($this->file);
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @ORM\PreRemove()
     */
    public function storeFilenameForRemove()
    {
        $this->tempAvatar = $this->getAbsoluteAvatar();
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if($this->tempAvatar && file_exists($this->tempAvatar)) {
            unlink($this->tempAvatar);
        }
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
     * Set slug
     *
     * @param string $slug
     * @return User
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
    * Set description
    *
    * @param string $description
    * @return User
    */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
    * Get description
    *
    * @return string
    */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string 
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Returns the old user names
     *
     * @return array The usernames
     */
    public function getOldUsernames()
    {
        return array_unique($this->oldUsernames);
    }

    /**
     * Set oldUsername
     *
     * @param array $oldUsername
     * @return User
     */
    public function setOldUsernames(array $oldUsernames)
    {
        $this->oldUsernames = array();

        foreach ($oldUsernames as $oldUsername) {
            $this->addOldUsername($oldUsername);
        }

        return $this;
    }

    /**
     * add oldusername
     */
    public function addOldUsername($username)
    {
        if (!in_array($username, $this->oldUsernames, true)) {
            $this->oldUsernames[] = $username;
        }

        return $this;
    }

    /**
     * remove oldusername
     */
    public function removeOldUsername($username)
    {
        if (false !== $key = array_search(strtoupper($username), $this->oldUsernames, true)) {
            unset($this->oldUsernames[$key]);
            $this->oldUsernames = array_values($this->oldUsernames);
        }

        return $this;
    }

    /**
     * Set oldUsernamesCanonical
     *
     * @param string $oldUsernamesCanonical
     * @return User
     */
    public function setOldUsernamesCanonical($oldUsernamesCanonical)
    {
        $this->oldUsernamesCanonical = $oldUsernamesCanonical;

        return $this;
    }

    /**
     * Get oldUsernamesCanonical
     *
     * @return string 
     */
    public function getOldUsernamesCanonical()
    {
        return $this->oldUsernamesCanonical;
    }

    /**
     * Set autoLogin
     *
     * @param string $autoLogin
     * @return User
     */
    public function setAutoLogin($autoLogin)
    {
        $this->autoLogin = $autoLogin;

        return $this;
    }

    /**
     * Get autoLogin
     *
     * @return string 
     */
    public function getAutoLogin()
    {
        return $this->autoLogin;
    }

    /**
     * Set receive_newsletter
     *
     * @param boolean $receiveNewsletter
     * @return User
     */
    public function setReceiveNewsletter($receiveNewsletter)
    {
        $this->receiveNewsletter = $receiveNewsletter;

        return $this;
    }

    /**
     * Get receive_newsletter
     *
     * @return boolean 
     */
    public function getReceiveNewsletter()
    {
        return $this->receiveNewsletter;
    }

    /**
     * Set receive_avertissement
     *
     * @param boolean $receiveAvertissement
     * @return User
     */
    public function setReceiveAvertissement($receiveAvertissement)
    {
        $this->receiveAvertissement = $receiveAvertissement;

        return $this;
    }

    /**
     * Get receive_avertissement
     *
     * @return boolean 
     */
    public function getReceiveAvertissement()
    {
        return $this->receiveAvertissement;
    }

    /**
     * Set locked
     *
     * @param boolean $locked
     * @return User
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get locked
     *
     * @return boolean 
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Set old_id
     *
     * @param integer $oldId
     * @return User
     */
    public function setOldId($oldId)
    {
        $this->old_id = $oldId;

        return $this;
    }

    /**
     * Get old_id
     *
     * @return integer 
     */
    public function getOldId()
    {
        return $this->old_id;
    }

    /**
     * Set old_login
     *
     * @param string $oldLogin
     * @return User
     */
    public function setOldLogin($oldLogin)
    {
        $this->old_login = $oldLogin;

        return $this;
    }

    /**
     * Get old_login
     *
     * @return string 
     */
    public function getOldLogin()
    {
        return $this->old_login;
    }


    public function serialize()
    {
        return serialize(array($this->facebookUid, parent::serialize()));
    }

    public function unserialize($data)
    {
        list($this->facebookUid, $parentData) = unserialize($data);
        parent::unserialize($parentData);
    }

    /**
     * Get the full name of the user (first + last name)
     * @return string
     */
    public function getFullName()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    /**
     * @param Array
     */
    public function setFBData($fbdata)
    {
        if (isset($fbdata['id'])) {
            $this->setFacebookUid($fbdata['id']);
        }
        if (isset($fbdata['first_name'])) {
            $this->setFirstname($fbdata['first_name']);
        }
        if (isset($fbdata['last_name'])) {
            $this->setLastname($fbdata['last_name']);
        }
        if (isset($fbdata['username'])) {
            $this->setUsername($fbdata['username']);
        }elseif (isset($fbdata['name'])) {
            $this->setUsername($fbdata['name']);
        }
        if (isset($fbdata['email'])) {
            $this->setEmail($fbdata['email']);
        }
        if (isset($fbdata['gender'])) {
            $this->setGender($fbdata['gender']=="male"?UserInterface::GENDER_MAN:UserInterface::GENDER_FEMALE);
        }
        if(isset($fbdata["birthday"])){
            $this->setDateOfBirth(\DateTime::createFromFormat('m/d/Y', $fbdata["birthday"]));
        }
		if(isset($fbdata['locale'])){
            $this->setLocale($fbdata['locale']=="fr_FR"?"fr":"en");
        }
    }

    /**
     * Set ninja
     *
     * @param \App\Entity\Game\Ninja $ninja
     * @return User
     */
    public function setNinja(\App\Entity\Game\Ninja $ninja = null)
    {
        $this->ninja = $ninja;

        return $this;
    }

    /**
     * Get ninja
     *
     * @return \App\Entity\Game\Ninja 
     */
    public function getNinja()
    {
        return $this->ninja;
    }

    /**
     * Set clan
     *
     * @param \App\Entity\Clan\ClanUtilisateur $clan
     * @return User
     */
    public function setClan(\App\Entity\Clan\ClanUtilisateur $clan = null)
    {
        $this->clan = $clan;

        return $this;
    }

    /**
     * Get clan
     *
     * @return \App\Entity\Clan\ClanUtilisateur 
     */
    public function getClan()
    {
        return $this->clan;
    }

    /**
     * Add recruts
     *
     * @param \App\Entity\Clan\ClanUtilisateur $recruts
     * @return User
     */
    public function addRecrut(\App\Entity\Clan\ClanUtilisateur $recruts)
    {
        $this->recruts[] = $recruts;

        return $this;
    }

    /**
     * Remove recruts
     *
     * @param \App\Entity\Clan\ClanUtilisateur $recruts
     */
    public function removeRecrut(\App\Entity\Clan\ClanUtilisateur $recruts)
    {
        $this->recruts->removeElement($recruts);
    }

    /**
     * Get recruts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRecruts()
    {
        return $this->recruts;
    }

    /**
     * Set recruts collection
     *
     * @return User
     */
    public function setRecruts(\Doctrine\Common\Collections\Collection $recruts)
    {
        $this->recruts = $recruts;

        return $this;
    }

    /**
     * Add ips
     *
     * @param \App\Entity\User\Ip $ips
     * @return User
     */
    public function addIp(\App\Entity\User\Ip $ips)
    {
        $this->ips[] = $ips;
        $ips->setUser($this);

        return $this;
    }

    /**
     * Remove ips
     *
     * @param \App\Entity\User\Ip $ips
     */
    public function removeIp(\App\Entity\User\Ip $ips)
    {
        $this->ips->removeElement($ips);
    }

    /**
     * Get ips
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getIps()
    {
        return $this->ips;
    }

    /**
     * Add messages
     *
     * @param \App\Entity\User\Message $messages
     * @return User
     */
    public function addMessage(\App\Entity\User\Message $message)
    {
        $this->messages[] = $message;
        $message->setAuthor($this);

        return $this;
    }

    /**
     * Remove messages
     *
     * @param \App\Entity\User\Message $messages
     */
    public function removeMessage(\App\Entity\User\Message $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
