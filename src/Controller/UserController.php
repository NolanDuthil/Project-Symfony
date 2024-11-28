<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        // Get the currently logged-in user
        $user = $this->getUser();

        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException('You must be logged in to access this page.');
        }

        return $this->render('user/index.html.twig', [
            'user' => $user,
        ]);
    }
}