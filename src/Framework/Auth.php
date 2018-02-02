<?php
namespace Framework;

use Framework\Auth\User;

/**
 * Interface Auth
 * @package Framework
 */
interface Auth
{
    /**
     * @return User|null
     */
    public function getUser(): ?User;
}
