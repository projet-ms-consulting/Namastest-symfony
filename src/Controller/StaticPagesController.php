<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StaticPagesController extends AbstractController
{
    /**
     * @Route("/mentions-legales", name="mentions_legales")
     */
    public function mentionsLegales(): Response
    {
        return $this->render('static_pages/mentions-legales.html.twig', [
        ]);
    }

    /**
     * @Route("/cgu-rgpd", name="cgu-rgpd")
     */
    public function cguRgpd(): Response
    {
        return $this->render('static_pages/cgu-rgpd.html.twig', [
        ]);
    }

    /**
     * @Route("/aide", name="aide")
     */
    public function aide(): Response
    {
        return $this->render('static_pages/aide.html.twig', [

        ]);
    }

    /**
     * @Route("/freemium", name="version_home")
     */
    public function home()
    {
        return $this->render('static_pages/freemium.html.twig');
    }
}
