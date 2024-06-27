<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomepageController extends AbstractController
{
    // Edycja strony głównej w admin panelu
    #[Route("/homepage_edit", name: "homepage_edit")]
    public function homepage_edit()
    {
        return $this->render('homepage/homepage_edit.twig');
    }

    #[Route("/slider", name: "slider")]
    public function slider()
    {
        return $this->render('slider.twig');
    }
}

