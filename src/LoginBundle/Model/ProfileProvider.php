<?php

namespace LoginBundle\Model;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;
use LoginBundle\Model\LoginModel;

class ProfileProvider implements UserProviderInterface {

    private $LoginModel, $App;

    public function __construct(ContainerInterface $container = null) {
        $ConfigurationModel = new ConfigurationModel();
        $this->App = $ConfigurationModel->getApp();
        $this->LoginModel = new LoginModel();
    }

    public function loadUserByUsername($username = "none") {
        if ($username == "" || $username == "*") {
            $username = "_none_username";
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }
        $Args = Array('Email' => "'" . $username . "'");
        $result_usuario = $this->LoginModel->findUser($Args);
        if (!$result_usuario['status']) {
            throw new UsernameNotFoundException($result_usuario['data']);
        }

        if (count($result_usuario['data']) == 0) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        $usuario = $result_usuario['data'][0];

        $roles = array('ROLE_ADMIN');
        $user = new Profile($usuario['Email'], $usuario['Password'], $this->App['salt'], $roles);
        $user->setData($usuario);
        return $user;
    }

    public function refreshUser(UserInterface $user) {
        if (!$user instanceof Profile) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $user;
    }

    public function supportsClass($class) {
        return $class === 'LoginBundle\Model\Profile';
    }

}
