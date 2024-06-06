<?php

namespace App\Controller;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $contact->setSubject($request->get("subject"));
        $contact->setMessage($request->get("message"));

        $email = (new Email())
            ->from("adam.racki@asterisk-dev.pl")
            ->to($request->get('email'))
            ->subject($request->get("subject"))
            ->text($request->get("message"));

        $mailer->send($email);

        $em->persist($contact);
        $em->flush();

        return $this->redirectToRoute("app_contact");
    }
}

