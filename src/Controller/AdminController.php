<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; 

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    public function users_saisie(UserRepository $listUsers): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $listUsers->findAll(),
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin/users/saisie/{id}', name: 'app_admin_users_saisie')]
    public function users(UserRepository $listUsers, EntityManagerInterface $em, Request $request, string $id = null): Response
    {
        $user = $id ? $listUsers->find($id) : null;

        if ($id && !$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        if (!$user) {
            $user = new User();
        }

        $form = $this->createFormBuilder($user)
            ->add(
                'email',
                EmailType::class,
                [
                    'required' => true,
                    'label' => 'Email',
                    'data' => $user ? $user->getEmail() : '',
                    'attr' => [
                        'placeholder' => 'Email',
                    ]
                ]
            )
            ->add(
                'username',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'Username',
                    'data' => $user ? $user->getUsername() : '',
                    'attr' => [
                        'placeholder' => 'Username',
                    ]
                ]
            )
            ->add(
                'password',
                PasswordType::class,
                [
                    'required' => true,
                    'label' => 'Password',
                    'attr' => [
                        'placeholder' => 'Password'
                    ]
                ]
            )
            ->add(
                'nom',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'Nom',
                    'data' => $user ? $user->getNom() : '',
                    'attr' => [
                        'placeholder' => 'Nom',
                    ]
                ]
            )
            ->add(
                'prenom',
                TextType::class,
                [
                    'required' => true,
                    'label' => 'Prenom',
                    'data' => $user ? $user->getPrenom() : '',
                    'attr' => [
                        'placeholder' => 'Prenom',
                    ]
                ]
            )
            ->add(
                'actif',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'Actif',
                    'data' => $user->isActif(),
                ]
            )
            ->add(
                'roles',
                ChoiceType::class,
                [
                    'required' => true,
                    'label' => 'Roles',
                    'choices' => [
                        'Administrateur' => 'ROLE_ADMIN',
                        'Gestionnaire' => 'ROLE_GESTIONNAIRE',
                        'Utilisateurs' => 'ROLE_USER',
                    ],
                    'multiple' => true,
                    'expanded' => true
                ]
            )
            ->add('save', SubmitType::class, ['label' => $id ? 'Modify user' : 'Create user'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('password')->getData()) {
                $encodedPassword = password_hash($form->get('password')->getData(), PASSWORD_BCRYPT);
                $user->setPassword($encodedPassword);
            }

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/users_saisie.html.twig', [
            'user' => $user,
            'form' => $form,
            'controller_name' => 'AdminController',
        ]);
    }
}