<?php

namespace App\Controller;

use App\Entity\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class MenuController extends AbstractController
{
    // Menu na stronie
    #[Route("/menu", name: "app_menu")]
    public function menu(EntityManagerInterface $em)
    {
        $menu = $em->getRepository(Menu::class)->findAll();

        return $this->render('menu/menu.twig', ['menu' => $menu]);
    }

    // Wypisywanie danych z bazy danych
    #[Route("/menu_edit", name: "menu_edit")]
    public function menu_edit(EntityManagerInterface $em)
    {
        $menu = $em->getRepository(Menu::class)->findAll();

        return $this->render('menu/menu_edit.twig', ['menu' => $menu]);
    }

    // Strona z dodawaniem dania
    #[Route("/menu_edit/add_dish", name: "add_dish_twig")]
    public function add_dish_twig()
    {
        return $this->render('menu/menu_add_dish.twig');
    }

    // Dodawanie dania do bazy danych
    #[Route("/menu_edit/add_dish/add", name: "add_dish", methods: "POST")]
    public function add_dish(Request $request, EntityManagerInterface $em, SluggerInterface $slugger)
    {
        $menu = new Menu();

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
            $menu->setImage($newFilename);
        }
        
        $menu->setName($request->get("name"));
        $menu->setIngredients($request->get("ingredients"));
        $menu->setPrice($request->get("price"));
        $menu->setDishType($request->get("rodzaj"));

        $em->persist($menu);
        $em->flush();

        return $this->redirectToRoute("menu_edit");
    }

    // Strona z edycją dania
    #[Route("/menu_edit/edit_dish/{id}", name: "edit_dish_twig")]
    public function edit_dish_twig(EntityManagerInterface $em, int $id)
    {
        $dish = $em->getRepository(Menu::class)->find($id);

        return $this->render('menu/menu_data_edit.twig', ['dish' => $dish, 'id' => $dish->getId()]);
    }

    // Edycja dania w bazie danych
    #[Route('/menu_edit/edit_dish/edit/{id}', name: 'edit_dish')]
    public function edit_dish(Request $request, EntityManagerInterface $em, int $id, SluggerInterface $slugger)
    {
        $dish = $em->getRepository(Menu::class)->find($id);

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
            $dish->setImage($newFilename);
        }

        $dish->setName($request->get("name"));
        $dish->setIngredients($request->get("ingredients"));
        $dish->setPrice($request->get("price"));
        $dish->setDishType($request->get("rodzaj"));
        $em->flush();

        return $this->redirectToRoute('menu_edit', [
            'id' => $dish->getId()
        ]);
    }

    // Usuwanie danych z bazy danych
    #[Route("/menu_edit/dish_delete/{id}", name: "delete_dish")]
    public function delete_dish(EntityManagerInterface $em, int $id)
    {
        $dish = $em->getRepository(Menu::class)->find($id);;

        $em->remove($dish);
        $em->flush();

        return $this->redirectToRoute("menu_edit", [
            'id' => $dish->getId()
        ]);
    }

    // Wyszukiwanie menu w admin panelu
    #[Route("/menu_edit/search", name: "menu_search")]
    function menu_search(Request $request, EntityManagerInterface $em)
    {
        $resp = [
            'rows' => []
        ];
    
        $phrase = $request->query->get('phrase');
        $search_offset = $request->query->get('search_offset');
        $search_size = $request->query->get('search_size');
    
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder->select('obj')->from(Menu::class, 'obj');
            
        $queryBuilder->orWhere("obj.name LIKE :phrase");
        $queryBuilder->orWhere("obj.ingredients LIKE :phrase");
        $queryBuilder->orWhere("obj.price LIKE :phrase");
        $queryBuilder->setParameter('phrase', "%$phrase%");
    
        $queryBuilder->orderBy("obj.id", 'ASC');
        $queryBuilder->setFirstResult($search_offset)->setMaxResults($search_size);
    
        $Records = $queryBuilder->getQuery()->execute();
    
        foreach($Records as $record){
            if($record->getName()){
                $name = $record->getName();
            }
                
            array_push($resp['rows'], [
                'id' => $record->getID(),
                'name' => $name,
                'ingredients' => $record->getIngredients(),
                'price' => $record->getPrice(),
                'dishType' => $record->getDishType(),
                'image' => $record->getImage()
            ]);
        }
    
        return new JsonResponse($resp);
    }
}

