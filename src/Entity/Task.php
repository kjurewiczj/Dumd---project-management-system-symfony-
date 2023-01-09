<?php

namespace App\Entity;

use App\Interfaces\Entity\CreatedAtInterface;
use App\Project\Enum\PriorityEnum;
use App\Project\Enum\StatusEnum;
use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task implements CreatedAtInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\Column(nullable: true, options: ['default' => 0])]
    private ?float $estimatedTime = null;

    #[ORM\Column(nullable: true, options: ['default' => 0])]
    private ?float $workedTime = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(nullable: true, options: ['default' => 0])]
    private ?int $progress = null;

    #[ORM\ManyToOne]
    private ?User $userAssigned = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $priority = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    public static function getPriorities(): array
    {
        return [
            PriorityEnum::LOW_PRIORITY->name => 'Niski',
            PriorityEnum::NORMAL_PRIORITY->name => 'Normalny',
            PriorityEnum::HIGH_PRIORITY->name => 'Wysoki',
            PriorityEnum::HIGHEST_PRIORITY->name => 'Pilny'
        ];
    }

    public static function getStatuses(): array
    {
        return [
            StatusEnum::TODO_STATUS->name => 'Do zrobienia',
            StatusEnum::IN_PROGRESS_STATUS->name => 'W trakcie pracy',
            StatusEnum::TESTING_STATUS->name => 'W trakcie testów',
            StatusEnum::DONE_STATUS->name => 'Zakończono'
        ];
    }

    public function getPriorityName(?string $default = ''): string
    {
        return !empty(self::getPriorities()[$this->getPriority()]) ? self::getPriorities()[$this->getPriority()] : $default;
    }

    public function getStatusName(?string $default = ''): string
    {
        return !empty(self::getStatuses()[$this->getStatus()]) ? self::getStatuses()[$this->getStatus()] : $default;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getEstimatedTime(): ?float
    {
        return $this->estimatedTime;
    }

    public function setEstimatedTime(?float $estimatedTime): self
    {
        $this->estimatedTime = $estimatedTime;

        return $this;
    }

    public function getWorkedTime(): ?float
    {
        return $this->workedTime;
    }

    public function setWorkedTime(?float $workedTime): self
    {
        $this->workedTime = $workedTime;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getProgress(): ?int
    {
        return $this->progress;
    }

    public function setProgress(?int $progress): self
    {
        $this->progress = $progress;

        return $this;
    }

    public function getUserAssigned(): ?User
    {
        return $this->userAssigned;
    }

    public function setUserAssigned(?User $userAssigned): self
    {
        $this->userAssigned = $userAssigned;

        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(?string $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
