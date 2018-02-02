<?php
namespace App\Auth;

use Framework\Auth\User as UserInterface;
use Framework\Session\SessionInterface;
use Framework\Database\NoRecordException;

class DatabaseAuth implements \Framework\Auth
{
    /**
     * @var UserTable
     */
    private $userTable;

    private $session;

    /**
     * @var UserInterface
     */
    private $user;

    public function __construct(UserTable $userTable, SessionInterface $session)
    {
        $this->userTable = $userTable;
        $this->session = $session;
    }

    /**
     * @param string $username
     * @param string $password
     * @return User|null
     */
    public function login(string $username, string $password): ?UserInterface
    {
        if (empty($username) || empty($password)) {
            return null;
        }

        /** @var \App\Auth\User $user  */
        $user = $this->userTable->findBy('username', $username);
        if ($user && password_verify($password, $user->getPassword())) {
            $this->session->set('auth.user', $user->getId());
            return $user;
        }
        return null;
    }

    public function logout(): void
    {
        $this->session->delete('auth.user');
    }

    /**
     * @return User|null
     */
    public function getUser(): ?UserInterface
    {
        if ($this->user) {
            return $this->user;
        }
        $userId = $this->session->get('auth.user');
        if ($userId) {
            try {
                $this->user = $this->userTable->find($userId);
                return $this->user;
            } catch (NoRecordException $exception) {
                $this->session->delete('auth.user');
                return null;
            }
        }
        return null;
    }

    public function setUser(User $user): void
    {
        $this->session->set('auth.user', $user->getId());
        $this->user = $user;
    }
}
