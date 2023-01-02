<?php

namespace App\Controller\Project;

use App\Entity\Project;
use App\Form\Project\ProjectType;
use App\Repository\ProjectRepository;
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
            'list_link' => 'app_project_index'
        ]);
    }

    #[Route('/show/{id}', name: 'app_project_show')]
    public function show(int $id, Request $request, ProjectRepository $projectRepository)
    {
        $project = $projectRepository->findOneBy(['id' => $id]);

        return $this->render('project/show.html.twig', [
            'title' => 'Projekt ' . $project->getName(),
            'project' => $project,
            'back_link' => 'app_project_index',
            'list_link' => 'app_project_index',
            'create_link' => 'app_project_create'
        ]);
    }
}
