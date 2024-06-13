<?php

namespace App\Controller;


use App\Entity\Invitation;
use App\Entity\Projet;
use App\Form\ImportProjetType;
use App\Form\ProjetType;
use App\Repository\CampagneRepository;
use App\Repository\CatalogueRepository;
use App\Repository\DroitRepository;
use App\Repository\InvitationRepository;
use App\Repository\ProjetRepository;
use App\Repository\TemplateTestRepository;
use App\Repository\TestRepository;
use App\Service\AutorisationsProjetService;
use App\Service\EtatsCampagneService;
use App\Service\EtatsTestsService;
use App\Service\FileService;
use App\Service\RoleGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route ("/projets", name="projet_")
 */
class ProjetController extends AbstractController
{
//    Définitions des autorisations d'accès aux routes dans security.yaml
//    Accès à l'utilisateur connecté en autowiring avec UserInterface
//
//    private $gSecurity;
//
//    public function __construct(Security $security) {
//        $this->gSecurity = $security;
//    }

    /**
     * Cette fonction permet de lister les projets ainsi que de créer un projet.
     *
     * @Route("/liste", name="liste")
     */
    public function liste(
        ProjetRepository        $projetRepository,
        InvitationRepository    $invitationRepository,
        UserInterface           $user,
        Request                 $request,
        RoleGeneratorService    $roleGeneratorService,
        EntityManagerInterface  $entityManager,
        EtatsCampagneService    $etatsCampagneService,
        EtatsTestsService       $etatsTestsService
    ): Response
    {
        $projetsAuteur = $projetRepository->findAllByAuteur($user);
        $projetsParticipe = $projetRepository->findAllByParticipant($user);
        $projetsInvite = $invitationRepository->findBy([
            'destinataire' => $user->getUserIdentifier(),
            'etat' => Invitation::EN_ATTENTE,
        ]);

        $projet = new Projet();
        $projet->setAuteur($user);
        $projetForm = $this->createForm(ProjetType::class, $projet);
        $projetForm->handleRequest($request);

        if ($projetForm->isSubmitted() && $projetForm->isValid())
        {

            $projet->setDateCreation(new \DateTime('now'));

            $roleGeneratorService->generate($projet);

            $entityManager->persist($projet);
            $entityManager->flush();

            $etatsCampagneService->initEtats($projet);
            $etatsTestsService->initEtats($projet);

            $this->addFlash('success', 'Le projet a bien été créé. Commencez sa configuration dès maintenant !');

            $projetsAuteur = $projetRepository->findAllByAuteur($user);
        }

        return $this->render('projet/liste.html.twig', [
            'projetsAuteur' => $projetsAuteur,
            'projetsParticipe' => $projetsParticipe,
            'projetsInvite' => $projetsInvite,
            'formulaireProjet' => $projetForm->createView()
        ]);
    }

    /**
     * Cette fonction permet de supprimer un projet.
     *
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(
        EntityManagerInterface $entityManager,
        UserInterface $user,
        ProjetRepository $repository,
        int $id
    )
    {

        $projet = $repository->findOneBy(['id' => $id]);

        if($projet && $projet->getAuteur() === $user || in_array('ROLE_ADMIN', $user->getRoles())) {

            $entityManager->remove($projet);
            $entityManager->flush();

            return $this->json(['ok' => true]);
        } else {
            if(!$projet) {
                $errorMsg = "Ce projet n'existe pas.";
            } else {
                $errorMsg = "Vous ne disposez pas des droits suffisants pour supprimer ce projet.";
            }

            return $this->json(['error' => $errorMsg]);
        }
    }

    /**
     * Cette fonction permet d'afficher le dashboard d'un projet.
     *
     * @Route("/{projetId}/dashboard", name="dashboard")
     */
    public function dashboard(
        ProjetRepository $projetRepository,
        AutorisationsProjetService $autorisationsProjet,
        UserInterface $user,
        $projetId): Response {

        $projet = $projetRepository->findOneById($projetId);

        if($projet) {
            if($autorisationsProjet->canAccess($projet, $user)) {
                return $this->render('projet/dashboard.html.twig', [
                    'projet' => $projet,
                ]);
            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé(e) à accéder à ce projet.");
            }
        } else {
            $this->addFlash('warning', "Le projet auquel vous essayez d'accéder n'existe pas.");
        }

        return $this->redirectToRoute('projet_liste');
    }

//    /**
//     * @Route("/acceder/{id}", name="acceder")
//     */
//    public function acceder(
//        int $id
//    ): Response
//    {
//        return $this->redirectToRoute('templates_test_liste', [
//            'id' => $id
//        ]);
//    }

