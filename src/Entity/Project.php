<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 * @ORM\Table(name="projects")
 */
class Project
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="project_id")
     */
    private $projectId;

    /**
     * @ORM\Column(type="string", length=255, name="project_name", nullable=true)
     */
    private $projectName;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="projects")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="Task", mappedBy="project")
     */
    private $tasks;

    /**
     * @ORM\Column(type="datetime", name="created_at", nullable=true)
     */
    private $createdAt;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    // Getters and Setters

    public function getProjectId(): ?int
    {
        return $this->projectId;
    }

    public function getProjectName(): ?string
    {
        return $this->projectName;
    }

    public function setProjectName(?string $projectName): self
    {
        $this->projectName = $projectName;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        $this->tasks[] = $task;
        $task->setProject($this);
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
