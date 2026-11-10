<?php

require_once __DIR__ . '/Case.php';

class Mer
{
    const TAILLE = 10;

    public array $grille;

    public function __construct()
    {
        for ($l = 0; $l < self::TAILLE; $l++)
            for ($c = 0; $c < self::TAILLE; $c++)
                $this->grille[$l][$c] = new Cellule($l, $c);
    }

    public function placerNavire($id, $l, $c, $taille, $orientation) {
        $positionsBateau = [];

        // --- ÉTAPE 1 : VÉRIFICATION ---
        for ($i = 0; $i < $taille; $i++) {
            $lig = ($orientation === 'vertical') ? $l + $i : $l;
            $col = ($orientation === 'horizontal') ? $c + $i : $c;

            if ($lig >= self::TAILLE || $col >= self::TAILLE || $lig < 0 || $col < 0) {
                return false;
            }

            if ($this->grille[$lig][$col]->idNavire !== null ||
                $this->grille[$lig][$col]->estZoneTampon === true) {
                return false;
            }

            $positionsBateau[] = [$lig, $col];
        }

        // --- ÉTAPE 2 : REMPLISSAGE ---
        foreach ($positionsBateau as $pos) {
            $ligActuelle = $pos[0];
            $colActuelle = $pos[1];

            // A. Poser le navire — idNavire ET etat NAVIRE
            $this->grille[$ligActuelle][$colActuelle]->idNavire = $id;
            $this->grille[$ligActuelle][$colActuelle]->etat     = Cellule::NAVIRE;

            // B. Créer le contour (3x3 autour de la case actuelle)
            for ($dl = -1; $dl <= 1; $dl++) {
                for ($dc = -1; $dc <= 1; $dc++) {
                    $v_lig = $ligActuelle + $dl;
                    $v_col = $colActuelle + $dc;

                    if (isset($this->grille[$v_lig][$v_col])) {
                        if ($this->grille[$v_lig][$v_col]->idNavire === null) {
                            $this->grille[$v_lig][$v_col]->estZoneTampon = true;
                        }
                    }
                }
            }
        }
        return true;
    }

    public function recevoirTir(int $ligne, int $colonne): string
    {
        $cellule  = $this->grille[$ligne][$colonne];
        $idNavire = $cellule->idNavire;
        $resultat = $cellule->recevoirTir();

        if ($resultat === Cellule::TOUCHE && $this->navireCoule($idNavire)) {
            foreach ($this->grille as $rangee)
                foreach ($rangee as $c)
                    if ($c->idNavire === $idNavire)
                        $c->etat = Cellule::COULE;
            return Cellule::COULE;
        }

        return $resultat;
    }

    public function estPerdu(): bool
    {
        foreach ($this->grille as $rangee)
            foreach ($rangee as $cellule)
                if ($cellule->etat === Cellule::NAVIRE || $cellule->etat === Cellule::TOUCHE)
                    return false;
        return true;
    }

    private function navireCoule(string $id): bool
    {
        foreach ($this->grille as $rangee)
            foreach ($rangee as $cellule)
                if ($cellule->idNavire === $id && $cellule->etat === Cellule::NAVIRE)
                    return false;
        return true;
    }
}