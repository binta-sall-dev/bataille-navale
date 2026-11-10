<?php
require_once __DIR__ . '/Navire.php';

class Colore extends Navire
{
    public function __construct()
    {
        $this->nom        = 'Coloré';
        $this->taille     = 3;
        $this->couleur    = '#f39c12';
        $this->touches    = 0;
        $this->place      = false;
        $this->tampon     = 1;
        $this->positions  = [];
        $this->zoneTampon = [];
    }
}