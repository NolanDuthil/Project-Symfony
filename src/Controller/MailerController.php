<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MailerController extends AbstractController
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    #[Route('/mailer', name: 'app_mailer')]
    public function sendEmail(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('to', EmailType::class, [
                'label' => 'To',
            ])
            ->add('subject', TextType::class, [
                'label' => 'Subject',
            ])
            ->add('content', TextType::class, [
                'label' => 'Content',
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $email = (new Email())
                ->from('duthilnolan@gmail.com')
                ->to($data['to'])
                ->subject($data['subject'])
                ->text($data['content']);

            $this->mailer->send($email);

            return new Response('Email envoyé avec succès');
        }

        return $this->render('mailer/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}