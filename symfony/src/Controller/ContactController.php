<?php

namespace App\Controller;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    // Kontakt na stronie
    #[Route("/contact", name: "app_contact")]
    public function contact(): Response
    {
        return $this->render('contact/contact.twig');
    }

    // Lista maili w admin panelu
    #[Route("/admin_contact", name: "admin_contact")]
    public function admin_contact(EntityManagerInterface $em)
    {
        $mail = $em->getRepository(Contact::class)->findAll();

        return $this->render('contact/admin_contact.twig', ['mail' => $mail]);
    }

    // Wysyłanie maili na stronie
    #[Route("/contact/send", name: "contact_mail")]
    public function contact_mail(Request $request, EntityManagerInterface $em, MailerInterface $mailer)
    {

        $contact = new Contact();

        $contact->setName($request->get("name"));
        $contact->setEmail($request->get("email"));

        $email = (new Email())
            ->from("adam.racki@asterisk-dev.pl")
            ->to($request->get('email'))
            ->subject("Newsletter")
            ->text("Dziękujemy za zapis do newslettera! Od teraz będziemy cię informać o wszystkich nowościach!");

        $mailer->send($email);

        $em->persist($contact);
        $em->flush();

        return $this->redirectToRoute("app_contact");
    }

        // Wyszukiwanie menu w admin panelu
        #[Route("/admin_contact/search", name: "contact_search")]
        function contact_search(Request $request, EntityManagerInterface $em)
        {
            $resp = [
                'rows' => []
            ];
        
            $phrase = $request->query->get('phrase');
            $search_offset = $request->query->get('search_offset');
            $search_size = $request->query->get('search_size');
        
            $queryBuilder = $em->createQueryBuilder();
            $queryBuilder->select('obj')->from(Contact::class, 'obj');
                
            $queryBuilder->orWhere("obj.email LIKE :phrase");
            $queryBuilder->setParameter('phrase', "%$phrase%");
        
            $queryBuilder->orderBy("obj.id", 'ASC');
            $queryBuilder->setFirstResult($search_offset)->setMaxResults($search_size);
        
            $Records = $queryBuilder->getQuery()->execute();
        
            foreach($Records as $record){
                if($record->getEmail()){
                    $email = $record->getEmail();
                }
                    
                array_push($resp['rows'], [
                    'id' => $record->getID(),
                    'name' => $record->getName(),
                    'email' => $email,
                    'subject' => $record->getSubject(),
                    'message' => $record->getMessage()
                ]);
            }
        
            return new JsonResponse($resp);
        }
}

