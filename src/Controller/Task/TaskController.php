<?php

namespace App\Controller\Task;

use App\Entity\Comment;
use App\Entity\Task;
use App\Entity\UserTask;
use App\Form\Project\ProjectType;
use App\Form\Task\AssignUserType;
use App\Form\Task\CommentType;
use App\Form\Task\TaskStatusType;
use App\Form\Task\TaskType;
use App\Repository\CommentRepository;
use App\Repository\PermissionsRepository;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Repository\UserTaskRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/projects/show/{projectId}/task')]
class TaskController extends AbstractController
{
    #[Route('/create', name: 'app_task_create')]
    public function new(int $projectId, ProjectRepository $projectRepository, TaskRepository $taskRepository, Request $request, PermissionsRepository $permissionsRepository, Security $security): Response
    {
        if (!$project = $projectRepository->findOneBy(['id' => $projectId])) {
            throw $this->createNotFoundException('W systemie nie istnieje projektu o takim id.');
        }
        if (!$permissions = $permissionsRepository->findOneBy(['project' => $project, 'user' => $security->getUser()])) {
            throw $this->createNotFoundException('Nie masz uprawnień do tego działania.');
        }
        if (!$permissions->isCreateTask()) {
            throw $this->createNotFoundException('Nie masz uprawnień do tego działania.');
        }

        $task = new Task();
        $task->setCreatedAt(new \DateTimeImmutable());
        $task->setProject($project);
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $taskRepository->save($task, true);

            return $this->redirectToRoute('app_project_show', ['projectId' => $projectId]);
        }

        return $this->render('defaults/new.html.twig', [
            'title' => 'Dodawanie zadania',
            'form' => $form->createView(),
            'back_link' => 'app_project_show',
            'list_link' => 'app_project_show'
        ]);
    }

    #[Route('/show/{taskId}', name: 'app_task_show')]
    public function show(int $projectId, int $taskId, ProjectRepository $projectRepository, TaskRepository $taskRepository, CommentRepository $commentRepository, Security $security, PaginatorInterface $paginator, Request $request, PermissionsRepository $permissionsRepository, UserTaskRepository $userTaskRepository): Response
    {
        if (!$project = $projectRepository->findOneBy(['id' => $projectId])) {
            throw $this->createNotFoundException('W systemie nie istnieje projektu o takim id.');
        }
        if (!$task = $taskRepository->findOneBy(['id' => $taskId])) {
            throw $this->createNotFoundException('W systemie nie istnieje zadanie o takim id.');
        }
        $comments = $commentRepository->findBy(['task' => $taskId]) ?? [];
        $assignedUsers = $userTaskRepository->findBy(['task' => $taskId]) ?? [];

        $pagination = $paginator->paginate(
            $comments,
            $request->query->getInt('page', 1),
            5
        );

        $userAssign = new UserTask();
        $userAssign->setTask($task);
        $userAssignForm = $this->createForm(AssignUserType::class, $userAssign);
        $userAssignForm->handleRequest($request);
        if ($userAssignForm->isSubmitted() && $userAssignForm->isValid()) {
            $userTaskRepository->save($userAssign, true);

            return $this->redirectToRoute('app_task_show', ['taskId' => $taskId, 'projectId' => $projectId]);
        }

        $comment = new Comment();
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setUserCreated($security->getUser());
        $comment->setTask($task);
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $commentRepository->save($comment, true);

            return $this->redirectToRoute('app_task_show', ['taskId' => $taskId, 'projectId' => $projectId]);
        }

        return $this->render('task/show.html.twig', [
            'title' => '#' . $task->getId() . ' ' . $task->getName(),
            'task' => $task,
            'commentForm' => $commentForm->createView(),
            'comments' => $pagination,
            'userAssignForm' => $userAssignForm->createView(),
            'assignedUsers' => $assignedUsers,
            'back_link' => 'app_project_show',
            'list_link' => 'app_project_show',
            'create_link' => $permissionsRepository->findOneBy(['project' => $project, 'user' => $security->getUser(), 'create_task' => 1]) ? 'app_task_create' : null,
            'delete_link' => $permissionsRepository->findOneBy(['project' => $project, 'user' => $security->getUser(), 'delete_task' => 1]) ? 'app_task_delete' : null,
        ]);
    }

    #[Route('/update/{taskId}', name: 'app_task_update')]
    public function update(int $projectId, int $taskId, ProjectRepository $projectRepository, TaskRepository $taskRepository, Request $request): Response
    {
        if (!$projectRepository->findOneBy(['id' => $projectId])) {
            throw $this->createNotFoundException('W systemie nie istnieje projektu o takim id.');
        }

        if (!$task = $taskRepository->findOneBy(['id' => $taskId])) {
            throw $this->createNotFoundException('W systemie nie istnieje zadanie o takim id.');
        }

        $form = $this->createForm(TaskStatusType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task->setWorkedTime($task->getWorkedTime() + $form->get('workedTime')->getData());
            $taskRepository->save($task, true);

            return $this->redirectToRoute('app_task_show', ['taskId' => $taskId, 'projectId' => $projectId]);
        }

        return $this->render('defaults/edit.html.twig', [
            'title' => 'Aktualizacja zadania',
            'form' => $form->createView(),
            'back_link' => 'app_task_show',
            'list_link' => 'app_task_show'
        ]);
    }

    #[Route('/{taskId}/comment/{commentId}/delete', name: 'app_task_comment_delete')]
    public function deleteComment(int $projectId, int $taskId, int $commentId, CommentRepository $commentRepository, ProjectRepository $projectRepository, TaskRepository $taskRepository): Response
    {
        if (!$projectRepository->findOneBy(['id' => $projectId])) {
            throw $this->createNotFoundException('W systemie nie istnieje projektu o takim id.');
        }
        if (!$taskRepository->findOneBy(['id' => $taskId])) {
            throw $this->createNotFoundException('W systemie nie istnieje zadanie o takim id.');
        }
        if (!$comment = $commentRepository->findOneBy(['id' => $commentId])) {
            throw $this->createNotFoundException('W systemie nie istnieje komentarz o takim id.');
        }

        $commentRepository->remove($comment, true);

        return $this->redirectToRoute('app_task_show', ['taskId' => $taskId, 'projectId' => $projectId]);
    }

    #[Route('/{taskId}/delete', name: 'app_task_delete')]
    public function delete(int $projectId, int $taskId, ProjectRepository $projectRepository, TaskRepository $taskRepository, PermissionsRepository $permissionsRepository, Security $security): Response
    {
        if (!$project = $projectRepository->findOneBy(['id' => $projectId])) {
            throw $this->createNotFoundException('W systemie nie istnieje projektu o takim id.');
        }
        if (!$task = $taskRepository->findOneBy(['id' => $taskId])) {
            throw $this->createNotFoundException('W systemie nie istnieje zadanie o takim id.');
        }
        if (!$permissions = $permissionsRepository->findOneBy(['project' => $project, 'user' => $security->getUser()])) {
            throw $this->createNotFoundException('Nie masz uprawnień do tego działania.');
        }
        if (!$permissions->isCreateTask()) {
            throw $this->createNotFoundException('Nie masz uprawnień do tego działania.');
        }

        $taskRepository->remove($task, true);

        return $this->redirectToRoute('app_project_show', ['projectId' => $projectId]);
    }
}
