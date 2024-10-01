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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="projects")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id", nullable=true, onDelete="SET NULL")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Task", mappedBy="project", cascade={"persist", "remove"}, orphanRemoval=true)
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
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setProject($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            // Set the owning side to null (unless already changed)
            if ($task->getProject() === $this) {
                $task->setProject(null);
            }
        }

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
