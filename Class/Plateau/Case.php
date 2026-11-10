<?php

class Cellule
{
    const EAU    = 'eau';
    const NAVIRE = 'navire';
    const RATE   = 'raté';
    const TOUCHE = 'touché';
    const COULE  = 'coulé';

    public $estZoneTampon = false;
    public int     $ligne;
    public int     $colonne;
    public string  $etat;
    public ?string $idNavire;

    public function __construct(int $ligne, int $colonne)
    {
        $this->ligne    = $ligne;
        $this->colonne  = $colonne;
        $this->etat     = self::EAU;
        $this->idNavire = null;
    }

    public function estLibre(): bool
    {
        return $this->etat === self::EAU;
    }

    public function recevoirTir(): string
    {
        if ($this->etat === self::NAVIRE) {
            $this->etat = self::TOUCHE;
        } else {
            $this->etat = self::RATE;
        }
        return $this->etat;
    }
}
