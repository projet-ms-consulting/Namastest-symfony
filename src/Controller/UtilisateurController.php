<?php

namespace App\Controller;

use App\Form\DeleteAccountType;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/utilisateur", name="utilisateur_")
 */
class UtilisateurController extends AbstractController
{
    /**
     * @Route("/modifier", name="modifier")
     */
    public function modifier(
        Request $request,
        EntityManagerInterface $entityManager,
        UserInterface $user,
        UserPasswordHasherInterface $passwordEncoder): Response
    {
        $form = $this->createForm(RegistrationFormType::class, $user, [
            'required_pwd' => false,
        ]);

        $form->remove('agreeTerms'); // Permet de réutiliser le formulaire d'inscription en retirant le champ inutile
        $form->remove('captcha');

        $form->handleRequest($request);

        $deleteForm = $this->createForm(DeleteAccountType::class, $user);

        $deleteForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

            // Si le champ plainPassword est renseigné
            if($plainPassword) {
                // encode the plain password
                $user->setPassword(
                    $passwordEncoder->hashPassword(
                        $user,
                        $plainPassword
                    )
                );
            }
            $serializer = $this->get('serializer');
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->render('utilisateur/modifier.html.twig', [
            'profilForm' => $form->createView(),
            'deleteForm' => $deleteForm->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/delete", name="delete")
     */
    public function delete(
        EntityManagerInterface $entityManager,
        UserInterface $user,
        Request $request
        ): Response
    {

        $form = $this->createForm(RegistrationFormType::class, $user, [
            'required_pwd' => false,
        ]);

        $form->remove('agreeTerms');

        $form->handleRequest($request);

        $deleteForm = $this->createForm(DeleteAccountType::class, $user);

        $deleteForm->handleRequest($request);

        if($user != null && $deleteForm != null){

            if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
                $email= $deleteForm->get('input')->getData();

                    if($user->getUserIdentifier() === $email) {
                        $entityManager->remove($user);
                        $entityManager->flush();
                        $session = new Session();
                        $session->invalidate();
                        return $this->redirectToRoute('app_logout');

                    } else {
                        $this->addFlash('danger' , 'L\'adresse mail est incorrecte');
                    }
                }

        } else {
            $this->addFlash('danger' , 'L\'utilisateur n\'existe pas');

        }

        return $this->render('utilisateur/modifier.html.twig', [
            'profilForm' => $form->createView(),
            'deleteForm' => $deleteForm->createView(),
            'user' => $user,
        ]);
    }

}
