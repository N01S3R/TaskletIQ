<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TaskRepository")
 * @ORM\Table(name="tasks")
 */
class Task
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="task_id")
     */
    private $taskId;

    /**
     * @ORM\Column(type="string", length=255, name="task_name", nullable=true)
     */
    private $taskName;

    /**
     * @ORM\Column(type="text", name="task_description", nullable=true)
     */
    private $taskDescription;

    /**
     * @ORM\Column(type="text", name="task_description_long")
     */
    private $taskDescriptionLong;

    /**
     * @ORM\Column(type="datetime", name="task_created_at", nullable=true)
     */
    private $taskCreatedAt;

    /**
     * @ORM\Column(type="integer", name="task_progress")
     */
    private $taskProgress;

    /**
     * @ORM\Column(type="string", length=50, name="task_status")
     */
    private $taskStatus;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="tasks")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="project_id", nullable=true, onDelete="SET NULL")
     */
    private $project;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TaskUser", mappedBy="task", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $taskUsers;

    /**
     * @ORM\Column(type="integer", name="user_id", nullable=true)
     */
    private $userId;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="tasks")
     * @ORM\JoinTable(name="tasks_users",
     *      joinColumns={@ORM\JoinColumn(name="task_id", referencedColumnName="task_id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="user_id")}
     * )
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->taskUsers = new ArrayCollection();
    }

    // Getters and Setters

    public function getTaskId(): ?int
    {
        return $this->taskId;
    }

    public function getTaskName(): ?string
    {
        return $this->taskName;
    }

    public function setTaskName(?string $taskName): self
    {
        $this->taskName = $taskName;
        return $this;
    }

    public function getTaskDescription(): ?string
    {
        return $this->taskDescription;
    }

    public function setTaskDescription(?string $taskDescription): self
    {
        $this->taskDescription = $taskDescription;
        return $this;
    }

    public function getTaskDescriptionLong(): ?string
    {
        return $this->taskDescriptionLong;
    }

    public function setTaskDescriptionLong(string $taskDescriptionLong): self
    {
        $this->taskDescriptionLong = $taskDescriptionLong;
        return $this;
    }

    public function getTaskCreatedAt(): ?\DateTimeInterface
    {
        return $this->taskCreatedAt;
    }

    public function setTaskCreatedAt(?\DateTimeInterface $taskCreatedAt): self
    {
        $this->taskCreatedAt = $taskCreatedAt;
        return $this;
    }

    public function getTaskProgress(): ?int
    {
        return $this->taskProgress;
    }

    public function setTaskProgress(int $taskProgress): self
    {
        $this->taskProgress = $taskProgress;
        return $this;
    }

    public function getTaskStatus(): ?string
    {
        return $this->taskStatus;
    }

    public function setTaskStatus(string $taskStatus): self
    {
        $this->taskStatus = $taskStatus;
        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addTask($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeTask($this);
        }

        return $this;
    }

    public function setUser(User $user): self
    {
        $this->addUser($user);
        return $this;
    }
}
