<?php

namespace App\Twig;

use App\Repository\DevisRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private readonly DevisRepository $devisRepository) {}

    public function getGlobals(): array
    {
        try {
            return ['nouveaux_devis' => $this->devisRepository->countNouveaux()];
        } catch (\Throwable) {
            return ['nouveaux_devis' => 0];
        }
    }
}