    /**
     * Cette fonction permet d'exporter tout un projet.
     *
     * @Route("/{projetId}/exporter", name="exporter")
     */
    public function exporter(
        int                         $projetId,
        ProjetRepository            $projetRepository,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $user,
        FileService                 $fileService,
        DroitRepository             $droitRepository,
        TestRepository              $testRepository
    ): Response {

        $projet = $projetRepository->findOneById($projetId);

        if($projet) {


            if($autorisationsProjet->canAccess($projet, $user)) {

                $spreadsheet = null;

                try {
                    $spreadsheet = $fileService->makeProjetCsv($projet, $testRepository);
                } catch (Exception $e) {
                }

                if ($spreadsheet !== null) {
                    $writer = new Csv($spreadsheet);
                    $writer->setDelimiter(';');
                    $writer->setEnclosure('"');
                    $writer->setLineEnding("\r\n");
                    $writer->setUseBOM(true);

                    $fileName = 'sauvegarde_'.date_format(new \DateTime('now'), 'd.m.Y_H.i').'_'.$projet->getNom().'.csv';
                    $temp_file = tempnam(sys_get_temp_dir(), $fileName);
                    $writer->save($temp_file);

                    return $this->file($temp_file, $fileName);
                }
                else {
                    $this->addFlash('danger', "Erreur lors de la création du fichier de sauvegarde.");
                }


            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé(e) à accéder à ce projet.");
            }
        } else {
            $this->addFlash('warning', "Le projet auquel vous essayez d'accéder n'existe pas.");
        }

        return $this->redirectToRoute('projet_liste');
    }

    /**
     * Cette fonction permet d'importer tout un projet.
     *
     * @Route("/{projetId}/importer", name="importer")
     */
    public function importer(
        ProjetRepository            $projetRepository,
        AutorisationsProjetService  $autorisationsProjet,
        UserInterface               $template,
        int                         $projetId,
        FileService                 $fileService,
        Request                     $request,
        EntityManagerInterface      $entityManager,
        ValidatorInterface          $validator,
        CatalogueRepository         $catalogueRepository,
        TemplateTestRepository      $templateTestRepository,
        EtatsCampagneService        $etatsCampagneService,
        EtatsTestsService           $etatsTestsService,
        CampagneRepository          $campagneRepository,
        DroitRepository             $droitRepository
    ): Response {

        $projet = $projetRepository->findOneById($projetId);

        if($projet) {
            if($autorisationsProjet->canAccess($projet, $template)) {

                $form = $this->createForm(ImportProjetType::class);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {

                    $data = file_get_contents($form->get('projetCsv')->getData());

                    $csvEncoder = new CsvEncoder();
                    $rowsData = $csvEncoder->decode($data, 'csv', [
                        CsvEncoder::DELIMITER_KEY => ';'
                    ]);

                    $fileService->importProjet(
                        $rowsData,
                        $projet,
                        $entityManager,
                        $validator,
                        $catalogueRepository,
                        $templateTestRepository,
                        $campagneRepository,
                        $etatsCampagneService,
                        $etatsTestsService
                    );


                    $this->addFlash('success', 'Le projet a bien été restauré.');

                }

                return $this->render('projet/backup.html.twig', [
                    'formUpload' => $form->createView(),
                    'projet' => $projet
                ]);

            } else {
                $this->addFlash('danger', "Vous n'êtes pas autorisé(e) à accéder à ce projet.");
            }
        } else {
            $this->addFlash('warning', "Le projet auquel vous essayez d'accéder n'existe pas.");
        }

        return $this->redirectToRoute('projet_liste');
    }

    /**
     * @Route ("/renommer/{id}", name="renommer")
     */

    public function renommer(Projet $projet, ProjetRepository $projetRepository, EntityManagerInterface $entityManager, Request $request){
        $nomChange = $request->get('input' . $projet->getId());
        if($nomChange !== ''){
            if($projetRepository->findBy(['nom' => $nomChange,'auteur' => $this->getUser()])) {
                if ($projetRepository->find($projet->getId())->getNom()===$nomChange){

                }else{
                    $this->addFlash('danger', 'Un projet de ce nom existe déjà !');
                }

            }
            else {
                $projet->setNom($nomChange);
                $entityManager->persist($projet);
                $entityManager->flush();
            }
        }else{
            $this->addFlash('danger', 'Le nom du projet ne peut pas etre vide.');
        }
        return $this->redirectToRoute('projet_liste');
    }



}
