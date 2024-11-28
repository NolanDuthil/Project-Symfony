<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalsMentionsController extends AbstractController
{
    #[Route('/legals/mentions', name: 'app_legals_mentions')]
    public function index(): Response
    {
        return $this->render('legals_mentions/index.html.twig', [
            'controller_name' => 'LegalsMentionsController',
        ]);
    }
}
