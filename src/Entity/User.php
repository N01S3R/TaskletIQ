<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @ORM\Column(type="string", length=100, name="user_login")
     */
    private $login;

    /**
     * @ORM\Column(type="string", length=255, name="user_password")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=100, name="user_name")
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=100, name="user_email")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="registration_token")
     */
    private $registrationToken;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="user_avatar")
     */
    private $avatar;

    /**
     * @ORM\Column(type="datetime", nullable=true, name="registration_date")
     */
    private $registrationDate;

    /**
     * @ORM\Column(type="boolean", options={"default": false}, name="user_logged")
     */
    private $logged;

    /**
     * @ORM\Column(type="string", length=50, name="user_role")
     */
    private $role;

    /**
     * @ORM\ManyToMany(targetEntity="Task", mappedBy="user")
     */
    private $tasks;

    /**
     * @ORM\OneToMany(targetEntity="TaskUser", mappedBy="user")
     */
    private $taskUsers;

    /**
     * @ORM\OneToMany(targetEntity="Project", mappedBy="user")
     */
    private $projects;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->taskUsers = new ArrayCollection();
        $this->projects = new ArrayCollection();
    }

    // Getters and Setters

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;
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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
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

    public function getRegistrationToken(): ?string
    {
        return $this->registrationToken;
    }

    public function setRegistrationToken(?string $registrationToken): self
    {
        $this->registrationToken = $registrationToken;
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

    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(?\DateTimeInterface $registrationDate): self
    {
        $this->registrationDate = $registrationDate;
        return $this;
    }

    public function isLogged(): ?bool
    {
        return $this->logged;
    }

    public function setLoggedIn(bool $logged): self
    {
        $this->logged = $logged;
        return $this;
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

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->addUser($this);
        }
        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            $task->removeUser($this);
        }
        return $this;
    }

    public function getTaskUsers(): Collection
    {
        return $this->taskUsers;
    }

    public function addTaskUser(TaskUser $taskUser): self
    {
        if (!$this->taskUsers->contains($taskUser)) {
            $this->taskUsers[] = $taskUser;
        }
        return $this;
    }

    public function removeTaskUser(TaskUser $taskUser): self
    {
        $this->taskUsers->removeElement($taskUser);
        return $this;
    }

    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setUser($this);
        }
        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->removeElement($project)) {
            if ($project->getUser() === $this) {
                $project->setUser(null);
            }
        }
        return $this;
    }
}
