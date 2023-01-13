<?php

namespace App\Project;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\User\UserProjectRepository;

class SettingsHandler
{
    public function __construct(private UserProjectRepository $userProjectRepository){}

    public function getAssignedUsers(Project $project, User $user): array
    {
        if (!$project->getUserCreated() == $user) {
            //throw $this->createNotFoundException('Nie jesteś właścicielem tego projektu.');
        }

        $assignedUsers = $this->userProjectRepository->findBy(['project' => $project]);

        return $assignedUsers;
    }
}