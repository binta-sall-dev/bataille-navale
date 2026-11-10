<?php
require_once __DIR__ . '/Navire.php';

class Anguille extends Navire
{
    public function __construct()
    {
        $this->nom        = 'Anguille';
        $this->taille     = 4;
        $this->couleur    = '#8e44ad';
        $this->touches    = 0;
        $this->place      = false;
        $this->tampon     = 1;
        $this->positions  = [];
        $this->zoneTampon = [];
    }
}