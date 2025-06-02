<?php

namespace App\Entity\User;

use App\Entity\Clan\ClanUtilisateur;
use App\Entity\Game\Lobby;
use App\Entity\Game\Ninja;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[ORM\Table(name: 'nt_user')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: 'usernameCanonical', message: 'ninja_tooken_user.username.already_used', errorPath: 'username', groups: ['Registration', 'Profile'])]
#[UniqueEntity(fields: 'emailCanonical', message: 'ninja_tooken_user.email.already_used', errorPath: 'email', groups: ['Registration', 'Profile'])]
class User implements UserInterface, SluggableInterface, PasswordAuthenticatedUserInterface
{
    use SluggableTrait;

    public const string GENDER_FEMALE = 'f';
    public const string GENDER_MALE = 'm';
    public const string GENDER_UNKNOWN = 'u';

    public const int MAX_APPLICATION_BY_DAY = 3;

    public const string ROLE_DEFAULT = 'ROLE_USER';
    public const string ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    protected \DateTime $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    protected \DateTime $updatedAt;

    #[ORM\Column(type: Types::STRING, length: 255)]
    protected string $username;

    #[ORM\Column(name: 'username_canonical', type: Types::STRING, length: 255, unique: true)]
    protected string $usernameCanonical;

    #[ORM\Column(type: Types::STRING, length: 255)]
    protected string $email;

    #[ORM\Column(name: 'email_canonical', type: Types::STRING, length: 255, unique: true)]
    protected string $emailCanonical;

    #[ORM\Column(name: 'enabled', type: Types::BOOLEAN)]
    protected bool $enabled;

    /**
     * The salt to use for hashing.
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    protected string $salt;

    /**
     * Encrypted password. Must be persisted.
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    protected string $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     */
    protected ?string $plainPassword;

    #[ORM\Column(name: 'last_login', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTime $lastLogin;

    /**
     * Random string sent to the user email address in order to verify it.
     */
    #[ORM\Column(name: 'confirmation_token', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $confirmationToken;

    #[ORM\Column(name: 'password_requested_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTime $passwordRequestedAt;

    /**
     * @var ?ArrayCollection<int, Group>
     */
    protected ?ArrayCollection $groups = null;

