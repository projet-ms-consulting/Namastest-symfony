<?php

namespace App\Controller;

use App\Entity\Catalogue;
use App\Entity\TemplateCatalogueRelation;
use App\Entity\TemplateTest;
use App\Entity\Test;
use App\Form\CampagneChoiceType;
use App\Form\CatalogueType;
use App\Form\TemplateTestType;
use App\Repository\CatalogueRepository;
use App\Repository\DroitRepository;
use App\Repository\ProjetRepository;
use App\Repository\TemplateCatalogueRelationRepository;
use App\Repository\TemplateTestRepository;
use App\Repository\TestRepository;
use App\Service\AutorisationsProjetService;
use App\Service\EtatsCampagneService;
use App\Service\EtatsTestsService;
use App\Service\FileService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\Reader\Word2007;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

/**
 * @Route("/projets/{projetId}/catalogues", name="catalogue_")
 */
class CatalogueController extends AbstractController
{
    /**
     * Cette fonction permet d'afficher la liste des catalogues.
     *
     * @Route("", name="liste")
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    public function liste(
        ProjetRepository           $projetRepository,
        CatalogueRepository        $catalogueRepository,
        DroitRepository            $droitRepository,
        AutorisationsProjetService $autorisationsProjet,
        UserInterface              $user,
                                   $projetId,
        Request $request,
        FileService $fileService,
        EntityManagerInterface $entityManager): Response
    {
        $projet = $projetRepository->findOneById($projetId);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'VOIRCATA']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $catalogues = $catalogueRepository->findBy(['projet' => $projet]);
                $catalogueImport=new Catalogue();

                $importForm=$this->createForm(CatalogueType::class, $catalogueImport );
                $importForm->handleRequest($request);


                if($importForm->isSubmitted()){


                    $fileData = $importForm->get('importCatalogue')->getData();

                    if($fileData !== null) {

                        $fileExtension = $fileData->getClientOriginalExtension();
                        $filename=pathinfo($fileData->getClientOriginalName().'.'.$fileExtension,PATHINFO_FILENAME);

                        if ($fileExtension === 'doc') {
                                $fileData->move($this->getParameter('wordfiles_directory'), $filename);

                                $fileHandle = fopen($this->getParameter('wordfiles_directory').'/'.$filename, "r");
                                $line = @fread($fileHandle, filesize($this->getParameter('wordfiles_directory').'/'.$filename));
                                $lines = explode(chr(0x0D),$line);
                                $outtext = "";
                                foreach($lines as $thisline)
                                {
                                    $pos = strpos($thisline, chr(0x00));
                                    if (($pos !== FALSE)||(strlen($thisline)==0))
                                    {

                                    } else {
                                        $outtext .= $thisline." ";
                                    }
                                }
                                #$outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);
                                dd($lines);

                            $this->addFlash('success', 'L\'importation du fichier s\'est déroulée correctement');
                        }
                    }
                }


                return $this->render('catalogue/liste.html.twig', [
                    'projet' => $projet,
                    'catalogues' => $catalogues,
                    'importForm' => $importForm->createView()

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
     * Cette fonction permet de créer un catalogue
     *
     * @Route("/nouveau", name="create")
     */
    public function create(
        EntityManagerInterface     $entityManager,
        ProjetRepository           $projetRepository,
        DroitRepository            $droitRepository,
        AutorisationsProjetService $autorisationsProjet,
        UserInterface              $user,
        Request                    $request,
                                   $projetId): Response {

        $projet = $projetRepository->findOneById($projetId);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'CREECATA']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $catalogue = new Catalogue();
                $catalogue->setProjet($projet);

                $form = $this->createForm(CatalogueType::class, $catalogue);
                $form->handleRequest($request);

                if($form->isSubmitted() && $form->isValid()) {

                    $entityManager->persist($catalogue);
                    $entityManager->flush();

                    return $this->redirectToRoute('catalogue_liste', ['projetId' => $projet->getId()]);
                }

