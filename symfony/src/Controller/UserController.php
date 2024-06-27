<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;
    
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    // Strona z użytkownikami w admin panelu
    #[Route("/users_edit", name: "users_edit")]
    public function about_edit(EntityManagerInterface $em)
    {
        $users = $em->getRepository(User::class)->findAll();

        return $this->render('users/users_edit.twig', ['users' => $users]);
    }

    
    // Formularz dodający użytkownika w admin panelu
    #[Route("/users_edit/add_user", name: "add_user_twig")]
    public function add_user_twig()
    {
        return $this->render('users/users_add_user.twig');
    }

    // Dodawanie użytkownika w admin panelu
    #[Route("/users_edit/add_user/add", name: "add_user")]
    public function add_user(Request $request, EntityManagerInterface $em)
    {
        if($request->get("password") == $request->get("password_repeat"))
        {
            $users = new User();

            $users->setPassword($this->passwordHasher->hashPassword($users, $request->get("password")));
            $users->setUsername($request->get("username"));
            $users->setRoles([$request->get("roles")]);

            $em->persist($users);
            $em->flush();

            return $this->redirectToRoute("users_edit");
        }
        else
        {
            return $this->render('users/users_add_user.twig');
        }
    }

    // Formularz z edycją użytkowników w admin panelu
    #[Route("/users_edit/edit/{id}", name: "edit_users_twig")]
    public function edit_users_twig(EntityManagerInterface $em, int $id)
    {
        $user = $em->getRepository(User::class)->find($id);

        return $this->render('users/users_data_edit.twig', ['user' => $user, 'id' => $user->getId()]);
    }

    // Edycja użytkowników w admin panelu
    #[Route('/users_edit/edit/edit/{id}', name: 'edit_users')]
    public function edit_users(Request $request, EntityManagerInterface $em, int $id)
    {
        if($request->get("password") == $request->get("password_repeat"))
        {
            $users = $em->getRepository(User::class)->find($id);

            $users->setUsername($request->get("username"));

            if($request->get("password") != null)
            {
                $users->setPassword($this->passwordHasher->hashPassword($users, $request->get("password")));
            }
            
            $users->setRoles([$request->get("roles")]);
        
            $em->flush();

            return $this->redirectToRoute('users_edit', [
                'id' => $users->getId()
            ]);
        }
        else
        {
            $user = $em->getRepository(User::class)->find($id);
            return $this->render('users/users_data_edit.twig', ['user' => $user, 'id' => $user->getId()]);
        }
    }

    // Usuwanie użytkowników w admin panelu
    #[Route("/user_edit/user_delete/{id}", name: "delete_user")]
    public function delete_dish(EntityManagerInterface $em, int $id)
    {
        $user = $em->getRepository(User::class)->find($id);;

        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute("users_edit", [
            'id' => $user->getId()
        ]);
    }

        // Wyszukiwanie menu w admin panelu
        #[Route("/users_edit/search", name: "users_search")]
        function users_search(Request $request, EntityManagerInterface $em)
        {
            $resp = [
                'rows' => []
            ];
    
            $phrase = $request->query->get('phrase');
            $search_offset = $request->query->get('search_offset');
            $search_size = $request->query->get('search_size');
    
            $queryBuilder = $em->createQueryBuilder();
            $queryBuilder->select('obj')->from(User::class, 'obj');
            
            $queryBuilder->orWhere("obj.username LIKE :phrase");
            $queryBuilder->orWhere("obj.password LIKE :phrase");
            $queryBuilder->orWhere("obj.roles LIKE :phrase");
            $queryBuilder->setParameter('phrase', "%$phrase%");
    
            $queryBuilder->orderBy("obj.id", 'ASC');
            $queryBuilder->setFirstResult($search_offset)->setMaxResults($search_size);
    
            $Records = $queryBuilder->getQuery()->execute();
    
            foreach($Records as $record){
                if($record->getUsername()){
                    $username = $record->getUsername();
                }
                
                array_push($resp['rows'], [
                    'id' => $record->getID(),
                    'username' => $username,
                    'password' => $record->getPassword(),
                    'roles' => $record->getRoles()
                ]);
            }
    
            return new JsonResponse($resp);
        }
}

