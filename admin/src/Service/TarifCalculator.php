<?php

namespace App\Service;

use App\Entity\PrestationConfig;

class TarifCalculator
{
    /**
     * Calcule la fourchette de prix pour une prestation.
     *
     * @param array $options Options sélectionnées [{id, label, prix_base, unite}, ...]
     * @return array{min: float, max: float}
     */
    public function calculate(PrestationConfig $prestation, array $options, int $nbPersonnes): array
    {
        $total = 0.0;

        foreach ($options as $option) {
            $prixBase = (float) ($option['prix_base'] ?? 0);
            $unite    = $option['unite'] ?? 'par_personne';

            $total += match ($unite) {
                'par_personne' => $prixBase * $nbPersonnes,
                'forfait'      => $prixBase,
                'au_kg'        => $prixBase * $nbPersonnes,
                'sur_devis'    => 0.0,
                default        => 0.0,
            };
        }

        return [
            'min' => round($total * 0.9, 2),
            'max' => round($total * 1.1, 2),
        ];
    }
}
