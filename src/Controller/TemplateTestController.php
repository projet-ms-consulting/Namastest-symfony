<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\TemplateCatalogueRelation;
use App\Entity\TemplateTest;
use App\Form\CommentaireType;
use App\Form\ImportTemplateTestType;
use App\Form\TemplateTestAndCatalogueChoiceBoxesType;
use App\Form\TemplateTestType;
use App\Repository\CatalogueRepository;
use App\Repository\DroitRepository;
use App\Repository\ProjetRepository;
use App\Repository\TemplateCatalogueRelationRepository;
use App\Repository\TemplateTestRepository;
use App\Repository\TestRepository;
use App\Service\AutorisationsProjetService;
use App\Service\FileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;


/**
 * @Route ("/projets/{id}/template-test", name="templates_test_")
 */
class TemplateTestController extends AbstractController
{

    // todo : user rôle conditions

    /**
     * @Route("/liste", name="liste")
     *
     * La fonction liste permet de lister l'ensemble des templates de tests.
     * Elle permet aussi d'ajouter un nombre infini de templates à un nombre infini de catalogues.
     * Elle permet aussi de supprimer un nombre infini de templates tant que ceux-ci ne sont pas insérer dans une campagne.
     * Elle permet d'importer des fichier XLS/CSV aves des cas de test
     * La liste est associée au fichier ApiController/ApiSearchbarCasDeTest.js
     * Ce fichier permet de regénérer la liste des cast de test lors d'une recherche avec la searchbar
     * Le traitement des données se fait en JSON pour l'affichage des cas de test
     */
    public function list(
        TemplateTestRepository              $templateTestRepository,
        ProjetRepository                    $projetRepository,
        AutorisationsProjetService          $autorisationsProjet,
        CatalogueRepository                 $catalogueRepository,
        DroitRepository                     $droitRepository,
        EntityManagerInterface              $entityManager,
        Request                             $request,
        UserInterface                       $user,
        TemplateCatalogueRelationRepository $templateCatalogueRelationRepository,
        FileService                         $fileService,
        int                                 $id
    ): Response
    {
        $projet = $projetRepository->findOneById($id);

        if ($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'VOIRTEMP']);

