<?php
namespace App\Entity\User;

use App\Entity\Clan\ClanUtilisateur;
use App\Entity\Game\Lobby;
use App\Entity\Game\Ninja;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * @ORM\Table(name="nt_user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @UniqueEntity(fields="usernameCanonical", errorPath="username", message="ninja_tooken_user.username.already_used", groups={"Registration", "Profile"})
 * @UniqueEntity(fields="emailCanonical", errorPath="email", message="ninja_tooken_user.email.already_used", groups={"Registration", "Profile"})
 */
class User implements UserInterface, SluggableInterface, PasswordAuthenticatedUserInterface
{
    use SluggableTrait;

    public const GENDER_FEMALE = 'f';
    public const GENDER_MALE = 'm';
    public const GENDER_UNKNOWN = 'u';

    public const MAX_APPLICATION_BY_DAY = 3;

    const ROLE_DEFAULT = 'ROLE_USER';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected DateTime $createdAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected DateTime $updatedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected string $username;

    /**
     * @ORM\Column(name="username_canonical", type="string", length=255, unique=true)
     */
    protected string $usernameCanonical;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected string $email;

    /**
     * @ORM\Column(name="email_canonical", type="string", length=255, unique=true)
     */
    protected string $emailCanonical;

    /**
     * @var bool
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    protected bool $enabled;

    /**
     * The salt to use for hashing.
     *
     * @ORM\Column(type="string", length=255)
     */
    protected string $salt;

    /**
     * Encrypted password. Must be persisted.
     *
     * @ORM\Column(type="string", length=255)
     */
    protected string $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     */
    protected ?string $plainPassword;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */
    protected ?DateTime $lastLogin;

    /**
     * Random string sent to the user email address in order to verify it.
     *
     * @ORM\Column(name="confirmation_token", type="string", length=255, nullable=true)
     */
    protected ?string $confirmationToken;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
     */
    protected ?DateTime $passwordRequestedAt;

    /**
     * @var Collection|null
     */
    protected ?Collection $groups = null;

    /**
     * @ORM\Column(type="array")
     */
    protected array $roles;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Game\Ninja", mappedBy="user", cascade={"persist", "remove"}, fetch="EAGER")
     */
    private ?Ninja $ninja;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Clan\ClanUtilisateur", mappedBy="membre", cascade={"persist", "remove"}, fetch="EAGER")
     */
    private ?ClanUtilisateur $clan;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Clan\ClanUtilisateur", mappedBy="recruteur", cascade={"persist", "remove"}, fetch="LAZY")
     * @ORM\OrderBy({"dateAjout" = "ASC"})
     */
    private Collection $recruts;

    /**
     * @var int
     *
     * @ORM\Column(name="old_id", type="integer", nullable=true)
     */
    private int $old_id;

    /**
    * @var string
    *
    * @ORM\Column(name="old_login", type="string", length=255, nullable=true)
    */
    private string $old_login;

    /**
    * @var string|null
     *
    * @ORM\Column(name="description", type="text", nullable=true)
    */
    private ?string $description;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
     */
    private string $avatar;

    // propriété utilisé temporairement pour la suppression
    private ?string $tempAvatar = null;
    private ?UploadedFile $file = null;

    /**
     * @var boolean
     *
     * @ORM\Column(name="receive_newsletter", type="boolean")
     */
    private bool $receiveNewsletter = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="receive_avertissement", type="boolean")
     */
    private bool $receiveAvertissement = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="locked", type="boolean")
     */
    private bool $locked = false;

    /**
     * @var array
     *
     * @ORM\Column(name="old_usernames", type="array")
     */
    private array $oldUsernames;

    /**
     * @var string
     *
     * @ORM\Column(name="old_usernames_canonical", type="string")
     */
    private string $oldUsernamesCanonical;

    /**
     * @var string|null
     *
     * @ORM\Column(name="auto_login", type="string", length=255, nullable=true)
     */
    private ?string $autoLogin;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User\Ip", mappedBy="user", cascade={"persist", "remove"}, fetch="LAZY")
     */
    private Collection $ips;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User\Message", mappedBy="author", fetch="LAZY")
     */
    private Collection $messages;

    /**
     * @var string
     */
    protected string $twoStepVerificationCode;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="date_of_birth", type="datetime", nullable=true)
     */
    protected ?DateTime $dateOfBirth;

    /**
     * @var string|null
     *
     * @ORM\Column(name="biography", type="string", length=255, nullable=true)
     */
    protected ?string $biography;

    /**
     * @var string
     *
     * @ORM\Column(name="gender", type="string", length=1, nullable=true)
     */
    protected string $gender = self::GENDER_UNKNOWN; // set the default to unknown

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=8, nullable=true)
     */
    protected string $locale;

    /**
     * @var string
     *
     * @ORM\Column(name="timezone", type="string", length=64, nullable=true)
     */
    protected string $timezone;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="date_application", type="datetime", nullable=true)
     */
    protected ?DateTime $dateApplication;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="date_message", type="datetime", nullable=true)
     */
    protected ?DateTime $dateMessage;

    /**
     * @var int|null
     *
     * @ORM\Column(name="number_application", type="integer", nullable=true)
     */
    protected ?int $numberApplication;

    /**
     * lobby
     *
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Game\Lobby", inversedBy="users")
     * @ORM\JoinTable(name="nt_lobby_user",
     *      inverseJoinColumns={@ORM\JoinColumn(name="lobby_id", referencedColumnName="id", onDelete="cascade")},
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="cascade")}
     * )
     */
    private Collection $lobbies;


    public function __construct()
    {
        $this->salt = "";
        $this->enabled = false;
        $this->roles = [];
        $this->numberApplication = 0;

        $this->setGender(self::GENDER_MALE);
        $this->oldUsernames = array();
        $this->oldUsernamesCanonical = "";
        $this->roles = array('ROLE_USER');
        $this->setConfirmationToken(null);
        $this->setAutoLogin(null);
        $this->setTimezone('Europe/Paris');
        $this->setDescription('');
        $this->setAvatar('');
        $this->recruts = new ArrayCollection();
        $this->ips = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->lobbies = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getUserIdentifier();
    }

    public function getUserIdentifier(): ?string
    {
        return $this->getUsername();
    }

    /**
     * @return string[]
     */
    public function getSluggableFields(): array
    {
        return ['username'];
    }

    public function shouldGenerateUniqueSlugs(): bool
    {
        return true;
    }

    public function generateSlugValue($values): string
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

        $unicodeString = (new AsciiSlugger())->slug($sluggableText, $this->getSlugDelimiter());

        $slug = strtolower($unicodeString->toString());

        if (empty($slug)) {
            $slug = md5($this->id ?? uniqid("user"));
        }

        return $slug;
    }

    public static function canonicalize(?string $string = null): string
    {
        if (null === $string) {
            return "";
        }

        $encoding = mb_detect_encoding($string);
        return $encoding
            ? mb_convert_case($string, MB_CASE_LOWER, $encoding)
            : mb_convert_case($string, MB_CASE_LOWER);
    }

    public function addRole(string $role): self
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

    public function serialize(): string
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

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        if (13 === count($data)) {
            // Unserializing a User object from 1.3.x
            unset($data[4], $data[5], $data[6], $data[9], $data[10]);
            $data = array_values($data);
        } else if (11 === count($data)) {
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

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        $this->setUsernameCanonical(self::canonicalize($username));

        return $this;
    }

    public function getUsernameCanonical(): string
    {
        return $this->usernameCanonical;
    }

    public function getSalt(): string
    {
        return $this->salt;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getEmailCanonical(): string
    {
        return $this->emailCanonical;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * Gets the last login time.
     *
     * @return DateTime|null
     */
    public function getLastLogin(): ?DateTime
    {
        return $this->lastLogin;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

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

    public function hasRole($role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function isAccountNonExpired(): bool
    {
        return true;
    }

    public function isAccountNonLocked(): bool
    {
        return true;
    }

    public function isCredentialsNonExpired(): bool
    {
        return true;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    public function removeRole(string $role): self
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function setUsernameCanonical(string $usernameCanonical): self
    {
        $this->usernameCanonical = $usernameCanonical;

        return $this;
    }

    public function setSalt(string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        $this->setEmailCanonical(self::canonicalize($email));

        return $this;
    }

    public function setEmailCanonical(string $emailCanonical): self
    {
        $this->emailCanonical = $emailCanonical;

        return $this;
    }

    public function setEnabled(bool $boolean): self
    {
        $this->enabled = $boolean;

        return $this;
    }

    public function setSuperAdmin(bool $boolean): self
    {
        if (true === $boolean) {
            $this->addRole(static::ROLE_SUPER_ADMIN);
        } else {
            $this->removeRole(static::ROLE_SUPER_ADMIN);
        }

        return $this;
    }

    public function setPlainPassword(?string $password): self
    {
        $this->plainPassword = $password;

        return $this;
    }

    public function setLastLogin(?DateTime $time = null): self
    {
        $this->lastLogin = $time;

        return $this;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function setPasswordRequestedAt(?DateTime $date = null): self
    {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return DateTime|null
     */
    public function getPasswordRequestedAt(): ?DateTime
    {
        return $this->passwordRequestedAt;
    }

    public function isPasswordRequestNonExpired(int $ttl): bool
    {
        return $this->getPasswordRequestedAt() instanceof DateTime &&
               $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    public function setRoles(array $roles): self
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    public function getGroups(): Collection
    {
        return $this->groups ?: $this->groups = new ArrayCollection();
    }

    public function getGroupNames(): array
    {
        $names = [];
        foreach ($this->getGroups() as $group) {
            $names[] = $group->getName();
        }

        return $names;
    }

    public function hasGroup(string $name): bool
    {
        return in_array($name, $this->getGroupNames());
    }

    public function addGroup(Group $group): self
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    public function removeGroup(Group $group): self
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }

        return $this;
    }

    public function isEqualTo(User $user): bool
    {
        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->getUserIdentifier() !== $user->getUserIdentifier()) {
            return false;
        }

        return true;
    }
    public function getAbsoluteAvatar(): ?string
    {
        return null === $this->avatar || "" === $this->avatar ? null : $this->getUploadRootDir().'/'.$this->avatar;
    }

    #[Pure]
    public function getWebAvatar(): ?string
    {
        return null === $this->avatar || "" === $this->avatar  ? null : $this->getUploadDir().'/'.$this->avatar;
    }

    #[Pure]
    protected function getUploadRootDir(): string
    {
        return __DIR__.'/../../../public/'.$this->getUploadDir();
    }

    protected function getUploadDir(): string
    {
        return 'avatar';
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();

        if (null !== $this->file) {
            $this->setAvatar(uniqid(mt_rand(), true).".".$this->file->guessExtension());
        }
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->updatedAt = new DateTime();
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
    public function getFile(): UploadedFile
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
     * Set description
     *
     * @param string|null $description
     * @return User
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     * @return User
     */
    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string 
     */
    public function getAvatar(): string
    {
        return $this->avatar;
    }

    /**
     * Returns the old usernames
     *
     * @return array The usernames
     */
    public function getOldUsernames(): array
    {
        return array_unique($this->oldUsernames);
    }

    /**
     * Set oldUsername
     *
     * @param array $oldUsernames
     * @return User
     */
    public function setOldUsernames(array $oldUsernames): self
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
    public function addOldUsername(string $username): self
    {
        if (!in_array($username, $this->oldUsernames, true)) {
            $this->oldUsernames[] = $username;
        }

        return $this;
    }

    /**
     * remove oldusername
     */
    public function removeOldUsername(string $username): self
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
    public function setOldUsernamesCanonical(string $oldUsernamesCanonical): self
    {
        $this->oldUsernamesCanonical = $oldUsernamesCanonical;

        return $this;
    }

    /**
     * Get oldUsernamesCanonical
     *
     * @return string 
     */
    public function getOldUsernamesCanonical(): string
    {
        return $this->oldUsernamesCanonical;
    }

    /**
     * Set autoLogin
     *
     * @param string|null $autoLogin
     * @return User
     */
    public function setAutoLogin(?string $autoLogin): self
    {
        $this->autoLogin = $autoLogin;

        return $this;
    }

    /**
     * Get autoLogin
     *
     * @return string 
     */
    public function getAutoLogin(): ?string
    {
        return $this->autoLogin;
    }

    /**
     * Set receive_newsletter
     *
     * @param boolean $receiveNewsletter
     * @return User
     */
    public function setReceiveNewsletter(bool $receiveNewsletter): self
    {
        $this->receiveNewsletter = $receiveNewsletter;

        return $this;
    }

    /**
     * Get receive_newsletter
     *
     * @return boolean 
     */
    public function getReceiveNewsletter(): bool
    {
        return $this->receiveNewsletter;
    }

    /**
     * Set receive_avertissement
     *
     * @param boolean $receiveAvertissement
     * @return User
     */
    public function setReceiveAvertissement(bool $receiveAvertissement): self
    {
        $this->receiveAvertissement = $receiveAvertissement;

        return $this;
    }

    /**
     * Get receive_avertissement
     *
     * @return boolean 
     */
    public function getReceiveAvertissement(): bool
    {
        return $this->receiveAvertissement;
    }

    /**
     * Set locked
     *
     * @param boolean $locked
     * @return User
     */
    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get locked
     *
     * @return boolean 
     */
    public function getLocked(): bool
    {
        return $this->locked;
    }

    /**
     * Set old_id
     *
     * @param integer $oldId
     * @return User
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
    public function getOldId(): int
    {
        return $this->old_id;
    }

    /**
     * Set old_login
     *
     * @param string $oldLogin
     * @return User
     */
    public function setOldLogin(string $oldLogin): self
    {
        $this->old_login = $oldLogin;

        return $this;
    }

    /**
     * Get old_login
     *
     * @return string 
     */
    public function getOldLogin(): string
    {
        return $this->old_login;
    }

    /**
     * Set ninja
     *
     * @param Ninja|null $ninja
     * @return User
     */
    public function setNinja(?Ninja $ninja = null): self
    {
        $this->ninja = $ninja;

        return $this;
    }

    /**
     * Get ninja
     *
     * @return Ninja|null
     */
    public function getNinja(): ?Ninja
    {
        return $this->ninja;
    }

    /**
     * Set clan
     *
     * @param ClanUtilisateur|null $clan
     * @return User
     */
    public function setClan(?ClanUtilisateur $clan = null): self
    {
        $this->clan = $clan;

        return $this;
    }

    /**
     * Get clan
     *
     * @return ClanUtilisateur|null
     */
    public function getClan(): ?ClanUtilisateur
    {
        return $this->clan;
    }

    /**
     * Add recruts
     *
     * @param ClanUtilisateur $recruts
     * @return User
     */
    public function addRecrut(ClanUtilisateur $recruts): self
    {
        $this->recruts[] = $recruts;

        return $this;
    }

    /**
     * Remove recruts
     *
     * @param ClanUtilisateur $recruts
     */
    public function removeRecrut(ClanUtilisateur $recruts)
    {
        $this->recruts->removeElement($recruts);
    }

    /**
     * Get recruts
     *
     * @return Collection|null
     */
    public function getRecruts(): ?Collection
    {
        return $this->recruts;
    }

    /**
     * Set recruts collection
     *
     * @param Collection $recruts
     * @return User
     */
    public function setRecruts(Collection $recruts): self
    {
        $this->recruts = $recruts;

        return $this;
    }

    /**
     * Add ips
     *
     * @param Ip $ips
     * @return User
     */
    public function addIp(Ip $ips): self
    {
        $this->ips[] = $ips;
        $ips->setUser($this);

        return $this;
    }

    /**
     * Remove ips
     *
     * @param Ip $ips
     */
    public function removeIp(Ip $ips)
    {
        $this->ips->removeElement($ips);
    }

    /**
     * Get ips
     *
     * @return Collection|null
     */
    public function getIps(): ?Collection
    {
        return $this->ips;
    }

    /**
     * Add messages
     *
     * @param Message $message
     * @return User
     */
    public function addMessage(Message $message): self
    {
        $this->messages[] = $message;
        $message->setAuthor($this);

        return $this;
    }

    /**
     * Remove messages
     *
     * @param Message $messages
     */
    public function removeMessage(Message $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return Collection|null
     */
    public function getMessages(): ?Collection
    {
        return $this->messages;
    }

    public function setCreatedAt(?DateTime $createdAt = null): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt = null): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setBiography(?string $biography): self
    {
        $this->biography = $biography;

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    /**
     * Returns the gender list.
     *
     * @return array
     */
    #[ArrayShape(['gender_unknown' => "string", 'gender_female' => "string", 'gender_male' => "string"])]
    public static function getGenderList(): array
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

    public function getDateOfBirth(): ?DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?DateTimeInterface $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    /**
     * @return Collection|Lobby[]
     */
    public function getLobbies(): ?Collection
    {
        return $this->lobbies;
    }

    public function addLobby(Lobby $lobby): self
    {
        if (!$this->lobbies->contains($lobby)) {
            $this->lobbies[] = $lobby;
        }

        return $this;
    }

    public function removeLobby(Lobby $lobby): self
    {
        $this->lobbies->removeElement($lobby);

        return $this;
    }

    public function getDateApplication(): ?DateTimeInterface
    {
        return $this->dateApplication;
    }

    public function setDateApplication(?DateTimeInterface $dateApplication): self
    {
        $this->dateApplication = $dateApplication;

        return $this;
    }

    public function getNumberApplication(): ?int
    {
        return $this->numberApplication;
    }

    public function setNumberApplication(?int $numberApplication): self
    {
        $this->numberApplication = $numberApplication;

        return $this;
    }

    public function getDateMessage(): ?DateTimeInterface
    {
        return $this->dateMessage;
    }

    public function setDateMessage(?DateTimeInterface $dateMessage): self
    {
        $this->dateMessage = $dateMessage;

        return $this;
    }
}