                return $this->render('catalogue/create.html.twig', [
                    'projet' => $projet,
                    'formCatalogue' => $form->createView()
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
     * Cette fonction permet d'afficher les détails d'un catalogue. Elle permet également d'ajouter les tests d'un catalogue à une campagne
     * ou de supprimer les tests d'un catalogue
     *
     * @Route("/{id}", name="details", methods={"GET", "POST"})
     */
    public function details(
        ProjetRepository                    $projetRepository,
        CatalogueRepository                 $catalogueRepository,
        DroitRepository                     $droitRepository,
        AutorisationsProjetService          $autorisationsProjet,
        UserInterface                       $user,
                                            $projetId,
                                            $id,
        Request                             $request,
        EntityManagerInterface              $entityManager,
        TestRepository                      $testRepository,
        TemplateCatalogueRelationRepository $relationRepository,
        EtatsCampagneService                $etatsCampagneService,
        EtatsTestsService                   $etatsTestsService
    ) {

        $projet = $projetRepository->findOneById($projetId);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'VOIRCATA']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $catalogue = $catalogueRepository->findCatalogueWithJoinsOrdered($projetId, $id);

                if($catalogue) {
                    $etat =  $etatsCampagneService->getEtatEnPrep($projet);
                    $formAddToCampagne = $this->createForm(CampagneChoiceType::class, null, ['projet' => $projetId, 'catalogue' => $id, 'etat' => $etat->getLibelle()]);
                    $formAddToCampagne->handleRequest($request);

                    if ($formAddToCampagne->isSubmitted() && $formAddToCampagne->isValid())
                    {
                        $templatesCheckboxes = $formAddToCampagne->get('test')->getData();
                        $campagne = $formAddToCampagne->get('campagne')->getData();

                        if ($request->request->has('add-to-campagne')) {

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
                            }
                            catch (NonUniqueResultException $e)
                            {
                                $this->addFlash('error', 'Erreur à la récupération de la position dans la campagne');
                            }

                            //REORDONNER LA LISTE RECUPEREE DES CHECKBOXES PAR ORDRE
                            $iterator = $templatesCheckboxes->getIterator();

                            $iterator->uasort(function ($a, $b) use ($catalogue) {
                                $relationsA = $a->getTemplateCatalogueRelation();
                                $ordreA = 0;
                                foreach ($relationsA as $relationA) {
                                    if ($relationA->getCatalogue() === $catalogue)
                                        $ordreA = $relationA->getOrdre();
                                }

                                $relationsB = $b->getTemplateCatalogueRelation();
                                $ordreB = 0;
                                foreach ($relationsB as $relationB) {
                                    if ($relationB->getCatalogue() === $catalogue)
                                        $ordreB = $relationB->getOrdre();
                                }

                                return ($ordreA < $ordreB) ? -1 : 1;
                            });

                            $templatesCheckboxes2 = new ArrayCollection(iterator_to_array($iterator));

                            $etat = $etatsTestsService->getEtatATester($projet);

                            foreach ($templatesCheckboxes2 as $template) {
                                $ordre ++;
                                $nouveauTest = new Test();
                                $nouveauTest->setTemplate($template);
                                $nouveauTest->setCampagne($campagne);
                                $nouveauTest->setEtat($etat);
                                $nouveauTest->setOrdre($ordre);
                                $entityManager->persist($nouveauTest);

                                $this->addFlash('success', "le template " . ($template->getNom() . " a bien été ajouté à la campagne ") . $campagne->getNom());
                            }

                            $entityManager->flush();
                        }
                        else if ($request->request->has('remove-tests')) {

                            foreach ($templatesCheckboxes as $template) {
                                $relations = $template->getTemplateCatalogueRelation();

                                foreach ($relations as $relation) {
                                    if ($relation->getCatalogue() === $catalogue) {
                                        $entityManager->remove($relation);
                                    }
                                }

                                $this->addFlash('success', "le template " . $template->getNom() . " a bien été retiré du catalogue. Il existe toujours dans la liste des templates");
                            }

                            $entityManager->flush();

                            $tests = $relationRepository->findBy(['catalogue' => $catalogue], ['ordre' => 'ASC']);
                            $ordre = 0;
                            foreach ($tests as $test) {
                                $ordre++;
                                $test->setOrdre($ordre);
                                $entityManager->persist($test);
                            }

                            $entityManager->flush();
                        }
                    }

                    return $this->render('catalogue/details.html.twig', [
                        'projet' => $projet,
                        'catalogue' => $catalogue,
                        'formAddToCampagne' => $formAddToCampagne->createView()
                    ]);

                } else {
                    $this->addFlash('warning', "Le catalogue auquel vous essayez d'accéder n'existe pas.");
                    return $this->redirectToRoute('catalogue_liste', ['projetId' => $projetId]);
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
     * Cette fonction permet de supprimer un catalogue
     *
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        EntityManagerInterface     $entityManager,
        ProjetRepository           $projetRepository,
        CatalogueRepository        $catalogueRepository,
        DroitRepository            $droitRepository,
        AutorisationsProjetService $autorisationsProjet,
        UserInterface              $user,
                                   $projetId, $id): JsonResponse {

        $projet = $projetRepository->findOneById($projetId);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'SUPPCATA']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $catalogue = $catalogueRepository->findOneBy(['id' => $id, 'projet' => $projet]);

                if($catalogue) {
                    $entityManager->remove($catalogue);
                    $entityManager->flush();

                    return $this->json(['ok' => true]);
                } else {
                    $errorMsg = "Le catalogue ciblé n'existe pas.";
                }
            } else {
                $errorMsg = "Vous ne disposez pas des droits suffisants pour supprimer ce catalogue.";
            }
        } else {
            $errorMsg = "Ce projet n'existe pas.";
        }

        return $this->json(['error' => $errorMsg]);
    }


    /**
     * cette fonction permet de supprimer un test d'un catalogue
     *
     * @Route("/{id}/{idDelete}", name="delete_test", methods={"DELETE"})
     */
    public function deleteTestFromCatalogue(
        EntityManagerInterface              $entityManager,
        ProjetRepository                    $projetRepository,
        CatalogueRepository                 $catalogueRepository,
        TemplateTestRepository              $templateTestRepository,
        DroitRepository                     $droitRepository,
        AutorisationsProjetService          $autorisationsProjet,
        UserInterface                       $user,
                                            $projetId, $id, $idDelete,
        TemplateCatalogueRelationRepository $relationRepository
    ) : Response
    {

        $projet = $projetRepository->findOneById($projetId);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'EDITCATA']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $catalogue = $catalogueRepository->findOneBy(['id' => $id]);
                $test = $templateTestRepository->find($idDelete);

                if($catalogue && $test) {
                    $relation = $catalogue->getTemplateCatalogueRelation();
                    foreach ($relation as $r) {
                        if ($r->getTemplate() === $test) {
                            $entityManager->remove($r);
                        }
                    }

                    $entityManager->flush();

                    $tests = $relationRepository->findBy(['catalogue' => $catalogue], ['ordre' => 'ASC']);
                    $ordre = 0;
                    foreach ($tests as $test) {
                        $ordre++;
                        $test->setOrdre($ordre);
                        $entityManager->persist($test);
                    }

                    $entityManager->flush();

                    return $this->json(['ok' => true]);

                } else {
                    $errorMsg = "Le catalogue ciblé n'existe pas.";
                }
            } else {
                $errorMsg = "Vous ne disposez pas des droits suffisants pour supprimer ce catalogue.";
            }
        } else {
            $errorMsg = "Ce projet n'existe pas.";
        }

        return $this->json(['error' => $errorMsg]);
    }

    /**
     * Cette fonction permet d'ordonner les tests d'un catalogue. Elle est appellée par de l'AJAX provenant du fichier dragAndDrop.js
     *
     * @Route("/{catalogueId}/ordonner", name="ordonner")
     */
    public function ordonner(
        int                         $projetId,
        int                         $catalogueId,
        EntityManagerInterface      $entityManager,
        TemplateTestRepository      $testRepository,
        ProjetRepository            $projetRepository,
        DroitRepository             $droitRepository,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        CatalogueRepository         $catalogueRepository
    ): Response
    {
        $projet = $projetRepository->findOneById($projetId);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'EDITCATA']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $catalogue = $catalogueRepository->find($catalogueId);

                $ids = $_POST['ids'];
                $idsArr = explode(',', $ids);
                $iteration = 0;
                foreach ($idsArr as $id) {

                    $iteration ++;
                    $test = $testRepository->find($id);
                    $relations = $test->getTemplateCatalogueRelation();

                    foreach ($relations as $r) {
                        if ($r->getCatalogue() === $catalogue) {
                            $r->setOrdre($iteration);
                            $entityManager->persist($r);
                        }
                    }
                }

                $entityManager->flush();

                return $this->json(['ok' => true]);

            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé(e) à accéder à cette fonctionnalité.");
            }
        } else {
            $this->addFlash('warning', "Le projet auquel vous essayez d'accéder n'existe pas.");
        }

        return $this->redirectToRoute('projet_liste');
    }

    /**
     * Cette fonction permet de modifier un catalogue
     *
     * @Route("/{catalogueId}/modifier", name="modifier")
     */
    public function modifier(
        int                         $projetId,
        int                         $catalogueId,
        EntityManagerInterface      $entityManager,
        ProjetRepository            $projetRepository,
        DroitRepository             $droitRepository,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        CatalogueRepository         $catalogueRepository,
        Request                     $request
    ): Response
    {
        $projet = $projetRepository->findOneById($projetId);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'EDITCATA']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $catalogue = $catalogueRepository->find($catalogueId);

                if ($catalogue) {

                    $formCatalogue = $this->createForm(CatalogueType::class, $catalogue);
                    $formCatalogue->handleRequest($request);

                    if ($formCatalogue->isSubmitted() && $formCatalogue->isValid()) {

                        $entityManager->persist($catalogue);
                        $entityManager->flush();

                        $this->addFlash('success', 'Le libellé du catalogue a bien été modifié');

                        return $this->redirectToRoute('catalogue_liste', ['projetId' => $projetId]);
                    }

                    return $this->render('catalogue/update.html.twig', [
                        'catalogue' => $catalogue,
                        'projet' => $projet,
                        'formCatalogue' => $formCatalogue->createView()
                    ]);


                } else {
                    $this->addFlash('warning', "Le catalogue auquel vous essayez d'accéder n'existe pas.");
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
     * Cette fonction permet de créer un test dans un catalogue
     *
     * @Route("/{catalogueId}/creer-test", name="creer_test")
     */
    public function creerTest(
        int                                 $projetId,
        int                                 $catalogueId,
        EntityManagerInterface              $entityManager,
        TemplateTestRepository              $testRepository,
        ProjetRepository                    $projetRepository,
        DroitRepository                     $droitRepository,
        AutorisationsProjetService          $autorisationsProjet,
        UserInterface                       $user,
        TemplateCatalogueRelationRepository $relationRepository,
        CatalogueRepository                 $catalogueRepository,
        Request                             $request
    ): Response
    {
        $projet = $projetRepository->findOneById($projetId);

        if($projet) {
            $droit1 = $droitRepository->findOneBy(['code' => 'CREETEMP']);
            $droit2 = $droitRepository->findOneBy(['code' => 'EDITCATA']);

            if($autorisationsProjet->canAccess($projet, $user, $droit1)
                && $autorisationsProjet->canAccess($projet, $user, $droit2)) {

                $catalogue = $catalogueRepository->find($catalogueId);

                if ($catalogue) {


                    $templateTest = new TemplateTest();
                    $templateTestForm = $this->createForm(TemplateTestType::class, $templateTest);
                    $templateTestForm->handleRequest($request);

                    if ($templateTestForm->isSubmitted() && $templateTestForm->isValid()) {

                        $templateTest->setProjet($projet);
                        $templateTest->setDateCreation(new \DateTime('now'));
                        $templateTest->setVersion(1);

                        //Ordre dans la liste des tests
                        $position = $testRepository->createPositionOfTest($projetId);
                        if ($position === null) {
                            $newMaxPosition = 1;
                        } else {
                            $newMaxPosition = $position['p'] + 1;
                        }
                        $templateTest->setPosition($newMaxPosition);

                        //Ordre dans un catalogue
                        $ordre = $relationRepository->createPositionOfTest($catalogueId);

                        if ($ordre === null) {
                            $newMaxOrdre = 1;
                        } else {
                            $newMaxOrdre = $ordre['p'] + 1;
                        }

                        $relation = new TemplateCatalogueRelation;
                        $relation->setOrdre($newMaxOrdre);
                        $relation->setCatalogue($catalogue);
                        $relation->setTemplate($templateTest);

                        $entityManager->persist($relation);
                        $entityManager->persist($templateTest);
                        $entityManager->flush();

                        $this->addFlash('success', 'Le cas de test a été créé dans le catalogue '. $catalogue->getLibelle());

                        return $this->redirectToRoute('catalogue_details', ['projetId' => $projetId, 'id' => $catalogueId]);
                    }

                    return $this->render('template_test/creer.html.twig', [
                        'formulaireTemplateTest' => $templateTestForm->createView(),
                        'projet' => $projet
                    ]);


                } else {
                    $this->addFlash('warning', "Le catalogue auquel vous essayez d'accéder n'existe pas.");
                }

            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé(e) à accéder à cette fonctionnalité.");
            }
        } else {
            $this->addFlash('warning', "Le projet auquel vous essayez d'accéder n'existe pas.");
        }

        return $this->redirectToRoute('projet_liste');
    }


}


