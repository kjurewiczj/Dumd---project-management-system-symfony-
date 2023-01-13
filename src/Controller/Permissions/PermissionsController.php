<?php

namespace App\Controller\Permissions;

use App\Entity\Permissions;
use App\Entity\Project;
use App\Form\Permissions\PermissionsType;
use App\Repository\PermissionsRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/projects')]
class PermissionsController extends AbstractController
{
    #[Route('/permissions/update/{projectId}/{userId}', name: 'app_permissions_update')]
    public function index(int $projectId, int $userId, PermissionsRepository $permissionsRepository, Request $request, UserRepository $userRepository, ProjectRepository $projectRepository): Response
    {
        if (!$user = $userRepository->find($userId)) {
            throw $this->createNotFoundException('W systemie nie istnieje użytkownik o takim id.');
        }
        if (!$project = $projectRepository->find($projectId)) {
            throw $this->createNotFoundException('W systemie nie istnieje projekt o takim id.');
        }
        if (!$permissions = $permissionsRepository->findOneBy(['project' => $project, 'user' => $user])) {
            $permissions = new Permissions();
        }
        $permissions->setUser($user);
        $permissions->setProject($project);
        $form = $this->createForm(PermissionsType::class, $permissions);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $permissionsRepository->save($permissions, true);

            return $this->redirectToRoute('app_project_settings', ['projectId' => $projectId, 'tab' => Project::PERMISSIONS]);
        }

        return $this->render('defaults/edit.html.twig', [
            'title' => 'Edycja uprawnień użytkownika ' . $user->getEmail(),
            'form' => $form->createView(),
            'back_link' => 'app_project_index',
            'list_link' => 'app_project_index'
        ]);
    }
}
