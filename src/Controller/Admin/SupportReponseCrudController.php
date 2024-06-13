<?php

namespace App\Controller\Admin;

use App\Entity\SupportReponse;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;

class SupportReponseCrudController extends AbstractContactReponseCrudController
{
    public static function getEntityFqcn(): string
    {
        return SupportReponse::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Réponses support technique')
            ->setPageTitle(Crud::PAGE_DETAIL, fn (SupportReponse $support) => 'Réponse support technique #' . $support->getId())
            ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters->add('support');
    }
}
