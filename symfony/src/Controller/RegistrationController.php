<?php

// src/Controller/RegistrationController.php
namespace App\Controller;

// ...

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    // Hashowanie hasła podczas dodawania użytkownika w admin panelu
    public function index(UserPasswordHasherInterface $passwordHasher, Request $request)
    {
        // ... e.g. get the user data from a registration form
        $user = new User();
        $plaintextPassword = $request->get("password");

        // hash the password (based on the security.yaml config for the $user class)
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($hashedPassword);
        // ...
    }
}