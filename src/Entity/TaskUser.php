<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TaskUserRepository")
 * @ORM\Table(name="tasks_users")
 */
class TaskUser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Task", inversedBy="taskUsers")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="task_id")
     */
    private $task;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="taskUsers")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $user;

    // Getters and Setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(Task $task): self
    {
        $this->task = $task;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
