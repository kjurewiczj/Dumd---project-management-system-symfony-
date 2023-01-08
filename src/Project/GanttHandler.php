<?php

namespace App\Project;

use App\Entity\Project;
use App\Repository\TaskRepository;
use DateTimeImmutable;

class GanttHandler
{
    public function __construct(private TaskRepository $taskRepository){}

    public function generateGantt(Project $project): array
    {
        $tasks = $this->taskRepository->findBy(['project' => $project->getId()]);
        $firstDate = $this->taskRepository->getMinStartDate($project);
        $lastDate = $this->taskRepository->getLastEndDate($project);
        $convertedFirstDate = new \DateTimeImmutable($firstDate);
        $convertedLastDate = new \DateTimeImmutable($lastDate);
        $dates = [];
        $currentDate = $convertedFirstDate;
        while ($currentDate <= $convertedLastDate) {
            $dates[] = $currentDate;
            $currentDate = $currentDate->modify('+1 day');
        }

        return $dates;
    }
}