<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\EventRepository;
use App\Repository\UserRepository;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class GestionController extends AbstractController
{

    private $mailer;
    private $eventRepository;
    private $userRepository;
    private $twig;

    public function __construct(MailerInterface $mailer, EventRepository $eventRepository, UserRepository $userRepository, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
        $this->twig = $twig;
    }

    #[Route('/gestion', name: 'app_gestion')]
    public function index(): Response
    {
        return $this->render('gestion/index.html.twig', [
            'controller_name' => 'GestionController',
        ]);
    }

    #[Route('/gestion/event', name: 'app_gestion_event')]
    public function events(EventRepository $listEvents): Response
    {
        return $this->render('gestion/event.html.twig', [
            'events' => $listEvents->findAll(),
            'controller_name' => 'GestionController',
        ]);
    }

    #[Route('/gestion/event/saisie/{id}', name: 'app_gestion_event_saisie')]
    public function events_saisie(EventRepository $listEvents, EntityManagerInterface $em, Request $request, string $id = null): Response
    {

        $event = $id ? $listEvents->find($id) : null;

        if ($id && !$event) {
            throw $this->createNotFoundException('événement non trouvé');
        }

        if (!$event) {
            $event = new Event();
        }

        $form = $this->createFormBuilder($event)

        ->add(
            'title',
            TextType::class,
            [
                'required' => true,
                'label' => 'Titre', 
                'data' => $event ? $event->getTitle() : '',
                'attr' => [
                    'placeholder' => 'Titre', 
                ]
            ]
        )
        
        ->add(
            'date_debut',
            DateTimeType::class,
            [
                'required' => true,
                'label' => 'Date de début',
                'data' => $event ? $event->getDateDebut() : null,
                'attr' => [
                    'placeholder' => 'Date de début', 
                ],
                'widget' => 'single_text',
                'html5' => true,
            ]
        )
        
        ->add(
            'date_fin',
            DateTimeType::class,
            [
                'required' => true,
                'label' => 'Date de fin',
                'data' => $event ? $event->getDateFin() : null,
                'attr' => [
                    'placeholder' => 'Date de fin',
                ],
                'widget' => 'single_text',
                'html5' => true,
            ]
        )

        ->add(
            'description',
            TextType::class,
            [
                'required' => true,
                'label' => 'Description', 
                'data' => $event ? $event->getDescription() : '',
                'attr' => [
                    'placeholder' => 'Description', 
                ]
            ]
        )

        ->add(
            'visibility',
            ChoiceType::class,
            [
                'required' => true,
                'label' => 'Visibilité',
                'choices' => [
                    'Public' => '1',
                    'Privé' => '0'
                ]
            ]
        )

        ->add('id_users', EntityType::class, [
            'class' => 'App\Entity\User',
            'choice_label' => 'username',
            'multiple' => true,
            'expanded' => true,
        ])

        ->add('save', SubmitType::class, ['label' => $id ? 'Modify event' : 'Create event'])
            
        ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($event->getDateDebut() > $event->getDateFin()) {
                $error = 'La date de début doit être inférieure à la date de fin';
            }
            else {
                $em->persist($event);
                $em->flush();
        
                return $this->redirectToRoute('app_gestion_event');
            }
        }

        return $this->render('gestion/event_saisie.html.twig', [
            'error' => $error ?? null,
            'event' => $event,
            'form' => $form,
            'controller_name' => 'GestionController',
        ]);

        // Récupérer les utilisateurs liés à l'événement
        $users = $event->getUsers();

        // Envoyer un email à chaque utilisateur
        foreach ($users as $user) {
            $email = (new Email())
                ->from('duthilnolan@gmail.com')
                ->to($user->getEmail())
                ->subject('Event Updated')
                ->content('Hello');

            $this->mailer->send($email);
        }

        return new Response('Event updated and emails sent successfully');
        
    }
}