            if ($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $templates = $templateTestRepository->findTemplateByProject($projet);
                $catalogueChoiceForm = $this->createForm(TemplateTestAndCatalogueChoiceBoxesType::class, null, ['projet' => $projet]);

                $catalogueChoiceForm->handleRequest($request);

                $importForm = $this->createForm(ImportTemplateTestType::class);

                $importForm->handleRequest($request);

                //Importation d'un fichier excel avec nom et description d'un test
                if ($importForm->isSubmitted() && $importForm->isValid()) {


                    $fileData = $importForm->get('importTemplateTestFile')->getData();
                    if($fileData != null) {
                        $fileExtension = $importForm->get('importTemplateTestFile')->getData()->getClientOriginalExtension();

                        if ($fileExtension === 'csv') {

                            $readerCSV = new \PhpOffice\PhpSpreadsheet\Reader\Csv();

                            $spreadsheetCSV = $readerCSV->load($importForm->get('importTemplateTestFile')->getData());
                            $fileService->importTemplateTest($spreadsheetCSV, $entityManager, $templateTestRepository, $projet);
                            $this->addFlash('success', 'L\'importation du fichier s\'est déroulée correctement');

                        } elseif ($fileExtension === 'xlsx') {

                            $readerXLSX = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

                            $spreadsheetXLSX = $readerXLSX->load($importForm->get('importTemplateTestFile')->getData());
                            $fileService->importTemplateTest($spreadsheetXLSX, $entityManager, $templateTestRepository, $projet);
                            $this->addFlash('success', 'L\'importation du fichier s\'est déroulée correctement');

                        } else {
                            $this->addFlash('warning', 'Le fichier ne respecte pas le format du fichier type');
                        }

                    } else {
                        $this->addFlash('danger', 'Veuillez sélectionner un fichier');
                    }

                    return $this->redirect($request->getUri());

                    //} else {
                    //  $this->addFlash('danger', 'L\'importation du fichier a échoué');
                }

                if ($catalogueChoiceForm->isSubmitted() && $catalogueChoiceForm->isValid()) {
                    //Formulaire pour ajouter plusieurs tests d'un coup
                    if ($request->request->has('add-tests')) {

                        $templatesCheckboxes = $catalogueChoiceForm->get('test')->getData();
                        $catalogues = $catalogueChoiceForm->get('catalogue')->getData();

                        if ($templatesCheckboxes->count() > 0 && $catalogues->count() > 0) {
                            foreach ($catalogues as $idCatalogue) {
                                $catalogue = $catalogueRepository->find($idCatalogue);

                                $iteration = 0;
                                $ordre = 0;

                                foreach ($templatesCheckboxes as $idTemplate) {
                                    $template = $templateTestRepository->find($idTemplate);

                                    if ($iteration === 0) {
                                        $ordre = $templateCatalogueRelationRepository->createPositionOfTest($catalogue->getId());
                                        if ($ordre === null) {
                                            $ordre = 1;
                                        } else {
                                            $ordre = $ordre['p'] + 1;
                                        }
                                    }

                                    $relation = $template->getTemplateCatalogueRelation();

                                    $isContenu = false;

                                    foreach ($relation as $r) {

                                        if ($r->getCatalogue() === $catalogue) {
                                            $isContenu = true;
                                        }
                                    }

                                    if (!$isContenu) {

                                        $relation = new TemplateCatalogueRelation();
                                        $relation->setCatalogue($catalogue);
                                        $relation->setTemplate($template);
                                        $ordre++;
                                        $relation->setOrdre($ordre);
                                        $catalogue->addTemplateCatalogueRelation($relation);
                                        $entityManager->persist($catalogue);
                                        $this->addFlash('success', "Le cas de test " . ($template->getNom() . " a bien été ajouté au catalogue ") . $catalogue->getLibelle());
                                    } else {
                                        $this->addFlash('warning', "Le cas de test " . ($template->getNom() . " est déjà présent dans le catalogue ") . $catalogue->getLibelle());
                                    }

                                    $iteration++;
                                }
                            }
                            $entityManager->flush();
                        } else {
                            $this->addFlash('danger', "Veuillez sélectionner au minimum un cas de test et un catalogue");
                        }
                        // Vider le formulaire avant de le renvoyer
                        return $this->redirect($request->getUri());
                    }
                }

                if ($catalogueChoiceForm->isSubmitted() && $catalogueChoiceForm->isValid()) {
                    //Formulaire pour supprimer plusieurs tests d'un coup
                    if ($request->request->has('delete-tests')) {

                        $templatesCheckboxes = $catalogueChoiceForm->get('test')->getData();


                        if ($templatesCheckboxes->count() > 0) {
                            foreach ($templatesCheckboxes as $idTemplate) {
                                $template = $templateTestRepository->find($idTemplate);
                                if ($template->getTests()->count() > 0) {
                                    $this->addFlash('danger', 'Impossible de supprimer un cas de test' . $template->getNom() . ' utilisé dans une campagne');
                                } else {
                                    $entityManager->remove($template);
                                }
                            }
                        }
                        $entityManager->flush();

                        $templates = $templateTestRepository->findBy(['projet' => $projet], ['position' => 'ASC']);
                        $ordre = 0;

                        foreach ($templates as $test) {
                            $ordre++;
                            $test->setPosition($ordre);
                            $entityManager->persist($test);
                        }

                        $entityManager->flush();

                    } else {

                        $this->addFlash('danger', "Impossible de supprimer le/les cas de test(s)");
                    }
                    // Vider le formulaire avant de le renvoyer
                    return $this->redirect($request->getUri());
                }
                return $this->render('template_test/liste.html.twig', [
                    'templateTest' => $templates,
                    'projet' => $projet,
                    'cataloguesForm' => $catalogueChoiceForm->createView(),
                    'importForm' => $importForm->createView(),
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
     * Cette fonction permet de créer un cas de test
     * @Route("/creer", name="creer")
     */
    public function creer(
        EntityManagerInterface     $entityManager,
        ProjetRepository           $projetRepository,
        TemplateTestRepository     $templateTestRepository,
        DroitRepository            $droitRepository,
        AutorisationsProjetService $autorisationsProjet,
        UserInterface              $user,
        Request                    $request,
        int                        $id

    ): Response
    {
        $projet = $projetRepository->findOneById($id);

        if ($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'CREETEMP']);

            if ($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $templateTest = new TemplateTest();
                $templateTestForm = $this->createForm(TemplateTestType::class, $templateTest);
                $templateTestForm->handleRequest($request);

                if ($templateTestForm->isSubmitted() && $templateTestForm->isValid()) {

                    $templateTest->setProjet($projet);
                    $templateTest->setDateCreation(new \DateTime('now'));
                    $templateTest->setVersion(1);
                    //Récupérer la position du dernier test
                    $position = $templateTestRepository->createPositionOfTest($id);
                    if ($position === null) {
                        $newMaxPosition = 1;
                    } else {
                        $newMaxPosition = $position['p'] + 1;
                    }
                    $templateTest->setPosition($newMaxPosition);

                    $entityManager->persist($templateTest);
                    $entityManager->flush();

                    $this->addFlash('success', 'Le cas de test a été créé');

                    return $this->redirectToRoute('templates_test_liste', ['id' => $id]);
                }

                return $this->render('template_test/creer.html.twig', [
                    'formulaireTemplateTest' => $templateTestForm->createView(),
                    'projet' => $projet
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
     * Cette fonction permet de voir le détail un cas de test
     * @Route("/detail/{idDetail}", name="detail")
     */
    public function detail(
        TemplateTestRepository     $templateTestRepository,
        EntityManagerInterface     $entityManager,
        ProjetRepository           $projetRepository,
        DroitRepository            $droitRepository,
        AutorisationsProjetService $autorisationsProjet,
        UserInterface              $user,
        Request                    $request,
        int                        $id,
        int                        $idDetail
    ): Response
    {
        $projet = $projetRepository->findOneById($id);
        $commentaire = new Commentaire();
        $commentaire->setDateCreation(new \DateTime());
        $commentaire->setAuteur($this->getUser());

        $formCommentaire = $this->createForm(CommentaireType::class, $commentaire);
        $formCommentaire->handleRequest($request);



        if ($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'VOIRTEMP']);

            if ($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $test = $templateTestRepository->find($idDetail);
                $commentaire->setTemplateTest($test);

                if( $formCommentaire->isSubmitted() && $formCommentaire->isValid()){

                    $entityManager->persist($commentaire);
                    $entityManager->flush();

                    return $this->redirectToRoute('templates_test_detail', ['idDetail' => $idDetail, 'id' => $id]);
                }

                if ($test) {

                    $commentaires = $test->getCommentaires();

                    return $this->render('template_test/detail.html.twig', [
                        'test' => $test,
                        'projet' => $projet,
                        'formCommentaire' => $formCommentaire->createView(),
                        'commentaires' => $commentaires
                    ]);
                } else {
                    $this->addFlash('warning', "Le cas de test auquel vous essayez d'accéder n'existe pas.");
                    return $this->redirectToRoute('templates_test_liste', ['id' => $id]);
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
     * Cette fonction permet de modifier un cas de test
     * @Route("/update/{idUpdate}", name="update")
     */
    public function update(
        EntityManagerInterface     $entityManager,
        templateTestRepository     $templateTestRepository,
        ProjetRepository           $projetRepository,
        DroitRepository            $droitRepository,
        AutorisationsProjetService $autorisationsProjet,
        UserInterface              $user,
        Request                    $request,
        int                        $id,
        int                        $idUpdate): Response
    {

        $projet = $projetRepository->findOneById($id);

        if ($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'EDITTEMP']);

            if ($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $test = $templateTestRepository->find($idUpdate);
                $nomInitial=$test->getNom();
                $templateTestForm = $this->createForm(TemplateTestType::class, $test);
                $templateTestForm->handleRequest($request);

                if ($test) {

                    if ($templateTestForm->isSubmitted() && $templateTestForm->isValid()) {

                        $nouveauNom=$test->getNom();
                        $testVerif=$templateTestRepository->findOneBy(['nom'=> $nouveauNom]);


                        if($testVerif && $testVerif->getProjet()->getAuteur()== $this->getUser() && $nomInitial !== $nouveauNom){

                            $this->addFlash('danger', 'Un cas de test de ce nom existe déjà');
                            return $this->redirectToRoute('templates_test_update', ['id' => $id, 'idUpdate'=> $idUpdate]);
                        }else{
                            $entityManager->persist($test);
                            $entityManager->flush();
                            $this->addFlash('success', 'Le cas de test a bien été modifié');
                            return $this->redirectToRoute('templates_test_detail', ['id' => $id, 'idDetail' => $idUpdate]);
                        }

                    } else {

                        return $this->render('template_test/update.html.twig', [
                            'templatesTestForm' => $templateTestForm->createView(),
                            'projet' => $projet,
                        ]);
                    }

                } else {
                    $this->addFlash('warning', "Le cas test auquel vous essayez d'accéder n'existe pas.");
                    return $this->redirectToRoute('templates_test_liste', ['id' => $id]);
                }


            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé(e) à accéder à cette fonctionnalité.");
                return $this->redirectToRoute('templates_test_liste', ['id' => $id]);
            }

        } else {
            $this->addFlash('warning', "Le projet auquel vous essayez d'accéder n'existe pas.");
        }

        return $this->redirectToRoute('projet_liste');
    }


    /**
     * Cette fonction permet de supprimer un cas de test
     * @Route("/supprimer/{idDelete}", name="delete", methods={"DELETE"})
     */
    public function delete(
        EntityManagerInterface     $entityManager,
        TemplateTestRepository     $templateTestRepository,
        DroitRepository            $droitRepository,
        AutorisationsProjetService $autorisationsProjet,
        ProjetRepository           $projetRepository,
        UserInterface              $user,
        int                        $idDelete,
        int                        $id
    ): JsonResponse
    {
        $projet = $projetRepository->findOneById($id);

        if ($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'SUPPTEMP']);

            if ($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $templateTest = $templateTestRepository->find($idDelete);

                if ($templateTest) {
                    if ($templateTest->getTests()->count() > 0) {
                        $this->addFlash('danger', 'Impossible de supprimer le cas de test ' . $templateTest->getNom() . ' utilisé dans une campagne');
                    } else {
                        $entityManager->remove($templateTest);
                    }

                    $entityManager->flush();
                    $templates = $templateTestRepository->findBy(['projet' => $projet], ['position' => 'ASC']);
                    $ordre = 0;

                    foreach ($templates as $test) {
                        $ordre++;
                        $test->setPosition($ordre);
                        $entityManager->persist($test);
                    }

                    $entityManager->flush();
                    return $this->json(['ok' => true]);
                } else {
                    $errorMsg = "Le cas de test ciblé n'existe pas.";
                }
            } else {
                $errorMsg = "Vous ne disposez pas des droits suffisants pour supprimer ce test.";
            }
        } else {
            $errorMsg = "Ce projet n'existe pas.";
        }

        return $this->json(['error' => $errorMsg]);
    }


    /**
     * Cette fonction permet d'ordonner la liste des cas de tests. Appelée par de l'AJAX depuis le script dragAndDrop.js
     *
     * @Route("/ordonner", name="ordonner")
     */
    public function ordonner(
        EntityManagerInterface $entityManager,
        TemplateTestRepository $testRepository
    ): Response
    {
        $ids = $_POST['ids'];
        $idsArr = explode(',', $ids);
        $iteration = 0;
        foreach ($idsArr as $id) {

            $iteration++;
            $test = $testRepository->find($id); // todo : améliorer : récupérer les tests avant l'itération ?
            $test->setPosition($iteration);
            $entityManager->persist($test);
        }

        $entityManager->flush();

        return $this->json(['']);
    }


    /**
     * cette fonction permet d'exporter un fichier type au format .xlsx
     *
     * @Route("/exportExcelXLSX", name="export_excel_xlsx")
     */
    public function exportExcelXLSX(
        ProjetRepository            $projetRepository,
        DroitRepository             $droitRepository,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        int                         $id
    ): Response
    {
        $projet = $projetRepository->findOneById($id);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'VOIRTEMP']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $response = new BinaryFileResponse('ExcelFiles/FichierTypeXLSX.xlsx');
                $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,'FichierTypeXLSX.xlsx');

            } else {
                $this->addFlash('danger','Vous n\'avez pas les droits nécessaire pour effectuer cette action.');
            }
        } else {
            $this->addFlash('danger','Ce projet n\'existe pas.');
        }
        return $response;
    }

