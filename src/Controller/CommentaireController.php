<?php

namespace App\Controller;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentaireController extends AbstractController
{
    /**
     * @Route("/commentaire/modifier/{idCommentaire}", name="commentaire_modifier")
     */
    public function modifier($idCommentaire, CommentaireRepository  $commentaireRepository, EntityManagerInterface $entityManager): Response
    {
        $commentaire=$commentaireRepository->find($idCommentaire);

        if($_POST ['messageActuel']!== ' ' && $_POST ['messageActuel']!== ''){
            $commentaire->setMessage($_POST['messageActuel']);

            $entityManager->persist($commentaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('templates_test_detail', ['idDetail'=> $commentaire->getTemplateTest()->getId(), 'id'=> $commentaire->getTemplateTest()->getProjet()->getId()]);
    }

    /**
     * @Route("/commentaire/supprimer/{idCommentaire}", name="commentaire_supprimer")
     */

    public function supprimer($idCommentaire, CommentaireRepository $commentaireRepository, EntityManagerInterface $entityManager) :Response
    {
        $commentaire=$commentaireRepository->find($idCommentaire);

        if($commentaire){
            $entityManager->remove($commentaire);
            $entityManager->flush();
        }
        return $this->redirectToRoute('templates_test_detail', ['idDetail'=> $commentaire->getTemplateTest()->getId(), 'id'=> $commentaire->getTemplateTest()->getProjet()->getId()]);
    }


}
