<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

        $queryBuilder->orderBy("obj.id", 'DESC');
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
                'image' => $record->getImage()
            ]);
        }

        return new JsonResponse($resp);
    }
}

