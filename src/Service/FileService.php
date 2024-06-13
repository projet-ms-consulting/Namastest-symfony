<?php

namespace App\Service;

use App\Entity\Campagne;
use App\Entity\Catalogue;
use App\Entity\Projet;
use App\Entity\TemplateCatalogueRelation;
use App\Entity\TemplateTest;
use App\Entity\Test;
use App\Repository\CampagneRepository;
use App\Repository\CatalogueRepository;
use App\Repository\TemplateTestRepository;
use App\Repository\TestRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FileService
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session=$session;
    }

    /**
     * @param $tests
     * @return Spreadsheet
     * @throws Exception
     *
     * Cette fonction permet de remplir une Spreadsheet avec les infos des tests de la campagne
     */
    public function makeCampagneCsv($tests) : Spreadsheet {

        $spreadsheet = new Spreadsheet();

        $worksheetTests = $spreadsheet->getActiveSheet();
        $worksheetTests->setTitle("Tests de la campagne");

        $ligne = 1;

        $worksheetTests->getCell('A' . $ligne)->setValue('Ordre');
        $worksheetTests->getCell('B' . $ligne)->setValue('Nom');
        $worksheetTests->getCell('C' . $ligne)->setValue('Description');
        $worksheetTests->getCell('D' . $ligne)->setValue('Résultat');
        $worksheetTests->getCell('E' . $ligne)->setValue('Précisions sur le résultat');

        foreach ($tests as $test) {
            $ligne++;
            if ($ligne > 1) {
                $worksheetTests->getCell('A' . $ligne)->setValue($test->getOrdre());
                $worksheetTests->getCell('B' . $ligne)->setValue(($test->getNom() === null ? $test->getTemplate()->getNom() : ($test->getTemplate() === null ? $test->getNom() : $test->getNom() . ' / ' . $test->getTemplate()->getNom())));
                $worksheetTests->getCell('C' . $ligne)->setValue(($test->getDescription() === null ? $test->getTemplate()->getDescription() : ($test->getTemplate() === null ? $test->getDescription() : $test->getDescription() . ' / ' . $test->getTemplate()->getDescription())));
                $worksheetTests->getCell('D' . $ligne)->setValue($test->getEtat()->getLibelle());
                $worksheetTests->getCell('E' . $ligne)->setValue(($test->getPrecisionsResultat() === null ? '' : $test->getPrecisionsResultat()));
            }
        }

        $worksheetTests->getColumnDimension('A')->setAutoSize(true);
        $worksheetTests->getColumnDimension('B')->setAutoSize(true);
        $worksheetTests->getColumnDimension('C')->setAutoSize(true);
        $worksheetTests->getColumnDimension('D')->setAutoSize(true);
        $worksheetTests->getColumnDimension('E')->setAutoSize(true);

        return $spreadsheet;
    }

    /**
     * @param $tests
     * @param $campagne
     * @return Spreadsheet
     * @throws Exception
     *
     * Cette fonction permet de remplir une Spreadsheet avec les infos des résultats de la campagne ainsi que les tests de la campagne (deux sheets différentes sur le fichier excel)
     */
    public function makeCampagneXlsx($tests, $campagne) : Spreadsheet {

        $spreadsheet = new Spreadsheet();

        $worksheetTests = $spreadsheet->getActiveSheet();
        $worksheetTests->setTitle("Tests de la campagne");

        $ligne = 1;

        $worksheetTests->getCell('A' . $ligne)->setValue('Ordre');
        $worksheetTests->getCell('B' . $ligne)->setValue('Nom');
        $worksheetTests->getCell('C' . $ligne)->setValue('Description');
        $worksheetTests->getCell('D' . $ligne)->setValue('Nom du parent');
        $worksheetTests->getCell('E' . $ligne)->setValue('Description du parent');
        $worksheetTests->getCell('F' . $ligne)->setValue('Résultat');
        $worksheetTests->getCell('G' . $ligne)->setValue('Précisions sur le résultat');

        foreach ($tests as $test) {
            $ligne++;
            if ($ligne > 1) {
                $worksheetTests->getCell('A' . $ligne)->setValue($test->getOrdre());
                $worksheetTests->getCell('B' . $ligne)->setValue(($test->getNom() === null ? $test->getTemplate()->getNom() : $test->getNom()));
                $worksheetTests->getCell('C' . $ligne)->setValue(($test->getDescription() === null ? $test->getTemplate()->getDescription() : $test->getDescription()));
                $worksheetTests->getCell('D' . $ligne)->setValue(($test->getTemplate() === null ? 'Aucun parent' : $test->getTemplate()->getNom()));
                $worksheetTests->getCell('E' . $ligne)->setValue(($test->getTemplate() === null ? 'Aucun parent' : $test->getTemplate()->getDescription()));
                $worksheetTests->getCell('F' . $ligne)->setValue($test->getEtat()->getLibelle());
                $worksheetTests->getCell('G' . $ligne)->setValue(($test->getPrecisionsResultat() === null ? '' : $test->getPrecisionsResultat()));
            }
        }

        $worksheetTests->getColumnDimension('A')->setAutoSize(true);
        $worksheetTests->getColumnDimension('B')->setAutoSize(true);
        $worksheetTests->getColumnDimension('C')->setAutoSize(true);
        $worksheetTests->getColumnDimension('D')->setAutoSize(true);
        $worksheetTests->getColumnDimension('E')->setAutoSize(true);
        $worksheetTests->getColumnDimension('F')->setAutoSize(true);
        $worksheetTests->getColumnDimension('G')->setAutoSize(true);

        // Create a new worksheet called "My Data"
        $worksheetResultats = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Résultats de la campagne');

        // Attach the "My Data" worksheet as the first worksheet in the Spreadsheet object
        $spreadsheet->addSheet($worksheetResultats, 1);

        $worksheetResultats->getCell('A1')->setValue('Nombre total de tests');
        $worksheetResultats->getCell('B1')->setValue('Nombre de tests OK');
        $worksheetResultats->getCell('C1')->setValue('Nombre de tests KO');
        $worksheetResultats->getCell('D1')->setValue('Nombre de tests non testés');
        $worksheetResultats->getCell('E1')->setValue('Pourcentage de tests OK');
        $worksheetResultats->getCell('F1')->setValue('Pourcentage de tests KO');
        $worksheetResultats->getCell('G1')->setValue('Pourcentage de tests non testés');
        $worksheetResultats->getCell('H1')->setValue('Temps d\'exécution de la campagne');

        $worksheetResultats->getCell('A2')->setValue($campagne->getData()['total']);
        $worksheetResultats->getCell('B2')->setValue($campagne->getData()['OK']);
        $worksheetResultats->getCell('C2')->setValue($campagne->getData()['KO']);
        $worksheetResultats->getCell('D2')->setValue($campagne->getData()['nonTeste']);
        $worksheetResultats->getCell('E2')->setValue($campagne->getData()['pctReussite']);
        $worksheetResultats->getCell('F2')->setValue($campagne->getData()['pctRatage']);
        $worksheetResultats->getCell('G2')->setValue($campagne->getData()['pctNonTeste']);
        $worksheetResultats->getCell('H2')->setValue($campagne->getData()['temps']);

        $worksheetResultats->getColumnDimension('A')->setAutoSize(true);
        $worksheetResultats->getColumnDimension('B')->setAutoSize(true);
        $worksheetResultats->getColumnDimension('C')->setAutoSize(true);
        $worksheetResultats->getColumnDimension('D')->setAutoSize(true);
        $worksheetResultats->getColumnDimension('E')->setAutoSize(true);
        $worksheetResultats->getColumnDimension('F')->setAutoSize(true);
        $worksheetResultats->getColumnDimension('G')->setAutoSize(true);
        $worksheetResultats->getColumnDimension('H')->setAutoSize(true);

        return $spreadsheet;

    }

    /**
     * @param $tests
     * @return Spreadsheet
     * @throws Exception
     *
     * Permet de remplir une Spreadheet avec uniquement les tests KO de la campagne
     */
    public function makeCampagneKOCsv($tests) : Spreadsheet {

        $spreadsheet = new Spreadsheet();

        $worksheetTests = $spreadsheet->getActiveSheet();
        $worksheetTests->setTitle("Tests de la campagne");

        $ligne = 1;

        $worksheetTests->getCell('A' . $ligne)->setValue('Ordre');
        $worksheetTests->getCell('B' . $ligne)->setValue('Nom');
        $worksheetTests->getCell('C' . $ligne)->setValue('Description');
        $worksheetTests->getCell('D' . $ligne)->setValue('Résultat');
        $worksheetTests->getCell('E' . $ligne)->setValue('Précisions sur le résultat');

        foreach ($tests as $test) {
            if ($test->getEtat()->getIsKO()){
                $ligne++;
                if ($ligne > 1) {
                    $worksheetTests->getCell('A' . $ligne)->setValue($test->getOrdre());
                    $worksheetTests->getCell('B' . $ligne)->setValue(($test->getNom() === null ? $test->getTemplate()->getNom() : ($test->getTemplate() === null ? $test->getNom() : $test->getNom() . ' / ' . $test->getTemplate()->getNom())));
                    $worksheetTests->getCell('C' . $ligne)->setValue(($test->getDescription() === null ? $test->getTemplate()->getDescription() : ($test->getTemplate() === null ? $test->getDescription() : $test->getDescription() . ' / ' . $test->getTemplate()->getDescription())));
                    $worksheetTests->getCell('D' . $ligne)->setValue($test->getEtat()->getLibelle());
                    $worksheetTests->getCell('E' . $ligne)->setValue(($test->getPrecisionsResultat() === null ? '' : $test->getPrecisionsResultat()));
                }
            }
        }

        $worksheetTests->getColumnDimension('A')->setAutoSize(true);
        $worksheetTests->getColumnDimension('B')->setAutoSize(true);
        $worksheetTests->getColumnDimension('C')->setAutoSize(true);
        $worksheetTests->getColumnDimension('D')->setAutoSize(true);
        $worksheetTests->getColumnDimension('E')->setAutoSize(true);

        return $spreadsheet;
    }

    /**
     * @param $spreadsheet
     * @param $entityManager
     * @param $templateTestRepository
     * @param $projet
     *
     * Cette fonction permet d'importer des tests depuis une feuille Excel ou Csv
     */
    public function importTemplateTest($spreadsheet,$entityManager, $templateTestRepository, $projet) {

    $worksheet = $spreadsheet->getActiveSheet();
    $numberOfRows = $worksheet->getCellCollection()->getHighestRow();

    $position = $templateTestRepository->createPositionOfTest($projet->getId());
    if($position === null){
    $newMaxPosition = 1;
    } else {
        $newMaxPosition= $position['p']+1;
    }

    //On commence la récupération des donnée à partir de la 2ème ligne du fichier Excel (sur la 1ère Nom & description)


            for($i = 2 ; $i <= $numberOfRows; $i++){
                $valueNom =  $worksheet->getCellByColumnAndRow(1,$i)->getValue();
                /*if(!$valueNom && $i==2){
                    //$this->session->getFlashBag()->add("danger", "Le fichier est vide ou mal remplie");
                    $this->addFlash("danger", "Le fichier est vide ou mal remplie");
                }
                if(!$valueNom){
                    break;
                }*/
                $valueDescription =  $worksheet->getCellByColumnAndRow(2,$i)->getValue();
                if($valueNom!==null && $valueDescription!==null){
                    $template = new TemplateTest();
                    $template->setNom($valueNom);
                    $template->setDescription($valueDescription);
                    $template->setVersion(1);
                    $template->setDateCreation(new \DateTime('now'));
                    $template->setProjet($projet);

                    $template->setPosition($newMaxPosition);
                    $newMaxPosition ++;

                    $entityManager->persist($template);
                }

            }

    $entityManager->flush();

    }

    /**
     * @param Projet $projet
     * @param TestRepository $testRepository
     * @return Spreadsheet
     * @throws Exception
     * Cette fonction permet de créer un fichier de sauvegarde de tout un projet
     */
    public function makeProjetCsv(Projet $projet, TestRepository $testRepository) : Spreadsheet {

        $spreadsheet = new Spreadsheet();

        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle("projetExport");

        $ligne = 1;

        // HEADERS DES COLONNES
        $worksheet->getCell('A' . $ligne)->setValue('Nom');
        $worksheet->getCell('B' . $ligne)->setValue('Description');
        $worksheet->getCell('C' . $ligne)->setValue('Catalogues');
        $worksheet->getCell('D' . $ligne)->setValue('Cas de Test Parent');
        $worksheet->getCell('E' . $ligne)->setValue('Nom Test');
        $worksheet->getCell('F' . $ligne)->setValue('Description Test');
        $worksheet->getCell('G' . $ligne)->setValue('Etat Test');
        $worksheet->getCell('H' . $ligne)->setValue('Précisions');
        $worksheet->getCell('I' . $ligne)->setValue('Campagne');
        $worksheet->getCell('J' . $ligne)->setValue('Nom Catalogue');
        $worksheet->getCell('K' . $ligne)->setValue('Nom Campagne');
        $worksheet->getCell('L' . $ligne)->setValue('Description Campagne');
        $worksheet->getCell('M' . $ligne)->setValue('Date début estimée Campagne');
        $worksheet->getCell('N' . $ligne)->setValue('Date fin estimée Campagne');
        $worksheet->getCell('O' . $ligne)->setValue('Date début réelle Campagne');
        $worksheet->getCell('P' . $ligne)->setValue('Date fin réelle Campagne');
        $worksheet->getCell('Q' . $ligne)->setValue('Etat Campagne');
        $worksheet->getCell('R' . $ligne)->setValue('Data Campagne');
        $worksheet->getCell('S' . $ligne)->setValue('Date Creation Template');


        // INSERTION DES CAS DE TEST DU PROJET
        $casTest = $projet->getTemplates();

        foreach ($casTest as $i => $test) {

            $ligneTests = $i + 2;

            $worksheet->getCell('A' . $ligneTests)->setValue($test->getNom());
            $worksheet->getCell('B' . $ligneTests)->setValue($test->getDescription());
            $worksheet->getCell('S' . $ligneTests)->setValue(date_format($test->getDateCreation(), 'd/m/Y H:i'));

        }

        // INSERTION DES CATALOGUES DU PROJET
        $catalogues = $projet->getCatalogues();

        foreach ($catalogues as $i => $catalogue) {
            $ligneCat = $i + 2;
            $worksheet->getCell('J' . $ligneCat)->setValue($catalogue->getLibelle());

            $relations = $catalogue->getTemplateCatalogueRelation();
            $catalogues = '';

            foreach ($relations as $z => $relation) {

                if ($z === 0) {
                    $catalogues = $relation->getTemplate()->getNom().'~~~~'.$relation->getTemplate()->getDescription() . '////';
                }
                else {
                    $catalogues = $catalogues . $relation->getTemplate()->getNom().'~~~~'.$relation->getTemplate()->getDescription(). '////';
                }
            }

            $worksheet->getCell('C' . $ligneCat)->setValue($catalogues);
        }

        // INSERTION DES CAMPAGNES DU PROJET
        $campagnes = $projet->getCampagnes();

        foreach ($campagnes as $i => $campagne) {
            $ligneCamp = $i + 2;

            $worksheet->getCell('K' . $ligneCamp)->setValue($campagne->getNom());
            $worksheet->getCell('L' . $ligneCamp)->setValue($campagne->getDescription());

            if ($campagne->getDateDebutEstimee() !== null) {
                $dateDebutEstimee = date_format($campagne->getDateDebutEstimee(), 'd/m/Y H:i');
            }
            else {
                $dateDebutEstimee = null;
            }

            $worksheet->getCell('M' . $ligneCamp)->setValue($dateDebutEstimee);

            if ($campagne->getDateFinEstimee() !== null) {
                $dateFinEstimee = date_format($campagne->getDateFinEstimee(), 'd/m/Y H:i');
            }
            else {
                $dateFinEstimee = null;
            }

            $worksheet->getCell('N' . $ligneCamp)->setValue($dateFinEstimee);

            if ($campagne->getDateDebutReelle() !== null) {
                $dateDebutReelle = date_format($campagne->getDateDebutReelle(), 'd/m/Y H:i');
            }
            else {
                $dateDebutReelle = null;
            }

            $worksheet->getCell('O' . $ligneCamp)->setValue($dateDebutReelle);

            if ($campagne->getDateFinReelle() !== null) {
                $dateFinReelle = date_format($campagne->getDateFinReelle(), 'd/m/Y H:i');
            }
            else {
                $dateFinReelle = null;
            }

            $worksheet->getCell('P' . $ligneCamp)->setValue($dateFinReelle);

            if ($campagne->getData() !== null) {
                $worksheet->getCell('R' . $ligneCamp)->setValue(implode('////', $campagne->getData()));
            }


            $worksheet->getCell('Q' . $ligneCamp)->setValue($campagne->getEtat()->getIsEnPrep() ? 'PREP' : ($campagne->getEtat()->getIsEnCours() ? 'COUR' : 'CLOT'));

        }

        //INSERTION DES TESTS DES CAMPAGNES
        $ligneTestsDansCamp = 1;
        foreach ($campagnes as $campagne) {
            // INSERTION DES TESTS DE LA CAMPAGNE
            $testsCamp = $testRepository->findByCampagneWithJoins($campagne);

            foreach ($testsCamp as $testC) {
                $ligneTestsDansCamp++;
                $worksheet->getCell('D' . $ligneTestsDansCamp)->setValue($testC->getTemplate()->getNom().'////'. $testC->getTemplate()->getDescription());
                $worksheet->getCell('E' . $ligneTestsDansCamp)->setValue($testC->getNom());
                $worksheet->getCell('F' . $ligneTestsDansCamp)->setValue($testC->getDescription());
                $worksheet->getCell('G' . $ligneTestsDansCamp)->setValue($testC->getEtat()->getIsOK() ? 'OK' : ($testC->getEtat()->getIsKO() ? 'KO' : 'NT'));
                $worksheet->getCell('H' . $ligneTestsDansCamp)->setValue($testC->getPrecisionsResultat());
                $worksheet->getCell('I' . $ligneTestsDansCamp)->setValue($campagne->getNom());
            }
        }

        return $spreadsheet;
    }

    /**
     * @param $rowsData
     * @param Projet $projet
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param CatalogueRepository $catalogueRepository
     * @param TemplateTestRepository $templateTestRepository
     * @param CampagneRepository $campagneRepository
     * @param EtatsCampagneService $etatsCampagneService
     * @param EtatsTestsService $etatsTestsService
     *
     * Cette fonction permet d'importer un projet grâce à un fichier de sauvegarde
     */
    public function importProjet(
        $rowsData,
        Projet $projet,
        EntityManagerInterface      $entityManager,
        ValidatorInterface          $validator,
        CatalogueRepository         $catalogueRepository,
        TemplateTestRepository      $templateTestRepository,
        CampagneRepository          $campagneRepository,
        EtatsCampagneService        $etatsCampagneService,
        EtatsTestsService           $etatsTestsService
    )
    {

        $this->viderProjet($projet, $entityManager);

        $this->insertCatalogues($rowsData, $projet, $entityManager, $validator);

        $this->insertCasDeTest($rowsData, $projet, $entityManager, $validator);

        $this->insertCasDeTestDansCatalogue($rowsData, $entityManager, $validator, $catalogueRepository, $templateTestRepository, $projet);

        $this->insertCampagne($rowsData, $projet, $entityManager, $validator, $etatsCampagneService);

        $this->insertTestDansCampagne($rowsData, $projet, $entityManager, $validator, $etatsTestsService, $templateTestRepository, $etatsCampagneService, $campagneRepository);

    }

    /**
     * @param Projet $projet
     * @param EntityManagerInterface $entityManager
     *
     * Cette fonction permet de vider complètement un projet.
     */
    private function viderProjet(Projet $projet, EntityManagerInterface $entityManager) {
        //on vide le projet
        $campagnes = $projet->getCampagnes();

        foreach ($campagnes as $campagne) {
            $entityManager->remove($campagne);
        }

        $catalogues = $projet->getCatalogues();

        foreach ($catalogues as $catalogue) {
            $relations = $catalogue->getTemplateCatalogueRelation();
            foreach ($relations as $relation) {
                $entityManager->remove($relation);
            }
            $entityManager->remove($catalogue);
        }

        $casDeTests = $projet->getTemplates();

        foreach ($casDeTests as $casDeTest) {
            $entityManager->remove($casDeTest);
        }

        $entityManager->flush();
    }

    /**
     * @param $rowsData
     * @param Projet $projet
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     *
     * Cette fonction récupère les catalogues depuis le fichier de sauvegarde et les insère en bdd
     */
    private function insertCatalogues($rowsData, Projet $projet, EntityManagerInterface $entityManager, ValidatorInterface $validator) {

        // on itère sur les catalogues
        foreach ($rowsData as $rowData) {

            //nouveay catalogue
            $catalogue = new Catalogue();

            if ($rowData['Nom Catalogue']) {
                $catalogue->setProjet($projet);
                $catalogue->setLibelle($rowData['Nom Catalogue']);
            }
            else {
                break;
            }

            $constraintViolations = $validator->validate($catalogue);

            if ($constraintViolations->count() === 0) {
                $entityManager->persist($catalogue);
            }

        }

        $entityManager->flush();
    }

    /**
     * @param $rowsData
     * @param Projet $projet
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     *
     * Cette fonction récupère les cas de tests/templates depuis un fichier de sauvegarde et les sauvegarde en bdd
     */
    private function insertCasDeTest($rowsData, Projet $projet, EntityManagerInterface $entityManager, ValidatorInterface $validator) {
        // tests

        $position = 0;

        foreach ($rowsData as $rowData) {

            //nouveay catalogue
            $template = new TemplateTest();

            if ($rowData['Nom'] && $rowData['Description'] && $rowData['Date Creation Template']) {
                $template->setProjet($projet);
                $template->setNom($rowData['Nom']);
                $template->setDescription($rowData['Description']);
                $template->setDateCreation(date_create_from_format('d/m/Y H:i', $rowData['Date Creation Template']));
            } else {
                break;
            }

            $constraintViolations = $validator->validate($template);

            if ($constraintViolations->count() === 0) {
                $position++;
                $template->setPosition($position);
                $template->setVersion(1);
                $entityManager->persist($template);
            }

        }

        $entityManager->flush();
    }

    /**
     * @param $rowsData
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param CatalogueRepository $catalogueRepository
     * @param TemplateTestRepository $templateTestRepository
     * @param Projet $projet
     *
     * Cette fonction récupère les cas de tests dans leurs catalogues depuis un fichier de sauvegarde afin de réinsérer les cas de tests/templates dans un catalogue.
     */
    private function insertCasDeTestDansCatalogue($rowsData, EntityManagerInterface $entityManager, ValidatorInterface $validator, CatalogueRepository $catalogueRepository, TemplateTestRepository $templateTestRepository, Projet $projet)
    {

        // tests dans les catalogues

        $ordre = 0;

        foreach ($rowsData as $rowData) {

            if ($rowData['Nom Catalogue']) {

                if ($rowData['Catalogues']) {

                    $catalogue = $catalogueRepository->findOneBy(['libelle' => $rowData['Nom Catalogue'], 'projet' => $projet]);

                    if ($catalogue) {
                        $arrTemp = explode('////', $rowData['Catalogues']);


                        foreach ($arrTemp as $infosTemplate) {
                            $arrInfos = explode('~~~~', $infosTemplate);
                            $relation = new TemplateCatalogueRelation();

                            if (count($arrInfos) === 2) {
                                $template = $templateTestRepository->findOneBy(['nom' => $arrInfos[0], 'description' => $arrInfos[1], 'projet' => $projet]);

                                if ($template) {
                                    $relation->setTemplate($template);
                                    $relation->setCatalogue($catalogue);
                                    $template->addTemplateCatalogueRelation($relation);
                                    $catalogue->addTemplateCatalogueRelation($relation);
                                }

                                $constraintViolations = $validator->validate($relation);

                                if ($constraintViolations->count() === 0) {
                                    $ordre++;
                                    $relation->setOrdre($ordre);
                                    $entityManager->persist($relation);
                                    $entityManager->persist($template);
                                    $entityManager->persist($catalogue);
                                }
                            }
                        }
                    }
                }
            }
            else
            {
                break;
            }

        }

        $entityManager->flush();

    }

    /**
     * @param $rowsData
     * @param Projet $projet
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param EtatsCampagneService $etatsCampagneService
     *
     * Cette fonction récupère les infos des campagnes depuis le fichier de sauvegarde et les insère en bdd
     */
    private function insertCampagne($rowsData, Projet $projet, EntityManagerInterface $entityManager, ValidatorInterface $validator, EtatsCampagneService $etatsCampagneService) {

        // campagnes

        foreach ($rowsData as $rowData) {

            $campagne = new Campagne();

            if ($rowData['Nom Campagne'] && $rowData['Etat Campagne']) {
                $campagne->setProjet($projet);
                $campagne->setNom($rowData['Nom Campagne']);
                $etat = null;
                if ($rowData['Etat Campagne'] === 'PREP') {
                    $etat = $etatsCampagneService->getEtatEnPrep($projet);
                } elseif ($rowData['Etat Campagne'] === 'COUR') {
                    $etat = $etatsCampagneService->getEtatEnCours($projet);
                } elseif ($rowData['Etat Campagne'] === 'CLOT') {
                    $etat = $etatsCampagneService->getEtatCloturee($projet);
                }
                $campagne->setEtat($etat);

                if ($rowData['Description Campagne']) {
                    $campagne->setDescription($rowData['Description Campagne']);
                }

                if ($rowData['Date début estimée Campagne']) {
                    $campagne->setDateDebutEstimee(date_create_from_format('d/m/Y H:i', $rowData['Date début estimée Campagne']));
                }

                if ($rowData['Date fin estimée Campagne']) {
                    $campagne->setDateFinEstimee(date_create_from_format('d/m/Y H:i', $rowData['Date fin estimée Campagne']));
                }

                if ($rowData['Date début réelle Campagne']) {
                    $campagne->setDateDebutReelle(date_create_from_format('d/m/Y H:i', $rowData['Date début réelle Campagne']));
                }

                if ($rowData['Date fin réelle Campagne']) {
                    $campagne->setDateFinReelle(date_create_from_format('d/m/Y H:i', $rowData['Date fin réelle Campagne']));
                }

                if ($rowData['Data Campagne']) {
                    $data = explode('////', $rowData['Data Campagne']);
                    $campagne->setData([
                        'OK' => $data[0],
                        'KO' => $data[1],
                        'nonTeste' => $data[2],
                        'total' => $data[3],
                        'temps' => $data[4],
                        'pctReussite' => $data[5],
                        'pctRatage' => $data[6],
                        'pctNonTeste' => $data[7]
                    ]);

                }
            }
            else
            {
                break;
            }

            $constraintViolations = $validator->validate($campagne);

            if ($constraintViolations->count() === 0) {
                $entityManager->persist($campagne);
            }

        }

        $entityManager->flush();
    }

    /**
     * @param $rowsData
     * @param Projet $projet
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param EtatsTestsService $etatsTestsService
     * @param TemplateTestRepository $templateTestRepository
     * @param EtatsCampagneService $etatsCampagneService
     * @param CampagneRepository $campagneRepository
     *
     * Cette fonction permet d'insérer les tests dans les campagnes depuis un fichier de sauvegarde
     */
    private function insertTestDansCampagne($rowsData, Projet $projet, EntityManagerInterface $entityManager, ValidatorInterface $validator, EtatsTestsService $etatsTestsService,TemplateTestRepository $templateTestRepository, EtatsCampagneService $etatsCampagneService, CampagneRepository $campagneRepository) {
        // tests dans les campagnes

        $ordre = 0;

        foreach ($rowsData as $rowData) {

            $test = new Test();

            if ($rowData['Etat Test'] === 'OK') {
                $test->setEtat($etatsTestsService->getEtatOK($projet));
            }
            else if ($rowData['Etat Test'] === 'KO') {
                $test->setEtat($etatsTestsService->getEtatKO($projet));
            }
            else if ($rowData['Etat Test'] === 'NT') {
                $test->setEtat($etatsTestsService->getEtatATester($projet));
            }


            if (($rowData['Cas de Test Parent'] || ($rowData['Nom Test'] && $rowData['Description Test'])) && $rowData['Etat Test'] && $rowData['Campagne']) {

                if ($rowData['Cas de Test Parent'] && $rowData['Nom Test'] && $rowData['Description Test']) {
                    $parentArr = explode('////', $rowData['Cas de Test Parent']);
                    $testParent = $templateTestRepository->findOneBy(['nom' => $parentArr[0], 'description' => $parentArr[1]]);
                    $test->setTemplate($testParent);
                    $test->setNom($rowData['Nom Test']);
                    $test->setDescription($rowData['Description Test']);
                }
                elseif ($rowData['Cas de Test Parent'] && (!$rowData['Nom Test'] && !$rowData['Description Test'])) {
                    $parentArr = explode('////', $rowData['Cas de Test Parent']);
                    $testParent = $templateTestRepository->findOneBy(['nom' => $parentArr[0], 'description' => $parentArr[1]]);
                    $test->setTemplate($testParent);
                }
                elseif (!$rowData['Cas de Test Parent'] && ($rowData['Nom Test'] && $rowData['Description Test'])) {
                    $test->setNom($rowData['Nom Test']);
                    $test->setDescription($rowData['Description Test']);
                }

                if ($rowData['Précisions']) {
                    $test->setPrecisionsResultat($rowData['Précisions']);
                }

                $campagneEntity = $campagneRepository->findOneBy([
                    'projet' => $projet,
                    'nom' => $rowData['Campagne']
                ]);

                if ($campagneEntity !== null) {
                    $test->setCampagne($campagneEntity);

                    $constraintViolations = $validator->validate($test);

                    if ($constraintViolations->count() === 0) {
                        $ordre++;
                        $test->setOrdre($ordre);
                        $entityManager->persist($test);
                    }
                }
            }
            else
            {
                break;
            }
        }

        $entityManager->flush();
    }
}
