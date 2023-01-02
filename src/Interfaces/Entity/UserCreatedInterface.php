<?php

namespace App\Interfaces\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserCreatedInterface
{
    public function getUserCreated(): ?UserInterface;

    public function setUserCreated(UserInterface $userCreated): self;
}