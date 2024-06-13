<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;

/**
 * @Route("/", name="accueil_")
 */
class AccueilController extends AbstractController
{
    /**
     * @Route("", name="index")
     */
    public function index(Request $request, MailerInterface $mailer)
    {

        return $this->render('accueil/index.html.twig', [

        ]);
    }

}
