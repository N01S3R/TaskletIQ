<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TokenRepository")
 * @ORM\Table(name="tokens")
 */
class Token
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime")
     */
    private $expiration;

    // Gettery i settery...

    public function getId(): int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getExpiration(): \DateTime
    {
        return $this->expiration;
    }

    public function setExpiration(\DateTime $expiration): self
    {
        $this->expiration = $expiration;
        return $this;
    }
}
