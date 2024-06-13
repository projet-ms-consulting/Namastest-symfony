<?php

namespace App\Controller;

use App\Entity\Campagne;
use App\Entity\Test;
use App\Form\CampagneType;
use App\Form\CampagneUpdateType;
use App\Form\CatalogueChoiceType;
use App\Form\TestEtatType;
use App\Repository\CampagneRepository;
use App\Repository\DroitRepository;
use App\Repository\ProjetRepository;
use App\Repository\TestRepository;
use App\Service\AutorisationsProjetService;
use App\Service\CampagneDataService;
use App\Service\EtatsCampagneService;
use App\Service\EtatsTestsService;
use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/projets/{idProjet}/campagnes", name="campagnes_")
 */
class CampagneController extends AbstractController
{


//TODO : Ajouter une sélection de tests depuis la liste de tous les tests
//TODO : API

    /**
     * Cette fonction sert à afficher la liste des campagnes d'un projet. Elle récupère les campagnes en bdd puis affiche la page
     *
     * @Route("", name="liste")
     */
    public function liste(
        int                         $idProjet,
        CampagneRepository          $campagneRepository,
        ProjetRepository            $projetRepository,
        DroitRepository             $droitRepository,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user
    ): Response
    {

        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'VOIRCAMP']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagnes = $campagneRepository->findBy(['projet' => $projet]);

