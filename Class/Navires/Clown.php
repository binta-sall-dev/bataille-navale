<?php
require_once __DIR__ . '/Navire.php';

class Clown extends Navire
{
    public function __construct()
    {
        $this->nom        = 'Clown';
        $this->taille     = 2;
        $this->couleur    = '#e74c3c';
        $this->touches    = 0;
        $this->place      = false;
        $this->tampon     = 1;
        $this->positions  = [];
        $this->zoneTampon = [];
    }
}