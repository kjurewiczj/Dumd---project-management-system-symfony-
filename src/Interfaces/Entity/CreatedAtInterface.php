<?php

namespace App\Interfaces\Entity;

use DateTimeImmutable;

interface CreatedAtInterface
{
    public function getCreatedAt(): ?DateTimeImmutable;

    public function setCreatedAt(DateTimeImmutable $createdAt): self;
}