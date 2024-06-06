<?php

namespace App\Controller;

use App\Entity\About;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class AboutController extends AbstractController
{
    // O nas na stronie
    #[Route("/about", name: "app_about")]
    public function about(EntityManagerInterface $em): Response
    {
        $about = $em->getRepository(About::class)->findAll();

        return $this->render('about/about.twig', ['about' => $about]);
    }

    // Strona O nas w admin panelu
    #[Route("/about_edit", name: "about_edit")]
    public function about_edit(EntityManagerInterface $em)
    {
        $about = $em->getRepository(About::class)->findAll();

        return $this->render('about/about_edit.twig', ['about' => $about]);
    }

    // Formularz edycji O nas w admin panelu
    #[Route("/about_edit/edit/{id}", name: "edit_about_twig")]
    public function edit_about_twig(EntityManagerInterface $em, int $id)
    {
        $about = $em->getRepository(About::class)->find($id);

        return $this->render('about/about_data_edit.twig', ['about' => $about, 'id' => $about->getId()]);
    }

    // Edycja O nas w admin panelu
    #[Route('/about_edit/edit/edit/{id}', name: 'edit_about')]
    public function edit_about(Request $request, EntityManagerInterface $em, int $id, SluggerInterface $slugger)
    {
        $about = $em->getRepository(About::class)->find($id);

        $image = $request->files->get('pic');

        if($image) {
            $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $image->move("../public/uploads", $newFilename);
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            // updates the 'brochureFilename' property to store the PDF file name
            // instead of its contents
            $about->setImage($newFilename);
        }

        $about->setHeader($request->get("header"));
        $about->setDescription($request->get("description"));
        $em->flush();

        return $this->redirectToRoute('about_edit', [
            'id' => $about->getId()
        ]);
    }
}

