<?php

namespace App\Controller\Project;

use App\Entity\Project;
use App\Form\Project\ProjectType;
use App\Project\GanttHandler;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
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
    public function index(Request $request, ProjectRepository $projectRepository, PaginatorInterface $paginator): Response
    {
        $projects = $projectRepository->getList();

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
    public function new(Request $request, ProjectRepository $projectRepository, Security $security): Response
    {
        $project = new Project();
        $project->setCreatedAt(new \DateTimeImmutable());
        $project->setUserCreated($security->getUser());
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $projectRepository->save($project, true);

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
    public function show(int $projectId, Request $request, ProjectRepository $projectRepository, TaskRepository $taskRepository, PaginatorInterface $paginator): Response
    {
        if (!$project = $projectRepository->findOneBy(['id' => $projectId])) {
            throw $this->createNotFoundException('W systemie nie istnieje projekt o takim id.');
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
            'create_link' => 'app_task_create',
            'edit_link' => 'app_project_edit',
            'delete_link' => 'app_project_delete',
            'gantt_link' => 'app_project_gantt',
        ]);
    }

    #[Route('/edit/{projectId}', name: 'app_project_edit', requirements: ['projectId' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $projectId, Request $request, ProjectRepository $projectRepository): Response
    {
        if (!$project = $projectRepository->findOneBy(['id' => $projectId])) {
            throw $this->createNotFoundException('W systemie nie istnieje projekt o takim id.');
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
    public function delete(int $projectId, Request $request, ProjectRepository $projectRepository): Response
    {
        if (!$project = $projectRepository->findOneBy(['id' => $projectId])) {
            throw $this->createNotFoundException('W systemie nie istnieje projekt o takim id.');
        }

        $projectRepository->remove($project, true);

        return $this->redirectToRoute('app_project_index');
    }

    #[Route('/gantt/{projectId}', name: 'app_project_gantt')]
    public function generateGantt(int $projectId, GanttHandler $ganttHandler, ProjectRepository $projectRepository, TaskRepository $taskRepository)
    {
        if (!$project = $projectRepository->findOneBy(['id' => $projectId])) {
            throw $this->createNotFoundException('W systemie nie istnieje projekt o takim id.');
        }
        $tasks = $taskRepository->findBy(['project' => $project]);
        $gantt = $ganttHandler->generateGantt($project);

        return $this->render('project/gantt.html.twig', [
            'title' => 'Wykres gantta',
            'gantt' => $gantt,
            'tasks' => $tasks,
            'list_link' => 'app_project_show'
        ]);
    }
}
