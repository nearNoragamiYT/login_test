<?php

namespace LoginBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class Profile implements UserInterface {

    private $username;
    private $password;
    private $salt;
    private $roles;
    private $data;
 
    public function __construct($username, $password, $salt, array $roles) {
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
    }

    public function getRoles() {
        return $this->roles;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getSalt() {
        return $this->salt;
    }

    public function getUsername() {
        return $this->username;
    }

    public function eraseCredentials() {
        
    }

    public function isEqualTo(UserInterface $user) {
        if (!$user instanceof WebserviceUser) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    protected function mergePasswordAndSalt($password, $salt) {
        if (empty($salt)) {
            return $password;
        }
        
        return $salt . $password; // or do whatever you need with the password and salt
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function setData($data) {
         $this->data = $data;
    }
    
}