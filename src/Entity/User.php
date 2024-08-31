<?php

namespace App\Entity;

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

    // Gettery i settery
    public function getUserId()
    {
        return $this->userId;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function isLoggedIn()
    {
        return $this->loggedIn;
    }

    public function setLoggedIn(bool $status)
    {
        $this->loggedIn = $status;
    }

    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(\DateTime $registrationDate): void
    {
        $this->registrationDate = $registrationDate;
    }
}
