<?php

namespace App\Entity;

use App\Repository\PermissionsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PermissionsRepository::class)]
class Permissions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Project $project = null;

    #[ORM\Column (options:['default' => 0])]
    private ?bool $create_task = null;

    #[ORM\Column (options:['default' => 0])]
    private ?bool $delete_task = null;

    #[ORM\Column (options:['default' => 0])]
    private ?bool $statistics = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function isCreateTask(): ?bool
    {
        return $this->create_task;
    }

    public function setCreateTask(bool $create_task): self
    {
        $this->create_task = $create_task;

        return $this;
    }

    public function isDeleteTask(): ?bool
    {
        return $this->delete_task;
    }

    public function setDeleteTask(bool $delete_task): self
    {
        $this->delete_task = $delete_task;

        return $this;
    }

    public function isStatistics(): ?bool
    {
        return $this->statistics;
    }

    public function setStatistics(bool $statistics): self
    {
        $this->statistics = $statistics;

        return $this;
    }
}