    /** @var array<int, string> $roles */
    #[ORM\Column(type: Types::ARRAY)]
    protected array $roles;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: Ninja::class, cascade: ['persist', 'remove'], fetch: 'EAGER')]
    private ?Ninja $ninja = null;

    #[ORM\OneToOne(mappedBy: 'membre', targetEntity: ClanUtilisateur::class, cascade: ['persist', 'remove'], fetch: 'EAGER')]
    private ?ClanUtilisateur $clan;

    /**
     * @var ArrayCollection<int, ClanUtilisateur>
     */
    #[ORM\OneToMany(mappedBy: 'recruteur', targetEntity: ClanUtilisateur::class, cascade: ['persist', 'remove'], fetch: 'LAZY')]
    #[ORM\OrderBy(['dateAjout' => 'ASC'])]
    private ArrayCollection $recruts;

    #[ORM\Column(name: 'old_id', type: Types::INTEGER, nullable: true)]
    private ?int $old_id;

    #[ORM\Column(name: 'old_login', type: Types::STRING, length: 255, nullable: true)]
    private ?string $old_login;

    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
    private ?string $description;

    #[ORM\Column(name: 'avatar', type: Types::STRING, length: 255, nullable: true)]
    private ?string $avatar;

    // propriété utilisé temporairement pour la suppression
    private ?string $tempAvatar = null;
    private ?UploadedFile $file = null;

    #[ORM\Column(name: 'receive_newsletter', type: Types::BOOLEAN)]
    private bool $receiveNewsletter = false;

    #[ORM\Column(name: 'receive_avertissement', type: Types::BOOLEAN)]
    private bool $receiveAvertissement = false;

    #[ORM\Column(name: 'locked', type: Types::BOOLEAN)]
    private bool $locked = false;

    /**
     * @var array<int, string> $oldUsernames
     */
    #[ORM\Column(name: 'old_usernames', type: Types::ARRAY)]
    private array $oldUsernames;

    #[ORM\Column(name: 'old_usernames_canonical', type: Types::STRING)]
    private string $oldUsernamesCanonical;

    #[ORM\Column(name: 'auto_login', type: Types::STRING, length: 255, nullable: true)]
    private ?string $autoLogin;

    /**
     * @var ArrayCollection<int, Ip>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Ip::class, cascade: ['persist', 'remove'], fetch: 'LAZY')]
    private ArrayCollection $ips;

    /**
     * @var ArrayCollection<int, Message>
     */
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Message::class, fetch: 'LAZY')]
    private ArrayCollection $messages;

    protected string $twoStepVerificationCode;

    #[ORM\Column(name: 'date_of_birth', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $dateOfBirth;

    #[ORM\Column(name: 'biography', type: Types::STRING, length: 255, nullable: true)]
    protected ?string $biography;

    #[ORM\Column(name: 'gender', type: Types::STRING, length: 1, nullable: true)]
    protected ?string $gender = self::GENDER_UNKNOWN; // set the default to unknown

    #[ORM\Column(name: 'locale', type: Types::STRING, length: 8, nullable: true)]
    protected ?string $locale;

    #[ORM\Column(name: 'timezone', type: Types::STRING, length: 64, nullable: true)]
    protected ?string $timezone;

    #[ORM\Column(name: 'date_application', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $dateApplication;

    #[ORM\Column(name: 'date_message', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $dateMessage;

    #[ORM\Column(name: 'number_application', type: Types::INTEGER, nullable: true)]
    protected ?int $numberApplication;

    /**
     * @var ArrayCollection<int, Lobby>
     */
    #[ORM\JoinTable(name: 'nt_lobby_user')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'cascade')]
    #[ORM\InverseJoinColumn(name: 'lobby_id', referencedColumnName: 'id', onDelete: 'cascade')]
    #[ORM\ManyToMany(targetEntity: Lobby::class, inversedBy: 'users')]
    private ArrayCollection $lobbies;

    private ?int $level = null;

    private int $numNewMessage;

    private int $numDemandesFriends;

    private int $numPropositionsRecrutement;
    public int|float $ratio;
    public string $classement;
    public int|float $total;

    public function __construct()
    {
        $this->salt = '';
        $this->enabled = false;
        $this->roles = [];
        $this->numberApplication = 0;

        $this->setGender(self::GENDER_MALE);
        $this->oldUsernames = [];
        $this->oldUsernamesCanonical = '';
        $this->roles = ['ROLE_USER'];
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

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): void
    {
        $this->level = $level;
    }

    public function getNumNewMessage(): int
    {
        return $this->numNewMessage;
    }

    public function setNumNewMessage(int $numNewMessage): void
    {
        $this->numNewMessage = $numNewMessage;
    }

    public function getNumDemandesFriends(): int
    {
        return $this->numDemandesFriends;
    }

    public function setNumDemandesFriends(int $numDemandesFriends): void
    {
        $this->numDemandesFriends = $numDemandesFriends;
    }

    public function getNumPropositionsRecrutement(): int
    {
        return $this->numPropositionsRecrutement;
    }

    public function setNumPropositionsRecrutement(int $numPropositionsRecrutement): void
    {
        $this->numPropositionsRecrutement = $numPropositionsRecrutement;
    }

    /**
     * @return array<int, string>
     */
    public function getSluggableFields(): array
    {
        return ['username'];
    }

    public function shouldGenerateUniqueSlugs(): bool
    {
        return true;
    }

    public function generateSlugValue(array $values): string
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
            $slug = md5($this->id ?? uniqid('user'));
        }

        return $slug;
    }

    public static function canonicalize(?string $string = null): string
    {
        if (null === $string) {
            return '';
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

    public function unserialize(string $serialized): void
    {
        $data = unserialize($serialized, ['allowed_classes' => false]);

        if (13 === count($data)) {
            // Unserializing a User object from 1.3.x
            unset($data[4], $data[5], $data[6], $data[9], $data[10]);
            $data = array_values($data);
        } elseif (11 === count($data)) {
            // Unserializing a User from a dev version somewhere between 2.0-alpha3 and 2.0-beta1
            unset($data[4], $data[7], $data[8]);
            $data = array_values($data);
        }

        [
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
            $this->emailCanonical
        ] = $data;
    }

    public function eraseCredentials(): void
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
     */
    public function getLastLogin(): ?\DateTime
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

    public function hasRole(string $role): bool
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

    public function setLastLogin(?\DateTime $time = null): self
    {
        $this->lastLogin = $time;

        return $this;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function setPasswordRequestedAt(?\DateTime $date = null): self
    {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     */
    public function getPasswordRequestedAt(): ?\DateTime
    {
        return $this->passwordRequestedAt;
    }

    public function isPasswordRequestNonExpired(int $ttl): bool
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime
               && $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    /**
     * @param array<int, string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * @return ArrayCollection<int, Group>
     */
    public function getGroups(): ArrayCollection
    {
        return $this->groups ?: $this->groups = new ArrayCollection();
    }

    /**
     * @return array<int, string>
     */
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
        return in_array($name, $this->getGroupNames(), true);
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
        return '' === $this->avatar ? null : $this->getUploadRootDir().'/'.$this->avatar;
    }

    public function getWebAvatar(): ?string
    {
        return '' === $this->avatar ? null : $this->getUploadDir().'/'.$this->avatar;
    }

    protected function getUploadRootDir(): string
    {
        return __DIR__.'/../../../public/'.$this->getUploadDir();
    }

    protected function getUploadDir(): string
    {
        return 'avatar';
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();

        if (null !== $this->file) {
            $this->setAvatar(uniqid((string) mt_rand(), true).'.'.$this->file->guessExtension());
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

            $this->setAvatar($file);
        }

        // met à jour les anciens pseudos
        $oldUsernamesCanonical = '';
        $oldUsernames = $this->getOldUsernames();
        if (!empty($oldUsernames)) {
            $oldUsernamesCanonical .= ',';
            foreach ($oldUsernames as $oldUsername) {
                $oldUsernamesCanonical .= self::canonicalize($oldUsername).',';
            }
        }
        $this->setOldUsernamesCanonical($oldUsernamesCanonical);
    }

    #[ORM\PostPersist]
    #[ORM\PostUpdate]
    public function upload(): void
    {
        if (null === $this->file) {
            return;
        }

        $this->file->move($this->getUploadRootDir(), $this->getAvatar());

        unset($this->file);
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
    public function getFile(): UploadedFile
    {
        return $this->file;
    }

    #[ORM\PreRemove]
    public function storeFilenameForRemove(): void
    {
        $this->tempAvatar = $this->getAbsoluteAvatar();
    }

    #[ORM\PostRemove]
    public function removeUpload(): void
    {
        if ($this->tempAvatar && file_exists($this->tempAvatar)) {
            unlink($this->tempAvatar);
        }
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
     * Set avatar.
     */
    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar.
     */
    public function getAvatar(): string
    {
        return $this->avatar;
    }

    /**
     * Returns the old usernames.
     *
     * @return array<int, string> The usernames
     */
    public function getOldUsernames(): array
    {
        return array_unique($this->oldUsernames);
    }

    /**
     * Set oldUsername.
     *
     * @param array<int, string> $oldUsernames
     */
    public function setOldUsernames(array $oldUsernames): self
    {
        $this->oldUsernames = [];

        foreach ($oldUsernames as $oldUsername) {
            $this->addOldUsername($oldUsername);
        }

        return $this;
    }

    /**
     * add oldusername.
     */
    public function addOldUsername(string $username): self
    {
        if (!in_array($username, $this->oldUsernames, true)) {
            $this->oldUsernames[] = $username;
        }

        return $this;
    }

    /**
     * remove oldusername.
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
     * Set oldUsernamesCanonical.
     */
    public function setOldUsernamesCanonical(string $oldUsernamesCanonical): self
    {
        $this->oldUsernamesCanonical = $oldUsernamesCanonical;

        return $this;
    }

    /**
     * Get oldUsernamesCanonical.
     */
    public function getOldUsernamesCanonical(): string
    {
        return $this->oldUsernamesCanonical;
    }

    /**
     * Set autoLogin.
     */
    public function setAutoLogin(?string $autoLogin): self
    {
        $this->autoLogin = $autoLogin;

        return $this;
    }

    /**
     * Get autoLogin.
     */
    public function getAutoLogin(): ?string
    {
        return $this->autoLogin;
    }

    /**
     * Set receive_newsletter.
     */
    public function setReceiveNewsletter(bool $receiveNewsletter): self
    {
        $this->receiveNewsletter = $receiveNewsletter;

        return $this;
    }

    /**
     * Get receive_newsletter.
     */
    public function getReceiveNewsletter(): bool
    {
        return $this->receiveNewsletter;
    }

    /**
     * Set receive_avertissement.
     */
    public function setReceiveAvertissement(bool $receiveAvertissement): self
    {
        $this->receiveAvertissement = $receiveAvertissement;

        return $this;
    }

    /**
     * Get receive_avertissement.
     */
    public function getReceiveAvertissement(): bool
    {
        return $this->receiveAvertissement;
    }

    /**
     * Set locked.
     */
    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get locked.
     */
    public function getLocked(): bool
    {
        return $this->locked;
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
    public function getOldId(): int
    {
        return $this->old_id;
    }

    /**
     * Set old_login.
     */
    public function setOldLogin(string $oldLogin): self
    {
        $this->old_login = $oldLogin;

        return $this;
    }

    /**
     * Get old_login.
     */
    public function getOldLogin(): string
    {
        return $this->old_login;
    }

    /**
     * Set ninja.
     */
    public function setNinja(?Ninja $ninja = null): self
    {
        $this->ninja = $ninja;

        return $this;
    }

    /**
     * Get ninja.
     */
    public function getNinja(): ?Ninja
    {
        return $this->ninja;
    }

    /**
     * Set clan.
     */
    public function setClan(?ClanUtilisateur $clan = null): self
    {
        $this->clan = $clan;

        return $this;
    }

    /**
     * Get clan.
     */
    public function getClan(): ?ClanUtilisateur
    {
        return $this->clan;
    }

    /**
     * Add recruts.
     */
    public function addRecrut(ClanUtilisateur $recruts): self
    {
        $this->recruts[] = $recruts;

        return $this;
    }

    /**
     * Remove recruts.
     */
    public function removeRecrut(ClanUtilisateur $recruts): void
    {
        $this->recruts->removeElement($recruts);
    }

    /**
     * Get recruts.
     *
     * @return ?ArrayCollection<int, ClanUtilisateur>
     */
    public function getRecruts(): ?ArrayCollection
    {
        return $this->recruts;
    }

    /**
     * Set recruts collection.
     *
     * @param ArrayCollection<int, ClanUtilisateur> $recruts
     */
    public function setRecruts(ArrayCollection $recruts): self
    {
        $this->recruts = $recruts;

        return $this;
    }

    /**
     * Add ips.
     */
    public function addIp(Ip $ips): self
    {
        $this->ips[] = $ips;
        $ips->setUser($this);

        return $this;
    }

    /**
     * Remove ips.
     */
    public function removeIp(Ip $ips): void
    {
        $this->ips->removeElement($ips);
    }

    /**
     * Get ips.
     */
    public function getIps(): ?ArrayCollection
    {
        return $this->ips;
    }

    /**
     * Add messages.
     */
    public function addMessage(Message $message): self
    {
        $this->messages[] = $message;
        $message->setAuthor($this);

        return $this;
    }

    /**
     * Remove messages.
     */
    public function removeMessage(Message $messages): void
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages.
     */
    public function getMessages(): ?ArrayCollection
    {
        return $this->messages;
    }

    public function setCreatedAt(?\DateTime $createdAt = null): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt = null): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
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
     * @return array<string, string>
     */
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

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getLobbies(): ?ArrayCollection
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

    public function getDateApplication(): ?\DateTimeInterface
    {
        return $this->dateApplication;
    }

    public function setDateApplication(?\DateTimeInterface $dateApplication): self
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

    public function getDateMessage(): ?\DateTimeInterface
    {
        return $this->dateMessage;
    }

    public function setDateMessage(?\DateTimeInterface $dateMessage): self
    {
        $this->dateMessage = $dateMessage;

        return $this;
    }
}
