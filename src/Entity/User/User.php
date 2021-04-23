<?php
namespace App\Entity\User;

use App\Entity\Clan\ClanUtilisateur;
use App\Entity\Game\Ninja;
use App\Entity\User\Group;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="nt_user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @UniqueEntity(fields="usernameCanonical", errorPath="username", message="ninja_tooken_user.username.already_used", groups={"Registration", "Profile"})
 * @UniqueEntity(fields="emailCanonical", errorPath="email", message="ninja_tooken_user.email.already_used", groups={"Registration", "Profile"})
 */
class User implements UserInterface
{
    public const GENDER_FEMALE = 'f';
    public const GENDER_MALE = 'm';
    public const GENDER_UNKNOWN = 'u';

    const ROLE_DEFAULT = 'ROLE_USER';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $username;

    /**
     * @ORM\Column(name="username_canonical", type="string", length=255, unique=true)
     */
    protected $usernameCanonical;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @ORM\Column(name="email_canonical", type="string", length=255, unique=true)
     */
    protected $emailCanonical;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    protected $enabled;

    /**
     * The salt to use for hashing.
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $salt;

    /**
     * Encrypted password. Must be persisted.
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     */
    protected $plainPassword;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    protected $lastLogin;

    /**
     * Random string sent to the user email address in order to verify it.
     *
     * @ORM\Column(name="confirmation_token", type="string", length=255, nullable=true)
     */
    protected $confirmationToken;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
     */
    protected $passwordRequestedAt;

    /**
     * @var Group[]|Collection
     */
    protected $groups;

    /**
     * @ORM\Column(type="array")
     */
    protected $roles;

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

    /**
     * @var string
     */
    protected $twoStepVerificationCode;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_of_birth", type="datetime", nullable=true)
     */
    protected $dateOfBirth;

    /**
     * @var string
     *
     * @ORM\Column(name="biography", type="string", length=255, nullable=true)
     */
    protected $biography;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=1, nullable=true)
     */
    protected $gender = self::GENDER_UNKNOWN; // set the default to unknown

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=8, nullable=true)
     */
    protected $locale;

    /**
     * @var string
     *
     * @ORM\Column(name="timezone", type="string", length=64, nullable=true)
     */
    protected $timezone;


    public function __construct()
    {
        $this->salt = "";
        $this->enabled = false;
        $this->roles = [];

        $this->setGender(self::GENDER_MALE);
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

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getUsername();
    }

    public static function canonicalize($string)
    {
        if (null === $string) {
            return;
        }

        $encoding = mb_detect_encoding($string);
        $result = $encoding
            ? mb_convert_case($string, MB_CASE_LOWER, $encoding)
            : mb_convert_case($string, MB_CASE_LOWER);

        return $result;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();

        if (null !== $this->file) {
            $this->setAvatar(uniqid(mt_rand(), true).".".$this->file->guessExtension());
        }

        $this->setUsernameCanonical(self::canonicalize($this->getUsername()));
        $this->setEmailCanonical(self::canonicalize($this->getEmail()));
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
        if (null !== $this->file) {
            $file = $this->id.'.'.$this->file->guessExtension();

            $fileAbsolute = $this->getUploadRootDir().$file;
            if(file_exists($fileAbsolute))
                unlink($fileAbsolute);

            $this->setAvatar($file);
        }

        // met à jour les anciens pseudos
        $oldUsernamesCanonical = '';
        $oldUsernames = $this->getOldUsernames();
        if(!empty($oldUsernames)){
            $oldUsernamesCanonical .= ',';
            foreach($oldUsernames as $oldUsername){
                $oldUsernamesCanonical .= self::canonicalize($oldUsername).',';
            }
        }
        $this->setOldUsernamesCanonical($oldUsernamesCanonical);
    }

    /**
     * {@inheritdoc}
     */
    public function addRole($role)
    {
        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
            $this->emailCanonical,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        if (13 === count($data)) {
            // Unserializing a User object from 1.3.x
            unset($data[4], $data[5], $data[6], $data[9], $data[10]);
            $data = array_values($data);
        } elseif (11 === count($data)) {
            // Unserializing a User from a dev version somewhere between 2.0-alpha3 and 2.0-beta1
            unset($data[4], $data[7], $data[8]);
            $data = array_values($data);
        }

        list(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
            $this->emailCanonical
        ) = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsernameCanonical()
    {
        return $this->usernameCanonical;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailCanonical()
    {
        return $this->emailCanonical;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Gets the last login time.
     *
     * @return \DateTime|null
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        foreach ($this->getGroups() as $group) {
            $roles = array_merge($roles, $group->getRoles());
        }

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;

        return array_values(array_unique($roles));
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole($role)
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function isSuperAdmin()
    {
        return $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole($role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setUsernameCanonical($usernameCanonical)
    {
        $this->usernameCanonical = $usernameCanonical;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailCanonical($emailCanonical)
    {
        $this->emailCanonical = $emailCanonical;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($boolean)
    {
        $this->enabled = (bool) $boolean;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSuperAdmin($boolean)
    {
        if (true === $boolean) {
            $this->addRole(static::ROLE_SUPER_ADMIN);
        } else {
            $this->removeRole(static::ROLE_SUPER_ADMIN);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLastLogin(\DateTime $time = null)
    {
        $this->lastLogin = $time;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPasswordRequestedAt(\DateTime $date = null)
    {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return \DateTime|null
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function isPasswordRequestNonExpired($ttl)
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime &&
               $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    /**
     * {@inheritdoc}
     */
    public function setRoles(array $roles)
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups()
    {
        return $this->groups ?: $this->groups = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupNames()
    {
        $names = [];
        foreach ($this->getGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    /**
     * {@inheritdoc}
     */
    public function hasGroup($name)
    {
        return in_array($name, $this->getGroupNames());
    }

    /**
     * {@inheritdoc}
     */
    public function addGroup(Group $group)
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeGroup(Group $group)
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(User $user)
    {
        if (!$user instanceof self) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->salt !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
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

    /**
     * Get the full name of the user (first + last name)
     * @return string
     */
    public function getFullName()
    {
        return $this->getFirstname() . ' ' . $this->getLastname();
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

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(?\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(?\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setBiography($biography)
    {
        $this->biography = $biography;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBiography()
    {
        return $this->biography;
    }

    /**
     * {@inheritdoc}
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Returns the gender list.
     *
     * @return array
     */
    public static function getGenderList()
    {
        return [
            'gender_unknown' => self::GENDER_UNKNOWN,
            'gender_female' => self::GENDER_FEMALE,
            'gender_male' => self::GENDER_MALE,
        ];
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }
}
