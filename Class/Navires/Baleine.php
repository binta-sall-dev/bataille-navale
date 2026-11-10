<?php
require_once __DIR__ . '/Navire.php';

class Baleine extends Navire
{
    public function __construct()
    {
        $this->nom        = 'Baleine';
        $this->taille     = 5;
        $this->couleur    = '#2980b9';
        $this->touches    = 0;
        $this->place      = false;
        $this->tampon     = 1;
        $this->positions  = [];
        $this->zoneTampon = [];
    }
}