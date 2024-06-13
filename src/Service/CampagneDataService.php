<?php

namespace App\Service;

use App\Entity\Campagne;

class CampagneDataService
{

    /**
     * @param Campagne $campagne
     * @return array
     * Cette fonction permet de calculer les statistiques d'une campagne en cours ou clôturée
     */
    public function  getData(Campagne $campagne) : array {

        $tests = $campagne->getTests();

        $reussite = 0;
        $ratage = 0;
        $nonTeste = 0;
        foreach ($tests as $test) {
            if ($test->getEtat()->getIsATester()) {
                $nonTeste++;
            } elseif ($test->getEtat()->getIsOK()) {
                $reussite++;
            } elseif ($test->getEtat()->getIsKO()) {
                $ratage++;
            }
        }

        $total = $tests->count();

        if ($campagne->getEtat()->getIsCloturee()) {
            $temps = ($campagne->getDateFinReelle()->diff($campagne->getDateDebutReelle()))
                ->format('%a jours %H heures %i minutes');
        } elseif ($campagne->getEtat()->getIsEnCours()) {
            $temps = ((new \DateTime('now'))->diff($campagne->getDateDebutReelle()))
                ->format('%a jours %H heures %i minutes');
        }

        if ($reussite !== 0 ) {
            $pourcentageReussite = $reussite / $total * 100;
        } else {
            $pourcentageReussite = 0;
        }

        if ($ratage !== 0 ) {
            $pourcentageRatage = $ratage / $total * 100;
        } else {
            $pourcentageRatage = 0;
        }

        if ($nonTeste !== 0 ) {
            $pourcentageNonTeste =  $nonTeste / $total * 100;
        } else {
            $pourcentageNonTeste = 0;
        }

        $pourcentageReussite = round($pourcentageReussite, 2);
        $pourcentageRatage = round($pourcentageRatage, 2);
        $pourcentageNonTeste = round($pourcentageNonTeste, 2);

        return [
            'OK' => $reussite,
            'KO' => $ratage,
            'nonTeste' => $nonTeste,
            'total' => $total,
            'temps' => $temps,
            'pctReussite' => $pourcentageReussite,
            'pctRatage' => $pourcentageRatage,
            'pctNonTeste' => $pourcentageNonTeste
        ];
    }
}