                return $this->render('campagne/liste.html.twig', [
                    'projet' => $projet,
                    'campagnes' => $campagnes
                ]);


            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé à accéder à ce projet.");
            }
        } else {
            $this->addFlash('warning', "Le projet auquel vous essayez d'accéder n'existe pas.");
        }

        return $this->redirectToRoute('projet_liste');
    }

    /**
     * Cette fonction sert à créer une campagne. Elle permet d'afficher un formulaire dans la page de création ainsi que
     * d'insérer la campagne en bdd une fois que l'utilisateur a validé le formulaire
     *
     * @Route("/creer", name="creer")
     */
    public function creer(
        int                         $idProjet,
        ProjetRepository            $projetRepository,
        DroitRepository             $droitRepository,
        EntityManagerInterface      $entityManager,
        Request                     $request,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        EtatsCampagneService        $etatsCampagneService
    ): Response
    {
        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'CREECAMP']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {


                $campagne = new Campagne();
                $campagne->setProjet($projet);
                $campagneForm = $this->createForm(CampagneType::class, $campagne);
                $campagneForm->handleRequest($request);

                if ($campagneForm->isSubmitted() && $campagneForm->isValid())
                {
                    $campagne->setEtat($etatsCampagneService->getEtatEnPrep($projet));

                    $entityManager->persist($campagne);
                    $entityManager->flush();

                    $this->addFlash('success', 'La campagne a bien été créée');

                    return $this->redirectToRoute('campagnes_liste', ['idProjet' => $idProjet]);
                }

                return $this->render('campagne/creer.html.twig', [
                    'projet' => $projet,
                    'campagneForm' => $campagneForm->createView()
                ]);


            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé à accéder à ce projet.");
            }
        } else {
            $this->addFlash('warning', "Le projet auquel vous essayez d'accéder n'existe pas.");
        }

        return $this->redirectToRoute('projet_liste');
    }

    /**
     * cette fonction sert à modifier une campagne, son nom, sa description, ses dates estimées.
     *
     * @Route("/modifier/{id}", name="modifier")
     */
    public function modifier(
        int                         $id,
        int                         $idProjet,
        ProjetRepository            $projetRepository,
        DroitRepository             $droitRepository,
        EntityManagerInterface      $entityManager,
        CampagneRepository          $campagneRepository,
        Request                     $request,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user
    ): Response
    {
        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'EDITCAMP']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagne = $campagneRepository->find($id);

                if ($campagne->getEtat()->getIsEnPrep())  {

                    $campagneUpdateForm = $this->createForm(CampagneUpdateType::class, $campagne);
                    $campagneUpdateForm->handleRequest($request);

                    if ($campagneUpdateForm->isSubmitted() && $campagneUpdateForm->isValid())
                    {
                        $entityManager->persist($campagne);
                        $entityManager->flush();

                        $this->addFlash('success', 'La campagne a bien été modifiée');

                        return $this->redirectToRoute('campagnes_detail', ['idProjet' => $idProjet, 'id' => $id]);
                    }

                    return $this->render('campagne/modifier.html.twig', [
                        'projet' => $projet,
                        'campagne' => $campagne,
                        'campagneForm' => $campagneUpdateForm->createView()
                    ]);
                }
                else
                {
                    $this->addFlash('danger', "Cette campagne ne peut pas être éditée : elle n'est pas en préparation.");

                    return $this->redirectToRoute('campagnes_detail', [
                        'id' => $id,
                        'idProjet' => $idProjet
                    ]);
                }

            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé à accéder à ce projet.");
            }
        } else {
            $this->addFlash('warning', "Le projet auquel vous essayez d'accéder n'existe pas.");
        }

        return $this->redirectToRoute('projet_liste');
    }

    /**
     * Cette fonction sert à afficher le détail d'une campagne. Elle affiche les campagnes en cours, en préparation et clôturées, donc
     * elle effectue un traitement en fonction de l'état de la campagne.
     * Si la campagne est en préparation : un formulaire d'ajout de catalogues et un formulaire de suppression de tests sera présent
     *      sur la page
     * Si la campagne est en cours : un formulaire d'exécution massive des tests (pour changer leur état)
     * Si la campagne est clôturée : aucun formulaire ne sera affiché
     *
     * @Route("/{id}", name="detail", methods={"GET","POST"})
     */
    public function detail(
        int                         $id,
        int                         $idProjet,
        ProjetRepository            $projetRepository,
        CampagneRepository          $campagneRepository,
        TestRepository              $testRepository,
        DroitRepository             $droitRepository,
        Request                     $request,
        EntityManagerInterface      $entityManager,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        EtatsTestsService           $etatsTestsService
    ): Response
    {
        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {

            $droit = $droitRepository->findOneBy(['code' => 'VOIRCAMP']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagne = $campagneRepository->find($id);
                $tests = $testRepository->findByCampagneWithJoins($campagne);

                // LE FORMULAIRE D'AJOUT DE CATALOGUES A UNE CAMPAGNE N'EST DISPONIBLE QUE DEPUIS UNE CAMPAGNE EN PREPARATION
                if ($campagne->getEtat()->getIsEnPrep())
                {
                    $formAddCatalogue = $this->createForm(CatalogueChoiceType::class, null, ['projet' => $projet]);
                }

                // CE FORMULAIRE SERT A PLUSIEURS CHOSES : SUPPRIMER UNE SELECTION DE TESTS
                $formMassExec = $this->createForm(TestEtatType::class, null, ['campagne' => $campagne, 'projet' => $projet]);

                // SI LA REQUETE EST UNE REQUETE POST
                if ($request->isMethod('POST')) {

                    // ON DEMANDE AU FORMULAIRE DE REAGIR AUX REQUETES
                    $formMassExec->handleRequest($request);

                    // ON TESTE L'ETAT DE LA CAMPAGNE, SI ELLE EST EN PREPARATION ON EFFECTUE LE TRAITEMENT
                    if ($campagne->getEtat()->getIsEnPrep())
                    {
                        // ON DEMANDE AU FORMULAIRE D'AJOUT DE CATALOGUES DE REAGIR AUX REQUETES
                        $formAddCatalogue->handleRequest($request);

                        // SI LE FORMULAIRE DE SUPPRESSION EST ENVOYE ET VALIDE
                        if ($formMassExec->isSubmitted() && $formMassExec->isValid()) {

                            //ON VERIFIE QUE C'EST BIEN LE BOUTON SUBMIT AYANT L'ATTRIBUT name="delete-tests" QUI A ENVOYE LE FORMULAIRE
                            // cette vérification permet de ne pas avoir de conflits au niveau des formulaires car il y en a plusieurs sur la même page et ça créait des bugs
                            if ($request->request->has('delete-tests')) {

                                // ON RECUPERE TOUS LES TESTS QUI ONT ETE COCHES
                                $testsCheckboxes = $formMassExec->get('test')->getData();

                                // SI LA LISTE DE TESTS COCHES N'EST PAS VIDE
                                if ($testsCheckboxes->count() > 0) {

                                    // POUR CHAQUE TEST ON LE REMOVE DE L'ENTITY MANAGER
                                    foreach ($testsCheckboxes as $test) {

                                        $entityManager->remove($test);
                                    }

                                    // ON ENVOIE LES REQUETES A LA BASE DE DONNEES
                                    $entityManager->flush();

                                    //ENSUITE ON REORDONNE LES TESTS DE LA CAMPAGNE : IL FAUT LES RE-RECUPERER DEPUIS LA BDD (DANS L'ORDRE)
                                    $tests =  $testRepository->findByCampagneWithJoins($campagne);

                                    // ON DECLARE UNE VARIABLE ORDRE
                                    $ordre = 0;

                                    // POUR CHAQUE TEST ON RE-AFFECTE SON ORDRE GRACE A LA VARIABLE ORDRE ET ON LE PERSIST DANS L'ENTITY MANAGER
                                    foreach ($tests as $test) {
                                        $ordre ++;
                                        $test->setOrdre($ordre);
                                        $entityManager->persist($test);
                                    }

                                    // ON ENVOIE LES REQUETES A LA BASE DE DONNEES
                                    $entityManager->flush();

                                    // ON INFORME L'UTILISATEUR
                                    $this->addFlash('success', 'Les tests ont bien été supprimés de la campagne');
                                }
                                // SI LA LISTE DES TESTS COCHES EST VIDE
                                else
                                {
                                    // ON INFORME L'UTILISATEUR
                                    $this->addFlash('warning', 'Aucun test sélectionné');
                                }

                                // ON RECHARGE LA PAGE
                                return $this->redirect($request->getUri());
                            }
                        }

                        // SI LE FORMULAIRE D'AJOUT D'UNE SELECTION DE TESTS A UN CATALOGUE EST ENVOYE ET VALIDE
                        if ($formAddCatalogue->isSubmitted() && $formAddCatalogue->isValid()) {

                            // ON VERIFIE QUE LE BOUTON SUMBIT QUI A ENVOYE LE FORMULAIRE CONTIENT L'ATTRIBUT name="add-catalogue"
                            if ($request->request->has('add-catalogue')) {

                                // ON RECUPERE LES INFORMATIONS DU FORMULAIRE
                                $catalogue = $formAddCatalogue->get('catalogue')->getData();
                                $templatesRelation = $catalogue->getTemplateCatalogueRelation();

                                // ON VERIFIE QUE LE CATALOGUE SELECTIONNE CONTIENT BIEN DES CAS DE TESTS
                                if ($templatesRelation->count() === 0) {
                                    // SI NON ON INFORME L'UTILISATEUR
                                    $this->addFlash('warning', 'Le catalogue sélectionné était vide');
                                }
                                // SI OUI ON EFFECTUE LE TRAITEMENT
                                else
                                {
                                    // D'ABORD ON RECUPERE L'ORDRE LE PLUS GRAND DANS LA CAMPAGNE (LA VALEUR DE LA VARIABLE ORDRE DU DERNIER TEST)
                                    try {
                                        $ordre = $testRepository->createPositionOfTest($id);

                                        if ($ordre === null) {

                                            $ordre = 0;

                                        } else {

                                            $ordre = $ordre['p'];
                                        }

                                    } catch (NonUniqueResultException $e) {
                                        $this->addFlash('error', 'Erreur à la récupération de la position dans la campagne');
                                    }

                                    // POUR CHAQUE CAS DE TEST DANS LE CATALOGUE
                                    foreach ($templatesRelation as $relation) {
                                        // ON AJOUTE 1 A LA VARIABLE ORDRE
                                        $ordre++;
                                        // ON CREE UN NOUVEAU TEST
                                        $nouveauTest = new Test();
                                        // ON ATTRIBUE LE CAS DE TEST DU CATALOGUE AU TEST
                                        $nouveauTest->setTemplate($relation->getTemplate());
                                        // ON AJOUTE LE NOUVEAU TEST A LA CAMPAGNE
                                        $nouveauTest->setCampagne($campagne);
                                        // ON RECUPERE L'ETAT A TESTER DU PROJET
                                        $etat = $etatsTestsService->getEtatATester($projet);
                                        // ON ATTRIBUE CET ETAT AU TEST
                                        $nouveauTest->setEtat($etat);
                                        // ON ATTRIBUE L'ORDRE AU TEST
                                        $nouveauTest->setOrdre($ordre);
                                        // ON PERSIST LE TEST DANS L'ENTITY MANAGER
                                        $entityManager->persist($nouveauTest);
                                    }

                                    // ON ENVOIE LES REQUETES EN BASE DE DONNEES
                                    $entityManager->flush();

                                    // ON INFORME L'UTILISATEUR QUE L'AJOUT S'EST BIEN PASSÉ
                                    $this->addFlash('success', 'Le catalogue a bien été ajouté à la campagne');
                                }

                                // ON RECHARGE LA PAGE
                                return $this->redirect($request->getUri());
                            }
                        }
                    }
                    // SI LA CAMPAGNE EST EN COURS
                    else if ($campagne->getEtat()->getIsEnCours()) {

                        // ON SET NULL LE FORMULAIRE D'AJOUT A UN CATALOGUE; IL N'EST PAS UTILISÉ DANS UNE CAMPAGNE EN COURS; IL NE CREERA DONC PAS DE BUG
                        $formAddCatalogue = null;

                        // SI LE FORMULAIRE D'EXECUTION MASSIVE DE TESTS EST ENVOYÉ ET VALIDÉ
                        if ($formMassExec->isSubmitted() && $formMassExec->isValid()) {

                            // ON VERIFIE QUE LE BOUTON SUBMIT QUI A ENVOYE LE FORMULAIRE CONTIENT L'ATTRIBUT name="exec-tests"
                            if ($request->request->has('exec-tests')) {

                                // ON RECUPERE LES INFOS DU FORMULAIRE
                                $testsCheckboxes = $formMassExec->get('test')->getData();
                                $etat = $formMassExec->get('etat')->getData();

                                // ON VERIFIE QUE LA LISTE DES TESTS COCHÉS N'EST PAS VIDE
                                if ($testsCheckboxes->count() > 0) {

                                    // POUR CHAQUE TEST
                                    foreach ($testsCheckboxes as $idTest) {

                                        // ON LE RECUPERE DEPUIS LA BDD
                                        $test = $testRepository->find($idTest);

                                        // ON LUI ATTRIBUT L'ETAT QUE L'UTILISATEUR A CHOISI DANS LE FORMULAIRE
                                        $test->setEtat($etat);
                                        // ON PERSIST LE TEST DANS L'ENTITY MANAGER
                                        $entityManager->persist($test);
                                    }

                                    // ON ENVOIE LES REQUETES A LA BASE DE DONNEES
                                    $entityManager->flush();

                                    // ON INFORME L'UTILISATEUR QUE L'ENREGISTREMENT C'EST BIEN EFFECTUÉ
                                    $this->addFlash('success', 'L\'enregistrement du résultat des tests est effectué');
                                }
                                // SI LISTE DES TESTS COCHÉS EST VIDE
                                else
                                {
                                    // ON INFORME L'UTILISATEUR
                                    $this->addFlash('warning', 'Aucun test sélectionné');
                                }

                                // ON RECHARGE LA PAGE
                                return $this->redirect($request->getUri());

                            }
                        }

                    }
                }

                // SI LA CAMPAGNE EST EN PREPARATION
                if ($campagne->getEtat()->getIsEnPrep()) {
                    return $this->render('campagne/detail.html.twig', [
                        // ON LUI ENVOIE TOUS LES FORMULAIRES
                        'formAddCatalogue' => $formAddCatalogue->createView(),
                        'formMassExec' => $formMassExec->createView(),
                        'projet' => $projet,
                        'campagne' => $campagne,
                        'tests' => $tests
                    ]);
                }
                // SI LA CAMPAGNE EST EN COURS
                else if ($campagne->getEtat()->getIsEnCours()) {
                    return $this->render('campagne/detail.html.twig', [
                        // ON NE LUI ENVOIE QUE LE FORMULAIRE D'EXECUTION DES TESTS
                        'formMassExec' => $formMassExec->createView(),
                        'projet' => $projet,
                        'campagne' => $campagne,
                        'tests' => $tests
                    ]);
                }
                // SI LA CAMPAGNE EST CLOTUREE
                else if ($campagne->getEtat()->getIsCloturee()) {
                    // ON NE LUI ENVOIE AUCUN FORMULAIRE
                    return $this->render('campagne/detail.html.twig', [
                        'projet' => $projet,
                        'campagne' => $campagne,
                        'tests' => $tests
                    ]);
                }

                // AUTREMENT ON RENVOIE LA PAGE DE DETAIL DE LA CAMPAGNE SANS AUCUN FORMULAIRE
                return $this->render('campagne/detail.html.twig', [
                    'projet' => $projet,
                    'campagne' => $campagne,
                    'tests' => $tests
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
     * Cette fonction permet d'ordonner les tests d'une campagne. Elle est appelée par du AJAX depuis le script dragAndDrop.js
     *
     * @Route("/{campagneId}/ordonner", name="ordonner")
     */
    public function ordonner(
        int                         $idProjet,
        int                         $campagneId,
        EntityManagerInterface      $entityManager,
        CampagneRepository          $campagneRepository,
        TestRepository              $testRepository,
        ProjetRepository            $projetRepository,
        DroitRepository             $droitRepository,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user
    ): Response
    {
        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'EDITCAMP']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagne = $campagneRepository->find($campagneId);

                if ($campagne) {
                    // ON RECUPERE LA LISTE D'IDS CREEE ET ENVOYEE DEPUIS LE FICHIER public/scripts/dragAndDrop.js
                    $ids = $_POST['ids'];
                    // ON CREE UN ARRAY GRÂCE A LA FONCTION EXPLODE DE PHP
                    $idsArr = explode(',', $ids);
                    // ON DECLARE UNE VARIABLE ITERATION
                    $iteration = 0;

                    //POUR CHAQUE ID DE L'ARRAY
                    foreach ($idsArr as $id) {
                        // ON AJOUTE 1 A LA VARIABLE ITERATION
                        $iteration ++;
                        // ON RECUPERE LE TEST DEPUIS LA BDD
                        $test = $testRepository->find($id); // todo : améliorer : récupérer les tests avant l'itération ?
                        // ON VERIFIE QUE LE TEST APPARTIENT BIEN A LA CAMPAGNE
                        if ($test->getCampagne() === $campagne) {
                            // ON ATTRIBUT LA VARIABLE ITERATION A L'ORDRE DU TEST
                            $test->setOrdre($iteration);
                            // ON PERSIST TOUT CELA DANS L'ENTITY MANAGER
                            $entityManager->persist($test);
                        }
                        // SI LE TEST N'APPARTIENT PAS A LA CAMPAGNE, ON RETOURNE UNE ERREUR
                        else
                        {
                            return $this->json(['error' => 'Une erreur est survenue']);
                        }
                    }
                    // ON ENVOIE LES REQUETES A LA BASE DE DONNEES APRES LA BOUCLE FOR
                    $entityManager->flush();

                    return $this->json(['ok' => true]);
                }
                else
                {
                    return $this->json(['error' => 'Une erreur est survenue lors de la récupération de la campagne']);
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
     * cette fonction permet de changer l'état d'une campagne. Elle vérifie que la campagne est bien à un état en préparation
     * avant de la passer en cours. La date de début réelle de la campagne est enregistrée.
     *
     * @Route("/{campagneId}/demarrer", name="demarrer")
     */
    public function demarrer(
        int                         $idProjet,
        int                         $campagneId,
        ProjetRepository            $projetRepository,
        CampagneRepository          $campagneRepository,
        DroitRepository             $droitRepository,
        EntityManagerInterface      $entityManager,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        EtatsCampagneService        $etatsCampagneService
    ): Response
    {
        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'ETATCAMP']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagne = $campagneRepository->find($campagneId);

                if ($campagne->getEtat()->getIsEnPrep()) {

                    if ($campagne->getTests()->count() > 0) {
                        $etat = $etatsCampagneService->getEtatEnCours($projet);

                        $campagne->setEtat($etat);
                        $campagne->setDateDebutReelle(new \DateTime);

                        $entityManager->persist($campagne);
                        $entityManager->flush();

                        $this->addFlash('success', 'La campagne a démarré. Exécutez vos tests dès maintenant.');

                        return $this->redirectToRoute('campagnes_detail', [
                            'id' => $campagneId,
                            'idProjet' => $idProjet
                        ]);
                    }
                    else {
                        $this->addFlash('danger', "La campagne ne peut pas démarrer, elle ne contient aucun test. Ajoutez au moins un test à la campagne pour la démarrer.");
                    }
                }
                else
                {
                    $this->addFlash('danger', "La campagne ne peut pas démarrer, elle n'est pas en préparation.");
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
     * cette fonction permet de changer l'état d'une campagne. Elle vérifie que la campagne est bien à un état en cours
     * avant de la passer cloturée. La date de fin réelle de la campagne est enregistrée.
     *
     * @Route("/{campagneId}/cloturer", name="cloturer")
     */
    public function cloturer(
        int                         $idProjet,
        int                         $campagneId,
        ProjetRepository            $projetRepository,
        CampagneRepository          $campagneRepository,
        DroitRepository             $droitRepository,
        EntityManagerInterface      $entityManager,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        CampagneDataService         $campagneDataService,
        EtatsCampagneService        $etatsCampagneService
    ): Response
    {
        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'ETATCAMP']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagne = $campagneRepository->find($campagneId);

                if ($campagne->getEtat()->getIsEnCours()) {

                    $etat = $etatsCampagneService->getEtatCloturee($projet);

                    $campagne->setEtat($etat);
                    $campagne->setDateFinReelle(new \DateTime);

                    $data = $campagneDataService->getData($campagne);

                    $campagne->setData($data);

                    $entityManager->persist($campagne);
                    $entityManager->flush();

                    $this->addFlash('success', 'La campagne s\'est terminée. Consultez ses résultats dès maintenant.');

                }
                else
                {
                    $this->addFlash('danger', "La campagne ne peut pas se clôturer, elle n'est pas en cours.");

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
     * cette fonction permet de supprimer une campagne.
     *
     * @Route("/{id}", name="supprimer", methods={"DELETE"})
     */
    public function supprimer(
        EntityManagerInterface      $entityManager,
        ProjetRepository            $projetRepository,
        CampagneRepository          $campagneRepository,
        DroitRepository             $droitRepository,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        int                         $idProjet,
        int                         $id
    ): JsonResponse {

        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'SUPPCAMP']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagne = $campagneRepository->findOneBy(['id' => $id, 'projet' => $projet]);

                if($campagne) {

                    if ($campagne->getEtat()->getIsEnCours()) {

                        $errorMsg = "La campagne est en cours, elle ne peut pas être supprimée pour le moment. Clôturez-la avant de la supprimer.";

                        return $this->json(['error' => $errorMsg]);
                    }
                    else
                    {
                        $entityManager->remove($campagne);
                        $entityManager->flush();

                        return $this->json(['ok' => true]);
                    }

                } else {
                    $errorMsg = "La campagne ciblée n'existe pas.";
                }
            } else {
                $errorMsg = "Vous ne disposez pas des droits suffisants pour supprimer cette campagne.";
            }
        } else {
            $errorMsg = "Ce projet n'existe pas.";
        }

        return $this->json(['error' => $errorMsg]);
    }



    /**
     * cette fonction permet d'exporter une campagne cloturee au format .xlsx
     *
     * @Route("/exportExcel/{id}", name="export_excel")
     */
    public function exportExcel(
        ProjetRepository            $projetRepository,
        CampagneRepository          $campagneRepository,
        DroitRepository             $droitRepository,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        int                         $idProjet,
        int                         $id,
        TestRepository              $testRepository,
        FileService                 $fileService
    ): Response {

        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'VOIRCAMP']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagne = $campagneRepository->findOneBy(['id' => $id, 'projet' => $projet]);

                if($campagne) {

                    $tests = $testRepository->findByCampagneWithJoins($campagne);

                    $spreadsheet = $fileService->makeCampagneXlsx($tests, $campagne);

                    $writer = new Xlsx($spreadsheet);

                    $fileName = 'campagneExport.xlsx';
                    $temp_file = tempnam(sys_get_temp_dir(), $fileName);
                    $writer->save($temp_file);

                    return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

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
     * cette fonction permet d'exporter une campagne cloturée au format csv
     *
     * @Route("/exportCsv/{id}", name="export_csv")
     */
    public function exportCsv(
        ProjetRepository            $projetRepository,
        CampagneRepository          $campagneRepository,
        DroitRepository             $droitRepository,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        int                         $idProjet,
        int                         $id,
        TestRepository              $testRepository,
        FileService                 $fileService
    ): Response {

        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'VOIRCAMP']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagne = $campagneRepository->findOneBy(['id' => $id, 'projet' => $projet]);

                if($campagne) {

                    $tests = $testRepository->findByCampagneWithJoins($campagne);

                    $spreadsheet = $fileService->makeCampagneCsv($tests);

                    $writer = new Csv($spreadsheet);
                    $writer->setDelimiter(';');
                    $writer->setEnclosure('"');
                    $writer->setLineEnding("\r\n");
                    $writer->setUseBOM(true);

                    $fileName = 'campagneExport.csv';
                    $temp_file = tempnam(sys_get_temp_dir(), $fileName);
                    $writer->save($temp_file);

                    return $this->file($temp_file, $fileName);

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
     * cette fonction permet d'exporter une campagne sous la forme d'un fichier au format csv. Seulement les tests KO de la campagne
     * seront exportés
     *
     * @Route("/exportCsvKO/{id}", name="export_csv_KO")
     */
    public function exportCsvKO(
        ProjetRepository            $projetRepository,
        CampagneRepository          $campagneRepository,
        DroitRepository             $droitRepository,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        int                         $idProjet,
        int                         $id,
        TestRepository              $testRepository,
        FileService                 $fileService
    ): Response {

        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'VOIRCAMP']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagne = $campagneRepository->findOneBy(['id' => $id, 'projet' => $projet]);

                if($campagne) {

                    $tests = $testRepository->findByCampagneWithJoins($campagne);

                    $spreadsheet = $fileService->makeCampagneKOCsv($tests);

                    $writer = new Csv($spreadsheet);
                    $writer->setDelimiter(';');
                    $writer->setEnclosure('"');
                    $writer->setLineEnding("\r\n");
                    $writer->setUseBOM(true);

                    $fileName = 'campagneKOExport.csv';
                    $temp_file = tempnam(sys_get_temp_dir(), $fileName);
                    $writer->save($temp_file);

                    return $this->file($temp_file, $fileName);

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
     * cette fonction permet d'afficher les statistiques d'une campagne en cours. Elle appelle le service CampagneDataService pour
     * calculer les statistiques de la campagne.
     *
     * @Route("/{campagneId}/voir-resultats", name="resultats")
     */
    public function resultats(
        int                         $idProjet,
        int                         $campagneId,
        ProjetRepository            $projetRepository,
        CampagneRepository          $campagneRepository,
        DroitRepository             $droitRepository,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        CampagneDataService         $campagneDataService
    ): Response
    {
        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'VOIRCAMP']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagne = $campagneRepository->find($campagneId);

                if ($campagne->getEtat()->getIsEnCours()) {

                    $data = $campagneDataService->getData($campagne);
                    return $this->render('campagne/results.html.twig', [
                        'data' => $data,
                        'projet' => $projet,
                        'campagne' => $campagne
                    ]);

                }
                else
                {
                    $this->addFlash('danger', "Vous ne pouvez pas calculer les résultats d'une campagne qui n'est pas en cours.");

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
     * Cette fonction permet de dupliquer une campagne
     *
     * @Route("/{campagneId}/dupliquer", name="dupliquer")
     */
    public function dupliquer(
        int                         $idProjet,
        int                         $campagneId,
        ProjetRepository            $projetRepository,
        CampagneRepository          $campagneRepository,
        DroitRepository             $droitRepository,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        Request                     $request,
        EtatsCampagneService        $etatsCampagneService,
        TestRepository              $testRepository,
        EtatsTestsService           $etatsTestsService,
        EntityManagerInterface      $entityManager
    ): Response
    {
        $projet = $projetRepository->findOneById($idProjet);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'EDITCAMP']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $campagne = $campagneRepository->find($campagneId);

                $newCamp = clone $campagne;

                $form = $this->createForm(CampagneType::class, $newCamp);

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {

                    $etat = $etatsCampagneService->getEtatEnPrep($projet);

                    $newCamp->setProjet($projet);
                    $newCamp->setEtat($etat);

                    $testsAncienneCamp = $testRepository->findByCampagneWithJoins($campagne);
                    $etatATester = $etatsTestsService->getEtatATester($projet);

                    foreach ($testsAncienneCamp as $ancienTest) {
                        $test = new Test();

                        if ($ancienTest->getTemplate() !== null) {
                            $test->setTemplate($ancienTest->getTemplate());
                        }

                        if ($ancienTest->getNom() !== null) {
                            $test->setNom($ancienTest->getNom());
                        }

                        if ($ancienTest->getDescription() !== null) {
                            $test->setDescription($ancienTest->getDescription());
                        }

                        $test->setEtat($etatATester);
                        $test->setOrdre($ancienTest->getOrdre());
                        $test->setCampagne($newCamp);
                        $entityManager->persist($test);
                        $newCamp->addTest($test);
                    }

                    $entityManager->persist($newCamp);
                    $entityManager->flush();

                    return $this->redirectToRoute('campagnes_liste', ['idProjet' => $idProjet]);

                }

                return $this->render('campagne/dupliquer.html.twig', [
                    'form' => $form->createView(),
                    'projet' => $projet,
                    'campagne' => $campagne
                ]);


            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé(e) à accéder à cette fonctionnalité.");
            }
        } else {
            $this->addFlash('warning', "Le projet auquel vous essayez d'accéder n'existe pas.");
        }

        return $this->redirectToRoute('projet_liste');
    }

}
