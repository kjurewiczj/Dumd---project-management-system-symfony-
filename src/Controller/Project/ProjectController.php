<?php

namespace App\Controller\Project;

use App\Entity\Permissions;
use App\Entity\Project;
use App\Entity\UserProject;
use App\Form\Project\AssignUserType;
use App\Form\Project\ProjectType;
use App\Project\PermissionsHandler;
use App\Project\SettingsHandler;
use App\Project\StatisticsHandler;
use App\Repository\PermissionsRepository;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Repository\User\UserProjectRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/projects')]
class ProjectController extends AbstractController
{
    #[Route('/', name: 'app_project_index')]
    public function index(Request $request, ProjectRepository $projectRepository, PaginatorInterface $paginator, Security $security): Response
    {
        $projects = $projectRepository->getList($security->getUser());

        $pagination = $paginator->paginate(
            $projects,
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('project/index.html.twig', [
            'pagination' => $pagination,
            'back_link' => 'app_project_index',
            'create_link' => 'app_project_create'
        ]);
    }

    #[Route('/create', name: 'app_project_create')]
    public function new(Request $request, ProjectRepository $projectRepository, Security $security, UserProjectRepository $userProjectRepository, PermissionsHandler $permissionsHandler, PermissionsRepository $permissionsRepository): Response
    {
        $project = new Project();
        $userProject = new UserProject();
        $permissions = new Permissions();
        $permissionsHandler->setValuesOnProjectCreate($permissions, $project, $security->getUser());
        $userProject->setProject($project);
        $userProject->setUser($security->getUser());
        $project->setCreatedAt(new \DateTimeImmutable());
        $project->setUserCreated($security->getUser());
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $projectRepository->save($project, true);
            $userProjectRepository->save($userProject, true);
            $permissionsRepository->save($permissions, true);

            return $this->redirectToRoute('app_project_index');
        }

        return $this->render('defaults/new.html.twig', [
            'title' => 'Dodawanie projektu',
            'form' => $form->createView(),
            'back_link' => 'app_project_index',
            'list_link' => 'app_project_index',
        ]);
    }

    #[Route('/show/{projectId}', name: 'app_project_show', requirements: ['projectId' => '\d+'])]
    public function show(int $projectId, Request $request, ProjectRepository $projectRepository, TaskRepository $taskRepository, PaginatorInterface $paginator, UserProjectRepository $userProjectRepository, Security $security, PermissionsRepository $permissionsRepository): Response
    {
        if (!$project = $projectRepository->findOneBy(['id' => $projectId])) {
            throw $this->createNotFoundException('W systemie nie istnieje projekt o takim id.');
        }
        if (!$userProjectRepository->findOneBy(['project' => $project, 'user' => $security->getUser()])) {
            throw $this->createNotFoundException('Nie masz dostępu do tego projektu.');
        }

        $tasks = $taskRepository->findBy(['project' => $projectId]) ?? [];

        $pagination = $paginator->paginate(
            $tasks,
            $request->query->getInt('page', 1),
            30
        );

        return $this->render('project/show.html.twig', [
            'title' => 'Projekt ' . $project->getName(),
            'project' => $project,
            'pagination' => $pagination,
            'back_link' => 'app_project_index',
            'list_link' => 'app_project_index',
            'create_link' => $permissionsRepository->findOneBy(['project' => $project, 'user' => $security->getUser(), 'create_task' => 1]) ? 'app_task_create' : null,
            'edit_link' => $project->getUserCreated() === $security->getUser() ? 'app_project_edit' : null,
            'delete_link' => $project->getUserCreated() === $security->getUser() ? 'app_project_delete' : null,
            'statistics_link' => $permissionsRepository->findOneBy(['project' => $project, 'user' => $security->getUser(), 'statistics' => 1]) ? 'app_project_statistics' : null,
            'settings_link' => $project->getUserCreated() === $security->getUser() ? 'app_project_settings' : null,
        ]);
    }

    #[Route('/edit/{projectId}', name: 'app_project_edit', requirements: ['projectId' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $projectId, Request $request, ProjectRepository $projectRepository, Security $security): Response
    {
        if (!$project = $projectRepository->findOneBy(['id' => $projectId])) {
            throw $this->createNotFoundException('W systemie nie istnieje projekt o takim id.');
        }

        if (!$project->getUserCreated() != $security->getUser()) {
            throw $this->createNotFoundException('Nie jesteś właścicielem tego projektu.');
        }

        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $projectRepository->save($project, true);

            return $this->redirectToRoute('app_project_show', ['projectId' => $projectId]);
        }

