<?php

namespace App\Controller\Admin;

use App\Entity\QuestionGeneraleReponse;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class QuestionGeneraleReponseCrudController extends AbstractContactReponseCrudController
{
    public static function getEntityFqcn(): string
    {
        return QuestionGeneraleReponse::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Réponses aux questions générales')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (QuestionGeneraleReponse $qg) => 'Réponse question générale #' . $qg->getId())
            ;
    }
}
