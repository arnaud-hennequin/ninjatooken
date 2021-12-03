<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
* Group
*
* @ORM\Table(name="nt_group")
* @ORM\Entity
*/
class Group
{
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    private ?int $id = null;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected string $name;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    protected array $roles;

    /**
     * Group constructor.
     *
     * @param string $name
     * @param array $roles
     */
    public function __construct(string $name, array $roles = [])
    {
        $this->name = $name;
        $this->roles = $roles;
    }

    public function addRole($role): self
    {
        if (!$this->hasRole($role)) {
            $this->roles[] = strtoupper($role);
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function hasRole($role): bool
    {
        return in_array(strtoupper($role), $this->roles, true);
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function removeRole($role): self
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}