    /**
     * cette fonction permet d'exporter un fichier type au format .csv
     *
     * @Route("/exportExcelCSV", name="export_excel_csv")
     */
    public function exportExcelCSV(
        ProjetRepository            $projetRepository,
        DroitRepository             $droitRepository,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        int                         $id
    ): Response
    {
        $projet = $projetRepository->findOneById($id);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'VOIRTEMP']);

            if($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $response = new BinaryFileResponse('ExcelFiles/FichierTypeCSV.csv');
                $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,'FichierTypeCSV.csv');
            } else {
                $this->addFlash('danger','Vous n\'avez pas les droits nécessaire pour effectuer cette action.');
            }
        } else {
            $this->addFlash('danger','Ce projet n\'existe pas.');
        }
        return $response;
    }

    /**
     * @Route ("/copier/{test}", name="copier")
     */
    public function copierTest($test, EntityManagerInterface $entityManager, TemplateTestRepository $templateTestRepository){
        $testACopier=$templateTestRepository->find($test);
        $testCopie= new TemplateTest();
        $nbCopies=$templateTestRepository->findMaxCopies($testACopier->getNom())+1;
        $testCopie->setNom($testACopier->getNom().' Copie '.$nbCopies);
        $testCopie->setDescription($testACopier->getDescription());
        $testCopie->setDateCreation(new \DateTime());
        $testCopie->setProjet($testACopier->getProjet());
        $testCopie->setVersion($testACopier->getVersion());
        $testCopie->setPosition($templateTestRepository->createPositionOfTest($testCopie->getProjet()->getId())[0]->getPosition()+1);

        $entityManager->persist($testCopie);
        $entityManager->flush();

        $this->addFlash('succes', 'Cas de test '. $testACopier->getNom().' a bien été copié');

        if(isset($_GET['modifier'])){

            return $this->redirectToRoute('templates_test_update', ['idUpdate'=> $testCopie->getId(),'id'=>$testACopier->getProjet()->getId()]);
        }else{
            return $this->redirectToRoute('templates_test_liste', ['id'=>$testACopier->getProjet()->getId()]);
        }
    }

    /**
     * @Route("/effacer/{testId}", name="effacer")
     */

    public function effacerTest($testId, EntityManagerInterface $entityManager, TemplateTestRepository $templateTestRepository){
        $test=$templateTestRepository->find($testId);
        $projet=$test->getProjet();


        $entityManager->remove($test);
        $entityManager->flush();


        return $this->redirectToRoute('templates_test_liste', ['id'=>$projet->getId()]);

    }

}
