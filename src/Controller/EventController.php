<?php
// src/Controller/EventController.php
namespace App\Controller;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/', name: 'app_event')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $events = $entityManager->getRepository(Event::class)->findAll();

        return $this->render('index/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/event/{id}', name: 'app_event_show')]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $event = $entityManager->getRepository(Event::class)->find($id);

        if (!$event) {
            throw $this->createNotFoundException('The event does not exist');
        }

        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/event/join/{id}', name: 'join_event')]
    public function join(Event $event, EntityManagerInterface $entityManager): Response
    {
        // Logique pour rejoindre l'événement (par exemple, ajouter l'utilisateur à la liste des participants)
        
        // Rediriger vers la page de l'événement ou une autre page appropriée
        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }
}