<?php

namespace App\Project;

use App\Entity\Permissions;
use App\Entity\Project;
use App\Entity\User;

class PermissionsHandler
{
    public function setValuesOnProjectCreate(Permissions $permissions, Project $project, User $user)
    {
        $permissions->setProject($project);
        $permissions->setUser($user);
        $permissions->setCreateTask(1);
        $permissions->setDeleteTask(1);
        $permissions->setStatistics(1);
    }
}