<?php

namespace App\Project;

use App\Entity\Project;
use App\Entity\User;
use App\Project\Enum\PriorityEnum;
use App\Project\Enum\StatusEnum;
use App\Repository\TaskRepository;
use App\Repository\User\UserProjectRepository;
use App\Repository\UserTaskRepository;

class StatisticsHandler
{
    public function __construct(
        private TaskRepository $taskRepository,
        private UserProjectRepository $userProjectRepository,
        private UserTaskRepository $userTaskRepository,
    ){}

    public function generateGantt(Project $project): array
    {
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

    public function generateStatistics(Project $project): array
    {
        $statistics = [];
        $statistics['estimatedTime'] = $statistics['workedTime'] = $statistics['openedTasks'] = $statistics['highestPriority'] = 0;
        $statistics['tasksAfterEndDate'] = [];
        $tasks = $this->taskRepository->findBy(['project' => $project]);
        foreach ($tasks as $task) {
            $statistics['estimatedTime'] += $task->getEstimatedTime();
            $statistics['workedTime'] += $task->getWorkedTime();
            if ($task->getStatus() != StatusEnum::DONE_STATUS) {
                $statistics['openedTasks']++;
            }
            if ($task->getPriority() == PriorityEnum::HIGHEST_PRIORITY->name && $task->getStatus() != StatusEnum::DONE_STATUS->name) {
                $statistics['highestPriority']++;
            }
            if ($task->getEndDate() != null) {
                if ($task->getEndDate() < new \DateTimeImmutable() && $task->getStatus() != StatusEnum::DONE_STATUS->name) {
                    $statistics['tasksAfterEndDate'][$task->getId()] = $task;
                }
            }
        }

        return $statistics;
    }

    public function generateStatistiscsForUser(User $user): array
    {
        $statistics = [];
        $statistics['assignedTasks'] = $this->taskRepository->findBy(['userAssigned' => $user]);
        $statistics['assignedProjects'] = $this->userProjectRepository->findBy(['user' => $user]);
        $userTasks = $this->userTaskRepository->findBy(['user' => $user]);
        foreach ($userTasks as $userTask) {
            if ($userTask->getUser() === $user) {
                array_push($statistics['assignedTasks'], $userTask->getTask());
            }
        }

        return $statistics;
    }
}