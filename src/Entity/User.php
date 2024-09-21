<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="users")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="user_id")
     */
    private $userId;

    /**
     * @ORM\Column(type="string", length=255, name="user_login")
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255, name="user_password")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, name="user_name", nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, name="user_email")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=50, name="user_role")
     */
    private $role;

    /**
     * @ORM\Column(type="boolean", name="user_logged", options={"default": false})
     */
    private $loggedIn;

    /**
     * @ORM\Column(type="datetime", name="registration_date")
     */
    private $registrationDate;

    /**
     * @ORM\Column(type="string", length=255, name="user_avatar", nullable=true)
     */
    private $avatar;

    /**
     * @ORM\OneToMany(targetEntity="Project", mappedBy="user")
     */
    private $projects;

    /**
     * @ORM\OneToMany(targetEntity="Task", mappedBy="user")
     */
    private $tasks;

    /**
     * @ORM\Column(type="string", length=255, name="registration_token", nullable=true)
     */
    private $registrationToken;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->tasks = new ArrayCollection();
    }

    // Gettery i settery

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function isLoggedIn(): ?bool
    {
        return $this->loggedIn;
    }

    public function setLoggedIn(bool $status): self
    {
        $this->loggedIn = $status;
        return $this;
    }

    public function getRegistrationDate(): ?\DateTime
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(\DateTime $registrationDate): self
    {
        $this->registrationDate = $registrationDate;
        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;
        return $this;
    }

    /**
     * Zwraca kolekcję projektów przypisanych do użytkownika.
     *
     * @return Collection|Project[]
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    /**
     * Zwraca kolekcję zadań przypisanych do użytkownika.
     *
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    /**
     * Zwraca token rejestracyjny użytkownika.
     *
     * @return string|null
     */
    public function getRegistrationToken(): ?string
    {
        return $this->registrationToken;
    }

    /**
     * Ustawia token rejestracyjny użytkownika.
     *
     * @param string $token
     * @return self
     */
    public function setRegistrationToken(string $token): self
    {
        $this->registrationToken = $token;
        return $this;
    }
}
