<?php

namespace App\Entity\User;

use App\Entity\Clan\ClanUtilisateur;
use App\Entity\Game\Lobby;
use App\Entity\Game\Ninja;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface UserInterface extends \Symfony\Component\Security\Core\User\UserInterface
{
    public function getUserIdentifier(): string;

    public function getLevel(): ?int;

    public function setLevel(?int $level): void;

    public function getNumNewMessage(): int;

    public function setNumNewMessage(int $numNewMessage): void;

    public function getNumDemandesFriends(): int;

    public function setNumDemandesFriends(int $numDemandesFriends): void;

    public function getNumPropositionsRecrutement(): int;

    public function setNumPropositionsRecrutement(int $numPropositionsRecrutement): void;

    public function addRole(string $role): User;

    public function serialize(): string;

    public function unserialize(string $serialized): void;

    public function eraseCredentials();

    public function getId(): ?int;

    public function getUsername(): string;

    public function setUsername(string $username): User;

    public function getUsernameCanonical(): string;

    public function getSalt(): string;

    public function getEmail(): string;

    public function getEmailCanonical(): string;

    public function getPassword(): string;

    public function setPassword(string $password): User;

    public function getPlainPassword(): ?string;

    /**
     * Gets the last login time.
     */
    public function getLastLogin(): ?\DateTime;

    public function getConfirmationToken(): ?string;

    public function getRoles(): array;

    public function hasRole(string $role): bool;

    public function isAccountNonExpired(): bool;

    public function isAccountNonLocked(): bool;

    public function isCredentialsNonExpired(): bool;

    public function isEnabled(): bool;

    public function isSuperAdmin(): bool;

    public function removeRole(string $role): User;

    public function setUsernameCanonical(string $usernameCanonical): User;

    public function setSalt(string $salt): User;

    public function setEmail(string $email): User;

    public function setEmailCanonical(string $emailCanonical): User;

    public function setEnabled(bool $boolean): User;

    public function setSuperAdmin(bool $boolean): User;

    public function setPlainPassword(?string $password): User;

    public function setLastLogin(?\DateTime $time = null): User;

    public function setConfirmationToken(?string $confirmationToken): User;

    public function setPasswordRequestedAt(?\DateTime $date = null): User;

    /**
     * Gets the timestamp that the user requested a password reset.
     */
    public function getPasswordRequestedAt(): ?\DateTime;

    public function isPasswordRequestNonExpired(int $ttl): bool;

    /**
     * @param array<int, string> $roles
     */
    public function setRoles(array $roles): User;

    /**
     * @return ArrayCollection<int, Group>
     */
    public function getGroups(): ArrayCollection;

    /**
     * @return array<int, string>
     */
    public function getGroupNames(): array;

    public function hasGroup(string $name): bool;

    public function addGroup(Group $group): User;

    public function removeGroup(Group $group): User;

    public function isEqualTo(User $user): bool;

    public function getAbsoluteAvatar(): ?string;

    public function getWebAvatar(): ?string;

    #[ORM\PrePersist]
    public function prePersist(): void;

    #[ORM\PreUpdate]
    public function preUpdate(): void;

    #[ORM\PostPersist]
    #[ORM\PostUpdate]
    public function upload(): void;

    /**
     * Sets file.
     */
    public function setFile(?UploadedFile $file = null): void;

    /**
     * Get file.
     */
    public function getFile(): UploadedFile;

    #[ORM\PreRemove]
    public function storeFilenameForRemove(): void;

    #[ORM\PostRemove]
    public function removeUpload(): void;

    /**
     * Set description.
     */
    public function setDescription(?string $description): User;

    /**
     * Get description.
     */
    public function getDescription(): ?string;

    /**
     * Set avatar.
     */
    public function setAvatar(string $avatar): User;

    /**
     * Get avatar.
     */
    public function getAvatar(): string;

    /**
     * Returns the old usernames.
     *
     * @return array<int, string> The usernames
     */
    public function getOldUsernames(): array;

    /**
     * Set oldUsername.
     *
     * @param array<int, string> $oldUsernames
     */
    public function setOldUsernames(array $oldUsernames): User;

    /**
     * add oldusername.
     */
    public function addOldUsername(string $username): User;

    /**
     * remove oldusername.
     */
    public function removeOldUsername(string $username): User;

    /**
     * Set oldUsernamesCanonical.
     */
    public function setOldUsernamesCanonical(string $oldUsernamesCanonical): User;

    /**
     * Get oldUsernamesCanonical.
     */
    public function getOldUsernamesCanonical(): string;

    /**
     * Set autoLogin.
     */
    public function setAutoLogin(?string $autoLogin): User;

    /**
     * Get autoLogin.
     */
    public function getAutoLogin(): ?string;

    /**
     * Set receive_newsletter.
     */
    public function setReceiveNewsletter(bool $receiveNewsletter): User;

    /**
     * Get receive_newsletter.
     */
    public function getReceiveNewsletter(): bool;

    /**
     * Set receive_avertissement.
     */
    public function setReceiveAvertissement(bool $receiveAvertissement): User;

    /**
     * Get receive_avertissement.
     */
    public function getReceiveAvertissement(): bool;

    /**
     * Set locked.
     */
    public function setLocked(bool $locked): User;

    /**
     * Get locked.
     */
    public function getLocked(): bool;

    /**
     * Set old_id.
     */
    public function setOldId(int $oldId): User;

    /**
     * Get old_id.
     */
    public function getOldId(): int;

    /**
     * Set old_login.
     */
    public function setOldLogin(string $oldLogin): User;

    /**
     * Get old_login.
     */
    public function getOldLogin(): string;

    /**
     * Set ninja.
     */
    public function setNinja(?Ninja $ninja = null): User;

    /**
     * Get ninja.
     */
    public function getNinja(): ?Ninja;

    /**
     * Set clan.
     */
    public function setClan(?ClanUtilisateur $clan = null): User;

    /**
     * Get clan.
     */
    public function getClan(): ?ClanUtilisateur;

    /**
     * Add recruts.
     */
    public function addRecrut(ClanUtilisateur $recruts): User;

    /**
     * Remove recruts.
     */
    public function removeRecrut(ClanUtilisateur $recruts): void;

    /**
     * @return ?ArrayCollection<int, ClanUtilisateur>
     */
    public function getRecruts(): ?ArrayCollection;

    /**
     * @param ArrayCollection<int, ClanUtilisateur> $recruts
     */
    public function setRecruts(ArrayCollection $recruts): User;

    /**
     * Add ips.
     */
    public function addIp(Ip $ips): User;

    /**
     * Remove ips.
     */
    public function removeIp(Ip $ips): void;

    /**
     * Get ips.
     *
     * @return ?ArrayCollection<int, Ip>
     */
    public function getIps(): ?ArrayCollection;

    /**
     * Add messages.
     */
    public function addMessage(Message $message): User;

    /**
     * Remove messages.
     */
    public function removeMessage(Message $messages): void;

    /**
     * Get messages.
     *
     * @return ?ArrayCollection<int, Message>
     */
    public function getMessages(): ?ArrayCollection;

    public function setCreatedAt(?\DateTime $createdAt = null): User;

    public function getCreatedAt(): ?\DateTime;

    public function setUpdatedAt(?\DateTime $updatedAt = null): User;

    public function getUpdatedAt(): ?\DateTime;

    public function setBiography(?string $biography): User;

    public function getBiography(): ?string;

    public function setGender(string $gender): User;

    public function getGender(): ?string;

    public function setLocale(string $locale): User;

    public function getLocale(): ?string;

    public function setTimezone(string $timezone): User;

    public function getTimezone(): ?string;

    public function getEnabled(): ?bool;

    public function getDateOfBirth(): ?\DateTimeInterface;

    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): User;

    /**
     * @return ?ArrayCollection<int, Lobby>
     */
    public function getLobbies(): ?ArrayCollection;

    public function addLobby(Lobby $lobby): User;

    public function removeLobby(Lobby $lobby): User;

    public function getDateApplication(): ?\DateTimeInterface;

    public function setDateApplication(?\DateTimeInterface $dateApplication): User;

    public function getNumberApplication(): ?int;

    public function setNumberApplication(?int $numberApplication): User;

    public function getDateMessage(): ?\DateTimeInterface;

    public function setDateMessage(?\DateTimeInterface $dateMessage): User;
}
