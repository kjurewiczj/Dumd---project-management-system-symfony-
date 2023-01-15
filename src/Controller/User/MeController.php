<?php

namespace App\Controller\User;

use App\Project\StatisticsHandler;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class MeController extends AbstractController
{
    #[Route('/me', name: 'app_user_me')]
    public function index(Security $security, UserRepository $userRepository, StatisticsHandler $statisticsHandler): Response
    {
        $currentUser = $security->getUser();
        $user = $userRepository->find($currentUser->getId());
        $userStatistics = $statisticsHandler->generateStatistiscsForUser($user);

        return $this->render('user/me.html.twig', [
            'title' => 'Panel użytkownika',
            'user' => $user,
            'statistics' => $userStatistics,
            'list_link' => 'app_project_index'
        ]);
    }
}
