<?php

namespace App\Controller;

use App\Entity\Newsmenu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class NewsController extends AbstractController
{
    // Aktualności na stronie
    #[Route("/news", name: "app_news")]
    public function news(EntityManagerInterface $em): Response
    {
        $newsmenu = $em->getRepository(Newsmenu::class)->findAll();

        return $this->render('news/news.twig', ['news' => $newsmenu]);
    }

    // Aktualności w admin panelu
    #[Route("/news_edit", name: "news_edit")]
    public function news_edit(EntityManagerInterface $em)
    {
        $newsmenu = $em->getRepository(Newsmenu::class)->findAll();

        return $this->render('news/news_edit.twig', ['newsmenu' => $newsmenu]);
    }

    // Formularz dodawania aktualności w admin panelu
    #[Route("/news_edit/add_news", name: "add_news_twig")]
    public function add_dish_twig()
    {
        return $this->render('news/news_add_data.twig');
    }
    
    // Dodawanie aktualności w admin panelu
    #[Route("/news_edit/add_news/add", name: "add_news")]
    public function add_dish(Request $request, EntityManagerInterface $em, SluggerInterface $slugger)
    {
        $news = new Newsmenu();

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
            $news->setImage($newFilename);
        }

        $news->setName($request->get("name"));
        $news->setIngredients($request->get("ingredients"));
        $news->setPrice($request->get("price"));

        $em->persist($news);
        $em->flush();

        return $this->redirectToRoute("news_edit");
    }
    
    // Formularz do edycji aktualności w admin panelu
    #[Route("/news_edit/edit_dish/{id}", name: "edit_news_twig")]
    public function edit_news_twig(EntityManagerInterface $em, int $id)
    {
        $news = $em->getRepository(Newsmenu::class)->find($id);

        return $this->render('news/news_data_edit.twig', ['news' => $news, 'id' => $news->getId()]);
    }
    
    // Edycja aktualności w admin panelu
    #[Route('/news_edit/edit_dish/edit/{id}', name: 'edit_news')]
    public function edit_news(Request $request, EntityManagerInterface $em, int $id, SluggerInterface $slugger)
    {
        $news = $em->getRepository(Newsmenu::class)->find($id);

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
            $news->setImage($newFilename);
        }

        $news->setName($request->get("name"));
        $news->setIngredients($request->get("ingredients"));
        $news->setPrice($request->get("price"));
        $em->flush();

        return $this->redirectToRoute('news_edit', [
            'id' => $news->getId()
        ]);
    }

    // Usuwanie aktualności w admin panelu
    #[Route("/news_edit/news_delete/{id}", name: "delete_news")]
    public function delete_news(EntityManagerInterface $em, int $id)
    {
        $news = $em->getRepository(Newsmenu::class)->find($id);;

        $em->remove($news);
        $em->flush();
    
        return $this->redirectToRoute("news_edit", [
            'id' => $news->getId()
        ]);
    }
}

