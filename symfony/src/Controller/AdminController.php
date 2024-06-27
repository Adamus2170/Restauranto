<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminController extends AbstractController
{
    // Strona główna admin panelu
    #[Route("/admin", name: "admin_homepage")]
    public function admin_homepage(EntityManagerInterface $em)
    {
        return $this->render('admin_homepage.twig');
    }

    // Strona główna 
    #[Route("/", name: "app_homepage")]
    public function homepage(): Response
    {
        return $this->render('homepage.twig');
    }

    // Policy na stronie
    #[Route("/policy", name: "app_policy")]
    public function policy(): Response
    {
        return $this->render('policy.twig');
    }

    // Logowanie w admin panelu
    #[Route("/login", name: "login")]
    public function login(AuthenticationUtils $authenticationUtils)
    {
        {
            // get the login error if there is one
            $error = $authenticationUtils->getLastAuthenticationError();

            // last username entered by the user
            $lastUsername = $authenticationUtils->getLastUsername();

            return $this->render('login.twig', [
                'last_username' => $lastUsername,
                'error'         => $error,
            ]);
        }
    }

    // Wylogowanie z admin 
    #[Route("/logout", name: "logout")]
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}

