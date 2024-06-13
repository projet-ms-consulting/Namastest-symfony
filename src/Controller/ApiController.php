<?php

namespace App\Controller;

use App\Entity\TemplateCatalogueRelation;
use App\Repository\CatalogueRepository;
use App\Repository\DroitRepository;
use App\Repository\ProjetRepository;
use App\Repository\TemplateCatalogueRelationRepository;
use App\Repository\TemplateTestRepository;
use App\Service\AutorisationsProjetService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/api", name="api_")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/projets/{projetId}/templates", name="liste_templates", methods={"GET"})
     */
    public function listeTemplates(
        ProjetRepository           $projetRepository,
        CatalogueRepository        $catalogueRepository,
        TemplateTestRepository     $templateTestRepository,
        DroitRepository            $droitRepository,
        AutorisationsProjetService $autorisationsProjet,
        ?UserInterface             $user,
        Request                    $request,
                                   $projetId): JsonResponse
    {
        $projet = $projetRepository->findOneById($projetId);
        $catalogue = null;

        if ($request->query->has("catalogue")) {
            $catalogueId = $request->query->get("catalogue");
            $catalogue = $catalogueRepository->findOneBy(['id' => $catalogueId, 'projet' => $projet]);
        }

        if ($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'VOIRTEMP']);

            if ($autorisationsProjet->canAccess($projet, $user, $droit)) {

                $templates = $templateTestRepository->findBy(['projet' => $projet]);
                $serializer = $this->container->get('serializer');

                $data = [];

                foreach ($templates as $t) {
                    $templateArray = $serializer->normalize($t, 'json', ['groups' => 'templates_api']);

                    $relation = $t->getTemplateCatalogueRelation();

                    foreach ($relation as $r) {
                        // Si ce template fait parti du catalogue en paramètre, on rajoute l'attribut "checked: true"
                        if ($r->getCatalogue() === $catalogue) $templateArray['checked'] = true;
                    }

                    array_push($data, $templateArray);

                }

                return $this->json($data);
            } else {
                $errorMsg = "Vous ne disposez pas des droits suffisants pour accéder à ces données.";
            }
        } else {
            $errorMsg = "Ce projet n'existe pas.";
        }

        return $this->json(['error' => $errorMsg]);
    }

    /**
     * @Route("/projets/{projetId}/catalogues/{catalogueId}/templates", name="post_templates_catalogue", methods={"POST"})
     */
    public function postTemplatesCatalogue(
        ProjetRepository                    $projetRepository,
        CatalogueRepository                 $catalogueRepository,
        TemplateTestRepository              $templateTestRepository,
        DroitRepository                     $droitRepository,
        AutorisationsProjetService          $autorisationsProjet,
        EntityManagerInterface              $entityManager,
        ?UserInterface                      $user,
        Request                             $request,
        TemplateCatalogueRelationRepository $templateCatalogueRelationRepository,
                                            $projetId, $catalogueId
    ): JsonResponse
    {

        $projet = $projetRepository->findOneById($projetId);

        if ($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'EDITCATA']);

            if ($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $catalogue = $catalogueRepository->findOneBy(['id' => $catalogueId, 'projet' => $projet]);

                if ($catalogue) {

                    $templatesId = $request->request->get('templates');

                    //ajout de tests à un catalogue
                    $relations = $templateCatalogueRelationRepository->findByCatalogue($catalogue);

                    if ($templatesId) {

                        $iteration = 0;
                        $ordre = 0;

                        foreach ($templatesId as $tId) {

                            $template = $templateTestRepository->findOneBy(['id' => $tId, 'projet' => $projet]);

                            if ($template) {

                                if ($iteration === 0) {
                                    $ordre = $templateCatalogueRelationRepository->createPositionOfTest($catalogue->getId());
                                    if ($ordre === null) {
                                        $ordre = 1;
                                    } else {
                                        $ordre = $ordre['p'] + 1;
                                    }
                                }

                                $relation = new TemplateCatalogueRelation();
                                $relation->setCatalogue($catalogue);
                                $relation->setTemplate($template);

                                $isContenu = false;

                                foreach ($relations as $r) {
                                    if ($r->getTemplate() === $template) {
                                        $isContenu = true;
                                    }
                                }

                                if (!$isContenu) {
                                    $ordre++;
                                    $relation->setOrdre($ordre);
                                    $catalogue->addTemplateCatalogueRelation($relation);
                                }

                            }

                            $iteration++;
                        }
                    }

                    $entityManager->persist($catalogue);

                    //Suppression des tests non sélectionnés
                    foreach ($relations as $relation) {

                        if ($templatesId) {

                            if (array_search($relation->getTemplate()->getId(), $templatesId) === false) {

                                $entityManager->remove($relation);
                            }
                        } else {
                            $entityManager->remove($relation);
                        }

                    }

                    $entityManager->flush();

                    //réordonner les tests après la modification (suppression ou ajout)
                    $relations = $templateCatalogueRelationRepository->findBy(['catalogue' => $catalogue], ['ordre' => 'DESC']);

                    $ordre = 0;

                    foreach ($relations as $r) {
                        $ordre++;
                        $r->setOrdre($ordre);
                        $entityManager->persist($r);
                    }

                    $entityManager->flush();

                    return $this->json(['ok' => true]);
                } else {
                    $errorMsg = "Ce catalogue n'existe pas.";
                }
            } else {
                $errorMsg = "Vous ne disposez pas des droits suffisants pour accéder à ces données.";
            }
        } else {
            $errorMsg = "Ce projet n'existe pas.";
        }

        return $this->json(['error' => $errorMsg]);
    }

    /**
     * @Route("/projets/{projetId}/api-template-test", name="api_template_test", methods={"GET"})
     */
    public function templateTestApi(
        ProjetRepository           $projetRepository,
        TemplateTestRepository     $templateTestRepository,
        DroitRepository            $droitRepository,
        int                        $projetId,
        AutorisationsProjetService $autorisationsProjet,
        ?UserInterface             $user): JsonResponse {

        $projet = $projetRepository->findOneById($projetId);

        if($projet) {
            $droit = $droitRepository->findOneBy(['code' => 'VOIRTEMP']);

            if ($autorisationsProjet->canAccess($projet, $user, $droit)) {
                $templates = $templateTestRepository->findTemplateByProject($projet);
                $serializer = $this->container->get('serializer');

                $data = $serializer->normalize($templates, 'json', ['groups' => 'templates_api']);
                return $this->json($data);
            } else {
                $errorMsg = "Vous ne disposez pas des droits suffisants pour accéder à ces données.";
            }
        } else {
            $errorMsg = "Ce projet n'existe pas.";
        }

        return $this->json(['error' => $errorMsg]);
    }
}

