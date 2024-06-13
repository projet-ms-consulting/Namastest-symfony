<?php

namespace App\Controller;

use App\Entity\QuestionGenerale;
use App\Entity\Support;
use App\Entity\Utilisateur;
use App\Form\QuestionGeneraleType;
use App\Form\SupportType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @Route("/support", name="support")
     */
    public function support(Request $request, EntityManagerInterface $entityManager): Response
    {
        $support = new Support();

        /**
         * @var Utilisateur $user
         */
        $user = $this->getUser();

        $support->setNom($user->getNom())
                ->setPrenom($user->getPrenom())
                ->setEmail($user->getEmail())
                ->setUtilisateur($user);

        $supportForm = $this->createForm(SupportType::class, $support);

        $supportForm->handleRequest($request);

        if ($supportForm->isSubmitted() && $supportForm->isValid())
        {
            $entityManager->persist($support);
            $entityManager->flush();

            $this->addFlash('success','Votre message a bien été transmis.');

            return $this->redirectToRoute('support');
        }
        return $this->render('contact/support.html.twig',
            [
                'supportType' => $supportForm->createView()
            ]);
    }

    /**
     * @Route("/question-generale", name="question_generale")
     */
    public function question(Request $request, EntityManagerInterface $entityManager): Response
    {
        //dd($request->getSession());

        $question = new QuestionGenerale();

        /**
         * @var Utilisateur $user
         */
        $user = $this->getUser();

        if ($user){
            $question->setNom($user->getNom())
                    ->setPrenom($user->getPrenom())
                    ->setEmail($user->getEmail());
        }

        $questionForm = $this->createForm(QuestionGeneraleType::class, $question);

        $questionForm->handleRequest($request);

        if ($questionForm->isSubmitted() && $questionForm->isValid())
        {
            $entityManager->persist($question);
            $entityManager->flush();

            $this->addFlash('success','Votre message a bien été transmis.');

            return $this->redirectToRoute('question_generale');
        }
        return $this->render('contact/question_generale.html.twig',
            [
                'questionForm' => $questionForm->createView()
            ]);
    }
}