        return $this->render('defaults/edit.html.twig', [
            'title' => 'Edycja projektu',
            'form' => $form->createView(),
            'back_link' => 'app_project_index',
            'list_link' => 'app_project_index'
        ]);
    }

    #[Route('/delete/{projectId}', name: 'app_project_delete', requirements: ['projectId' => '\d+'], methods: ['GET', 'POST'] )]
    public function delete(int $projectId, Request $request, ProjectRepository $projectRepository, Security $security): Response
    {
        if (!$project = $projectRepository->findOneBy(['id' => $projectId])) {
            throw $this->createNotFoundException('W systemie nie istnieje projekt o takim id.');
        }

        if (!$project->getUserCreated() != $security->getUser()) {
            throw $this->createNotFoundException('Nie jesteś właścicielem tego projektu.');
        }

        $projectRepository->remove($project, true);

        return $this->redirectToRoute('app_project_index');
    }

    #[Route('/statistics/{projectId}', name: 'app_project_statistics')]
    public function generateStatistics(int $projectId, StatisticsHandler $statisticsHandler, ProjectRepository $projectRepository, TaskRepository $taskRepository, PermissionsRepository $permissionsRepository, Security $security): Response
    {
        if (!$project = $projectRepository->findOneBy(['id' => $projectId])) {
            throw $this->createNotFoundException('W systemie nie istnieje projekt o takim id.');
        }
        if (!$permissions = $permissionsRepository->findOneBy(['project' => $project, 'user' => $security->getUser()])) {
            throw $this->createNotFoundException('Nie masz uprawnień do tego działania.');
        }
        if (!$permissions->isStatistics()) {
            throw $this->createNotFoundException('Nie masz uprawnień do tego działania.');
        }

        $tasks = $taskRepository->findBy(['project' => $project]);
        $gantt = $statisticsHandler->generateGantt($project);
        $statistics = $statisticsHandler->generateStatistics($project);

        return $this->render('project/statistics.html.twig', [
            'title' => 'Wykres gantta',
            'gantt' => $gantt,
            'tasks' => $tasks,
            'statistics' => $statistics,
            'list_link' => 'app_project_show',
            'settings_link' => 'app_project_settings'
        ]);
    }

    #[Route('/settings/{projectId}/{tab}', name: 'app_project_settings')]
    public function projectSettings(int $projectId, Request $request, Security $security, SettingsHandler $settingsHandler, ProjectRepository $projectRepository, UserProjectRepository $userProjectRepository, int $tab = Project::ASSIGNED_USERS): Response
    {
        if (!$project = $projectRepository->findOneBy(['id' => $projectId])) {
            throw $this->createNotFoundException('W systemie nie istnieje projekt o takim id.');
        }

        if ($tab == Project::ASSIGNED_USERS) {
            $assignedUsers = $settingsHandler->getAssignedUsers($project, $security->getUser());
        } elseif ($tab == Project::PERMISSIONS) {
            $assignedUsers = $settingsHandler->getAssignedUsers($project, $security->getUser());
        } else {
            throw $this->createNotFoundException('W systemie nie istnieją takie ustawienia.');
        }

        $assignUser = new UserProject();
        $assignUser->setProject($project);
        $form = $this->createForm(AssignUserType::class, $assignUser);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userProjectRepository->save($assignUser, true);

            return $this->redirectToRoute('app_project_settings', ['projectId' => $projectId]);
        }

        return $this->render('project/settings.html.twig', [
            'title' => 'Ustawienia',
            'assignedUsers' => $assignedUsers,
            'project' => $project,
            'list_link' => 'app_project_show',
            'statistics_link' => 'app_project_statistics',
            'tab' => $tab,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/removeUser/{projectId}/{userId}', name: 'app_project_removeUser', requirements: ['projectId' => '\d+', 'userId' => '\d+'], methods: ['GET', 'POST'] )]
    public function removeUser(int $projectId, Request $request, UserRepository $userRepository, UserProjectRepository $userProjectRepository, ProjectRepository $projectRepository, Security $security, int $userId = 0): Response
    {
        if ($userId == 0) {
            throw $this->createNotFoundException('W systemie nie istnieje użytkownik o takim id.');
        }
        if (!$project = $projectRepository->findOneBy(['id' => $projectId])) {
            throw $this->createNotFoundException('W systemie nie istnieje projekt o takim id.');
        }
        if (!$user = $userRepository->findOneBy(['id' => $userId])) {
            throw $this->createNotFoundException('W systemie nie istnieje użytkownik o takim id.');
        }
        if ($project->getUserCreated() != $security->getUser()) {
            throw $this->createNotFoundException('Nie jesteś właścicielem tego projektu.');
        }
        if (!$userProject = $userProjectRepository->findOneBy(['project' => $project, 'user' => $user])) {
            throw $this->createNotFoundException('Nie ma takiej relacji użytkownik - projekt.');
        }

        $userProjectRepository->remove($userProject, true);

        return $this->redirectToRoute('app_project_settings', ['projectId' => $projectId]);
    }
}
