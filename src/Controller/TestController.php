<?php

namespace App\Controller;

use App\Entity\Test;
use App\Form\TestResultType;
use App\Form\TestType;
use App\Repository\CampagneRepository;
use App\Repository\DroitRepository;
use App\Repository\ProjetRepository;
use App\Repository\TestRepository;
use App\Service\AutorisationsProjetService;
use App\Service\EtatsTestsService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/projets/{idProjet}/campagnes/{campagneId}", name="campagnes_tests_")
 */
class TestController extends AbstractController
{
    /**
     * cette fonction permet de créer un test dans une campagne
     *
     * @Route("/nouveau-test", name="creer")
     */
    public function creer(
        int                         $idProjet,
        int                         $campagneId,
        ProjetRepository            $projetRepository,
        CampagneRepository          $campagneRepository,
        EntityManagerInterface      $entityManager,
        DroitRepository             $droitRepository,
        Request                     $request,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        TestRepository              $testRepository,
        EtatsTestsService           $etatsTestsService
    ): Response
    {
        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'AJOUTEST']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagne = $campagneRepository->find($campagneId);

                if ($campagne->getEtat()->getIsEnPrep())  {

                    $test = new Test();
                    $test->setCampagne($campagne);
                    $test->setEtat($etatsTestsService->getEtatATester($projet));
                    $formCreerTest = $this->createForm(TestType::class, $test, ['projet' => $idProjet]);
                    $formCreerTest->handleRequest($request);

                    if ($formCreerTest->isSubmitted() && $formCreerTest->isValid()) {

                        try {
                            $ordre = $testRepository->createPositionOfTest($campagne->getId());

                            if ($ordre === null)
                            {
                                $ordre = 0;
                            }
                            else
                            {
                                $ordre = $ordre['p'];
                            }

                            $test->setOrdre($ordre +1);

                            $entityManager->persist($test);
                            $entityManager->flush();

                            $this->addFlash('success', 'Test ajouté à la campagne');
                        }
                        catch (NonUniqueResultException $e)
                        {
                            $this->addFlash('error', 'Erreur à la récupération de la position dans la campagne');
                        }

                        return $this->redirectToRoute('campagnes_detail', ['idProjet' => $idProjet, 'id' => $campagneId]);
                    }

                    return $this->render('test/creer.html.twig', [
                        'projet' => $projet,
                        'campagne' => $campagne,
                        'formCreerTest' => $formCreerTest->createView()
                    ]);
                }
                else
                {
                    $this->addFlash('danger', "Cette campagne ne peut pas être éditée : elle n'est pas en préparation.");

                    return $this->redirectToRoute('campagnes_detail', [
                        'id' => $campagneId,
                        'idProjet' => $idProjet
                    ]);
                }

            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé(e) à accéder à cette fonctionnalité.");
            }
        } else {
            $this->addFlash('warning', "Le projet auquel vous essayez d'accéder n'existe pas.");
        }

        return $this->redirectToRoute('projet_liste');
    }

    /**
     * cette fonction permet d'exécuter un test dans une campagne. C'est à dire que l'utilisateur peut choisir un état pour son test
     * Ko ou ok et ajouter des précisions au tets
     *
     * @Route("/executer/{testId}", name="executer")
     */
    public function executer(
        int                         $idProjet,
        int                         $campagneId,
        int                         $testId,
        ProjetRepository            $projetRepository,
        CampagneRepository          $campagneRepository,
        DroitRepository             $droitRepository,
        EntityManagerInterface      $entityManager,
        Request                     $request,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        TestRepository              $testRepository
    ): Response
    {
        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'ETATTEST']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagne = $campagneRepository->find($campagneId);

                if ($campagne->getEtat()->getIsEnCours()) {

                    $test = $testRepository->find($testId);

                    if ($campagne->getTests()->contains($test))
                    {
                        $execTestForm = $this->createForm(TestResultType::class, $test, [
                            'projet' => $projet,
                        ]);
                        $execTestForm->handleRequest($request);

                        if ($execTestForm->isSubmitted() && $execTestForm->isValid()) {

                            $entityManager->persist($test);
                            $entityManager->flush();

                            $this->addFlash('success', 'Enregistrement du test réussi');

                            return $this->redirectToRoute('campagnes_detail', [
                                'id' => $campagneId,
                                'idProjet' => $idProjet
                            ]);
                        }

                        return $this->render('test/executer.html.twig', [
                            'projet' => $projet,
                            'campagne' => $campagne,
                            'test' => $test,
                            'execTestForm' => $execTestForm->createView()
                        ]);

                    }
                    else
                    {
                        $this->addFlash('danger', 'Une erreur s\'est produite');
                    }
                }
                else
                {
                    $this->addFlash('danger', "Ce test ne peut pas être exécuté, la campagne n'est pas en cours.");
                }

                return $this->redirectToRoute('campagnes_detail', [
                    'id' => $campagneId,
                    'idProjet' => $idProjet
                ]);


            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé(e) à accéder à cette fonctionnalité.");
            }
        } else {
            $this->addFlash('warning', "Le projet auquel vous essayez d'accéder n'existe pas.");
        }

        return $this->redirectToRoute('projet_liste');
    }

    /**
     * cette fonction permet de modifier un test dans une campagne
     *
     * @Route("/modifier/{testId}", name="modifier")
     */
    public function modifierTest(
        int                         $idProjet,
        int                         $campagneId,
        int                         $testId,
        ProjetRepository            $projetRepository,
        CampagneRepository          $campagneRepository,
        DroitRepository             $droitRepository,
        EntityManagerInterface      $entityManager,
        Request                     $request,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        TestRepository              $testRepository
    ): Response
    {
        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            // Si l'utilisateur peut ajouter des tests, il peut aussi les modifier ? Ou faire un droit EDITTEST ?
            $droit = $droitRepository->findOneBy(['code' => 'AJOUTEST']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagne = $campagneRepository->find($campagneId);

                if ($campagne->getEtat()->getIsEnPrep())  {

                    $test = $testRepository->find($testId);

                    if ($campagne->getTests()->contains($test))
                    {
                        $formUpdateTest = $this->createForm(TestType::class, $test, ['projet' => $projet]);
                        $formUpdateTest->handleRequest($request);

                        if ($formUpdateTest->isSubmitted() && $formUpdateTest->isValid()) {

                            $entityManager->persist($test);
                            $entityManager->flush();

                            $this->addFlash('success', 'Le test '.$test->getNom().' a été modifié');
                            return $this->redirectToRoute('campagnes_detail', [
                                'idProjet' => $idProjet,
                                'id' => $campagneId
                            ]);

                        }

                        return $this->render('test/modifier.html.twig', [
                            'projet' => $projet,
                            'campagne' => $campagne,
                            'test' => $test,
                            'formModifier' => $formUpdateTest->createView()
                        ]);

                    }
                    else
                    {
                        $this->addFlash('danger', 'Une erreur s\'est produite');
                    }
                }
                else
                {
                    $this->addFlash('danger', "Cette campagne ne peut pas être éditée : elle n'est pas en préparation.");
                }

                return $this->redirectToRoute('campagnes_detail', [
                    'id' => $campagneId,
                    'idProjet' => $idProjet
                ]);

            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé(e) à accéder à cette fonctionnalité.");
            }
        } else {
            $this->addFlash('warning', "Le projet auquel vous essayez d'accéder n'existe pas.");
        }

        return $this->redirectToRoute('projet_liste');
    }


    /**
     * cette fonction permet de supprimer un test dans une campagne
     *
     * @Route("/supprimer/{testId}", name="supprimer")
     */
    public function supprimer(
        int                         $idProjet,
        int                         $campagneId,
        int                         $testId,
        ProjetRepository            $projetRepository,
        CampagneRepository          $campagneRepository,
        DroitRepository             $droitRepository,
        EntityManagerInterface      $entityManager,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        TestRepository              $testRepository
    ): Response
    {
        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'SUPPTEST']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagne = $campagneRepository->find($campagneId);

                if ($campagne->getEtat()->getIsEnPrep())  {

                    $test = $testRepository->find($testId);

                    if ($campagne->getTests()->contains($test))
                    {
                        $entityManager->remove($test);
                        $entityManager->flush();

                        $tests =  $testRepository->findByCampagneWithJoins($campagne);

                        $ordre = 0;

                        foreach ($tests as $test) {
                            $ordre ++;
                            $test->setOrdre($ordre);
                            $entityManager->persist($test);
                        }

                        $entityManager->flush();

                        return $this->redirectToRoute('campagnes_detail', [
                            'id' => $campagneId,
                            'idProjet' => $idProjet
                        ]);

                    }
                    else
                    {
                        $this->addFlash('danger', 'Une erreur s\'est produite');
                    }

                }
                else
                {
                    $this->addFlash('danger', "Cette campagne ne peut pas être éditée : elle n'est pas en préparation.");
                }

                return $this->redirectToRoute('campagnes_detail', [
                    'id' => $campagneId,
                    'idProjet' => $idProjet
                ]);

            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé(e) à accéder à cette fonctionnalité.");
            }
        } else {
            $this->addFlash('warning', "Le projet auquel vous essayez d'accéder n'existe pas.");
        }

        return $this->redirectToRoute('projet_liste');
    }


    /**
     * cette fonction permet de passer l'état d'un test dans une campagne à ok
     *
     * @Route("/valider/{testId}", name="valider")
     */
    public function validerTest(
        ProjetRepository            $projetRepository,
        CampagneRepository          $campagneRepository,
        DroitRepository             $droitRepository,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        int                         $idProjet,
        int                         $campagneId,
        int                         $testId,
        TestRepository              $testRepository,
        EtatsTestsService           $etatsTestsService,
        EntityManagerInterface      $entityManager
    ): Response {

        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'VOIRCAMP']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagne = $campagneRepository->findOneBy(['id' => $campagneId, 'projet' => $projet]);

                if($campagne) {

                    $test = $testRepository->findOneBy(['campagne' => $campagne, 'id' => $testId]);

                    if ($test) {

                        $test->setEtat($etatsTestsService->getEtatOK($projet));

                        $entityManager->persist($test);
                        $entityManager->flush();


                        return $this->redirectToRoute('campagnes_detail', ['idProjet' => $idProjet, 'id' => $campagneId]);

                    } else {
                        $errorMsg = "Le test ciblé n'existe pas.";
                    }

                } else {
                    $errorMsg = "La campagne ciblée n'existe pas.";
                }
            } else {
                $errorMsg = "Vous ne disposez pas des droits suffisants pour exporter cette campagne.";
            }
        } else {
            $errorMsg = "Ce projet n'existe pas.";
        }

        return $this->json(['error' => $errorMsg]);
    }

    /**
     * cette fonction permet de passer l'état d'un test dans une campagne à ko
     *
     * @Route("/refuser/{testId}", name="refuser")
     */
    public function refuserTest(
        ProjetRepository            $projetRepository,
        CampagneRepository          $campagneRepository,
        DroitRepository             $droitRepository,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        int                         $idProjet,
        int                         $campagneId,
        int                         $testId,
        TestRepository              $testRepository,
        EtatsTestsService           $etatsTestsService,
        EntityManagerInterface      $entityManager
    ): Response {

        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'VOIRCAMP']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagne = $campagneRepository->findOneBy(['id' => $campagneId, 'projet' => $projet]);

                if($campagne) {

                    $test = $testRepository->findOneBy(['campagne' => $campagne, 'id' => $testId]);

                    if ($test) {

                        $test->setEtat($etatsTestsService->getEtatKO($projet));

                        $entityManager->persist($test);
                        $entityManager->flush();


                        return $this->redirectToRoute('campagnes_detail', ['idProjet' => $idProjet, 'id' => $campagneId]);

                    } else {
                        $errorMsg = "Le test ciblé n'existe pas.";
                    }

                } else {
                    $errorMsg = "La campagne ciblée n'existe pas.";
                }
            } else {
                $errorMsg = "Vous ne disposez pas des droits suffisants pour exporter cette campagne.";
            }
        } else {
            $errorMsg = "Ce projet n'existe pas.";
        }

        return $this->json(['error' => $errorMsg]);
    }
}
